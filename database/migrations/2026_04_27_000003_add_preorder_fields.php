<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('preorder_enabled')->default(false)->after('status');
            $table->string('preorder_message', 200)->nullable()->after('preorder_enabled');
            $table->date('preorder_expected_date')->nullable()->after('preorder_message');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->boolean('is_preorder')->default(false)->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['preorder_enabled', 'preorder_message', 'preorder_expected_date']);
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('is_preorder');
        });
    }
};
