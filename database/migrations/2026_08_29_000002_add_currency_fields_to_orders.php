<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Money on an order is recorded twice:
     *
     *   - the existing columns stay in BASE currency, because ~24 report queries
     *     SUM(total) and a mixed-currency column would silently corrupt all of them;
     *   - the presentment_* columns record what the customer was actually shown and
     *     charged. They are stored, never recomputed from exchange_rate — rounding is
     *     per-line and non-invertible, so a recomputed figure would not reproduce the
     *     invoice the customer agreed to.
     *
     * base_currency is snapshotted per order rather than read from settings so that
     * history stays interpretable if the store ever changes its base currency.
     *
     * Precision moves to (12,4): (10,2) caps at 99,999,999.99 (too tight for IDR/VND
     * presentment) and truncates the 3-decimal currencies (KWD, BHD, OMR, TND).
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('discount_amount', 12, 4)->default(0)->change();
            $table->decimal('subtotal', 12, 4)->default(0)->change();
            $table->decimal('shipping_cost', 12, 4)->default(0)->change();
            $table->decimal('tax', 12, 4)->default(0)->change();
            $table->decimal('total', 12, 4)->default(0)->change();

            $table->char('base_currency', 3)->default('BDT')->after('total');
            $table->char('presentment_currency', 3)->default('BDT')->after('base_currency');
            $table->decimal('exchange_rate', 18, 8)->default(1)->after('presentment_currency');

            $table->decimal('presentment_subtotal', 12, 4)->default(0)->after('exchange_rate');
            $table->decimal('presentment_shipping_cost', 12, 4)->default(0)->after('presentment_subtotal');
            $table->decimal('presentment_tax', 12, 4)->default(0)->after('presentment_shipping_cost');
            $table->decimal('presentment_discount_amount', 12, 4)->default(0)->after('presentment_tax');
            $table->decimal('presentment_total', 12, 4)->default(0)->after('presentment_discount_amount');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->decimal('unit_price', 12, 4)->change();
            $table->decimal('total', 12, 4)->change();

            $table->decimal('presentment_unit_price', 12, 4)->default(0)->after('unit_price');
            $table->decimal('presentment_total', 12, 4)->default(0)->after('total');
        });

        // Historic orders were all placed in BDT at rate 1, so presentment equals base.
        DB::table('orders')->update([
            'base_currency' => 'BDT',
            'presentment_currency' => 'BDT',
            'exchange_rate' => 1,
            'presentment_subtotal' => DB::raw('subtotal'),
            'presentment_shipping_cost' => DB::raw('shipping_cost'),
            'presentment_tax' => DB::raw('tax'),
            'presentment_discount_amount' => DB::raw('discount_amount'),
            'presentment_total' => DB::raw('total'),
        ]);

        DB::table('order_items')->update([
            'presentment_unit_price' => DB::raw('unit_price'),
            'presentment_total' => DB::raw('total'),
        ]);
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['presentment_unit_price', 'presentment_total']);
            $table->decimal('unit_price', 10, 2)->change();
            $table->decimal('total', 10, 2)->change();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'base_currency', 'presentment_currency', 'exchange_rate',
                'presentment_subtotal', 'presentment_shipping_cost', 'presentment_tax',
                'presentment_discount_amount', 'presentment_total',
            ]);
            $table->decimal('discount_amount', 10, 2)->default(0)->change();
            $table->decimal('subtotal', 10, 2)->default(0)->change();
            $table->decimal('shipping_cost', 10, 2)->default(0)->change();
            $table->decimal('tax', 10, 2)->default(0)->change();
            $table->decimal('total', 10, 2)->default(0)->change();
        });
    }
};
