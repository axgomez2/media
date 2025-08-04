<?php

namespace App\Models;

use App\Services\ClientStatisticsService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ClientUser extends Model
{
    use HasFactory;

    protected $table = 'client_users';

    // Usa UUID manualmente gerado, sem incremento automático
    public $incrementing = false;
    protected $keyType = 'string';

    public $timestamps = true;

    protected $fillable = [
        'id',
        'name',
        'email',
        'phone',
        'cpf',
        'birth_date',
        'password',
        'google_id',
        'email_verified_at',
        'status',
        'status_updated_at',
        'status_reason',
        'status_updated_by',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'status_updated_at' => 'datetime',
        'birth_date' => 'date',
        'id' => 'string',
    ];

    /**
     * Relacionamento com endereços
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class, 'user_id');
    }

    /**
     * Relacionamento com endereço padrão
     */
    public function defaultAddress(): HasOne
    {
        return $this->hasOne(Address::class, 'user_id')->where('is_default', true);
    }

    /**
     * Relacionamento com pedidos
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'user_id');
    }

    /**
     * Relacionamento com carrinho
     */
    public function cart(): HasOne
    {
        return $this->hasOne(Cart::class, 'user_id');
    }

    /**
     * Relacionamento com itens do carrinho através do carrinho
     */
    public function cartItems(): HasManyThrough
    {
        return $this->hasManyThrough(
            CartItem::class,    // Final model
            Cart::class,        // Intermediate model
            'user_id',          // Foreign key on intermediate model (carts table)
            'cart_id',          // Foreign key on final model (cart_items table)
            'id',               // Local key on this model (client_users table)
            'id'                // Local key on intermediate model (carts table)
        );
    }



    /**
     * Relacionamento com lista de desejos
     */
    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class, 'user_id');
    }

    /**
     * Relacionamento com lista de procurados
     */
    public function wantlists(): HasMany
    {
        return $this->hasMany(Wantlist::class, 'user_id');
    }

    /**
     * Scope para clientes com email verificado
     */
    public function scopeVerified($query)
    {
        return $query->whereNotNull('email_verified_at');
    }

    /**
     * Scope para clientes ativos (login nos últimos 30 dias)
     */
    public function scopeActive($query, $days = 30)
    {
        return $query->where('updated_at', '>=', now()->subDays($days));
    }

    /**
     * Scope para clientes que fizeram pedidos
     */
    public function scopeWithOrders($query)
    {
        return $query->whereHas('orders');
    }

    /**
     * Scope para buscar por nome ou email
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%");
        });
    }

    /**
     * Scope para clientes cadastrados em um período
     */
    public function scopeCreatedBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope para clientes novos no mês
     */
    public function scopeNewThisMonth($query)
    {
        return $query->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year);
    }

    /**
     * Scope para clientes não verificados
     */
    public function scopeUnverified($query)
    {
        return $query->whereNull('email_verified_at');
    }

    /**
     * Scope para clientes com carrinho abandonado
     */
    public function scopeWithAbandonedCart($query)
    {
        return $query->whereHas('cart', function ($cartQuery) {
            $cartQuery->where('updated_at', '<=', now()->subDays(7))
                     ->whereHas('products');
        });
    }

    /**
     * Scope para clientes por status de verificação
     */
    public function scopeByVerificationStatus($query, $verified = true)
    {
        return $verified ? $query->verified() : $query->unverified();
    }

    /**
     * Scope para clientes ativos
     */
    public function scopeActiveStatus($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope para clientes inativos
     */
    public function scopeInactiveStatus($query)
    {
        return $query->where('status', 'inactive');
    }

    /**
     * Scope para clientes por status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Accessor para avatar baseado no nome
     */
    public function getAvatarAttribute(): string
    {
        $words = explode(' ', trim($this->name));

        if (count($words) >= 2) {
            // Multiple words: take first letter of first two words
            return strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
        } else {
            // Single word: take first two characters
            return strtoupper(substr($this->name, 0, 2));
        }
    }

    /**
     * Accessor para endereço completo padrão
     */
    public function getFullAddressAttribute(): ?string
    {
        $defaultAddress = $this->defaultAddress;

        return $defaultAddress ? $defaultAddress->full_address : null;
    }

    /**
     * Accessor para verificar se o email foi verificado
     */
    public function getIsVerifiedAttribute(): bool
    {
        return !is_null($this->email_verified_at);
    }

    /**
     * Accessor para verificar se o cliente está ativo
     */
    public function getIsActiveAttribute(): bool
    {
        return $this->updated_at >= now()->subDays(30);
    }

    /**
     * Accessor para total de pedidos (cached)
     */
    public function getTotalOrdersAttribute(): int
    {
        return Cache::remember("client_{$this->id}_total_orders", 3600, function () {
            return $this->orders()->count();
        });
    }

    /**
     * Accessor para valor total gasto (cached)
     */
    public function getTotalSpentAttribute(): float
    {
        return Cache::remember("client_{$this->id}_total_spent", 3600, function () {
            return $this->orders()
                ->whereIn('status', ['delivered', 'payment_approved'])
                ->sum('total');
        });
    }

    /**
     * Accessor para último pedido
     */
    public function getLastOrderAttribute(): ?Order
    {
        return $this->orders()->latest()->first();
    }

    /**
     * Accessor para itens no carrinho (cached)
     */
    public function getCartItemsCountAttribute(): int
    {
        return Cache::remember("client_{$this->id}_cart_items_count", 1800, function () {
            return $this->cart ? $this->cart->products()->count() : 0;
        });
    }

    /**
     * Accessor para verificar se tem itens abandonados no carrinho (cached)
     */
    public function getHasAbandonedCartAttribute(): bool
    {
        return Cache::remember("client_{$this->id}_has_abandoned_cart", 1800, function () {
            if (!$this->cart) {
                return false;
            }

            return $this->cart->updated_at <= now()->subDays(7) && $this->cart->products()->count() > 0;
        });
    }

    /**
     * Accessor para valor total do carrinho (cached)
     */
    public function getCartTotalAttribute(): float
    {
        return Cache::remember("client_{$this->id}_cart_total", 1800, function () {
            if (!$this->cart) {
                return 0;
            }

            return $this->cart->products()->sum(function ($product) {
                return $product->price * $product->pivot->quantity;
            });
        });
    }

    /**
     * Accessor para itens na wishlist (cached)
     */
    public function getWishlistItemsCountAttribute(): int
    {
        return Cache::remember("client_{$this->id}_wishlist_items_count", 3600, function () {
            return $this->wishlists()->count();
        });
    }

    /**
     * Accessor para primeiro nome
     */
    public function getFirstNameAttribute(): string
    {
        return explode(' ', $this->name)[0];
    }

    /**
     * Accessor para verificar se o cliente está ativo por status
     */
    public function getIsActiveStatusAttribute(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Accessor para verificar se o cliente está inativo por status
     */
    public function getIsInactiveStatusAttribute(): bool
    {
        return $this->status === 'inactive';
    }

    /**
     * Accessor para label do status
     */
    public function getStatusLabelAttribute(): string
    {
        return $this->status === 'active' ? 'Ativo' : 'Inativo';
    }

    /**
     * Accessor para cor do badge do status
     */
    public function getStatusColorAttribute(): string
    {
        return $this->status === 'active' ? 'green' : 'red';
    }

    /**
     * Clear all cached data for this client
     */
    public function clearCache(): void
    {
        $cacheKeys = [
            "client_{$this->id}_total_orders",
            "client_{$this->id}_total_spent",
            "client_{$this->id}_cart_items_count",
            "client_{$this->id}_has_abandoned_cart",
            "client_{$this->id}_cart_total",
            "client_{$this->id}_wishlist_items_count"
        ];

        foreach ($cacheKeys as $key) {
            Cache::forget($key);
        }
    }

    /**
     * Boot method to handle cache invalidation on model events
     */
    protected static function boot()
    {
        parent::boot();

        // Clear cache when client is updated
        static::updated(function ($client) {
            $client->clearCache();

            // Also clear global statistics cache
            app(ClientStatisticsService::class)->clearCache();
        });

        // Clear cache when client is deleted
        static::deleted(function ($client) {
            $client->clearCache();

            // Also clear global statistics cache
            app(ClientStatisticsService::class)->clearCache();
        });
    }
}
