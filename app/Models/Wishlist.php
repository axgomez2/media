<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wishlist extends Model
{
    protected $table = 'wishlists';
    public $incrementing = false;            // sem PK autoincremental
    protected $keyType = 'string';           // UUID
    public $timestamps = true;

    protected $fillable = ['user_id', 'product_id'];

    public function user()
    {
        return $this->belongsTo(ClientUser::class, 'user_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}