<?php

use App\Contracts\SmsInterface;
use App\Integrations\ProviderRegistry;
use App\Models\Integration;
use App\Models\Product;
use App\Services\SmsService;
use App\Support\PhoneNumber;
use Illuminate\Support\Facades\Http;

it('normalises national input to E.164 using the country', function () {
    expect(PhoneNumber::toE164('01711111111', 'BD'))->toBe('+8801711111111')
        ->and(PhoneNumber::toE164('(555) 123-4567', 'US'))->toBe('+15551234567')
        ->and(PhoneNumber::toE164('07700 900123', 'GB'))->toBe('+447700900123')
        // already international, in three different notations
        ->and(PhoneNumber::toE164('+8801711111111'))->toBe('+8801711111111')
        ->and(PhoneNumber::toE164('00880 1711 111111'))->toBe('+8801711111111')
        ->and(PhoneNumber::toE164('8801711111111', 'BD'))->toBe('+8801711111111');
});

it('rejects input that cannot be a phone number', function () {
    expect(PhoneNumber::toE164('abc', 'BD'))->toBeNull()
        ->and(PhoneNumber::toE164('123', 'US'))->toBeNull()          // too short for E.164
        ->and(PhoneNumber::toE164('1234567890123456789', 'US'))->toBeNull()  // too long
        ->and(PhoneNumber::toE164('01711111111', null))->toBeNull()  // national with no country
        ->and(PhoneNumber::toE164('', 'BD'))->toBeNull();
});

it('converts back to the local form a domestic gateway expects', function () {
    expect(PhoneNumber::national('+8801711111111'))->toBe('01711111111')
        ->and(PhoneNumber::national('+447700900123'))->toBe('07700900123')
        // NANP has no trunk prefix
        ->and(PhoneNumber::national('+15551234567'))->toBe('5551234567');
});

it('rejects an unusable phone at checkout instead of failing later at the gateway', function () {
    $product = Product::create([
        'name' => 'P', 'slug' => 'p-'.uniqid(), 'status' => 'active',
        'price' => 100, 'shipping_included' => true,
    ]);

    $payload = fn (string $phone) => [
        'customer_name' => 'Buyer', 'customer_phone' => $phone,
        'customer_email' => 'b'.uniqid().'@example.com',
        'country' => 'US', 'address' => '1 Main St', 'city' => 'Austin',
        'state' => 'TX', 'postal_code' => '78701', 'payment_method' => 'cod',
        'items' => [['product_id' => $product->id, 'name' => 'P', 'price' => 100, 'quantity' => 1]],
    ];

    $this->post('/checkout', $payload('not-a-number'))->assertSessionHasErrors('customer_phone');
    $this->post('/checkout', $payload('555'))->assertSessionHasErrors('customer_phone');
    $this->post('/checkout', $payload('(555) 123-4567'))->assertSessionHasNoErrors();
});

it('hands every gateway a number in the shape it expects', function () {
    Http::fake();

    // Bangladeshi gateway: wants a local 01… number even though we store E.164
    Integration::create([
        'provider' => 'bulksmsbd', 'group' => 'sms', 'label' => 'BulkSMSBD',
        'credentials' => ['api_key' => 'k', 'sender_id' => 's'],
        'is_active' => true, 'is_default' => true,
    ]);

    app(SmsService::class)->send('+8801711111111', 'hello');

    Http::assertSent(fn ($request) => ($request['number'] ?? null) === '01711111111');
});

it('gives international gateways E.164 even from national input', function () {
    Http::fake(['*' => Http::response(['messages' => [['status' => '0']]])]);

    Integration::create([
        'provider' => 'vonage', 'group' => 'sms', 'label' => 'Vonage',
        'credentials' => ['api_key' => 'k', 'api_secret' => 's', 'from' => 'Shop'],
        'is_active' => true, 'is_default' => true, 'environment' => 'live',
    ]);

    // typed in national US form; the country tells us how to read it
    app(SmsService::class)->send('(555) 123-4567', 'hello', 'US');

    Http::assertSent(fn ($request) => ($request['to'] ?? null) === '15551234567');
});

it('registers the international gateways against the SMS contract', function () {
    $registry = app(ProviderRegistry::class);

    foreach (['vonage', 'messagebird', 'plivo', 'awssns'] as $slug) {
        expect($registry->driver($slug))->toBeInstanceOf(SmsInterface::class);
    }
});
