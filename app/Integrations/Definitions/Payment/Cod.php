<?php

namespace App\Integrations\Definitions\Payment;

use App\Integrations\ProviderDefinition;
use App\Integrations\ProvidesDefinition;

class Cod implements ProvidesDefinition
{
    public static function definition(): ProviderDefinition
    {
        return new ProviderDefinition(
            slug: 'cod',
            group: 'payment',
            label: 'Cash on Delivery',
            driver: \App\Services\Payments\CodGateway::class,
            fields: [],
            environments: ['live'],
            capabilities: [],
            hint: 'No credentials needed. Orders are created unpaid and collected on delivery.',
            sort: 1,
        );
    }
}
