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
        Schema::create('order_coupons', function (Blueprint $table) {
            $table->id();

            // Relacionamento com pedido
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');

            // Dados do cupom
            $table->string('coupon_code'); // Código do cupom usado
            $table->string('coupon_name')->nullable(); // Nome/descrição do cupom

            // Tipo de desconto
            $table->enum('discount_type', ['percentage', 'fixed_amount', 'free_shipping']);
            $table->decimal('discount_value', 10, 2); // Valor ou percentual do desconto
            $table->decimal('discount_amount', 10, 2); // Valor efetivo do desconto aplicado

            // Aplicação do desconto
            $table->enum('applies_to', ['total', 'subtotal', 'shipping', 'specific_items'])->default('subtotal');
            $table->json('applicable_items')->nullable(); // IDs dos itens se aplicável a itens específicos

            // Limites e condições
            $table->decimal('minimum_amount', 10, 2)->nullable(); // Valor mínimo para aplicar
            $table->decimal('maximum_discount', 10, 2)->nullable(); // Desconto máximo

            // Dados do cupom no momento da aplicação (snapshot)
            $table->json('coupon_snapshot'); // Dados completos do cupom

            // Validação
            $table->boolean('is_valid')->default(true); // Se o cupom era válido
            $table->text('validation_notes')->nullable(); // Observações sobre validação

            $table->timestamps();

            // Índices
            $table->index('order_id');
            $table->index('coupon_code');
            $table->index('discount_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_coupons');
    }
};
