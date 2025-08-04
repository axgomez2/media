<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ValidateClientReportsAccess
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            // Verificar se o usuário está autenticado
            if (!auth()->check()) {
                Log::warning('Tentativa de acesso não autenticado aos relatórios de clientes', [
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'url' => $request->fullUrl()
                ]);

                return redirect()->route('login')->with('error', 'Você precisa estar logado para acessar esta página.');
            }

            // Verificar se o usuário tem permissão de admin
            if (!auth()->user()->isAdmin()) {
                Log::warning('Tentativa de acesso não autorizado aos relatórios de clientes', [
                    'user_id' => auth()->id(),
                    'user_email' => auth()->user()->email,
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'url' => $request->fullUrl()
                ]);

                abort(403, 'Você não tem permissão para acessar esta página.');
            }

            // Validar parâmetros específicos para diferentes rotas
            $this->validateRouteParameters($request);

            // Log de acesso para auditoria
            Log::info('Acesso aos relatórios de clientes', [
                'user_id' => auth()->id(),
                'user_email' => auth()->user()->email,
                'route' => $request->route()->getName(),
                'parameters' => $request->route()->parameters(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return $next($request);

        } catch (\Exception $e) {
            Log::error('Erro no middleware de validação de acesso aos relatórios de clientes', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
                'ip' => $request->ip(),
                'url' => $request->fullUrl()
            ]);

            return response()->view('errors.500', [], 500);
        }
    }

    /**
     * Validar parâmetros específicos baseados na rota
     */
    private function validateRouteParameters(Request $request): void
    {
        $routeName = $request->route()->getName();
        $parameters = $request->route()->parameters();

        switch ($routeName) {
            case 'admin.reports.clients.show':
            case 'admin.reports.clients.update_status':
                // Validar ID do cliente
                if (isset($parameters['id'])) {
                    $this->validateClientId($parameters['id'], $request);
                }
                break;

            case 'admin.reports.clients.export':
                // Validar parâmetros de exportação
                $this->validateExportParameters($request);
                break;
        }
    }

    /**
     * Validar ID do cliente
     */
    private function validateClientId(string $id, Request $request): void
    {
        // Verificar se é um UUID válido
        if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $id)) {
            Log::warning('ID de cliente inválido fornecido', [
                'invalid_id' => $id,
                'user_id' => auth()->id(),
                'ip' => $request->ip(),
                'route' => $request->route()->getName()
            ]);

            abort(400, 'ID do cliente inválido.');
        }

        // Verificar se o cliente existe (apenas para operações críticas)
        if ($request->route()->getName() === 'admin.reports.clients.update_status') {
            $clientExists = \App\Models\ClientUser::where('id', $id)->exists();

            if (!$clientExists) {
                Log::warning('Tentativa de acesso a cliente inexistente', [
                    'client_id' => $id,
                    'user_id' => auth()->id(),
                    'ip' => $request->ip(),
                    'route' => $request->route()->getName()
                ]);

                abort(404, 'Cliente não encontrado.');
            }
        }
    }

    /**
     * Validar parâmetros de exportação
     */
    private function validateExportParameters(Request $request): void
    {
        $queryParams = $request->query();

        // Verificar se há muitos parâmetros (possível tentativa de ataque)
        if (count($queryParams) > 10) {
            Log::warning('Muitos parâmetros na exportação de clientes', [
                'param_count' => count($queryParams),
                'user_id' => auth()->id(),
                'ip' => $request->ip(),
                'params' => array_keys($queryParams)
            ]);

            abort(400, 'Muitos parâmetros fornecidos.');
        }

        // Verificar tamanho dos valores dos parâmetros
        foreach ($queryParams as $key => $value) {
            if (is_string($value) && strlen($value) > 1000) {
                Log::warning('Parâmetro muito longo na exportação', [
                    'param' => $key,
                    'length' => strlen($value),
                    'user_id' => auth()->id(),
                    'ip' => $request->ip()
                ]);

                abort(400, 'Parâmetro muito longo fornecido.');
            }
        }
    }
}
