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
        Schema::table('client_users', function (Blueprint $table) {
            $table->enum('status', ['active', 'inactive'])->default('active')->after('email_verified_at');
            $table->timestamp('status_updated_at')->nullable()->after('status');
            $table->text('status_reason')->nullable()->after('status_updated_at');
            $table->uuid('status_updated_by')->nullable()->after('status_reason');

            // Add index for status field for better query performance
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('client_users', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropColumn(['status', 'status_updated_at', 'status_reason', 'status_updated_by']);
        });
    }
};
