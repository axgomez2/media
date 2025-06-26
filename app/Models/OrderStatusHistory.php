<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderStatusHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'status_from',
        'status_to',
        'notes',
        'metadata',
        'created_by',
        'change_type',
        'webhook_source',
        'webhook_data',
    ];

    protected $casts = [
        'metadata' => 'array',
        'webhook_data' => 'array',
    ];

    // Relacionamentos
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(ClientUser::class, 'created_by');
    }

    // Métodos auxiliares
    public function getStatusFromLabel(): string
    {
        if (!$this->status_from) {
            return 'Inicial';
        }

        return match($this->status_from) {
            'pending' => 'Aguardando Pagamento',
            'payment_approved' => 'Pagamento Aprovado',
            'preparing' => 'Preparando Envio',
            'shipped' => 'Enviado',
            'delivered' => 'Entregue',
            'canceled' => 'Cancelado',
            'refunded' => 'Reembolsado',
            default => ucfirst($this->status_from),
        };
    }

    public function getStatusToLabel(): string
    {
        return match($this->status_to) {
            'pending' => 'Aguardando Pagamento',
            'payment_approved' => 'Pagamento Aprovado',
            'preparing' => 'Preparando Envio',
            'shipped' => 'Enviado',
            'delivered' => 'Entregue',
            'canceled' => 'Cancelado',
            'refunded' => 'Reembolsado',
            default => ucfirst($this->status_to),
        };
    }

    public function getChangeTypeLabel(): string
    {
        return match($this->change_type) {
            'manual' => 'Manual',
            'automatic' => 'Automático',
            'webhook' => 'Webhook',
            default => ucfirst($this->change_type),
        };
    }

    public function getChangeTypeIcon(): string
    {
        return match($this->change_type) {
            'manual' => '👤',
            'automatic' => '🤖',
            'webhook' => '🔗',
            default => '📝',
        };
    }

    // Método estático para criar histórico
    public static function createHistory(
        int $orderId,
        ?string $statusFrom,
        string $statusTo,
        ?string $notes = null,
        ?array $metadata = null,
        ?string $createdBy = null,
        string $changeType = 'manual',
        ?string $webhookSource = null,
        ?array $webhookData = null
    ): self {
        return self::create([
            'order_id' => $orderId,
            'status_from' => $statusFrom,
            'status_to' => $statusTo,
            'notes' => $notes,
            'metadata' => $metadata,
            'created_by' => $createdBy,
            'change_type' => $changeType,
            'webhook_source' => $webhookSource,
            'webhook_data' => $webhookData,
        ]);
    }
}
