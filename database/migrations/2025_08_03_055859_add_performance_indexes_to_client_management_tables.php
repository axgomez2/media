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
        // Indexes for client_users table
        Schema::table('client_users', function (Blueprint $table) {
            // Composite index for search functionality (name + email)
            $table->index(['name', 'email'], 'idx_client_users_name_email');

            // Index for email verification status queries
            $table->index('email_verified_at', 'idx_client_users_email_verified');

            // Composite index for filtering by status and creation date
            $table->index(['status', 'created_at'], 'idx_client_users_status_created');

            // Index for updated_at (used for active user queries)
            $table->index('updated_at', 'idx_client_users_updated_at');

            // Index for created_at (used for date range filtering)
            $table->index('created_at', 'idx_client_users_created_at');
        });

        // Indexes for addresses table
        Schema::table('addresses', function (Blueprint $table) {
            // Composite index for user_id and is_default (for default address queries)
            $table->index(['user_id', 'is_default'], 'idx_addresses_user_default');

            // Index for city and state (for location-based queries)
            $table->index(['city', 'state'], 'idx_addresses_city_state');
        });

        // Indexes for orders table
        Schema::table('orders', function (Blueprint $table) {
            // Composite index for user_id and status (for user order queries)
            $table->index(['user_id', 'status'], 'idx_orders_user_status');

            // Composite index for user_id and created_at (for chronological user orders)
            $table->index(['user_id', 'created_at'], 'idx_orders_user_created');

            // Composite index for status and created_at (for status-based date filtering)
            $table->index(['status', 'created_at'], 'idx_orders_status_created');
        });

        // Indexes for wishlists table
        Schema::table('wishlists', function (Blueprint $table) {
            // Composite index for user_id and created_at (for user wishlist queries)
            $table->index(['user_id', 'created_at'], 'idx_wishlists_user_created');
        });

        // Indexes for carts table
        Schema::table('carts', function (Blueprint $table) {
            // Index for updated_at (for abandoned cart queries)
            $table->index('updated_at', 'idx_carts_updated_at');
        });

        // Indexes for cart_items table (if exists)
        if (Schema::hasTable('cart_items')) {
            Schema::table('cart_items', function (Blueprint $table) {
                // Composite index for cart_id and created_at
                $table->index(['cart_id', 'created_at'], 'idx_cart_items_cart_created');
            });
        }

        // Indexes for cart_product pivot table (if exists)
        if (Schema::hasTable('cart_product')) {
            Schema::table('cart_product', function (Blueprint $table) {
                // Composite index for cart_id and created_at
                $table->index(['cart_id', 'created_at'], 'idx_cart_product_cart_created');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop indexes from client_users table
        Schema::table('client_users', function (Blueprint $table) {
            $table->dropIndex('idx_client_users_name_email');
            $table->dropIndex('idx_client_users_email_verified');
            $table->dropIndex('idx_client_users_status_created');
            $table->dropIndex('idx_client_users_updated_at');
            $table->dropIndex('idx_client_users_created_at');
        });

        // Drop indexes from addresses table
        Schema::table('addresses', function (Blueprint $table) {
            $table->dropIndex('idx_addresses_user_default');
            $table->dropIndex('idx_addresses_city_state');
        });

        // Drop indexes from orders table
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('idx_orders_user_status');
            $table->dropIndex('idx_orders_user_created');
            $table->dropIndex('idx_orders_status_created');
        });

        // Drop indexes from wishlists table
        Schema::table('wishlists', function (Blueprint $table) {
            $table->dropIndex('idx_wishlists_user_created');
        });

        // Drop indexes from carts table
        Schema::table('carts', function (Blueprint $table) {
            $table->dropIndex('idx_carts_updated_at');
        });

        // Drop indexes from cart_items table (if exists)
        if (Schema::hasTable('cart_items')) {
            Schema::table('cart_items', function (Blueprint $table) {
                $table->dropIndex('idx_cart_items_cart_created');
            });
        }

        // Drop indexes from cart_product pivot table (if exists)
        if (Schema::hasTable('cart_product')) {
            Schema::table('cart_product', function (Blueprint $table) {
                $table->dropIndex('idx_cart_product_cart_created');
            });
        }
    }
};
