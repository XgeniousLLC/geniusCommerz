<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('currencies', function (Blueprint $table) {
            // ISO 4217 minor-unit exponent, so admin-added currencies carry their own
            // precision rather than everything being assumed to have two decimals.
            $table->unsignedTinyInteger('exponent')->default(2)->after('symbol');

            // Only api-sourced, unlocked rows are touched by the refresh job — existing
            // manually-entered rates keep working untouched.
            $table->string('rate_source', 10)->default('manual')->after('rate');
            $table->timestamp('rate_updated_at')->nullable()->after('rate_source');
            $table->boolean('rate_locked')->default(false)->after('rate_updated_at');

            // Covers FX spread and cross-currency gateway fees; baked into the rate the
            // customer is quoted at, and therefore into the order's frozen exchange_rate.
            $table->decimal('rate_markup_percent', 5, 2)->default(0)->after('rate_locked');
        });

        foreach (DB::table('currencies')->get() as $currency) {
            DB::table('currencies')->where('id', $currency->id)->update([
                'exponent' => \App\Support\Currencies::exponent($currency->code),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('currencies', function (Blueprint $table) {
            $table->dropColumn(['exponent', 'rate_source', 'rate_updated_at', 'rate_locked', 'rate_markup_percent']);
        });
    }
};
