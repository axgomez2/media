<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShippingLabel extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'melhor_envio_id',
        'tracking_code',
        'protocol',
        'label_url',
        'label_path',
        'status',
        'service_name',
        'service_id',
        'company_name',
        'declared_value',
        'shipping_cost',
        'package_dimensions',
        'package_weight',
        'origin_address',
        'destination_address',
        'tracking_events',
        'last_tracking_update',
        'melhor_envio_data',
        'notes',
    ];

    protected $casts = [
        'package_dimensions' => 'array',
        'origin_address' => 'array',
        'destination_address' => 'array',
        'tracking_events' => 'array',
        'melhor_envio_data' => 'array',
        'declared_value' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'package_weight' => 'decimal:3',
        'last_tracking_update' => 'datetime',
    ];

    // Relacionamentos
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    // Scopes
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeNeedsTracking($query)
    {
        return $query->whereIn('status', ['generated', 'posted', 'in_transit'])
                    ->where(function ($q) {
                        $q->whereNull('last_tracking_update')
                          ->orWhere('last_tracking_update', '<', now()->subHours(2));
                    });
    }

    // Métodos auxiliares
    public function getStatusLabel(): string
    {
        return match($this->status) {
            'pending' => 'Pendente',
            'generated' => 'Gerada',
            'posted' => 'Postada',
            'in_transit' => 'Em Trânsito',
            'delivered' => 'Entregue',
            'returned' => 'Devolvida',
            'canceled' => 'Cancelada',
            default => 'Status Desconhecido',
        };
    }

    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            'pending' => 'bg-yellow-100 text-yellow-800',
            'generated' => 'bg-blue-100 text-blue-800',
            'posted' => 'bg-purple-100 text-purple-800',
            'in_transit' => 'bg-orange-100 text-orange-800',
            'delivered' => 'bg-green-100 text-green-800',
            'returned' => 'bg-red-100 text-red-800',
            'canceled' => 'bg-gray-100 text-gray-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getFormattedWeight(): string
    {
        return number_format($this->package_weight, 3, ',', '.') . ' kg';
    }

    public function getFormattedValue(): string
    {
        return 'R$ ' . number_format($this->declared_value, 2, ',', '.');
    }

    public function getFormattedCost(): string
    {
        return 'R$ ' . number_format($this->shipping_cost, 2, ',', '.');
    }

    public function hasTrackingCode(): bool
    {
        return !empty($this->tracking_code);
    }

    public function canTrack(): bool
    {
        return $this->hasTrackingCode() && in_array($this->status, ['posted', 'in_transit']);
    }

    public function getLastTrackingEvent(): ?array
    {
        if (!$this->tracking_events || empty($this->tracking_events)) {
            return null;
        }

        return collect($this->tracking_events)->last();
    }

    public function getTrackingUrl(): ?string
    {
        if (!$this->hasTrackingCode()) {
            return null;
        }

        return match($this->company_name) {
            'Correios' => "https://www.correios.com.br/rastreamento/{$this->tracking_code}",
            'Jadlog' => "https://www.jadlog.com.br/tracking/{$this->tracking_code}",
            default => null,
        };
    }
}
