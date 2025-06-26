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
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->uuid('user_id');
            $table->enum('status', ['active', 'converted'])
                  ->default('active');
            $table->timestamps();

            // Chave estrangeira para client_users
            $table->foreign('user_id')
                  ->references('id')
                  ->on('client_users')
                  ->onDelete('cascade');

            // Cada usuário só pode ter um carrinho ativo
            $table->unique(['user_id', 'status'], 'unique_active_cart_per_user');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};
