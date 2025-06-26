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
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();

            // Relacionamento com pedido
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');

            // Dados do Mercado Pago
            $table->string('payment_id')->unique(); // ID do pagamento no Mercado Pago
            $table->string('preference_id')->nullable(); // ID da preferência
            $table->string('collection_id')->nullable(); // ID da cobrança
            $table->string('external_reference')->nullable(); // Referência externa (order_number)

            // Tipo e método de pagamento
            $table->string('payment_type'); // credit_card, debit_card, ticket, bank_transfer, etc
            $table->string('payment_method'); // visa, master, pix, boleto, etc
            $table->string('payment_method_id')->nullable(); // ID específico do método

            // Status da transação
            $table->enum('status', [
                'pending',          // Pendente
                'approved',         // Aprovado
                'authorized',       // Autorizado
                'in_process',       // Em processamento
                'in_mediation',     // Em mediação
                'rejected',         // Rejeitado
                'cancelled',        // Cancelado
                'refunded',         // Reembolsado
                'charged_back'      // Chargeback
            ]);

            $table->string('status_detail')->nullable(); // Detalhes do status

            // Valores
            $table->decimal('transaction_amount', 10, 2); // Valor da transação
            $table->decimal('net_received_amount', 10, 2)->nullable(); // Valor líquido recebido
            $table->decimal('total_paid_amount', 10, 2)->nullable(); // Valor total pago
            $table->string('currency_id', 3)->default('BRL'); // Moeda

            // Taxas e descontos
            $table->decimal('mercadopago_fee', 10, 2)->nullable(); // Taxa do Mercado Pago
            $table->decimal('discount_amount', 10, 2)->nullable(); // Valor do desconto
            $table->json('fee_details')->nullable(); // Detalhes das taxas

            // Dados do pagador
            $table->json('payer_data'); // Dados do pagador

            // Dados específicos do método de pagamento
            $table->json('payment_method_data')->nullable(); // Dados específicos (últimos 4 dígitos, etc)

            // Parcelamento (para cartão de crédito)
            $table->integer('installments')->nullable(); // Número de parcelas
            $table->decimal('installment_amount', 10, 2)->nullable(); // Valor da parcela

            // Datas importantes
            $table->timestamp('date_approved')->nullable(); // Data de aprovação
            $table->timestamp('date_created')->nullable(); // Data de criação no MP
            $table->timestamp('date_last_updated')->nullable(); // Última atualização no MP
            $table->timestamp('money_release_date')->nullable(); // Data de liberação do dinheiro

            // Dados de PIX (se aplicável)
            $table->string('pix_qr_code')->nullable(); // Código QR do PIX
            $table->string('pix_qr_code_base64')->nullable(); // QR Code em base64
            $table->string('pix_transaction_id')->nullable(); // ID da transação PIX

            // Dados completos da resposta do Mercado Pago
            $table->json('mercadopago_response'); // Resposta completa da API

            // Webhook data
            $table->json('webhook_notifications')->nullable(); // Notificações recebidas
            $table->timestamp('last_webhook_received')->nullable(); // Última notificação

            // Observações
            $table->text('notes')->nullable();

            $table->timestamps();

            // Índices
            $table->index('order_id');
            $table->index('payment_id');
            $table->index('status');
            $table->index('payment_type');
            $table->index('date_approved');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};
