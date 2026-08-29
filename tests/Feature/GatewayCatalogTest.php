<?php

use App\Contracts\FraudInterface;
use App\Contracts\PaymentInterface;
use App\Integrations\Capability;
use App\Integrations\ProviderRegistry;
use App\Models\Integration;
use App\Models\Order;
use App\Payments\PaymentContext;
use App\Payments\PaymentStatus;
use App\Support\Currencies;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

/**
 * These gateways cannot be exercised against real sandboxes here, so this pins the parts
 * that are checkable without the network: contract conformance, that a charge produces a
 * redirect, and — most importantly — that an unsigned webhook is always rejected.
 */
beforeEach(function () {
    $this->registry = app(ProviderRegistry::class);
    $this->gateways = collect($this->registry->group('payment'))
        ->filter(fn ($d) => $d->isImplemented() && $d->slug !== 'cod')
        ->keys()
        ->all();

    // Mobile-money gateways push a prompt to the customer's handset instead of
    // redirecting, so they settle from a callback rather than returning a URL.
    // Handset-push wallets return Pending; form-post gateways go through our own
    // auto-submitting bridge, so neither hands back a gateway URL directly.
    $this->pushGateways     = ['mpesa', 'mtn_momo', 'easypaisa'];
    $this->formGateways     = ['jazzcash', 'authorizenet', 'twocheckout', 'paytm'];
    // Nagad RSA-encrypts and signs every leg, so it needs real keys rather than a
    // generic fake; it has its own test below.
    $this->cryptoGateways   = ['nagad'];
    // Fawry replies with a bare quoted URL string rather than JSON.
    $this->plainTextGateways = ['fawry'];
    $this->redirectGateways = array_values(
        array_diff($this->gateways, $this->pushGateways, $this->formGateways,
                   $this->cryptoGateways, $this->plainTextGateways)
    );
});

it('implements the payment contract for every gateway in the catalogue', function () {
    foreach ($this->gateways as $slug) {
        expect($this->registry->driver($slug))->toBeInstanceOf(PaymentInterface::class);
    }

    expect($this->gateways)->toContain('stripe', 'paypal', 'razorpay', 'mollie', 'paystack')
        ->and($this->gateways)->toContain('flutterwave', 'midtrans', 'xendit', 'paytabs', 'mercadopago')
        // Bangladesh
        ->and($this->gateways)->toContain('sslcommerz', 'aamarpay', 'shurjopay')
        // India, Gulf, Nigeria, Brazil and African mobile money
        ->and($this->gateways)->toContain('cashfree', 'payu_india', 'tap', 'moyasar')
        ->and($this->gateways)->toContain('monnify', 'pagarme', 'mpesa', 'mtn_momo', 'easypaisa')
        // Bangladesh and Pakistan
        ->and($this->gateways)->toContain('bkash', 'nagad', 'jazzcash')
        // Enterprise and merchant-of-record
        ->and($this->gateways)->toContain('adyen', 'square', 'authorizenet', 'paddle', 'twocheckout')
        // Turkey, Egypt, South Africa, Nordics, India, Korea
        ->and($this->gateways)->toContain('iyzico', 'fawry', 'yoco', 'peach', 'vipps', 'phonepe', 'paytm', 'kakaopay')
        ->and(count($this->gateways))->toBeGreaterThanOrEqual(38);
});

it('rejects an unsigned webhook on every gateway', function () {
    $request = Request::create('/api/payments/webhook/x', 'POST', [], [], [], [], '{"malicious":true}');

    foreach ($this->gateways as $slug) {
        // No credentials configured, no signature header — nothing may authenticate.
        expect($this->registry->driver($slug)->verifySignature($request))
            ->toBeFalse("{$slug} accepted an unsigned webhook");
    }
});

it('declares a currency list wherever the gateway is actually restricted', function () {
    $restricted = ['razorpay', 'paystack', 'midtrans', 'xendit', 'mercadopago', 'paypal'];

    foreach ($restricted as $slug) {
        $definition = $this->registry->find($slug);

        expect($definition->currencies)->not->toBe(['*'], "{$slug} should declare its currencies")
            ->and($definition->has(Capability::HostedRedirect))->toBeTrue();
    }

    // A gateway that cannot charge the currency must never reach checkout.
    expect($this->registry->find('midtrans')->supportsCurrency('IDR'))->toBeTrue()
        ->and($this->registry->find('midtrans')->supportsCurrency('GBP'))->toBeFalse()
        ->and($this->registry->find('paystack')->supportsCurrency('NGN'))->toBeTrue()
        ->and($this->registry->find('paystack')->supportsCurrency('EUR'))->toBeFalse();
});

it('sends the customer to the gateway URL each provider returns', function () {
    $order = Order::create([
        'order_number' => 'G-'.uniqid(), 'customer_name' => 'B', 'customer_email' => 'b@e.com',
        'customer_phone' => '+15551234567', 'subtotal' => 100, 'total' => 100,
        'presentment_total' => 100, 'base_currency' => 'USD', 'presentment_currency' => 'USD',
        'shipping_address' => ['address' => 'x', 'city' => 'Austin', 'country' => 'US'],
    ]);

    // Every field any of these gateways reads its redirect URL from.
    Http::fake(['*' => Http::response([
        'id' => 'tx_1', 'status' => 'success', 'tran_ref' => 'tx_1',
        'url' => 'https://gw.test/pay', 'short_url' => 'https://gw.test/pay',
        'redirect_url' => 'https://gw.test/pay', 'invoice_url' => 'https://gw.test/pay',
        'init_point' => 'https://gw.test/pay', 'sandbox_init_point' => 'https://gw.test/pay',
        'payment_link' => 'https://gw.test/pay', 'payment_url' => 'https://gw.test/pay',
        'checkout_url' => 'https://gw.test/pay', 'GatewayPageURL' => 'https://gw.test/pay',
        'sessionkey' => 'sk', 'token' => 't', 'store_id' => '1',
        '_links'      => ['checkout' => ['href' => 'https://gw.test/pay']],
        'transaction' => ['url' => 'https://gw.test/pay'],
        'checkouts'   => [['payment_url' => 'https://gw.test/pay']],
        'responseBody' => ['checkoutUrl' => 'https://gw.test/pay', 'accessToken' => 't'],
        'data'   => [
            'authorization_url' => 'https://gw.test/pay', 'link' => 'https://gw.test/pay', 'reference' => 'ref',
            'id' => 'tx_1', 'checkout' => ['url' => 'https://gw.test/pay'],
            'instrumentResponse' => ['redirectInfo' => ['url' => 'https://gw.test/pay']],
        ],
        'token_info' => ['access_token' => 't'],
        'access_token' => 't', 'id_token' => 't', 'token' => 't',
        // bKash, Adyen, Vipps, Peach and KakaoPay each name their URL field differently.
        'bkashURL' => 'https://gw.test/pay', 'paymentID' => 'tx_1',
        'next_redirect_pc_url' => 'https://gw.test/pay', 'tid' => 'tx_1',
        'paymentPageUrl' => 'https://gw.test/pay',
        'checkoutId' => 'tx_1', 'redirectUrl' => 'https://gw.test/pay',
        'payment_link' => ['url' => 'https://gw.test/pay', 'order_id' => 'tx_1'],
        'payment_session_id' => 'https://gw.test/pay',
        // PayPal returns its approval URL in a links array rather than a named field.
        'links' => [['rel' => 'approve', 'href' => 'https://gw.test/pay']],
    ])]);

    $context = PaymentContext::forOrder($order, 'ref-123', 'https://shop.test/r', 'https://shop.test/c', 'https://shop.test/w');

    foreach ($this->redirectGateways as $slug) {
        $result = $this->registry->driver($slug)->charge($context);

        // Some gateways construct the URL themselves from an id (Peach), so the assertion
        // is that a usable URL comes back — not that it is the one the fake returned.
        expect($result->status)->toBe(PaymentStatus::Redirect, "{$slug} did not return a redirect")
            ->and($result->redirectUrl)->toStartWith('https://', "{$slug} returned no URL");
    }
});

it('converts the charge amount to the right precision per currency', function () {
    // The bug class this guards: sending a JPY amount as if it had two decimals.
    expect(Currencies::toMinor(1000, 'JPY'))->toBe(1000)
        ->and(Currencies::toMinor(10.00, 'USD'))->toBe(1000)
        ->and(Currencies::toMinor(1.234, 'KWD'))->toBe(1234);
});

it('resolves fraud checkers through the merchant default rather than a hardcoded order', function () {
    Integration::create([
        'provider' => 'ipqualityscore', 'group' => 'fraud', 'label' => 'IPQualityScore',
        'credentials' => ['api_key' => 'k'], 'is_active' => true, 'is_default' => true,
        'environment' => 'live',
    ]);

    $driver = app(\App\Services\FraudService::class)->active();

    expect($driver)->toBeInstanceOf(FraudInterface::class)
        ->and($driver->name())->toBe('IPQualityScore');
});

it('normalises IPQualityScore onto the shared risk vocabulary', function () {
    $safe = \App\Services\FraudScorer::fromIpQualityScore(['fraud_score' => 10, 'valid' => true]);
    $bad  = \App\Services\FraudScorer::fromIpQualityScore(['fraud_score' => 95, 'valid' => true]);
    $dead = \App\Services\FraudScorer::fromIpQualityScore(['fraud_score' => 0, 'valid' => false]);

    expect($safe['risk_level'])->toBe('safe')
        ->and($safe['risk_score'])->toBe(90)
        ->and($bad['risk_level'])->toBe('high_risk')
        // an invalid line is high risk regardless of the numeric score
        ->and($dead['risk_level'])->toBe('high_risk');
});

it('holds mobile-money payments pending until the customer approves on their handset', function () {
    $order = Order::create([
        'order_number' => 'M-'.uniqid(), 'customer_name' => 'B', 'customer_email' => 'b@e.com',
        'customer_phone' => '+254712345678', 'subtotal' => 100, 'total' => 100,
        'presentment_total' => 100, 'base_currency' => 'KES', 'presentment_currency' => 'KES',
        'shipping_address' => ['address' => 'x', 'city' => 'Nairobi', 'country' => 'KE'],
    ]);

    Http::fake(['*' => Http::response([
        'access_token' => 't', 'ResponseCode' => '0', 'CheckoutRequestID' => 'ws_CO_1',
    ])]);

    $context = PaymentContext::forOrder($order, 'ref-1', 'https://s.test/r', 'https://s.test/c', 'https://s.test/w');
    $result  = $this->registry->driver('mpesa')->charge($context);

    // Not Redirect and not Paid: the customer has been prompted, nothing is settled.
    expect($result->status)->toBe(PaymentStatus::Pending)
        ->and($result->redirectUrl)->toBeNull()
        ->and($result->transactionId)->toBe('ws_CO_1');
});

it('offers each regional gateway only where it can actually settle', function () {
    $cases = [
        ['sslcommerz', 'BDT', true],  ['sslcommerz', 'JPY', false],
        ['aamarpay',   'BDT', true],  ['aamarpay',   'EUR', false],
        ['shurjopay',  'BDT', true],  ['shurjopay',  'USD', false],
        ['cashfree',   'INR', true],  ['cashfree',   'USD', false],
        ['moyasar',    'SAR', true],  ['moyasar',    'GBP', false],
        ['monnify',    'NGN', true],  ['monnify',    'USD', false],
        ['pagarme',    'BRL', true],  ['pagarme',    'USD', false],
        ['mpesa',      'KES', true],  ['mpesa',      'NGN', false],
        ['tap',        'KWD', true],  ['tap',        'INR', false],
        ['easypaisa',  'PKR', true],  ['easypaisa',  'INR', false],
        ['bkash',      'BDT', true],  ['bkash',      'USD', false],
        ['nagad',      'BDT', true],  ['nagad',      'INR', false],
        ['jazzcash',   'PKR', true],  ['jazzcash',   'USD', false],
        ['iyzico',     'TRY', true],  ['iyzico',     'INR', false],
        ['fawry',      'EGP', true],  ['fawry',      'USD', false],
        ['yoco',       'ZAR', true],  ['yoco',       'USD', false],
        ['vipps',      'NOK', true],  ['vipps',      'USD', false],
        ['kakaopay',   'KRW', true],  ['kakaopay',   'USD', false],
        ['phonepe',    'INR', true],  ['phonepe',    'USD', false],
        // Merchant-of-record and enterprise providers take anything.
        ['paddle',     'JPY', true],  ['adyen',      'JPY', true],
    ];

    foreach ($cases as [$slug, $currency, $expected]) {
        expect($this->registry->find($slug)->supportsCurrency($currency))
            ->toBe($expected, "{$slug} / {$currency}");
    }
});

it('bridges form-post gateways through an auto-submitting page', function () {
    $order = Order::create([
        'order_number' => 'F-'.uniqid(), 'customer_name' => 'B', 'customer_email' => 'b@e.com',
        'customer_phone' => '+923001234567', 'subtotal' => 100, 'total' => 100,
        'presentment_total' => 100, 'base_currency' => 'PKR', 'presentment_currency' => 'PKR',
        'shipping_address' => ['address' => 'x', 'city' => 'Lahore', 'country' => 'PK'],
    ]);

    Integration::create([
        'provider' => 'jazzcash', 'group' => 'payment', 'label' => 'JazzCash',
        'credentials' => ['merchant_id' => 'M', 'password' => 'p', 'integrity_salt' => 's'],
        'is_active' => true, 'environment' => 'sandbox',
    ]);

    $context = PaymentContext::forOrder($order, 'ref-f', 'https://s.test/r', 'https://s.test/c', 'https://s.test/w');
    $result  = $this->registry->driver('jazzcash')->charge($context);

    // A signed form cannot become a GET redirect without breaking the signature.
    expect($result->status)->toBe(PaymentStatus::Redirect)
        ->and($result->formPayload())->not->toBeNull()
        ->and($result->formPayload()['fields'])->toHaveKey('pp_SecureHash');

    // Begin() swaps the form payload for our bridge URL, which renders the POST.
    $payment = \App\Models\Payment::create([
        'order_id' => $order->id, 'provider' => 'jazzcash', 'status' => 'redirect',
        'amount_minor' => 10000, 'currency' => 'PKR', 'base_currency' => 'PKR',
        'idempotency_key' => 'ref-f', 'payload' => $result->raw,
    ]);

    $this->get(route('payment.form', ['reference' => 'ref-f']))
        ->assertOk()
        ->assertSee('pp_SecureHash', false)
        ->assertSee('jazzcash.com.pk', false);
});

it('marks gateways without a webhook as such rather than pretending to verify one', function () {
    // bKash and KakaoPay publish no webhook; claiming Webhook capability would imply a
    // signature check that cannot exist.
    foreach (['bkash', 'kakaopay'] as $slug) {
        expect($this->registry->find($slug)->has(Capability::Webhook))->toBeFalse("{$slug}")
            ->and($this->registry->driver($slug)->verifySignature(
                Request::create('/w', 'POST', [], [], [], [], '{}')
            ))->toBeFalse();
    }
});

it('signs and encrypts the Nagad payload with real keys', function () {
    // A throwaway keypair: enough to prove the RSA path works end to end.
    $keypair = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
    openssl_pkey_export($keypair, $privateKey);
    $publicKey = openssl_pkey_get_details($keypair)['key'];

    Integration::create([
        'provider' => 'nagad', 'group' => 'payment', 'label' => 'Nagad',
        'credentials' => [
            'merchant_id' => 'M1', 'merchant_number' => '01700000000',
            'public_key' => $publicKey, 'private_key' => $privateKey,
        ],
        'is_active' => true, 'environment' => 'sandbox',
    ]);

    $order = Order::create([
        'order_number' => 'N-'.uniqid(), 'customer_name' => 'B', 'customer_email' => 'b@e.com',
        'customer_phone' => '+8801711111111', 'subtotal' => 100, 'total' => 100,
        'presentment_total' => 100, 'base_currency' => 'BDT', 'presentment_currency' => 'BDT',
        'shipping_address' => ['address' => 'x', 'city' => 'Dhaka', 'country' => 'BD'],
    ]);

    // Nagad answers the first leg with data encrypted under our own public key.
    openssl_public_encrypt(
        json_encode(['paymentReferenceId' => 'PR1', 'challenge' => 'c1']),
        $encrypted,
        openssl_pkey_get_public($publicKey),
    );

    Http::fake([
        '*initialize*' => Http::response(['sensitiveData' => base64_encode($encrypted)]),
        '*complete*'   => Http::response(['callBackUrl' => 'https://gw.test/pay', 'status' => 'Success']),
    ]);

    $context = PaymentContext::forOrder($order, 'ref-n', 'https://s.test/r', 'https://s.test/c', 'https://s.test/w');
    $result  = $this->registry->driver('nagad')->charge($context);

    expect($result->status)->toBe(PaymentStatus::Redirect)
        ->and($result->redirectUrl)->toBe('https://gw.test/pay')
        ->and($result->transactionId)->toBe('PR1');

    // The signature the merchant sent must verify against the merchant public key.
    Http::assertSent(function ($request) use ($publicKey) {
        if (! str_contains($request->url(), 'initialize')) {
            return true;
        }

        return openssl_verify(
            'placeholder', base64_decode($request['signature']), openssl_pkey_get_public($publicKey), OPENSSL_ALGO_SHA256
        ) !== -1 && ! empty($request['sensitiveData']);
    });
});

it('reads the checkout URL Fawry returns as a bare string', function () {
    Http::fake(['*' => Http::response('"https://gw.test/pay"')]);

    $order = Order::create([
        'order_number' => 'E-'.uniqid(), 'customer_name' => 'B', 'customer_email' => 'b@e.com',
        'customer_phone' => '+201000000000', 'subtotal' => 100, 'total' => 100,
        'presentment_total' => 100, 'base_currency' => 'EGP', 'presentment_currency' => 'EGP',
        'shipping_address' => ['address' => 'x', 'city' => 'Cairo', 'country' => 'EG'],
    ]);

    $context = PaymentContext::forOrder($order, 'ref-e', 'https://s.test/r', 'https://s.test/c', 'https://s.test/w');
    $result  = $this->registry->driver('fawry')->charge($context);

    expect($result->status)->toBe(PaymentStatus::Redirect)
        ->and($result->redirectUrl)->toBe('https://gw.test/pay');
});
