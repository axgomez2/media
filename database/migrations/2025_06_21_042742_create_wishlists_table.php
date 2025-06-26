<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('wishlists', function (Blueprint $table) {
            $table->id();
            $table->uuid('user_id'); // Assumindo que ClientUser usa UUID
            $table->unsignedBigInteger('product_id');
            $table->timestamps();

            // Índices
            $table->index(['user_id', 'product_id']);
            $table->unique(['user_id', 'product_id']); // Evitar duplicatas

            // Chaves estrangeiras
            $table->foreign('user_id')->references('id')->on('client_users')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wishlists');
    }
};
