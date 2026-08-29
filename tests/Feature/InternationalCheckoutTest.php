<?php

use App\Models\Order;
use App\Models\Product;
use App\Models\SiteSetting;
use App\Support\Countries;

function intlProduct(): Product
{
    return Product::create([
        'name' => 'Test Product',
        'slug' => 'test-product-'.uniqid(),
        'status' => 'active',
        'price' => 100,
        'shipping_included' => true, // keeps shipping at 0 so totals are easy to assert
    ]);
}

function checkoutPayload(Product $product, array $overrides = []): array
{
    return array_merge([
        'customer_name' => 'Test Buyer',
        'customer_phone' => '5551234567',
        'customer_email' => 'buyer'.uniqid().'@example.com',
        'payment_method' => 'cod',
        'items' => [[
            'product_id' => $product->id,
            'name' => $product->name,
            'price' => 100,
            'quantity' => 2,
        ]],
    ], $overrides);
}

it('persists a US address with state and ZIP', function () {
    $product = intlProduct();

    $this->post('/checkout', checkoutPayload($product, [
        'country' => 'US',
        'address' => '1600 Amphitheatre Parkway',
        'city' => 'Mountain View',
        'state' => 'CA',
        'postal_code' => '94043',
    ]))->assertSessionHasNoErrors();

    $order = Order::latest('id')->first();

    expect($order->shipping_address)->toMatchArray([
        'address' => '1600 Amphitheatre Parkway',
        'city' => 'Mountain View',
        'state' => 'CA',
        'postal_code' => '94043',
        'country' => 'US',
    ]);

    // billing was never populated by the storefront before; it now mirrors shipping
    expect($order->billing_address)->toBe($order->shipping_address);
});

it('persists a UK address with no state and an alphanumeric postcode', function () {
    $product = intlProduct();

    $this->post('/checkout', checkoutPayload($product, [
        'country' => 'GB',
        'address' => '10 Downing Street',
        'city' => 'London',
        'postal_code' => 'SW1A 2AA',
    ]))->assertSessionHasNoErrors();

    $order = Order::latest('id')->first();

    expect($order->shipping_address['country'])->toBe('GB')
        ->and($order->shipping_address['postal_code'])->toBe('SW1A 2AA')
        ->and($order->shipping_address['state'])->toBeNull();
});

it('requires a postal code only where the country actually uses one', function () {
    $product = intlProduct();

    // US requires it
    $this->post('/checkout', checkoutPayload($product, [
        'country' => 'US', 'address' => '1 Main St', 'city' => 'Austin', 'state' => 'TX',
    ]))->assertSessionHasErrors('postal_code');

    // the UAE has no postal system at all, so requiring one would make checkout impossible
    $this->post('/checkout', checkoutPayload($product, [
        'country' => 'AE', 'address' => 'Sheikh Zayed Road', 'city' => 'Dubai',
    ]))->assertSessionHasNoErrors();

    expect(Order::latest('id')->first()->shipping_address['country'])->toBe('AE');
});

it('rejects a country code that is not a real country', function () {
    $product = intlProduct();

    $this->post('/checkout', checkoutPayload($product, [
        'country' => 'ZZ', 'address' => '1 Main St', 'city' => 'Nowhere', 'postal_code' => '00000',
    ]))->assertSessionHasErrors('country');
});

it('records base currency, presentment currency and the rate on the order', function () {
    $product = intlProduct();

    $this->post('/checkout', checkoutPayload($product, [
        'country' => 'US', 'address' => '1 Main St', 'city' => 'Austin',
        'state' => 'TX', 'postal_code' => '78701',
    ]))->assertSessionHasNoErrors();

    $order = Order::latest('id')->first();
    $base = SiteSetting::get('general.currency', 'BDT');

    // Multi-currency is off by default, so presentment must fall back to base at rate 1
    // rather than silently recording a converted figure.
    expect($order->base_currency)->toBe($base)
        ->and($order->presentment_currency)->toBe($base)
        ->and((float) $order->exchange_rate)->toBe(1.0)
        ->and((float) $order->presentment_total)->toBe((float) $order->total);

    expect((float) $order->items()->first()->presentment_unit_price)
        ->toBe((float) $order->items()->first()->unit_price);
});

it('exposes every country to the checkout page with subdivisions where they matter', function () {
    expect(Countries::exists('US'))->toBeTrue()
        ->and(Countries::subdivisions('US'))->toHaveKey('CA')
        ->and(Countries::subdivisions('GB'))->toBeEmpty()
        ->and(Countries::requiresPostalCode('AE'))->toBeFalse()
        ->and(Countries::hasPostalCode('AE'))->toBeFalse()
        ->and(Countries::dial('GB'))->toBe('44');
});

it('passes the country list and store country to the checkout page', function () {
    $product = intlProduct();

    $this->withSession(['cart' => []])
        ->get('/checkout')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            // second arg skips Inertia's page-file lookup: this app keeps pages in
            // resources/js/storefront/pages, not the default resources/js/Pages
            ->component('Checkout', false)
            ->where('storeCountry', 'BD')
            ->has('countries', Countries::all() ? count(Countries::all()) : 0)
            ->has('countries.0', fn ($c) => $c
                ->hasAll(['code', 'name', 'dial', 'currency', 'postal', 'states'])
            )
        );
});
