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
        Schema::table('carts', function (Blueprint $table) {
            // Altera a coluna ENUM para incluir o novo status 'completed'
            $table->enum('status', ['active', 'converted', 'completed'])
                  ->default('active')
                  ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            // Reverte a alteração, removendo 'completed'
            $table->enum('status', ['active', 'converted'])
                  ->default('active')
                  ->change();
        });
    }
};
