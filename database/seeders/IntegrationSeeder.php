<?php

namespace Database\Seeders;

use App\Integrations\ProviderRegistry;
use App\Models\Integration;
use Illuminate\Database\Seeder;

/**
 * Integration rows are no longer seeded.
 *
 * The provider catalog lives in App\Integrations\Definitions and a row is created lazily
 * the first time a merchant saves credentials — pre-seeding a row per provider is what
 * forced every new provider to be registered in six places, and it does not scale to a
 * large gateway catalog.
 *
 * This seeder now only re-syncs the denormalised group/label columns onto rows that
 * already exist, so it stays safe to run at any time.
 */
class IntegrationSeeder extends Seeder
{
    public function run(): void
    {
        $registry = app(ProviderRegistry::class);
        $synced   = 0;

        foreach (Integration::all() as $integration) {
            $definition = $registry->find($integration->provider);

            if (! $definition) {
                continue;
            }

            $integration->fill([
                'group' => $definition->group,
                'label' => $definition->label,
            ]);

            if ($integration->isDirty()) {
                $integration->save();
                $synced++;
            }
        }

        $this->command?->info("Integrations synced from registry: {$synced} updated, ".count($registry->all()).' providers available.');
    }
}
