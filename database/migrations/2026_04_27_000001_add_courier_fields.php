<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Allow only one courier to be the default
        Schema::table('integrations', function (Blueprint $table) {
            $table->boolean('is_default')->default(false)->after('is_active');
        });

        // Store consignment / tracking data per courier on each order
        Schema::table('orders', function (Blueprint $table) {
            $table->string('courier_provider', 50)->nullable()->after('tracking_number');
            $table->string('consignment_id', 100)->nullable()->after('courier_provider');
            $table->string('courier_status', 100)->nullable()->after('consignment_id');
            $table->json('courier_data')->nullable()->after('courier_status'); // raw API response
        });
    }

    public function down(): void
    {
        Schema::table('integrations', function (Blueprint $table) {
            $table->dropColumn('is_default');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['courier_provider', 'consignment_id', 'courier_status', 'courier_data']);
        });
    }
};
