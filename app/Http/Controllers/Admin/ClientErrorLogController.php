<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ClientErrorLogController extends Controller
{
    /**
     * Log de erros do lado do cliente
     */
    public function logError(Request $request)
    {
        try {
            $request->validate([
                'type' => 'required|string|max:100',
                'data' => 'required|array',
                'url' => 'required|string|max:500',
                'userAgent' => 'required|string|max:500',
                'timestamp' => 'required|date'
            ]);

            // Sanitizar dados sensíveis
            $data = $this->sanitizeErrorData($request->input('data'));

            Log::error('Erro do cliente JavaScript', [
                'type' => $request->input('type'),
                'data' => $data,
                'url' => $request->input('url'),
                'user_agent' => $request->input('userAgent'),
                'client_timestamp' => $request->input('timestamp'),
                'server_timestamp' => now()->toISOString(),
                'user_id' => auth()->id(),
                'ip_address' => $request->ip(),
                'session_id' => session()->getId()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Erro registrado com sucesso'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Dados inválidos no log de erro do cliente', [
                'errors' => $e->errors(),
                'input' => $request->except(['data']),
                'user_id' => auth()->id(),
                'ip' => $request->ip()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos'
            ], 422);

        } catch (\Exception $e) {
            Log::error('Erro ao processar log de erro do cliente', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
                'ip' => $request->ip()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Sanitizar dados de erro removendo informações sensíveis
     */
    private function sanitizeErrorData(array $data): array
    {
        $sanitized = [];

        foreach ($data as $key => $value) {
            // Remover informações sensíveis
            if (in_array(strtolower($key), ['password', 'token', 'secret', 'key', 'auth'])) {
                $sanitized[$key] = '[REDACTED]';
                continue;
            }

            if (is_string($value)) {
                // Limitar tamanho de strings
                $sanitized[$key] = strlen($value) > 1000 ? substr($value, 0, 1000) . '...' : $value;
            } elseif (is_array($value)) {
                // Recursivamente sanitizar arrays
                $sanitized[$key] = $this->sanitizeErrorData($value);
            } else {
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }
}
