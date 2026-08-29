<?php

namespace App\Integrations\Definitions\Payment;

use App\Integrations\Capability;
use App\Integrations\CredentialField;
use App\Integrations\ProviderDefinition;
use App\Integrations\ProvidesDefinition;

class AuthorizeNet implements ProvidesDefinition
{
    public static function definition(): ProviderDefinition
    {
        return new ProviderDefinition(
            slug: 'authorizenet',
            group: 'payment',
            label: 'Authorize.Net',
            driver: \App\Services\Payments\AuthorizeNetGateway::class,
            fields: [
                CredentialField::text('login_id', 'API Login ID'),
                CredentialField::secret('transaction_key', 'Transaction Key'),
                CredentialField::secret('signature_key', 'Signature Key', 'For webhook verification'),
            ],
            capabilities: [
                Capability::HostedRedirect,
                Capability::Webhook,
                Capability::Refund,
            ],
            currencies: ['USD', 'CAD', 'GBP', 'EUR', 'AUD', 'NZD'],
            docsUrl: 'https://developer.authorize.net/api/reference/features/accept_hosted.html',
            hint: 'Large US install base. Accept Hosted, via a signed browser form post.',
            sort: 42,
        );
    }
}
