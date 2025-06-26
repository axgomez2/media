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
        Schema::table('payment_transactions', function (Blueprint $table) {
            // Alterar campos de QR Code para suportar strings maiores
            $table->text('pix_qr_code')->nullable()->change();
            $table->longText('pix_qr_code_base64')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_transactions', function (Blueprint $table) {
            // Reverter para os tipos originais
            $table->string('pix_qr_code')->nullable()->change();
            $table->string('pix_qr_code_base64')->nullable()->change();
        });
    }
};
