<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_number',
        'status',
        'payment_status',
        'payment_method',
        'payment_id',
        'preference_id',
        'payment_data',
        'subtotal',
        'shipping_cost',
        'discount',
        'total',
        'shipping_address',
        'billing_address',
        'shipping_quote_id',
        'tracking_code',
        'shipping_data',
        'notes',
        'customer_notes',
        'shipped_at',
        'delivered_at',
    ];

    protected $casts = [
        'payment_data' => 'array',
        'shipping_address' => 'array',
        'billing_address' => 'array',
        'shipping_data' => 'array',
        'subtotal' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    // Relacionamentos
    public function user(): BelongsTo
    {
        return $this->belongsTo(ClientUser::class, 'user_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    public function shippingLabel(): HasOne
    {
        return $this->hasOne(ShippingLabel::class);
    }

    public function paymentTransactions(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class);
    }

    public function coupons(): HasMany
    {
        return $this->hasMany(OrderCoupon::class);
    }

    public function shippingQuote(): BelongsTo
    {
        return $this->belongsTo(ShippingQuote::class);
    }

    // Scopes para filtros no admin
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByPaymentStatus($query, $paymentStatus)
    {
        return $query->where('payment_status', $paymentStatus);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('order_number', 'like', "%{$search}%")
              ->orWhereHas('user', function ($userQuery) use ($search) {
                  $userQuery->where('name', 'like', "%{$search}%")
                           ->orWhere('email', 'like', "%{$search}%");
              });
        });
    }

    // Métodos auxiliares para o admin
    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            'pending' => 'bg-yellow-100 text-yellow-800',
            'payment_approved' => 'bg-green-100 text-green-800',
            'preparing' => 'bg-blue-100 text-blue-800',
            'shipped' => 'bg-purple-100 text-purple-800',
            'delivered' => 'bg-green-100 text-green-800',
            'canceled' => 'bg-red-100 text-red-800',
            'refunded' => 'bg-gray-100 text-gray-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getStatusLabel(): string
    {
        return match($this->status) {
            'pending' => 'Aguardando Pagamento',
            'payment_approved' => 'Pagamento Aprovado',
            'preparing' => 'Preparando Envio',
            'shipped' => 'Enviado',
            'delivered' => 'Entregue',
            'canceled' => 'Cancelado',
            'refunded' => 'Reembolsado',
            default => 'Status Desconhecido',
        };
    }

    public function getPaymentStatusLabel(): string
    {
        return match($this->payment_status) {
            'pending' => 'Pendente',
            'approved' => 'Aprovado',
            'rejected' => 'Rejeitado',
            'cancelled' => 'Cancelado',
            'refunded' => 'Reembolsado',
            'in_process' => 'Processando',
            default => 'Status Desconhecido',
        };
    }

    public function canGenerateShippingLabel(): bool
    {
        return in_array($this->status, ['payment_approved', 'preparing'])
               && $this->payment_status === 'approved'
               && !$this->shippingLabel;
    }

    public function canUpdateStatus(): bool
    {
        return !in_array($this->status, ['delivered', 'canceled', 'refunded']);
    }

    // Gerador de número do pedido
    public static function generateOrderNumber(): string
    {
        $year = date('Y');
        $lastOrder = self::whereYear('created_at', $year)->latest()->first();
        $nextNumber = $lastOrder ? (intval(substr($lastOrder->order_number, -6)) + 1) : 1;

        return 'ORD-' . $year . '-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }

    // Boot method para auto-gerar order_number
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (!$order->order_number) {
                $order->order_number = self::generateOrderNumber();
            }
        });
    }
}
