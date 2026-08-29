<?php

use App\Contracts\CourierInterface;
use App\Models\Integration;
use App\Models\Order;
use App\Models\Product;
use App\Models\SiteSetting;
use App\Services\CourierService;
use App\Services\ShippingCalculator;

/**
 * Regression cover for a live defect: the storefront displayed a courier zone rate from
 * /api/courier/charge while CheckoutController wrote the flat rate onto the order, so the
 * customer was quoted one price and charged another.
 */
beforeEach(function () {
    SiteSetting::updateOrCreate(['key' => 'shipping.flat_rate'], ['value' => '60', 'type' => 'text', 'group' => 'shipping']);
    SiteSetting::updateOrCreate(['key' => 'shipping.courier_location_charges'], ['value' => '1', 'type' => 'boolean', 'group' => 'shipping']);

    Integration::create([
        'provider' => 'pathao', 'group' => 'courier', 'label' => 'Pathao',
        'credentials' => [], 'is_active' => true, 'is_default' => true,
    ]);

    // Stand-in courier that prices purely on weight, so the assertions are deterministic.
    $this->courier = new class implements CourierInterface
    {
        public array $lastParams = [];

        public function createOrder(\App\Models\Order $order, array $extra = []): array { return []; }
        public function getStatus(string $consignmentId): array { return []; }
        public function getCities(): array { return [['id' => 1, 'name' => 'Dhaka']]; }
        public function getZones(int $cityId): array { return [['id' => 9, 'name' => 'Gulshan']]; }
        public function getAreas(int $zoneId): array { return []; }
        public function name(): string { return 'Fake'; }

        public function calculateCharge(array $params): ?float
        {
            $this->lastParams = $params;
            return 100 + ((float) $params['item_weight'] * 20);
        }
    };

    $fake = $this->courier;
    $this->instance(CourierService::class, new class($fake) extends CourierService {
        public function __construct(private $fake) {}
        public function driver(?string $provider = null): CourierInterface { return $this->fake; }
        public function hasDefault(): bool { return true; }
    });
});

function shipProduct(float $weight = 2.0): Product
{
    return Product::create([
        'name' => 'Heavy', 'slug' => 'heavy-'.uniqid(), 'status' => 'active',
        'price' => 500, 'weight' => $weight, 'shipping_included' => false,
    ]);
}

it('charges the courier zone rate that was quoted, not the flat rate', function () {
    $product = shipProduct(2.0);

    $quoted = $this->postJson('/api/courier/charge', [
        'city_id' => 1, 'zone_id' => 9,
        'items'   => [['product_id' => $product->id, 'quantity' => 1]],
    ])->assertOk()->json('charge');

    $quoted = (float) $quoted;      // JSON hands back an int for a whole number
    expect($quoted)->toBe(140.0);   // 100 + 2kg * 20

    $this->post('/checkout', [
        'customer_name' => 'Buyer', 'customer_phone' => '01711111111',
        'customer_email' => 'b@example.com',
        'country' => 'BD', 'address' => 'Road 1', 'city' => 'Dhaka', 'postal_code' => '1212',
        'payment_method' => 'cod',
        'courier_city_id' => 1, 'courier_zone_id' => 9,
        'items' => [['product_id' => $product->id, 'name' => $product->name, 'price' => 500, 'quantity' => 1]],
    ])->assertSessionHasNoErrors();

    $order = Order::latest('id')->first();

    expect((float) $order->shipping_cost)->toBe($quoted)
        ->and((float) $order->shipping_cost)->not->toBe(60.0);
});

it('derives weight from the cart rather than trusting the request', function () {
    $product = shipProduct(1.5);

    // A client claiming a featherweight cart must not change the price.
    $this->postJson('/api/courier/charge', [
        'city_id' => 1, 'zone_id' => 9, 'item_weight' => 0.1,
        'items'   => [['product_id' => $product->id, 'quantity' => 2]],
    ])->assertOk()->assertJson(['charge' => 160.0]);   // 100 + 3kg * 20

    expect($this->courier->lastParams['item_weight'])->toBe(3.0);
});

it('falls back to the flat rate when no zone was chosen', function () {
    $product = shipProduct(1.0);

    $this->post('/checkout', [
        'customer_name' => 'Buyer', 'customer_phone' => '01711111111',
        'customer_email' => 'b2@example.com',
        'country' => 'BD', 'address' => 'Road 1', 'city' => 'Dhaka', 'postal_code' => '1212',
        'payment_method' => 'cod',
        'items' => [['product_id' => $product->id, 'name' => $product->name, 'price' => 500, 'quantity' => 1]],
    ])->assertSessionHasNoErrors();

    expect((float) Order::latest('id')->first()->shipping_cost)->toBe(60.0);
});

it('uses real product weights, defaulting only where none is recorded', function () {
    $withWeight = shipProduct(2.5);
    $noWeight   = Product::create(['name' => 'Light', 'slug' => 'light-'.uniqid(), 'status' => 'active', 'price' => 10]);

    $calculator = app(ShippingCalculator::class);

    expect($calculator->weight([['product_id' => $withWeight->id, 'quantity' => 2]]))->toBe(5.0)
        ->and($calculator->weight([['product_id' => $noWeight->id, 'quantity' => 3]]))->toBe(1.5)  // 0.5 default
        ->and($calculator->weight([]))->toBe(0.5);
});
