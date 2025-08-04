<?php

namespace App\Logging;

use Illuminate\Support\Facades\Log;

class ClientReportsLogger
{
    /**
     * Log de operações de busca de clientes
     */
    public static function logSearch(array $filters, int $resultCount, ?int $userId = null): void
    {
        Log::channel('client_reports')->info('Busca de clientes realizada', [
            'operation' => 'client_search',
            'filters' => $filters,
            'result_count' => $resultCount,
            'user_id' => $userId ?? auth()->id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * Log de visualização de detalhes do cliente
     */
    public static function logClientView(string $clientId, ?int $userId = null): void
    {
        Log::channel('client_reports')->info('Detalhes do cliente visualizados', [
            'operation' => 'client_view',
            'client_id' => $clientId,
            'user_id' => $userId ?? auth()->id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * Log de alteração de status do cliente
     */
    public static function logStatusChange(string $clientId, string $oldStatus, string $newStatus, ?string $reason = null, ?int $userId = null): void
    {
        Log::channel('client_reports')->warning('Status do cliente alterado', [
            'operation' => 'client_status_change',
            'client_id' => $clientId,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'reason' => $reason,
            'user_id' => $userId ?? auth()->id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * Log de exportação de dados
     */
    public static function logExport(array $filters, int $recordCount, ?int $userId = null): void
    {
        Log::channel('client_reports')->info('Exportação de clientes realizada', [
            'operation' => 'client_export',
            'filters' => $filters,
            'record_count' => $recordCount,
            'user_id' => $userId ?? auth()->id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * Log de erros de validação
     */
    public static function logValidationError(string $operation, array $errors, array $input = [], ?int $userId = null): void
    {
        Log::channel('client_reports')->warning('Erro de validação nos relatórios de clientes', [
            'operation' => $operation,
            'validation_errors' => $errors,
            'input_data' => $input,
            'user_id' => $userId ?? auth()->id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * Log de erros de sistema
     */
    public static function logSystemError(string $operation, \Exception $exception, array $context = [], ?int $userId = null): void
    {
        Log::channel('client_reports')->error('Erro de sistema nos relatórios de clientes', [
            'operation' => $operation,
            'error_message' => $exception->getMessage(),
            'error_code' => $exception->getCode(),
            'error_file' => $exception->getFile(),
            'error_line' => $exception->getLine(),
            'stack_trace' => $exception->getTraceAsString(),
            'context' => $context,
            'user_id' => $userId ?? auth()->id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * Log de tentativas de acesso suspeitas
     */
    public static function logSuspiciousActivity(string $activity, array $details = [], ?int $userId = null): void
    {
        Log::channel('security')->warning('Atividade suspeita nos relatórios de clientes', [
            'activity' => $activity,
            'details' => $details,
            'user_id' => $userId ?? auth()->id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * Log de performance para operações demoradas
     */
    public static function logPerformance(string $operation, float $executionTime, array $metrics = [], ?int $userId = null): void
    {
        $level = $executionTime > 5.0 ? 'warning' : 'info';

        Log::channel('performance')->{$level}('Performance dos relatórios de clientes', [
            'operation' => $operation,
            'execution_time' => $executionTime,
            'metrics' => $metrics,
            'user_id' => $userId ?? auth()->id(),
            'ip_address' => request()->ip(),
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * Log de acesso para auditoria
     */
    public static function logAccess(string $route, array $parameters = [], ?int $userId = null): void
    {
        Log::channel('audit')->info('Acesso aos relatórios de clientes', [
            'route' => $route,
            'parameters' => $parameters,
            'user_id' => $userId ?? auth()->id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'session_id' => session()->getId(),
            'timestamp' => now()->toISOString()
        ]);
    }
}
