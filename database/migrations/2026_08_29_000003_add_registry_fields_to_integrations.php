<?php

use App\Integrations\ProviderRegistry;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('integrations', function (Blueprint $table) {
            // Lets "all payment integrations" and defaultFor('courier') be plain SQL,
            // replacing the in_array chains that used the model's class constants.
            $table->string('group', 20)->default('other')->index()->after('provider');

            // Couriers/SMS/AI/fraud have one default; payments have many active gateways
            // in a customer-visible order, which is what this orders.
            $table->unsignedSmallInteger('sort_order')->default(0)->after('is_default');

            // Nullable overrides of the definition's own lists. Null means "trust the
            // definition" — a row may narrow availability, never widen it.
            $table->json('countries')->nullable()->after('environment');
            $table->json('currencies')->nullable()->after('countries');

            // Non-secret per-provider config (checkout title, description, surcharge).
            // Kept out of `credentials` so it is not dragged through Crypt on every read.
            $table->json('settings')->nullable()->after('notes');
        });

        $registry = new ProviderRegistry();

        foreach ($registry->all() as $slug => $definition) {
            DB::table('integrations')
                ->where('provider', $slug)
                ->update(['group' => $definition->group, 'sort_order' => $definition->sort]);
        }
    }

    public function down(): void
    {
        Schema::table('integrations', function (Blueprint $table) {
            $table->dropIndex(['group']);
            $table->dropColumn(['group', 'sort_order', 'countries', 'currencies', 'settings']);
        });
    }
};
