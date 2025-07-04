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
        Schema::table('vinyl_secs', function (Blueprint $table) {
            $table->boolean('is_presale')->default(false)->after('is_promotional');
            $table->date('presale_arrival_date')->nullable()->after('is_presale');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vinyl_secs', function (Blueprint $table) {
            $table->dropColumn(['is_presale', 'presale_arrival_date']);
        });
    }
};
