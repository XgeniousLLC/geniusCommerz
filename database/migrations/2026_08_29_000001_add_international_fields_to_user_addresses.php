<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_addresses', function (Blueprint $table) {
            // Existing rows are all Bangladeshi, so BD is the correct backfill value
            // rather than a placeholder.
            $table->char('country', 2)->default('BD')->after('phone');
            $table->string('address_line_2')->nullable()->after('address');
            $table->string('state')->nullable()->after('city');
            $table->string('postal_code', 20)->nullable()->after('state');
            $table->string('company')->nullable()->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('user_addresses', function (Blueprint $table) {
            $table->dropColumn(['country', 'address_line_2', 'state', 'postal_code', 'company']);
        });
    }
};
