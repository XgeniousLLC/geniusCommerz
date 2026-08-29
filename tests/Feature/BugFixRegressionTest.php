<?php

use App\Models\Admin;
use App\Models\Integration;
use App\Models\Order;
use App\Models\Product;
use App\Models\SiteSetting;
use App\Services\FraudBdService;
use Illuminate\Support\Facades\Http;

it('does not fall back to a hardcoded API key when FraudBD is unconfigured', function () {
    Http::fake();

    $service = new FraudBdService();

    expect($service->isConfigured())->toBeFalse()
        ->and($service->check('01711111111'))->toHaveKey('error');

    // It must not fire a doomed request with a borrowed key.
    Http::assertNothingSent();
});

it('does not send international customers to the Bangladesh fraud API', function () {
    Integration::create([
        'provider' => 'bdcourier', 'group' => 'fraud', 'label' => 'BDCourier',
        'credentials' => ['api_key' => 'k'], 'is_active' => true, 'is_default' => true,
    ]);
    SiteSetting::updateOrCreate(['key' => 'tracking.meta_pixel_id'], ['value' => '123', 'type' => 'text', 'group' => 'tracking']);

    Http::fake();

    $product = Product::create([
        'name' => 'P', 'slug' => 'p-'.uniqid(), 'status' => 'active',
        'price' => 100, 'shipping_included' => true,
    ]);

    $this->post('/checkout', [
        'customer_name' => 'Buyer', 'customer_phone' => '15551234567',
        'customer_email' => 'us@example.com',
        'country' => 'US', 'address' => '1 Main St', 'city' => 'Austin',
        'state' => 'TX', 'postal_code' => '78701',
        'payment_method' => 'cod',
        'items' => [['product_id' => $product->id, 'name' => 'P', 'price' => 100, 'quantity' => 1]],
    ])->assertSessionHasNoErrors();

    Http::assertNotSent(fn ($request) => str_contains($request->url(), 'bdcourier.com'));
});

it('passes the admin COD override through to every courier', function () {
    $admin = Admin::factory()->create();

    Integration::create([
        'provider' => 'pathao', 'group' => 'courier', 'label' => 'Pathao',
        'credentials' => ['client_id' => 'a', 'client_secret' => 'b', 'username' => 'u', 'password' => 'p'],
        'is_active' => true, 'is_default' => true,
    ]);

    $order = Order::create([
        'order_number' => 'T-'.uniqid(), 'customer_name' => 'B', 'customer_email' => 'b@e.com',
        'customer_phone' => '01711111111', 'subtotal' => 1000, 'total' => 1000,
        'base_currency' => 'BDT', 'presentment_currency' => 'BDT',
        'shipping_address' => ['address' => 'x', 'city' => 'Dhaka', 'country' => 'BD'],
    ]);

    Http::fake([
        '*issue-token*' => Http::response(['token_info' => ['access_token' => 't', 'expires_in' => 3000]]),
        '*'             => Http::response(['data' => ['consignment_id' => 'C1', 'delivery_fee' => 60]]),
    ]);

    $this->actingAs($admin, 'admin')->post(route('admin.orders.dispatch-courier', $order), [
        'city_id' => 1, 'zone_id' => 2, 'cod_amount' => 250,
    ]);

    // The form field is cod_amount; Pathao reads amount_to_collect, so the override used
    // to be dropped and the full order total collected instead.
    Http::assertSent(function ($request) {
        return ! str_contains($request->url(), 'issue-token')
            && ($request['amount_to_collect'] ?? null) == 250;
    });
});

it('reads a boolean setting the same way whichever type the row was written with', function () {
    SiteSetting::updateOrCreate(['key' => 'a.flag'], ['value' => '1', 'type' => 'text', 'group' => 'a']);
    SiteSetting::updateOrCreate(['key' => 'b.flag'], ['value' => '1', 'type' => 'boolean', 'group' => 'b']);

    expect(SiteSetting::bool('a.flag'))->toBeTrue()
        ->and(SiteSetting::bool('b.flag'))->toBeTrue()
        ->and(SiteSetting::bool('missing.flag'))->toBeFalse()
        ->and(SiteSetting::bool('missing.flag', true))->toBeTrue();
});
