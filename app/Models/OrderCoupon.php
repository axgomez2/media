<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderCoupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'coupon_code',
        'coupon_name',
        'discount_type',
        'discount_value',
        'discount_amount',
        'applies_to',
        'applicable_items',
        'minimum_amount',
        'maximum_discount',
        'coupon_snapshot',
        'is_valid',
        'validation_notes',
    ];

    protected $casts = [
        'applicable_items' => 'array',
        'coupon_snapshot' => 'array',
        'discount_value' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'minimum_amount' => 'decimal:2',
        'maximum_discount' => 'decimal:2',
        'is_valid' => 'boolean',
    ];

    // Relacionamentos
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    // Métodos auxiliares
    public function getDiscountTypeLabel(): string
    {
        return match($this->discount_type) {
            'percentage' => 'Percentual',
            'fixed_amount' => 'Valor Fixo',
            'free_shipping' => 'Frete Grátis',
            default => 'Tipo Desconhecido',
        };
    }

    public function getAppliesToLabel(): string
    {
        return match($this->applies_to) {
            'total' => 'Total do Pedido',
            'subtotal' => 'Subtotal dos Produtos',
            'shipping' => 'Frete',
            'specific_items' => 'Itens Específicos',
            default => 'Aplicação Desconhecida',
        };
    }

    public function getFormattedDiscountValue(): string
    {
        if ($this->discount_type === 'percentage') {
            return number_format($this->discount_value, 1) . '%';
        }

        return 'R$ ' . number_format($this->discount_value, 2, ',', '.');
    }

    public function getFormattedDiscountAmount(): string
    {
        return 'R$ ' . number_format($this->discount_amount, 2, ',', '.');
    }

    public function getFormattedMinimumAmount(): string
    {
        if (!$this->minimum_amount) {
            return 'Sem mínimo';
        }

        return 'R$ ' . number_format($this->minimum_amount, 2, ',', '.');
    }

    public function getFormattedMaximumDiscount(): string
    {
        if (!$this->maximum_discount) {
            return 'Sem limite';
        }

        return 'R$ ' . number_format($this->maximum_discount, 2, ',', '.');
    }

    public function getValidationBadgeClass(): string
    {
        return $this->is_valid
            ? 'bg-green-100 text-green-800'
            : 'bg-red-100 text-red-800';
    }

    public function getValidationLabel(): string
    {
        return $this->is_valid ? 'Válido' : 'Inválido';
    }

    public function isFreeShipping(): bool
    {
        return $this->discount_type === 'free_shipping';
    }

    public function isPercentage(): bool
    {
        return $this->discount_type === 'percentage';
    }

    public function isFixedAmount(): bool
    {
        return $this->discount_type === 'fixed_amount';
    }
}
