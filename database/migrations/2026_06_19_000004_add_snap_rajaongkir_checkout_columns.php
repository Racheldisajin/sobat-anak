<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('user_addresses')) {
            Schema::table('user_addresses', function (Blueprint $table) {
                if (! Schema::hasColumn('user_addresses', 'location_url')) $table->text('location_url')->nullable()->after('postal_code');
                if (! Schema::hasColumn('user_addresses', 'latitude')) $table->decimal('latitude', 10, 7)->nullable()->after('location_url');
                if (! Schema::hasColumn('user_addresses', 'longitude')) $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
                if (! Schema::hasColumn('user_addresses', 'destination_id')) $table->string('destination_id', 30)->nullable()->after('longitude');
                if (! Schema::hasColumn('user_addresses', 'district_name')) $table->string('district_name', 120)->nullable()->after('destination_id');
            });
        }

        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if (! Schema::hasColumn('orders', 'selected_payment_method')) $table->string('selected_payment_method', 80)->nullable()->after('payment_type');
                if (! Schema::hasColumn('orders', 'selected_payment_label')) $table->string('selected_payment_label', 120)->nullable()->after('selected_payment_method');
                if (! Schema::hasColumn('orders', 'enabled_payments')) $table->longText('enabled_payments')->nullable()->after('selected_payment_label');
                if (! Schema::hasColumn('orders', 'payment_detail')) $table->longText('payment_detail')->nullable();
                if (! Schema::hasColumn('orders', 'snap_token')) $table->string('snap_token', 255)->nullable();
                if (! Schema::hasColumn('orders', 'snap_redirect_url')) $table->text('snap_redirect_url')->nullable();
                if (! Schema::hasColumn('orders', 'shipping_snapshot')) $table->longText('shipping_snapshot')->nullable();
                if (! Schema::hasColumn('orders', 'expired_at')) $table->timestamp('expired_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        // Tidak rollback otomatis supaya tidak menghapus data alamat/order user.
    }
};
