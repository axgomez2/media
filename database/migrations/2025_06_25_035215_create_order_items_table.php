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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();

            // Relacionamento com pedido
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');

            // Relacionamentos com produtos
            $table->foreignId('product_id')->nullable()->constrained('products')->onDelete('set null');
            $table->foreignId('vinyl_id')->nullable()->constrained('vinyl_secs')->onDelete('set null');

            // Snapshot do produto no momento da compra (importante para histórico)
            $table->json('product_snapshot'); // Dados completos do produto

            // Dados do item
            $table->string('product_name'); // Nome do produto
            $table->string('product_sku')->nullable(); // SKU/Código
            $table->string('product_image')->nullable(); // Imagem principal

            // Quantidade e preços
            $table->integer('quantity'); // Quantidade comprada
            $table->decimal('unit_price', 10, 2); // Preço unitário na época da compra
            $table->decimal('promotional_price', 10, 2)->nullable(); // Preço promocional se aplicável
            $table->decimal('total_price', 10, 2); // Total do item (quantidade * preço)

            // Dados específicos do vinyl (se aplicável)
            $table->string('artist_name')->nullable();
            $table->string('album_title')->nullable();
            $table->string('vinyl_condition')->nullable(); // M, NM, VG+, etc
            $table->string('cover_condition')->nullable();

            $table->timestamps();

            // Índices
            $table->index('order_id');
            $table->index(['product_id', 'vinyl_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
