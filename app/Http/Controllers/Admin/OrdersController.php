<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Enums\OrderStatus;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use PDF;

class OrdersController extends Controller
{
    protected $melhorEnvioApiUrl = 'https://sandbox.melhorenvio.com.br/api/v2/';
    
    /**
     * Display a listing of orders.
     */
    public function index(Request $request)
    {
        // Obter filtros da query
        $status = $request->input('status');
        $date = $request->input('date');
        $search = $request->input('search');
        
        // Consulta base
        $query = Order::query()->with(['user', 'payment']);
        
        // Aplicar filtros se fornecidos
        if ($status) {
            // Converter string para Enum OrderStatus
            try {
                // Tentar criar um Enum a partir do valor da string
                $statusEnum = OrderStatus::from($status);
                $query->where('status', $statusEnum);
            } catch (\ValueError $e) {
                // Se o valor não for válido, ignorar o filtro
                Log::warning("Valor de status inválido: {$status}");
            }
        }
        
        if ($date) {
            // Filtrar por data (hoje, semana, mês)
            if ($date == 'today') {
                $query->whereDate('created_at', now()->toDateString());
            } elseif ($date == 'week') {
                $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
            } elseif ($date == 'month') {
                $query->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]);
            }
        }
        
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                               ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }
        
        // Ordenar e paginar resultados
        $orders = $query->orderBy('created_at', 'desc')
                       ->paginate(15)
                       ->withQueryString();
        
        // Obter contadores para dashboard usando objetos enum
        $counters = [
            'total' => Order::count(),
            'pending' => Order::where('status', OrderStatus::PENDING)->count(),
            'payment_approved' => Order::where('status', OrderStatus::PAYMENT_APPROVED)->count(),
            'delivered' => Order::where('status', OrderStatus::DELIVERED)->count(),
            'canceled' => Order::where('status', OrderStatus::CANCELED)->count(),
        ];
        
        return view('admin.orders.index', compact('orders', 'counters', 'status', 'date', 'search'));
    }
    
    /**
     * Display the specified order details.
     */
    public function show(Order $order)
    {
        $order->load(['items.vinylMaster.artists', 'user', 'address', 'payment']);
        return view('admin.orders.show', compact('order'));
    }
    
    /**
     * Update order status.
     */
    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:pending,payment_approved,preparing,shipped,delivered,canceled',
        ]);
        
        // Status anterior para comparação
        $oldStatus = $order->status;
        
        // Definindo o status principal como objeto enum
        switch ($validated['status']) {
            case 'pending':
                $order->status = OrderStatus::PENDING;
                break;
            case 'payment_approved':
                $order->status = OrderStatus::PAYMENT_APPROVED;
                // Atualizar o payment_status quando o pagamento for aprovado
                $order->payment_status = 'approved';
                break;
            case 'preparing':
                $order->status = OrderStatus::PREPARING;
                // Verificar se o pagamento está aprovado, caso contrário é inconsistente
                if ($order->payment_status !== 'approved') {
                    $order->payment_status = 'approved'; // Sincronizar
                }
                break;
            case 'shipped':
                $order->status = OrderStatus::SHIPPED;
                // Atualizar shipping_status para refletir que o pedido foi enviado
                $order->shipping_status = 'shipped';
                break;
            case 'delivered':
                $order->status = OrderStatus::DELIVERED;
                // Atualizar shipping_status para refletir a entrega
                $order->shipping_status = 'delivered';
                break;
            case 'canceled':
                $order->status = OrderStatus::CANCELED;
                break;
            default:
                // Se receber um status não reconhecido, volta para página com erro
                return redirect()->back()->with('error', 'Status inválido');
        }
        
        // Validar a consistência entre os status
        $this->validateStatusConsistency($order);
        
        $order->save();
        
        // Mensagem mais detalhada quando houver mudança de status
        if ($oldStatus !== $order->status) {
            $message = "Status do pedido atualizado de '" . self::getStatusLabel($oldStatus) . "' para '" . self::getStatusLabel($order->status) . "'";
            return redirect()->back()->with('success', $message);
        }
        
        return redirect()->back()->with('success', 'Status do pedido atualizado com sucesso!');
    }
    
    /**
     * Update payment method.
     */
    public function updatePaymentMethod(Request $request, Order $order)
    {
        $validated = $request->validate([
            'payment_method' => 'required|string|in:credit_card,pix,boleto',
        ]);
        
        // Atualizar o método de pagamento
        if ($order->payment) {
            $order->payment->payment_method = $validated['payment_method'];
            $order->payment->save();
            return redirect()->back()->with('success', 'Método de pagamento atualizado com sucesso!');
        }
        
        return redirect()->back()->with('error', 'Não foi possível atualizar o método de pagamento.');
    }
    
    /**
     * Generate invoice for the order.
     * 
     * @param Order $order
     * @return \Illuminate\View\View
     */
    public function generateInvoice(Order $order)
    {
        $order->load(['items.vinylMaster.artists', 'user', 'address', 'payment']);
        
        // Renderizar a fatura como HTML para impressão
        return view('admin.orders.invoice', compact('order'));
    }
    
    /**
     * Generate shipping label using Melhor Envio integration.
     */
    public function generateShippingLabel(Order $order)
    {
        // Status elegíveis para geração de etiqueta
        // Somente pedidos pagos, em preparação ou já enviados (para regeneração)
        $statusPermitidos = [
            OrderStatus::PAYMENT_APPROVED->value, 
            OrderStatus::PREPARING->value,
            OrderStatus::SHIPPED->value   // Permitir regeneração mesmo para pedidos já enviados
        ];
        
        // Log completo do pedido para depuração
        \Illuminate\Support\Facades\Log::info('Dados do pedido: ' . json_encode([
            'id' => $order->id,
            'payment_status' => $order->payment_status ?? 'null',
            'shipping_status' => $order->shipping_status ?? 'null',
        ]));
        
        // Garantir que temos uma string para comparação
        $orderStatus = $order->status;
        $orderStatusString = '';
        
        // Converter o status para string de forma segura
        if (is_object($orderStatus)) {
            if ($orderStatus instanceof \App\Enums\OrderStatus) {
                $orderStatusString = $orderStatus->value;
                \Illuminate\Support\Facades\Log::info('Status do pedido (enum): ' . $orderStatusString);
            } else {
                // Tentar obter o valor do objeto de forma segura
                try {
                    if (method_exists($orderStatus, 'value')) {
                        $orderStatusString = $orderStatus->value;
                    } elseif (method_exists($orderStatus, '__toString')) {
                        $orderStatusString = (string) $orderStatus;
                    } else {
                        $orderStatusString = 'unknown';
                    }
                } catch (\Exception $e) {
                    $orderStatusString = 'error_converting';
                    \Illuminate\Support\Facades\Log::error('Erro ao converter status: ' . $e->getMessage());
                }
            }
        } else {
            // Já é um tipo primitivo
            $orderStatusString = (string) $orderStatus;
        }
        
        // Log para depuração
        \Illuminate\Support\Facades\Log::info('Status do pedido convertido: ' . $orderStatusString);
        \Illuminate\Support\Facades\Log::info('Status permitidos: ' . print_r($statusPermitidos, true));
        
        // Verificar se o status é elegível usando uma abordagem mais flexível
        $statusMatched = false;
        
        // Verificar se o status contém 'payment_approved' ou 'approved' de forma case-insensitive
        if (stripos($orderStatusString, 'payment_approved') !== false || 
            stripos($orderStatusString, 'approved') !== false ||
            stripos($orderStatusString, 'preparing') !== false ||
            stripos($orderStatusString, 'shipped') !== false) {
            $statusMatched = true;
        }
        
        // Verificar o payment_status também, se disponível
        if (!$statusMatched && isset($order->payment_status) && 
            (stripos($order->payment_status, 'approved') !== false)) {
            $statusMatched = true;
        }
        
        if (!$statusMatched) {
            $statusLabels = implode(', ', array_map(function($status) {
                return self::getStatusLabel($status);
            }, $statusPermitidos));
            
            \Illuminate\Support\Facades\Log::warning('Status não elegível: ' . $orderStatus . ' não está em ' . implode(', ', $statusPermitidos));
            
            return redirect()->back()->with('error', "Este pedido não está em um status elegível para gerar etiqueta de envio. Status permitidos: {$statusLabels}.");
        }
        
        // Verificar se o pedido tem endereço de entrega
        if (!$order->address) {
            return redirect()->back()->with('error', 'O pedido não possui endereço de entrega.');
        }
        
        try {
            // Preparar os dados para o Melhor Envio
            $packageData = $this->preparePackageData($order);
            
            // Em uma implementação real, você enviaria esses dados para o Melhor Envio
            // e receberia o link da etiqueta ou o PDF
            
            // Chamada para API do Melhor Envio
            $response = $this->callMelhorEnvioApi($packageData);
            
            if ($response['success']) {
                // URL da etiqueta gerada
                $labelUrl = $response['label_url'];
                
                // Verificar se o pedido já tem uma etiqueta gerada
                $isRegeneration = !empty($order->shipping_label_url);
                
                // Salvar a URL da etiqueta no pedido
                $order->shipping_label_url = $labelUrl;
                $order->save();
                
                // Atualizar o status do pedido para enviado
                $order->status = OrderStatus::SHIPPED->value;
                $order->shipping_status = 'label_generated';
                $order->tracking_number = $response['tracking_code']; 
                $order->save();
                
                // Mensagem apropriada para geração ou regeneração
                $message = $isRegeneration ? 'Etiqueta de envio regenerada com sucesso!' : 'Etiqueta de envio gerada com sucesso!';
                
                // Redirecionar de volta com mensagem de sucesso e URL da etiqueta
                return redirect()->back()->with([
                    'success' => $message,
                    'label_url' => $labelUrl
                ]);
            } else {
                // Redirecionar de volta com mensagem de erro
                return redirect()->back()->with('error', 'Erro ao gerar etiqueta: ' . $response['message']);
            }
            
        } catch (\Exception $e) {
            Log::error('Erro ao gerar etiqueta: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao gerar etiqueta: ' . $e->getMessage());
        }
    }
    
    /**
     * Prepare package data for Melhor Envio API
     */
    private function preparePackageData(Order $order)
    {
        // Obter dados do endereço de entrega
        $shippingAddress = $order->address;
        
        // Preparar informações do destinatário
        $recipient = [
            'name' => $order->user->name,
            'phone' => $order->user->phone ?? '(00) 00000-0000',
            'email' => $order->user->email,
            'address' => $shippingAddress->street,
            'number' => $shippingAddress->number,
            'complement' => $shippingAddress->complement,
            'district' => $shippingAddress->neighborhood,
            'city' => $shippingAddress->city,
            'state' => $shippingAddress->state,
            'postal_code' => $shippingAddress->zipcode
        ];
        
        // Calcular peso e dimensões com base nos itens do pedido
        $totalWeight = 0;
        $items = [];
        
        foreach ($order->items as $item) {
            $weight = $item->vinylMaster->weight ?? 0.3; // Peso padrão se não estiver definido
            $totalWeight += $weight * $item->quantity;
            
            $items[] = [
                'name' => $item->name,
                'quantity' => $item->quantity,
                'weight' => $weight
            ];
        }
        
        // Dados da embalagem (valores padrão para discos de vinil)
        $package = [
            'weight' => $totalWeight,
            'width' => 32, // Largura típica de uma embalagem para disco de vinil em cm
            'height' => 32, // Altura típica de uma embalagem para disco de vinil em cm
            'length' => 5 * count($order->items), // Espessura estimada baseada no número de itens
        ];
        
        return [
            'order_id' => $order->id,
            'recipient' => $recipient,
            'package' => $package,
            'items' => $items,
        ];
    }
    
    /**
     * Call Melhor Envio API to generate shipping label
     */
    private function callMelhorEnvioApi($packageData)
    {
        try {
            // Em ambiente de produção, descomentar este código e usar a API real do Melhor Envio
            /*
            // Configurar o token de acesso à API do Melhor Envio
            $token = config('services.melhorenvio.token');
            
            // Etapa 1: Calcular o frete (cotação)
            $quoteResponse = Http::withToken($token)
                ->post($this->melhorEnvioApiUrl . 'me/shipment/calculate', [
                    'from' => [
                        'postal_code' => config('services.melhorenvio.from_postal_code'),
                        'address' => config('services.melhorenvio.from_address'),
                        'number' => config('services.melhorenvio.from_number')
                    ],
                    'to' => [
                        'postal_code' => $packageData['recipient']['postal_code'],
                        'address' => $packageData['recipient']['address'],
                        'number' => $packageData['recipient']['number']
                    ],
                    'package' => $packageData['package'],
                    'options' => [
                        'insurance_value' => $packageData['order_value'] ?? 0,
                        'receipt' => true,
                        'own_hand' => false,
                        'collect' => false,
                    ],
                    'services' => config('services.melhorenvio.service_ids') // IDs dos serviços desejados (ex: [1, 2] para PAC e SEDEX)
                ]);
            
            if (!$quoteResponse->successful()) {
                return [
                    'success' => false,
                    'message' => 'Erro ao calcular frete: ' . $quoteResponse->body()
                ];
            }
            
            $quotes = $quoteResponse->json();
            
            // Selecionar o serviço mais barato ou preferencial
            $selectedService = null;
            $lowestPrice = PHP_FLOAT_MAX;
            
            foreach ($quotes as $quote) {
                if ($quote['price'] < $lowestPrice) {
                    $selectedService = $quote;
                    $lowestPrice = $quote['price'];
                }
            }
            
            if (!$selectedService) {
                return [
                    'success' => false,
                    'message' => 'Nenhum serviço de entrega disponível para este endereço.'
                ];
            }
            
            // Etapa 2: Criar o carrinho com o frete selecionado
            $cartResponse = Http::withToken($token)
                ->post($this->melhorEnvioApiUrl . 'me/cart', [
                    'service' => $selectedService['id'],
                    'agency' => config('services.melhorenvio.agency_id'), // ID da agência dos Correios ou transportadora
                    'from' => [
                        'name' => config('services.melhorenvio.from_name'),
                        'phone' => config('services.melhorenvio.from_phone'),
                        'email' => config('services.melhorenvio.from_email'),
                        'document' => config('services.melhorenvio.from_document'),
                        'company_document' => config('services.melhorenvio.from_company_document'),
                        'state_register' => config('services.melhorenvio.from_state_register'),
                        'postal_code' => config('services.melhorenvio.from_postal_code'),
                        'address' => config('services.melhorenvio.from_address'),
                        'number' => config('services.melhorenvio.from_number'),
                        'district' => config('services.melhorenvio.from_district'),
                        'city' => config('services.melhorenvio.from_city'),
                        'state_abbr' => config('services.melhorenvio.from_state_abbr'),
                        'country_id' => 'BR'
                    ],
                    'to' => [
                        'name' => $packageData['recipient']['name'],
                        'phone' => $packageData['recipient']['phone'],
                        'email' => $packageData['recipient']['email'],
                        'document' => $packageData['recipient']['document'] ?? '',
                        'postal_code' => $packageData['recipient']['postal_code'],
                        'address' => $packageData['recipient']['address'],
                        'number' => $packageData['recipient']['number'],
                        'complement' => $packageData['recipient']['complement'] ?? '',
                        'district' => $packageData['recipient']['district'],
                        'city' => $packageData['recipient']['city'],
                        'state_abbr' => $packageData['recipient']['state'],
                        'country_id' => 'BR'
                    ],
                    'products' => array_map(function($item) {
                        return [
                            'name' => $item['name'],
                            'quantity' => $item['quantity'],
                            'unitary_value' => $item['price'] ?? 0
                        ];
                    }, $packageData['items']),
                    'volumes' => [
                        $packageData['package']
                    ],
                    'options' => [
                        'insurance_value' => $packageData['order_value'] ?? 0,
                        'receipt' => true,
                        'own_hand' => false,
                        'collect' => false,
                        'reverse' => false,
                        'non_commercial' => true,
                        'invoice' => [
                            'key' => $packageData['invoice_key'] ?? null
                        ],
                        'platform' => 'Loja de Discos Online',
                        'tags' => [
                            ['tag' => 'Pedido #' . $packageData['order_id']]
                        ]
                    ]
                ]);
            
            if (!$cartResponse->successful()) {
                return [
                    'success' => false,
                    'message' => 'Erro ao adicionar ao carrinho: ' . $cartResponse->body()
                ];
            }
            
            $cartItem = $cartResponse->json();
            $orderNumber = $cartItem['order_id'] ?? $cartItem['id'];
            
            // Etapa 3: Comprar a etiqueta
            $purchaseResponse = Http::withToken($token)
                ->post($this->melhorEnvioApiUrl . 'me/shipment/checkout', [
                    'orders' => [$orderNumber]
                ]);
            
            if (!$purchaseResponse->successful()) {
                return [
                    'success' => false,
                    'message' => 'Erro ao comprar etiqueta: ' . $purchaseResponse->body()
                ];
            }
            
            // Etapa 4: Gerar a etiqueta
            $labelResponse = Http::withToken($token)
                ->post($this->melhorEnvioApiUrl . 'me/shipment/generate', [
                    'orders' => [$orderNumber]
                ]);
            
            if (!$labelResponse->successful()) {
                return [
                    'success' => false,
                    'message' => 'Erro ao gerar etiqueta: ' . $labelResponse->body()
                ];
            }
            
            // Etapa 5: Obter a URL da etiqueta
            $printResponse = Http::withToken($token)
                ->post($this->melhorEnvioApiUrl . 'me/shipment/print', [
                    'orders' => [$orderNumber]
                ]);
            
            if (!$printResponse->successful()) {
                return [
                    'success' => false,
                    'message' => 'Erro ao obter URL da etiqueta: ' . $printResponse->body()
                ];
            }
            
            $labelUrl = $printResponse->body();
            $trackingCode = $cartItem['tracking'] ?? ('ME' . $orderNumber);
            
            return [
                'success' => true,
                'label_url' => $labelUrl,
                'tracking_code' => $trackingCode,
                'message' => 'Etiqueta gerada com sucesso!'
            ];
            */
            
            // Simulação para ambiente de sandbox do Melhor Envio
            // Em um ambiente real, você usaria o código comentado acima
            $trackingId = 'ME' . date('Ymd') . rand(1000, 9999);
            $labelUrl = 'https://sandbox.melhorenvio.com.br/etiquetas/' . $trackingId . '.pdf';
            
            // Log para debug em ambiente de desenvolvimento
            Log::info('Simulação de etiqueta gerada:', [
                'order_id' => $packageData['order_id'],
                'tracking_code' => $trackingId,
                'label_url' => $labelUrl
            ]);
            
            return [
                'success' => true,
                'label_url' => $labelUrl,
                'tracking_code' => $trackingId,
                'message' => 'Etiqueta gerada com sucesso! (Sandbox)'
            ];
            
        } catch (\Exception $e) {
            Log::error('Erro na chamada à API do Melhor Envio: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro na chamada à API: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Validate and ensure consistency between different status fields
     */
    private function validateStatusConsistency(Order $order)
    {
        // Garantir que payment_status e status geral sejam consistentes
        if (in_array($order->status, [OrderStatus::PAYMENT_APPROVED->value, OrderStatus::PREPARING->value, 
                                     OrderStatus::SHIPPED->value, OrderStatus::DELIVERED->value])) {
            // Todos esses status exigem que o pagamento esteja aprovado
            if ($order->payment_status !== 'approved') {
                $order->payment_status = 'approved';
            }
        }
        
        // Garantir que shipping_status e status geral sejam consistentes
        if ($order->status === OrderStatus::SHIPPED->value && $order->shipping_status !== 'shipped') {
            $order->shipping_status = 'shipped';
        } else if ($order->status === OrderStatus::DELIVERED->value && $order->shipping_status !== 'delivered') {
            $order->shipping_status = 'delivered';
        }
        
        // Se o pedido for cancelado
        if ($order->status === OrderStatus::CANCELED->value) {
            // Se tiver sido enviado, o shipping_status continua como estava
            // Caso contrário, cancelamos também o shipping_status
            if (!in_array($order->shipping_status, ['shipped', 'delivered'])) {
                $order->shipping_status = 'cancelled';
            }
        }
        
        return $order;
    }
    
    /**
     * Get human-readable status label
     * 
     * @param mixed $status
     * @return string
     */
    public static function getStatusLabel($status)
    {
        // Extrair o valor do enum se for um objeto OrderStatus
        if (is_object($status) && $status instanceof OrderStatus) {
            $status = $status->value;
        }
        
        $labels = [
            'pending' => 'Aguardando Pagamento',
            'payment_approved' => 'Pagamento Aprovado',
            'preparing' => 'Em Preparação',
            'shipped' => 'Enviado',
            'delivered' => 'Entregue',
            'canceled' => 'Cancelado'
        ];
        
        return $labels[$status] ?? ucfirst($status);
    }
    

}