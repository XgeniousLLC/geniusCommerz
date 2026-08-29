<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Same country → state → postal shape as tax zones, so both resolve a destination
        // the same way and "most specific wins" means one thing across the system.
        Schema::create('shipping_zones', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->char('country', 2)->index();
            $table->string('state', 100)->nullable();
            $table->string('postal_pattern', 40)->nullable();
            $table->unsignedSmallInteger('priority')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('shipping_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipping_zone_id')->constrained()->cascadeOnDelete();
            $table->string('name');                             // shown to the customer: "Standard"
            $table->decimal('price', 12, 4)->default(0);        // base cost for the band
            $table->decimal('per_kg', 12, 4)->default(0);       // added per kg above min_weight

            // Bands. Null means unbounded on that side.
            $table->decimal('min_weight', 10, 3)->nullable();
            $table->decimal('max_weight', 10, 3)->nullable();
            $table->decimal('min_subtotal', 12, 4)->nullable();
            $table->decimal('max_subtotal', 12, 4)->nullable();

            $table->decimal('free_above', 12, 4)->nullable();   // free when subtotal reaches this
            $table->string('delivery_estimate')->nullable();    // "3-5 business days"
            $table->unsignedSmallInteger('priority')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['shipping_zone_id', 'priority']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('shipping_method')->nullable()->after('shipping_cost');
        });

        Schema::table('products', function (Blueprint $table) {
            // Required on customs paperwork for cross-border shipments.
            $table->string('hs_code', 20)->nullable()->after('weight');
            $table->char('country_of_origin', 2)->nullable()->after('hs_code');
            // Carriers price on volumetric weight when a parcel is bulky but light.
            $table->decimal('length', 8, 2)->nullable()->after('country_of_origin');
            $table->decimal('width', 8, 2)->nullable()->after('length');
            $table->decimal('height', 8, 2)->nullable()->after('width');
        });

        // The Dhaka rate settings were written by the settings form but never read by any
        // calculation. Zones replace them, so remove the rows rather than leave a UI that
        // appears to control pricing and does not.
        DB::table('site_settings')->whereIn('key', [
            'shipping.rate_inside_dhaka', 'shipping.rate_outside_dhaka',
            'shipping.delivery_inside_dhaka', 'shipping.delivery_outside_dhaka',
        ])->delete();
    }

    public function down(): void
    {
        Schema::table('products', fn (Blueprint $t) => $t->dropColumn(['hs_code', 'country_of_origin', 'length', 'width', 'height']));
        Schema::table('orders', fn (Blueprint $t) => $t->dropColumn('shipping_method'));
        Schema::dropIfExists('shipping_rates');
        Schema::dropIfExists('shipping_zones');
    }
};
