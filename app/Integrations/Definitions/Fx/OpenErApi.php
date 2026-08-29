<?php

namespace App\Integrations\Definitions\Fx;

use App\Integrations\ProviderDefinition;
use App\Integrations\ProvidesDefinition;

class OpenErApi implements ProvidesDefinition
{
    public static function definition(): ProviderDefinition
    {
        return new ProviderDefinition(
            slug: 'open_er_api',
            group: 'fx',
            label: 'Open Exchange Rates (free)',
            driver: \App\Services\Fx\OpenErApiDriver::class,
            fields: [],
            environments: ['live'],
            docsUrl: 'https://www.exchangerate-api.com/docs/free',
            hint: 'No API key needed. Updates once daily — fine for retail pricing.',
            sort: 10,
        );
    }
}
