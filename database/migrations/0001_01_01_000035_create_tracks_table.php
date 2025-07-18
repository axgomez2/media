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
        Schema::create('tracks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vinyl_master_id');
            $table->string('name');
            $table->integer('position')->default(0);
            $table->string('duration')->nullable();
            $table->integer('duration_seconds')->nullable();
            $table->string('youtube_url')->nullable();
            $table->timestamps();

            $table->foreign('vinyl_master_id')->references('id')->on('vinyl_masters')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tracks');
    }
};
