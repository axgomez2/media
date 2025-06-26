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
        Schema::create('order_status_history', function (Blueprint $table) {
            $table->id();

            // Relacionamento com pedido
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');

            // Status anterior e novo
            $table->string('status_from')->nullable(); // Status anterior (null para primeiro status)
            $table->string('status_to'); // Novo status

            // Dados da mudança
            $table->text('notes')->nullable(); // Observações sobre a mudança
            $table->json('metadata')->nullable(); // Dados adicionais (ex: dados do tracking)

            // Quem fez a mudança (client_users usando UUID)
            $table->uuid('created_by')->nullable();
            $table->foreign('created_by')->references('id')->on('client_users')->onDelete('set null');
            $table->enum('change_type', ['manual', 'automatic', 'webhook'])->default('manual'); // Tipo da mudança

            // Dados específicos por tipo de mudança
            $table->string('webhook_source')->nullable(); // Ex: mercadopago, melhorenvio
            $table->json('webhook_data')->nullable(); // Dados do webhook

            $table->timestamps();

            // Índices
            $table->index(['order_id', 'created_at']);
            $table->index('status_to');
            $table->index('change_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_status_history');
    }
};
