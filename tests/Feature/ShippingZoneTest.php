<?php

use App\Models\Order;
use App\Models\Product;
use App\Models\ShippingZone;
use App\Models\SiteSetting;
use App\Services\ShippingCalculator;

function shipZone(string $name, string $country, ?string $state = null, ?string $postal = null): ShippingZone
{
    return ShippingZone::create([
        'name' => $name, 'country' => $country, 'state' => $state,
        'postal_pattern' => $postal, 'priority' => 0, 'is_active' => true,
    ]);
}

function zoneProduct(float $weight = 1.0): Product
{
    return Product::create([
        'name' => 'P', 'slug' => 'p-'.uniqid(), 'status' => 'active',
        'price' => 100, 'weight' => $weight, 'shipping_included' => false,
    ]);
}

beforeEach(function () {
    SiteSetting::updateOrCreate(['key' => 'shipping.flat_rate'], ['value' => '60', 'type' => 'text', 'group' => 'shipping']);
    $this->calc = app(ShippingCalculator::class);
});

it('falls back to the flat rate when no zone covers the destination', function () {
    $product = zoneProduct();

    $quote = $this->calc->quote(['country' => 'US'], [['product_id' => $product->id, 'quantity' => 1]], 100);

    expect($quote['cost'])->toBe(60.0)->and($quote['method'])->toBeNull();
});

it('charges a zone rate with a per-kg component', function () {
    $uk = shipZone('United Kingdom', 'GB');
    $uk->rates()->create(['name' => 'Standard', 'price' => 5, 'per_kg' => 2, 'is_active' => true]);

    $product = zoneProduct(3.0);
    $quote   = $this->calc->quote(['country' => 'GB'], [['product_id' => $product->id, 'quantity' => 1]], 100);

    expect($quote['cost'])->toBe(11.0)                       // 5 + 3kg * 2
        ->and($quote['method'])->toBe('United Kingdom — Standard');
});

it('picks the weight band that covers the cart', function () {
    $uk = shipZone('United Kingdom', 'GB');
    $uk->rates()->create(['name' => 'Light', 'price' => 3, 'max_weight' => 2, 'priority' => 1, 'is_active' => true]);
    $uk->rates()->create(['name' => 'Heavy', 'price' => 9, 'min_weight' => 2, 'priority' => 2, 'is_active' => true]);

    $light = zoneProduct(1.0);
    $heavy = zoneProduct(5.0);

    expect($this->calc->quote(['country' => 'GB'], [['product_id' => $light->id, 'quantity' => 1]], 100)['cost'])->toBe(3.0)
        ->and($this->calc->quote(['country' => 'GB'], [['product_id' => $heavy->id, 'quantity' => 1]], 100)['cost'])->toBe(9.0);
});

it('prefers the most specific zone', function () {
    $us = shipZone('United States', 'US');
    $us->rates()->create(['name' => 'Standard', 'price' => 20, 'is_active' => true]);

    $ca = shipZone('California', 'US', 'CA');
    $ca->rates()->create(['name' => 'In-state', 'price' => 5, 'is_active' => true]);

    $product = zoneProduct();

    $inState = $this->calc->quote(['country' => 'US', 'state' => 'CA'], [['product_id' => $product->id, 'quantity' => 1]], 100);
    $outOfState = $this->calc->quote(['country' => 'US', 'state' => 'TX'], [['product_id' => $product->id, 'quantity' => 1]], 100);

    expect($inState['method'])->toBe('California — In-state')
        ->and($inState['cost'])->toBe(5.0)
        ->and($outOfState['cost'])->toBe(20.0);
});

it('ships free above the threshold on the rate', function () {
    $uk = shipZone('United Kingdom', 'GB');
    $uk->rates()->create(['name' => 'Standard', 'price' => 5, 'free_above' => 150, 'is_active' => true]);

    $product = zoneProduct();

    expect($this->calc->quote(['country' => 'GB'], [['product_id' => $product->id, 'quantity' => 1]], 100)['cost'])->toBe(5.0)
        ->and($this->calc->quote(['country' => 'GB'], [['product_id' => $product->id, 'quantity' => 1]], 200)['cost'])->toBe(0.0);
});

it('ships free when every product includes shipping', function () {
    $uk = shipZone('United Kingdom', 'GB');
    $uk->rates()->create(['name' => 'Standard', 'price' => 5, 'is_active' => true]);

    $included = Product::create([
        'name' => 'P', 'slug' => 'p-'.uniqid(), 'status' => 'active',
        'price' => 100, 'shipping_included' => true,
    ]);

    $quote = $this->calc->quote(['country' => 'GB'], [['product_id' => $included->id, 'quantity' => 1]], 100);

    expect($quote['cost'])->toBe(0.0)->and($quote['method'])->toBe('Included');
});

it('records the resolved shipping method on the order', function () {
    $uk = shipZone('United Kingdom', 'GB');
    $uk->rates()->create(['name' => 'Standard', 'price' => 7, 'is_active' => true]);

    $product = zoneProduct(1.0);

    $this->post('/checkout', [
        'customer_name' => 'Buyer', 'customer_phone' => '5551234567',
        'customer_email' => 'b@example.com',
        'country' => 'GB', 'address' => '10 Downing St', 'city' => 'London', 'postal_code' => 'SW1A 2AA',
        'payment_method' => 'cod',
        'items' => [['product_id' => $product->id, 'name' => 'P', 'price' => 100, 'quantity' => 1]],
    ])->assertSessionHasNoErrors();

    $order = Order::latest('id')->first();

    expect((float) $order->shipping_cost)->toBe(7.0)
        ->and($order->shipping_method)->toBe('United Kingdom — Standard');
});

it('matches a postal pattern ignoring spacing', function () {
    $london = shipZone('Central London', 'GB', null, 'SW1A%');
    $london->rates()->create(['name' => 'Same day', 'price' => 15, 'is_active' => true]);

    $rest = shipZone('United Kingdom', 'GB');
    $rest->rates()->create(['name' => 'Standard', 'price' => 5, 'is_active' => true]);

    $product = zoneProduct();
    $items   = [['product_id' => $product->id, 'quantity' => 1]];

    expect($this->calc->quote(['country' => 'GB', 'postal_code' => 'SW1A 2AA'], $items, 100)['cost'])->toBe(15.0)
        ->and($this->calc->quote(['country' => 'GB', 'postal_code' => 'M1 1AE'], $items, 100)['cost'])->toBe(5.0);
});
