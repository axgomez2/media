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
        Schema::create('shipping_labels', function (Blueprint $table) {
            $table->id();

            // Relacionamento com pedido
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');

            // Dados da etiqueta no Melhor Envio
            $table->string('melhor_envio_id')->unique(); // ID da etiqueta no Melhor Envio
            $table->string('tracking_code')->nullable(); // Código de rastreamento
            $table->string('protocol')->nullable(); // Protocolo da postagem

            // URLs e arquivos
            $table->string('label_url')->nullable(); // URL da etiqueta em PDF
            $table->string('label_path')->nullable(); // Caminho local da etiqueta (se salva)

            // Status da etiqueta
            $table->enum('status', [
                'pending',      // Pendente
                'generated',    // Gerada
                'posted',       // Postada
                'in_transit',   // Em trânsito
                'delivered',    // Entregue
                'returned',     // Devolvida
                'canceled'      // Cancelada
            ])->default('pending');

            // Dados do serviço de envio
            $table->string('service_name'); // Nome do serviço (PAC, SEDEX, etc)
            $table->string('service_id'); // ID do serviço no Melhor Envio
            $table->string('company_name'); // Correios, Jadlog, etc

            // Valores
            $table->decimal('declared_value', 10, 2); // Valor declarado
            $table->decimal('shipping_cost', 10, 2); // Custo do frete

            // Dimensões e peso
            $table->json('package_dimensions'); // Dimensões do pacote
            $table->decimal('package_weight', 8, 3); // Peso do pacote em kg

            // Endereços
            $table->json('origin_address'); // Endereço de origem
            $table->json('destination_address'); // Endereço de destino

            // Dados de rastreamento
            $table->json('tracking_events')->nullable(); // Eventos de rastreamento
            $table->timestamp('last_tracking_update')->nullable(); // Última atualização do tracking

            // Dados completos da resposta do Melhor Envio
            $table->json('melhor_envio_data')->nullable(); // Resposta completa da API

            // Observações
            $table->text('notes')->nullable();

            $table->timestamps();

            // Índices
            $table->index('order_id');
            $table->index('melhor_envio_id');
            $table->index('tracking_code');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipping_labels');
    }
};
