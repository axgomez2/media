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
        Schema::create('market_analysis', function (Blueprint $table) {
            $table->id();
            $table->date('analysis_date')->unique()->comment('Data da coleta da análise.');
            $table->unsignedBigInteger('total_listings')->default(0)->comment('Total de anúncios de vinil no mundo.');

            // Colunas dedicadas para os países monitorados
            $table->unsignedInteger('br_listings')->default(0)->comment('Anúncios do Brasil.');
            $table->unsignedInteger('gb_listings')->default(0)->comment('Anúncios do Reino Unido.');
            $table->unsignedInteger('de_listings')->default(0)->comment('Anúncios da Alemanha.');
            $table->unsignedInteger('us_listings')->default(0)->comment('Anúncios dos EUA.');
            $table->unsignedInteger('fr_listings')->default(0)->comment('Anúncios da França.');
            $table->unsignedInteger('it_listings')->default(0)->comment('Anúncios da Itália.');
            $table->unsignedInteger('jp_listings')->default(0)->comment('Anúncios do Japão.');
            $table->unsignedInteger('ca_listings')->default(0)->comment('Anúncios do Canadá.');
            $table->unsignedInteger('be_listings')->default(0)->comment('Anúncios da Bélgica.');
            $table->unsignedInteger('se_listings')->default(0)->comment('Anúncios da Suécia.');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('market_analysis');
    }
};
