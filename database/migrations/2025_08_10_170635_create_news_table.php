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
        Schema::create('news', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable(); // Resumo/descrição curta
            $table->longText('content'); // Conteúdo principal
            $table->string('featured_image')->nullable(); // Imagem de destaque
            $table->json('gallery_images')->nullable(); // Galeria de imagens
            $table->json('topics')->nullable(); // Tópicos/tags
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->string('meta_description')->nullable(); // SEO
            $table->string('meta_keywords')->nullable(); // SEO
            $table->timestamp('published_at')->nullable();
            $table->uuid('author_id'); // Referência ao usuário admin
            $table->integer('views_count')->default(0);
            $table->boolean('featured')->default(false); // Notícia em destaque
            $table->timestamps();

            // Índices
            $table->index(['status', 'published_at']);
            $table->index(['featured', 'published_at']);
            $table->index('author_id');

            // Fulltext index apenas para MySQL
            if (config('database.default') === 'mysql') {
                $table->fullText(['title', 'content']); // Para busca
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('news');
    }
};
