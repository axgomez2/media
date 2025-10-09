<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ClientSearchRequest;
use App\Http\Requests\Admin\ClientStatusUpdateRequest;
use App\Logging\ClientReportsLogger;
use App\Models\ClientUser;
use App\Models\Order;
use App\Services\ClientStatisticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

class ClientReportsController extends Controller
{
    protected ClientStatisticsService $statisticsService;

    public function __construct(ClientStatisticsService $statisticsService)
    {
        $this->statisticsService = $statisticsService;
    }
    /**
     * Exibe a listagem de clientes com filtros e busca
     */
    public function index(ClientSearchRequest $request)
    {
        try {
            $startTime = microtime(true);

            // Log de acesso para auditoria
            ClientReportsLogger::logAccess('admin.reports.clients.index', $request->only(['search', 'verified', 'account_status', 'period']));

            $query = ClientUser::with([
                'addresses' => function ($query) {
                    $query->select('id', 'user_id', 'street', 'number', 'city', 'state', 'is_default');
                },
                'orders' => function ($query) {
                    $query->select('id', 'user_id', 'total', 'status', 'created_at')
                          ->whereIn('status', ['delivered', 'payment_approved'])
                          ->latest()
                          ->limit(5); // Only load recent orders for listing
                },
                'defaultAddress' => function ($query) {
                    $query->select('id', 'user_id', 'street', 'number', 'city', 'state', 'zip_code');
                }
            ])
            ->withCount(['orders', 'wishlists', 'cartItems'])
            ->withSum(['orders' => function ($query) {
                $query->whereIn('status', ['delivered', 'payment_approved']);
            }], 'total');

            // Aplicar busca por nome ou email
            if ($request->filled('search')) {
                $search = $request->get('search');
                $query->search($search);
            }

            // Filtro por status de verificação
            if ($request->filled('verified') && $request->get('verified') !== 'all') {
                if ($request->get('verified') === 'verified') {
                    $query->verified();
                } else {
                    $query->unverified();
                }
            }

            // Filtro por status da conta
            if ($request->filled('account_status') && $request->get('account_status') !== 'all') {
                $query->byStatus($request->get('account_status'));
            }

            // Filtro por período de cadastro
            if ($request->filled('period') && $request->get('period') !== 'all') {
                switch ($request->get('period')) {
                    case 'today':
                        $query->whereDate('created_at', today());
                        break;
                    case 'week':
                        $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                        break;
                    case 'month':
                        $query->newThisMonth();
                        break;
                    case 'year':
                        $query->whereYear('created_at', now()->year);
                        break;
                }
            }

            // Filtro por data personalizada
            if ($request->filled('start_date') && $request->filled('end_date')) {
                $query->createdBetween($request->get('start_date'), $request->get('end_date'));
            }

            // Ordenação padrão
            $query->orderBy('created_at', 'desc');

            // Paginação
            $perPage = $request->get('per_page', 50);
            $clients = $query->paginate($perPage)->withQueryString();

            // Estatísticas gerais para os cards (cached)
            $stats = $this->statisticsService->getClientStats();

            // Log da busca realizada
            ClientReportsLogger::logSearch(
                $request->only(['search', 'verified', 'account_status', 'period']),
                $clients->total()
            );

            // Log de performance
            $executionTime = microtime(true) - $startTime;
            ClientReportsLogger::logPerformance('client_listing', $executionTime, [
                'total_clients' => $clients->total(),
                'per_page' => $clients->perPage(),
                'current_page' => $clients->currentPage()
            ]);

            return view('admin.reports.clients.index', compact('clients', 'stats'));

        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Erro de banco de dados na listagem de clientes', [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'user_id' => auth()->id(),
                'filters' => $request->only(['search', 'verified', 'account_status', 'period'])
            ]);

            return back()->with('error', 'Erro ao consultar o banco de dados. Nossa equipe foi notificada.');
        } catch (\Exception $e) {
            Log::error('Erro geral na listagem de clientes', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
                'filters' => $request->only(['search', 'verified', 'account_status', 'period'])
            ]);

            return back()->with('error', 'Erro interno do servidor. Tente novamente em alguns minutos.');
        }
    }

    /**
     * Exibe os detalhes completos de um cliente
     */
    public function show(Request $request, $id)
    {
        try {
            $startTime = microtime(true);

            // Log de acesso
            ClientReportsLogger::logAccess('admin.reports.clients.show', ['client_id' => $id]);

            $client = ClientUser::with([
                'addresses' => function ($query) {
                    $query->select('id', 'user_id', 'street', 'number', 'complement', 'neighborhood', 'city', 'state', 'zip_code', 'is_default');
                },
                'orders' => function ($query) {
                    $query->select('id', 'user_id', 'order_number', 'total', 'status', 'created_at', 'updated_at')
                          ->with(['items' => function ($itemQuery) {
                              $itemQuery->select('id', 'order_id', 'product_id', 'quantity', 'unit_price', 'total_price', 'product_name')
                                       ->with(['product' => function ($productQuery) {
                                           $productQuery->select('id', 'name', 'productable_type', 'productable_id');
                                       }]);
                          }])
                          ->orderBy('created_at', 'desc')
                          ->limit(20);
                },
                'wishlists' => function ($query) {
                    $query->select('id', 'user_id', 'product_id', 'created_at')
                          ->with(['product' => function ($productQuery) {
                              $productQuery->select('id', 'name', 'productable_type', 'productable_id')
                                          ->with(['productable' => function ($productableQuery) {
                                              $productableQuery->select('id', 'title')
                                                              ->with(['artists', 'vinylSec:id,vinyl_master_id,price']);
                                          }]);
                          }])
                          ->limit(10);
                },
                'cart' => function ($query) {
                    $query->select('id', 'user_id', 'created_at', 'updated_at')
                          ->with(['products' => function ($productQuery) {
                              $productQuery->select('products.id', 'name', 'productable_type', 'productable_id')
                                          ->withPivot('quantity', 'created_at')
                                          ->with(['productable' => function ($productableQuery) {
                                              $productableQuery->select('id', 'title')
                                                              ->with(['artists', 'vinylSec:id,vinyl_master_id,price']);
                                          }])
                                          ->limit(10);
                          }]);
                }
            ])
            ->withCount(['orders', 'wishlists'])
            ->findOrFail($id);

            // Estatísticas específicas do cliente
            $clientStats = [
                'total_orders' => $client->orders()->count(),
                'total_spent' => $client->orders()
                    ->whereIn('status', ['delivered', 'payment_approved'])
                    ->sum('total'),
                'average_order_value' => $client->orders()
                    ->whereIn('status', ['delivered', 'payment_approved'])
                    ->avg('total') ?? 0,
                'last_order_date' => $client->orders()->latest()->first()?->created_at,
                'wishlist_items' => $client->wishlists()->count(),
                'cart_items' => $client->cart ? $client->cart->products()->count() : 0,
                'cart_total' => $client->cart_total,
                'has_abandoned_cart' => $client->has_abandoned_cart,
                'registration_days' => $client->created_at->diffInDays(now()),
                'last_activity' => $client->updated_at
            ];

            // Log da visualização
            ClientReportsLogger::logClientView($id);

            // Track recently accessed clients for cache optimization
            $this->trackRecentClientAccess($id);

            // Log de performance
            $executionTime = microtime(true) - $startTime;
            ClientReportsLogger::logPerformance('client_detail_view', $executionTime, [
                'client_id' => $id,
                'orders_count' => $clientStats['total_orders'],
                'wishlist_items' => $clientStats['wishlist_items']
            ]);

            return view('admin.reports.clients.show', compact('client', 'clientStats'));

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('Tentativa de acesso a cliente inexistente', [
                'client_id' => $id,
                'user_id' => auth()->id(),
                'ip' => $request->ip()
            ]);

            return redirect()->route('admin.reports.clients.index')
                ->with('error', 'Cliente não encontrado. Verifique se o ID está correto.');
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Erro de banco de dados ao carregar cliente', [
                'client_id' => $id,
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'user_id' => auth()->id()
            ]);

            return back()->with('error', 'Erro ao consultar dados do cliente. Nossa equipe foi notificada.');
        } catch (\Exception $e) {
            Log::error('Erro geral ao carregar detalhes do cliente', [
                'client_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id()
            ]);

            return back()->with('error', 'Erro interno do servidor. Tente novamente em alguns minutos.');
        }
    }

    /**
     * Exporta a lista de clientes para CSV
     */
    public function export(ClientSearchRequest $request)
    {
        try {
            $startTime = microtime(true);

            // Log de acesso
            ClientReportsLogger::logAccess('admin.reports.clients.export', $request->only(['search', 'verified', 'account_status', 'period']));

            $query = ClientUser::with([
                'defaultAddress' => function ($query) {
                    $query->select('id', 'user_id', 'street', 'number', 'city', 'state', 'zip_code');
                }
            ])
            ->withCount(['orders', 'wishlists', 'cartItems'])
            ->withSum(['orders' => function ($query) {
                $query->whereIn('status', ['delivered', 'payment_approved']);
            }], 'total');

            // Aplicar os mesmos filtros da listagem
            if ($request->filled('search')) {
                $query->search($request->get('search'));
            }

            if ($request->filled('verified') && $request->get('verified') !== 'all') {
                if ($request->get('verified') === 'verified') {
                    $query->verified();
                } else {
                    $query->unverified();
                }
            }

            if ($request->filled('account_status') && $request->get('account_status') !== 'all') {
                $query->byStatus($request->get('account_status'));
            }

            if ($request->filled('period') && $request->get('period') !== 'all') {
                switch ($request->get('period')) {
                    case 'today':
                        $query->whereDate('created_at', today());
                        break;
                    case 'week':
                        $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                        break;
                    case 'month':
                        $query->newThisMonth();
                        break;
                    case 'year':
                        $query->whereYear('created_at', now()->year);
                        break;
                }
            }

            if ($request->filled('start_date') && $request->filled('end_date')) {
                $query->createdBetween($request->get('start_date'), $request->get('end_date'));
            }

            $clients = $query->orderBy('created_at', 'desc')->get();

            // Log da exportação
            ClientReportsLogger::logExport(
                $request->only(['search', 'verified', 'account_status', 'period']),
                $clients->count()
            );

            // Log de performance
            $executionTime = microtime(true) - $startTime;
            ClientReportsLogger::logPerformance('client_export', $executionTime, [
                'record_count' => $clients->count(),
                'filters_applied' => count(array_filter($request->only(['search', 'verified', 'account_status', 'period'])))
            ]);

            // Gerar CSV
            $filename = 'clientes_' . now()->format('Y-m-d_H-i-s') . '.csv';

            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function() use ($clients) {
                $file = fopen('php://output', 'w');

                // BOM para UTF-8
                fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

                // Cabeçalhos do CSV
                fputcsv($file, [
                    'ID',
                    'Nome',
                    'Email',
                    'Email Verificado',
                    'Status da Conta',
                    'Data de Cadastro',
                    'Último Acesso',
                    'Total de Pedidos',
                    'Valor Total Gasto',
                    'Itens na Wishlist',
                    'Itens no Carrinho',
                    'Endereço Principal',
                    'Cidade',
                    'Estado',
                    'CEP'
                ], ';');

                foreach ($clients as $client) {
                    $defaultAddress = $client->defaultAddress;

                    fputcsv($file, [
                        $client->id,
                        $client->name,
                        $client->email,
                        $client->is_verified ? 'Sim' : 'Não',
                        $client->status_label,
                        $client->created_at->format('d/m/Y H:i'),
                        $client->updated_at->format('d/m/Y H:i'),
                        $client->orders_count,
                        number_format($client->total_spent, 2, ',', '.'),
                        $client->wishlists_count,
                        $client->cart_items_count,
                        $defaultAddress ? $defaultAddress->short_address : '',
                        $defaultAddress ? $defaultAddress->city : '',
                        $defaultAddress ? $defaultAddress->state : '',
                        $defaultAddress ? $defaultAddress->formatted_zip_code : ''
                    ], ';');
                }

                fclose($file);
            };

            return Response::stream($callback, 200, $headers);

        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Erro de banco de dados na exportação de clientes', [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'user_id' => auth()->id(),
                'filters' => $request->only(['search', 'verified', 'account_status', 'period'])
            ]);

            return back()->with('error', 'Erro ao consultar dados para exportação. Nossa equipe foi notificada.');
        } catch (\Exception $e) {
            Log::error('Erro geral na exportação de clientes', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
                'filters' => $request->only(['search', 'verified', 'account_status', 'period'])
            ]);

            return back()->with('error', 'Erro ao gerar arquivo de exportação. Tente novamente em alguns minutos.');
        }
    }

    /**
     * Atualiza o status de um cliente (ativar/desativar)
     */
    public function updateStatus(ClientStatusUpdateRequest $request, $id)
    {
        try {
            // Validar se o ID é um UUID válido
            if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $id)) {
                Log::warning('Tentativa de atualizar status com ID inválido', [
                    'invalid_id' => $id,
                    'user_id' => auth()->id(),
                    'ip' => $request->ip()
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'ID do cliente inválido.'
                ], 400);
            }

            $client = ClientUser::findOrFail($id);

            $oldStatus = $client->status;
            $newStatus = $request->get('status');

            // Verificar se houve mudança de status
            if ($oldStatus === $newStatus) {
                return response()->json([
                    'success' => false,
                    'message' => 'O cliente já possui este status.'
                ], 422);
            }

            // Atualizar status
            $client->update([
                'status' => $newStatus,
                'status_updated_at' => now(),
                'status_reason' => $request->get('reason'),
                'status_updated_by' => auth()->id()
            ]);

            // Log de auditoria detalhado usando o logger especializado
            ClientReportsLogger::logStatusChange(
                $client->id,
                $oldStatus,
                $newStatus,
                $request->get('reason')
            );

            // Log adicional no sistema padrão para compatibilidade
            Log::info('Status do cliente alterado', [
                'action' => 'client_status_update',
                'client_id' => $client->id,
                'client_name' => $client->name,
                'client_email' => $client->email,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'reason' => $request->get('reason'),
                'admin_user_id' => auth()->id(),
                'admin_user_email' => auth()->user()->email ?? 'N/A',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'timestamp' => now()->toISOString()
            ]);

            $statusLabel = $newStatus === 'active' ? 'ativado' : 'desativado';

            // Clear client-specific cache after status update
            $client->clearCache();

            return response()->json([
                'success' => true,
                'message' => "Cliente {$statusLabel} com sucesso.",
                'status' => $newStatus,
                'status_label' => $client->status_label,
                'status_color' => $client->status_color,
                'updated_at' => $client->status_updated_at->format('d/m/Y H:i')
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('Tentativa de atualizar status de cliente inexistente', [
                'client_id' => $id,
                'admin_user_id' => auth()->id(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Cliente não encontrado. Verifique se o ID está correto.'
            ], 404);
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Erro de banco de dados ao atualizar status do cliente', [
                'client_id' => $id,
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'admin_user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao salvar alterações no banco de dados. Nossa equipe foi notificada.'
            ], 500);
        } catch (\Exception $e) {
            Log::error('Erro geral ao atualizar status do cliente', [
                'client_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'admin_user_id' => auth()->id(),
                'request_data' => $request->except(['_token'])
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor. Tente novamente em alguns minutos.'
            ], 500);
        }
    }

    /**
     * Get additional client statistics for detailed views
     */
    private function getAdditionalStats(): array
    {
        try {
            return [
                'activity_stats' => $this->statisticsService->getClientActivityStats(),
                'top_clients' => $this->statisticsService->getTopClientsBySpending(5),
                'abandoned_cart_stats' => $this->statisticsService->getAbandonedCartStats()
            ];
        } catch (\Exception $e) {
            Log::error('Erro ao calcular estatísticas adicionais de clientes', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'activity_stats' => [],
                'top_clients' => [],
                'abandoned_cart_stats' => []
            ];
        }
    }

    /**
     * Clear all client-related cache
     */
    public function clearCache(Request $request)
    {
        try {
            // Clear statistics cache
            $this->statisticsService->clearCache();

            // Clear individual client caches (for recently accessed clients)
            $recentClientIds = Cache::get('recent_client_accesses', []);
            foreach ($recentClientIds as $clientId) {
                $cacheKeys = [
                    "client_{$clientId}_total_orders",
                    "client_{$clientId}_total_spent",
                    "client_{$clientId}_cart_items_count",
                    "client_{$clientId}_has_abandoned_cart",
                    "client_{$clientId}_cart_total",
                    "client_{$clientId}_wishlist_items_count"
                ];

                foreach ($cacheKeys as $key) {
                    Cache::forget($key);
                }
            }

            // Clear the recent accesses list
            Cache::forget('recent_client_accesses');

            Log::info('Cache de clientes limpo manualmente', [
                'admin_user_id' => auth()->id(),
                'ip_address' => $request->ip()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Cache limpo com sucesso.'
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao limpar cache de clientes', [
                'error' => $e->getMessage(),
                'admin_user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao limpar cache. Tente novamente.'
            ], 500);
        }
    }

    /**
     * Track recently accessed clients for cache optimization
     */
    private function trackRecentClientAccess(string $clientId): void
    {
        $recentAccesses = Cache::get('recent_client_accesses', []);

        // Add current client to the beginning of the array
        array_unshift($recentAccesses, $clientId);

        // Keep only unique values and limit to 50 most recent
        $recentAccesses = array_unique($recentAccesses);
        $recentAccesses = array_slice($recentAccesses, 0, 50);

        // Store back in cache for 1 hour
        Cache::put('recent_client_accesses', $recentAccesses, 3600);
    }

    /**
     * Envia email de lembrete de carrinho abandonado
     */
    public function sendAbandonedCartEmail(Request $request, $id)
    {
        try {
            $client = ClientUser::with(['cart.products.productable.artists', 'cart.products.productable.vinylSec'])
                ->findOrFail($id);

            // Verifica se o cliente tem carrinho com itens
            if (!$client->cart || $client->cart->products->count() === 0) {
                return back()->with('error', 'Este cliente não possui itens no carrinho.');
            }

            // Verifica se o carrinho foi abandonado (mais de 7 dias sem atualização)
            if ($client->cart->updated_at > now()->subDays(7)) {
                return back()->with('warning', 'O carrinho deste cliente foi atualizado recentemente. Considere aguardar mais alguns dias antes de enviar o lembrete.');
            }

            // Envia o email
            \Mail::to($client->email)->send(new \App\Mail\AbandonedCartReminder($client));

            // Log da ação
            ClientReportsLogger::logAction('abandoned_cart_email_sent', [
                'client_id' => $id,
                'client_email' => $client->email,
                'cart_items_count' => $client->cart->products->count(),
                'cart_total' => $client->cart_total
            ]);

            return back()->with('success', 'Email de carrinho abandonado enviado com sucesso para ' . $client->email);

        } catch (\Exception $e) {
            ClientReportsLogger::logError('send_abandoned_cart_email', [
                'client_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'Erro ao enviar email: ' . $e->getMessage());
        }
    }
}
