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
        Schema::table('coupons', function (Blueprint $table) {
            $table->enum('applies_to', ['all', 'specific_products', 'specific_categories'])
                  ->default('all')->after('maximum_discount');
            $table->boolean('is_auto_apply')->default(false)->after('is_active');
            $table->unsignedInteger('per_customer_limit')->nullable()->after('usage_limit');
        });
    }

    public function down(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->dropColumn(['applies_to', 'is_auto_apply', 'per_customer_limit']);
        });
    }
};
