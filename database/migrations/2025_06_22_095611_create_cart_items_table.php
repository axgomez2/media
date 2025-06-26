<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCartItemsTable extends Migration
{
    public function up()
    {
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')
                  ->constrained('carts')
                  ->onDelete('cascade');
            $table->foreignId('product_id')
                  ->constrained('products')
                  ->onDelete('cascade');
            $table->timestamps();

            // impede duplicar o mesmo item no carrinho
            $table->unique(['cart_id','product_id'], 'unique_cart_item');
        });
    }

    public function down()
    {
        Schema::dropIfExists('cart_items');
    }
}
