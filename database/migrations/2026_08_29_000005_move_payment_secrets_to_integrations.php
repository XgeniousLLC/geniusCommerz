<?php

use App\Models\Integration;
use App\Models\SiteSetting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Payment credentials had two homes: encrypted in integrations.credentials, and in
 * plaintext site_settings rows under payment.*.
 *
 * Worse, SettingsController::update() only blank-protects storage.*, so re-saving the
 * Payment settings tab with empty password fields wiped whatever was stored there. This
 * moves anything still in site_settings into the encrypted store and removes the
 * duplicate keys so there is exactly one home for a secret.
 */
return new class extends Migration
{
    private const MOVES = [
        'sslcommerz' => [
            'payment.sslcommerz_store_id'       => 'store_id',
            'payment.sslcommerz_store_password' => 'store_password',
        ],
        'stripe' => [
            'payment.stripe_sk' => 'secret_key',
        ],
    ];

    private const ENABLE_FLAGS = [
        'payment.cod_enabled'        => 'cod',
        'payment.stripe_enabled'     => 'stripe',
        'payment.sslcommerz_enabled' => 'sslcommerz',
        'payment.bkash_enabled'      => 'bkash',
        'payment.nagad_enabled'      => 'nagad',
    ];

    /** Removed outright: never consumed, or superseded by integrations.is_active. */
    private const DROP = [
        'payment.stripe_pk', 'payment.sslcommerz_sandbox', 'payment.rocket_enabled',
        'payment.bkash_number', 'payment.nagad_number', 'payment.rocket_number',
    ];

    public function up(): void
    {
        foreach (self::MOVES as $provider => $map) {
            $values = [];

            foreach ($map as $settingKey => $credentialKey) {
                $value = SiteSetting::where('key', $settingKey)->value('value');

                if ($value !== null && $value !== '') {
                    $values[$credentialKey] = $value;
                }
            }

            if ($values === []) {
                continue;
            }

            $integration = Integration::forSlug($provider);
            // These were live production keys, so they belong to the live environment.
            $integration->environment = 'live';
            $integration->mergeCredentials(
                $values,
                array_fill_keys(array_keys($values), true),
                'live',
            );
            $integration->save();
        }

        // Carry the enable toggles over to the integration rows they now live on.
        foreach (self::ENABLE_FLAGS as $settingKey => $provider) {
            if (! SiteSetting::where('key', $settingKey)->value('value')) {
                continue;
            }

            $integration = Integration::forSlug($provider);
            $integration->is_active = true;
            $integration->save();
        }

        $stale = array_merge(
            array_merge(...array_map('array_keys', array_values(self::MOVES))),
            array_keys(self::ENABLE_FLAGS),
            self::DROP,
        );

        SiteSetting::whereIn('key', $stale)->delete();

        DB::table('cache')->where('key', 'like', '%site_settings_payment%')->delete();
    }

    public function down(): void
    {
        // Deliberately not reversed: putting plaintext secrets back into site_settings
        // would undo the point of the migration.
    }
};
