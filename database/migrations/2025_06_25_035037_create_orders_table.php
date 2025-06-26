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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            // Relacionamento com usuário (usando UUID como na shipping_quotes)
            $table->uuid('user_id');
            $table->foreign('user_id')->references('id')->on('client_users')->onDelete('cascade');

            // Identificação do pedido
            $table->string('order_number')->unique(); // Ex: ORD-2024-000001

            // Status do pedido
            $table->enum('status', [
                'pending',           // Aguardando pagamento
                'payment_approved',  // Pagamento aprovado
                'preparing',         // Preparando para envio
                'shipped',          // Enviado
                'delivered',        // Entregue
                'canceled',         // Cancelado
                'refunded'          // Reembolsado
            ])->default('pending');

            // Status do pagamento
            $table->enum('payment_status', [
                'pending',          // Aguardando
                'approved',         // Aprovado
                'rejected',         // Rejeitado
                'cancelled',        // Cancelado
                'refunded',         // Reembolsado
                'in_process'        // Em processamento
            ])->default('pending');

            // Dados do pagamento Mercado Pago
            $table->string('payment_method')->nullable(); // pix, credit_card, debit_card, etc
            $table->string('payment_id')->nullable(); // ID do pagamento no Mercado Pago
            $table->string('preference_id')->nullable(); // ID da preferência no Mercado Pago
            $table->json('payment_data')->nullable(); // Dados completos do pagamento

            // Valores monetários
            $table->decimal('subtotal', 10, 2); // Subtotal dos produtos
            $table->decimal('shipping_cost', 10, 2)->default(0); // Custo do frete
            $table->decimal('discount', 10, 2)->default(0); // Desconto aplicado
            $table->decimal('total', 10, 2); // Total final

            // Endereços (JSON para flexibilidade)
            $table->json('shipping_address'); // Endereço de entrega
            $table->json('billing_address')->nullable(); // Endereço de cobrança

            // Dados do frete (referenciando shipping_quotes existente)
            $table->foreignId('shipping_quote_id')->nullable()->constrained('shipping_quotes')->onDelete('set null');
            $table->string('tracking_code')->nullable(); // Código de rastreamento
            $table->json('shipping_data')->nullable(); // Dados do frete selecionado

            // Observações e notas
            $table->text('notes')->nullable(); // Observações internas
            $table->text('customer_notes')->nullable(); // Observações do cliente

            // Timestamps
            $table->timestamp('shipped_at')->nullable(); // Data de envio
            $table->timestamp('delivered_at')->nullable(); // Data de entrega
            $table->timestamps();

            // Índices para performance
            $table->index(['user_id', 'created_at']);
            $table->index(['status', 'created_at']);
            $table->index(['payment_status']);
            $table->index('order_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
