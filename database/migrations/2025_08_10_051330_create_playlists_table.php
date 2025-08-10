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
        Schema::create('playlists', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['dj', 'chart']); // Tipo da playlist
            $table->string('title'); // Nome do DJ ou título do chart
            $table->text('description')->nullable(); // Resumo/descrição
            $table->date('chart_date')->nullable(); // Data do chart (apenas para type=chart)
            $table->json('social_links')->nullable(); // Links das redes sociais (apenas para type=dj)
            $table->boolean('is_active')->default(true);
            $table->integer('position')->default(0); // Para ordenação
            $table->timestamps();

            // Índices
            $table->index(['type', 'is_active']);
            $table->index('position');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('playlists');
    }
};
