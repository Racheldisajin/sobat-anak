<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_addresses', function (Blueprint $table) {
            if (! Schema::hasColumn('user_addresses', 'location_url')) {
                $table->text('location_url')->nullable()->after('postal_code');
            }
            if (! Schema::hasColumn('user_addresses', 'latitude')) {
                $table->decimal('latitude', 10, 7)->nullable()->after('location_url');
            }
            if (! Schema::hasColumn('user_addresses', 'longitude')) {
                $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            }
        });
    }

    public function down(): void
    {
        Schema::table('user_addresses', function (Blueprint $table) {
            if (Schema::hasColumn('user_addresses', 'longitude')) {
                $table->dropColumn('longitude');
            }
            if (Schema::hasColumn('user_addresses', 'latitude')) {
                $table->dropColumn('latitude');
            }
            if (Schema::hasColumn('user_addresses', 'location_url')) {
                $table->dropColumn('location_url');
            }
        });
    }
};
