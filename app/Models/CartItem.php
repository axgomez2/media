<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    protected $table = 'cart_items';
    public $incrementing = false;            // sem id autoincremental
    protected $primaryKey = null;            // PK composta
    public $timestamps = true;

    protected $fillable = ['cart_id', 'product_id', 'quantity'];

    protected $casts = [
        'quantity' => 'integer'
    ];

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Scope para itens abandonados
     */
    public function scopeAbandoned($query, $days = 7)
    {
        return $query->where('updated_at', '<=', now()->subDays($days));
    }

    /**
     * Accessor para subtotal do item
     */
    public function getSubtotalAttribute(): float
    {
        return $this->product ? $this->product->price * $this->quantity : 0;
    }
}
