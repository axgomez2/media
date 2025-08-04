<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'total',
        'status',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'user_id' => 'string',
        'total' => 'decimal:2'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(ClientUser::class, 'user_id');
    }
}
