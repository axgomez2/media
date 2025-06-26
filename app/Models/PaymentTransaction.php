<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'payment_id',
        'preference_id',
        'collection_id',
        'external_reference',
        'payment_type',
        'payment_method',
        'payment_method_id',
        'status',
        'status_detail',
        'transaction_amount',
        'net_received_amount',
        'total_paid_amount',
        'currency_id',
        'mercadopago_fee',
        'discount_amount',
        'fee_details',
        'payer_data',
        'payment_method_data',
        'installments',
        'installment_amount',
        'date_approved',
        'date_created',
        'date_last_updated',
        'money_release_date',
        'pix_qr_code',
        'pix_qr_code_base64',
        'pix_transaction_id',
        'mercadopago_response',
        'webhook_notifications',
        'last_webhook_received',
        'notes',
    ];

    protected $casts = [
        'fee_details' => 'array',
        'payer_data' => 'array',
        'payment_method_data' => 'array',
        'mercadopago_response' => 'array',
        'webhook_notifications' => 'array',
        'transaction_amount' => 'decimal:2',
        'net_received_amount' => 'decimal:2',
        'total_paid_amount' => 'decimal:2',
        'mercadopago_fee' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'installment_amount' => 'decimal:2',
        'date_approved' => 'datetime',
        'date_created' => 'datetime',
        'date_last_updated' => 'datetime',
        'money_release_date' => 'datetime',
        'last_webhook_received' => 'datetime',
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

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeByPaymentType($query, $type)
    {
        return $query->where('payment_type', $type);
    }

    // Métodos auxiliares
    public function getStatusLabel(): string
    {
        return match($this->status) {
            'pending' => 'Pendente',
            'approved' => 'Aprovado',
            'authorized' => 'Autorizado',
            'in_process' => 'Processando',
            'in_mediation' => 'Em Mediação',
            'rejected' => 'Rejeitado',
            'cancelled' => 'Cancelado',
            'refunded' => 'Reembolsado',
            'charged_back' => 'Chargeback',
            default => 'Status Desconhecido',
        };
    }

    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            'pending' => 'bg-yellow-100 text-yellow-800',
            'approved' => 'bg-green-100 text-green-800',
            'authorized' => 'bg-blue-100 text-blue-800',
            'in_process' => 'bg-orange-100 text-orange-800',
            'in_mediation' => 'bg-purple-100 text-purple-800',
            'rejected' => 'bg-red-100 text-red-800',
            'cancelled' => 'bg-gray-100 text-gray-800',
            'refunded' => 'bg-indigo-100 text-indigo-800',
            'charged_back' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getPaymentTypeLabel(): string
    {
        return match($this->payment_type) {
            'credit_card' => 'Cartão de Crédito',
            'debit_card' => 'Cartão de Débito',
            'ticket' => 'Boleto',
            'bank_transfer' => 'Transferência Bancária',
            'account_money' => 'Dinheiro em Conta',
            'digital_wallet' => 'Carteira Digital',
            default => ucfirst(str_replace('_', ' ', $this->payment_type)),
        };
    }

    public function getPaymentMethodLabel(): string
    {
        return match($this->payment_method) {
            'pix' => 'PIX',
            'visa' => 'Visa',
            'master' => 'Mastercard',
            'amex' => 'American Express',
            'elo' => 'Elo',
            'hipercard' => 'Hipercard',
            'bolbradesco' => 'Boleto Bradesco',
            default => ucfirst($this->payment_method),
        };
    }

    public function getFormattedAmount(): string
    {
        return 'R$ ' . number_format($this->transaction_amount, 2, ',', '.');
    }

    public function getFormattedNetAmount(): string
    {
        if (!$this->net_received_amount) {
            return 'N/A';
        }
        return 'R$ ' . number_format($this->net_received_amount, 2, ',', '.');
    }

    public function getFormattedFee(): string
    {
        if (!$this->mercadopago_fee) {
            return 'R$ 0,00';
        }
        return 'R$ ' . number_format($this->mercadopago_fee, 2, ',', '.');
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function isPix(): bool
    {
        return $this->payment_method === 'pix';
    }

    public function hasPix(): bool
    {
        return !empty($this->pix_qr_code);
    }

    public function getInstallmentsText(): string
    {
        if (!$this->installments || $this->installments <= 1) {
            return 'À vista';
        }

        return "{$this->installments}x de " . number_format($this->installment_amount, 2, ',', '.');
    }
}
