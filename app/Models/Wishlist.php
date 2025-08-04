<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Wishlist extends Model
{
    protected $fillable = [
        'user_id',
        'product_id'
    ];

    protected $casts = [
        'user_id' => 'string'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(ClientUser::class, 'user_id');
    }
}
