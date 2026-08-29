<?php

namespace App\Integrations\Definitions\Payment;

use App\Integrations\Capability;
use App\Integrations\CredentialField;
use App\Integrations\ProviderDefinition;
use App\Integrations\ProvidesDefinition;

class KakaoPay implements ProvidesDefinition
{
    public static function definition(): ProviderDefinition
    {
        return new ProviderDefinition(
            slug: 'kakaopay',
            group: 'payment',
            label: 'KakaoPay',
            driver: \App\Services\Payments\KakaoPayGateway::class,
            fields: [
                CredentialField::secret('secret_key', 'Secret Key', 'From the KakaoPay developer console'),
                CredentialField::optional('cid', 'CID', 'TC0ONETIME for testing'),
            ],
            capabilities: [
                Capability::HostedRedirect,
            ],
            currencies: ['KRW'],
            countries: ['KR'],
            docsUrl: 'https://developers.kakaopay.com/docs/payment/online/single-payment',
            hint: 'South Korea — hosted redirect. Chosen over Toss, which needs a client-side widget.',
            sort: 52,
        );
    }
}
