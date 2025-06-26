<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $table = 'carts';
    protected $fillable = ['user_id'];

    public function user()
    {
        return $this->belongsTo(ClientUser::class, 'user_id');
    }

    public function products()
    {
        return $this->belongsToMany(
            Product::class,
            'cart_items',
            'cart_id',
            'product_id'
        )->withPivot('quantity');
    }
}