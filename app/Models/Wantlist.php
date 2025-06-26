<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wantlist extends Model
{
    protected $table = 'wantlists';
    public $incrementing = false;
    protected $keyType = 'string';
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

