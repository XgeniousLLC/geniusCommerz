<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // A destination the merchant is registered to charge tax in. Matching is
        // country → state → postal pattern, most specific first, which is what US
        // state/county rates and EU per-country VAT both need.
        Schema::create('tax_zones', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->char('country', 2)->index();
            // Null means "the whole country" / "anywhere in the state".
            $table->string('state', 100)->nullable();
            // SQL LIKE pattern, e.g. '94%' for a San Francisco county rate.
            $table->string('postal_pattern', 40)->nullable();
            $table->unsignedSmallInteger('priority')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Several rates can apply in one zone (e.g. US state + county, or Canadian
        // GST + PST), and a zone charges a different rate per tax class.
        Schema::create('tax_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tax_zone_id')->constrained()->cascadeOnDelete();
            $table->string('name');                       // shown on the invoice: "VAT", "GST"
            $table->string('tax_class', 30)->default('standard');
            $table->decimal('rate', 7, 4)->default(0);    // percent, e.g. 20.0000
            $table->boolean('applies_to_shipping')->default(true);
            $table->unsignedSmallInteger('priority')->default(0);
            $table->timestamps();

            $table->index(['tax_zone_id', 'tax_class']);
        });

        Schema::table('products', function (Blueprint $table) {
            // Reduced and zero rates are why this cannot be a single store-wide
            // percentage — EU food, books and children's clothing differ from standard.
            $table->string('tax_class', 30)->default('standard')->after('price');
        });

        Schema::table('orders', function (Blueprint $table) {
            // What was charged, per rate, frozen at order time. The invoice reads this
            // rather than recomputing, so the invoice and the charge cannot disagree.
            $table->json('tax_breakdown')->nullable()->after('tax');
            $table->boolean('prices_include_tax')->default(false)->after('tax_breakdown');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->decimal('tax_amount', 12, 4)->default(0)->after('total');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', fn (Blueprint $t) => $t->dropColumn('tax_amount'));
        Schema::table('orders', fn (Blueprint $t) => $t->dropColumn(['tax_breakdown', 'prices_include_tax']));
        Schema::table('products', fn (Blueprint $t) => $t->dropColumn('tax_class'));
        Schema::dropIfExists('tax_rates');
        Schema::dropIfExists('tax_zones');
    }
};
