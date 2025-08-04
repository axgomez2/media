<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    protected $table = 'carts';
    protected $fillable = ['user_id'];

    protected $casts = [
        'user_id' => 'string'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(ClientUser::class, 'user_id');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(
            Product::class,
            'cart_items',
            'cart_id',
            'product_id'
        )->withPivot('quantity', 'created_at', 'updated_at');
    }

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Scope para carrinhos abandonados
     */
    public function scopeAbandoned($query, $days = 7)
    {
        return $query->where('updated_at', '<=', now()->subDays($days))
                    ->whereHas('products');
    }

    /**
     * Accessor para total do carrinho
     */
    public function getTotalAttribute(): float
    {
        return $this->products()->sum(function ($product) {
            return $product->price * $product->pivot->quantity;
        });
    }

    /**
     * Accessor para quantidade total de itens
     */
    public function getTotalItemsAttribute(): int
    {
        return $this->products()->sum('cart_items.quantity');
    }
}
