<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShippingQuote extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'cart_id',
        'cep_destino',
        'quote_data',
        'selected_service',
        'expires_at',
    ];

    protected $casts = [
        'user_id' => 'string',
        'quote_data' => 'array',
        'selected_service' => 'array',
        'expires_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(ClientUser::class, 'user_id');
    }

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
