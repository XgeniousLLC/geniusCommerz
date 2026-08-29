<?php

use App\Models\Order;
use App\Models\Product;
use App\Models\SiteSetting;
use App\Models\TaxRate;
use App\Models\TaxZone;
use App\Tax\TaxCalculator;

function taxOn(bool $enabled = true, bool $inclusive = false): void
{
    SiteSetting::updateOrCreate(['key' => 'tax.enabled'], ['value' => $enabled ? '1' : '0', 'type' => 'boolean', 'group' => 'tax']);
    SiteSetting::updateOrCreate(['key' => 'accounting.prices_include_tax'], ['value' => $inclusive ? '1' : '0', 'type' => 'boolean', 'group' => 'accounting']);
    cache()->flush();
}

function zone(string $name, string $country, ?string $state = null, ?string $postal = null, int $priority = 0): TaxZone
{
    return TaxZone::create(compact('name', 'country', 'state', 'postal') + [
        'postal_pattern' => $postal, 'priority' => $priority, 'is_active' => true,
    ]);
}

function rate(TaxZone $z, string $name, float $percent, string $class = 'standard', bool $shipping = true): TaxRate
{
    return $z->rates()->create([
        'name' => $name, 'rate' => $percent, 'tax_class' => $class, 'applies_to_shipping' => $shipping,
    ]);
}

function taxProduct(string $class = 'standard'): Product
{
    return Product::create([
        'name' => 'P', 'slug' => 'p-'.uniqid(), 'status' => 'active',
        'price' => 100, 'tax_class' => $class, 'shipping_included' => true,
    ]);
}

beforeEach(fn () => taxOn());

it('applies UK VAT at 20 percent on top of net prices', function () {
    rate(zone('UK VAT', 'GB'), 'VAT', 20);
    $product = taxProduct();

    $result = app(TaxCalculator::class)->calculate(
        [['product_id' => $product->id, 'total' => 100.0]],
        ['country' => 'GB'],
    );

    expect($result->total)->toBe(20.0)
        ->and($result->addedToTotal())->toBe(20.0)
        ->and($result->breakdown[0]['name'])->toBe('VAT');
});

it('applies German VAT at 19 percent', function () {
    rate(zone('DE VAT', 'DE'), 'MwSt', 19);
    $product = taxProduct();

    expect(app(TaxCalculator::class)->calculate(
        [['product_id' => $product->id, 'total' => 200.0]],
        ['country' => 'DE'],
    )->total)->toBe(38.0);
});

it('stacks US state and county rates and prefers the postal-specific zone', function () {
    // Statewide California
    $california = zone('California', 'US', 'CA');
    rate($california, 'CA State Tax', 7.25);

    // San Francisco county, matched on postal prefix — must win over the statewide zone
    $sanFrancisco = zone('San Francisco', 'US', 'CA', '941%');
    rate($sanFrancisco, 'CA State Tax', 7.25);
    rate($sanFrancisco, 'SF District Tax', 1.375);

    $product = taxProduct();
    $calc    = app(TaxCalculator::class);

    $statewide = $calc->calculate(
        [['product_id' => $product->id, 'total' => 100.0]],
        ['country' => 'US', 'state' => 'CA', 'postal_code' => '90001'],
    );
    expect($statewide->total)->toBe(7.25)->and($statewide->zoneName)->toBe('California');

    $city = $calc->calculate(
        [['product_id' => $product->id, 'total' => 100.0]],
        ['country' => 'US', 'state' => 'CA', 'postal_code' => '94103'],
    );

    expect($city->zoneName)->toBe('San Francisco')
        ->and($city->total)->toBe(8.63)             // 7.25 + 1.375, rounded to cents
        ->and($city->breakdown)->toHaveCount(2);
});

it('charges nothing on a zero-rated product', function () {
    $uk = zone('UK VAT', 'GB');
    rate($uk, 'VAT', 20, 'standard');
    // No 'zero' rate exists in the zone, which is what makes zero-rated goods untaxed.

    $book = taxProduct('zero');

    expect(app(TaxCalculator::class)->calculate(
        [['product_id' => $book->id, 'total' => 100.0]],
        ['country' => 'GB'],
    )->total)->toBe(0.0);
});

it('uses the reduced rate for reduced-class goods in the same zone', function () {
    $uk = zone('UK VAT', 'GB');
    rate($uk, 'VAT', 20, 'standard');
    rate($uk, 'VAT (reduced)', 5, 'reduced');

    $standard = taxProduct('standard');
    $reduced  = taxProduct('reduced');
    $calc     = app(TaxCalculator::class);

    $result = $calc->calculate([
        ['product_id' => $standard->id, 'total' => 100.0],
        ['product_id' => $reduced->id,  'total' => 100.0],
    ], ['country' => 'GB']);

    expect($result->total)->toBe(25.0)              // 20 + 5
        ->and($result->lineTax[0])->toBe(20.0)
        ->and($result->lineTax[1])->toBe(5.0);
});

it('extracts rather than adds tax when prices already include it', function () {
    taxOn(inclusive: true);
    rate(zone('UK VAT', 'GB'), 'VAT', 20);
    $product = taxProduct();

    $result = app(TaxCalculator::class)->calculate(
        [['product_id' => $product->id, 'total' => 120.0]],
        ['country' => 'GB'],
    );

    // £120 gross at 20% contains £20 of VAT — and the customer is not charged again.
    expect($result->total)->toBe(20.0)
        ->and($result->inclusive)->toBeTrue()
        ->and($result->addedToTotal())->toBe(0.0);
});

it('taxes the discounted amount, not the list price', function () {
    rate(zone('UK VAT', 'GB'), 'VAT', 20);
    $product = taxProduct();

    expect(app(TaxCalculator::class)->calculate(
        [['product_id' => $product->id, 'total' => 100.0]],
        ['country' => 'GB'],
        discount: 50.0,
    )->total)->toBe(10.0);
});

it('taxes shipping only when the rate says so', function () {
    $uk = zone('UK VAT', 'GB');
    rate($uk, 'VAT', 20, 'standard', shipping: true);
    $product = taxProduct();

    expect(app(TaxCalculator::class)->calculate(
        [['product_id' => $product->id, 'total' => 100.0]],
        ['country' => 'GB'],
        shipping: 10.0,
    )->total)->toBe(22.0);

    TaxRate::query()->update(['applies_to_shipping' => false]);

    expect(app(TaxCalculator::class)->calculate(
        [['product_id' => $product->id, 'total' => 100.0]],
        ['country' => 'GB'],
        shipping: 10.0,
    )->total)->toBe(20.0);
});

it('charges no tax for a destination with no zone', function () {
    rate(zone('UK VAT', 'GB'), 'VAT', 20);
    $product = taxProduct();

    expect(app(TaxCalculator::class)->calculate(
        [['product_id' => $product->id, 'total' => 100.0]],
        ['country' => 'AU'],
    )->total)->toBe(0.0);
});

it('charges no tax at all while the feature is off', function () {
    taxOn(enabled: false);
    rate(zone('UK VAT', 'GB'), 'VAT', 20);
    $product = taxProduct();

    expect(app(TaxCalculator::class)->calculate(
        [['product_id' => $product->id, 'total' => 100.0]],
        ['country' => 'GB'],
    )->total)->toBe(0.0);
});

it('persists tax on the order and the invoice shows the same figure', function () {
    rate(zone('UK VAT', 'GB'), 'VAT', 20);
    $product = Product::create([
        'name' => 'Widget', 'slug' => 'w-'.uniqid(), 'status' => 'active',
        'price' => 100, 'tax_class' => 'standard', 'shipping_included' => true,
    ]);

    $this->post('/checkout', [
        'customer_name' => 'Buyer', 'customer_phone' => '5551234567',
        'customer_email' => 'b@example.com',
        'country' => 'GB', 'address' => '10 Downing St', 'city' => 'London', 'postal_code' => 'SW1A 2AA',
        'payment_method' => 'cod',
        'items' => [['product_id' => $product->id, 'name' => 'Widget', 'price' => 100, 'quantity' => 2]],
    ])->assertSessionHasNoErrors();

    $order = Order::latest('id')->first();

    expect((float) $order->tax)->toBe(40.0)                    // 200 net * 20%
        ->and((float) $order->total)->toBe(240.0)              // tax added on top
        ->and($order->tax_breakdown)->toHaveCount(1)
        ->and($order->tax_breakdown[0]['name'])->toBe('VAT')
        ->and((float) $order->tax_breakdown[0]['amount'])->toBe(40.0)
        ->and((float) $order->items()->first()->tax_amount)->toBe(40.0)
        // the invoice must read the stored figure, not recompute one
        ->and((float) $order->tax)->toBe((float) collect($order->tax_breakdown)->sum('amount'));
});

it('leaves the total untouched when tax-inclusive pricing is used', function () {
    taxOn(inclusive: true);
    rate(zone('UK VAT', 'GB'), 'VAT', 20);
    $product = Product::create([
        'name' => 'Widget', 'slug' => 'w-'.uniqid(), 'status' => 'active',
        'price' => 120, 'tax_class' => 'standard', 'shipping_included' => true,
    ]);

    $this->post('/checkout', [
        'customer_name' => 'Buyer', 'customer_phone' => '5551234567',
        'customer_email' => 'b2@example.com',
        'country' => 'GB', 'address' => '10 Downing St', 'city' => 'London', 'postal_code' => 'SW1A 2AA',
        'payment_method' => 'cod',
        'items' => [['product_id' => $product->id, 'name' => 'Widget', 'price' => 120, 'quantity' => 1]],
    ])->assertSessionHasNoErrors();

    $order = Order::latest('id')->first();

    expect((float) $order->total)->toBe(120.0)      // customer pays the listed price
        ->and((float) $order->tax)->toBe(20.0)      // of which £20 is VAT
        ->and($order->prices_include_tax)->toBeTrue();
});
