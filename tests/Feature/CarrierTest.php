<?php

use App\Contracts\CourierInterface;
use App\Contracts\ShippingRateInterface;
use App\Integrations\ProviderRegistry;
use App\Models\Integration;
use App\Services\CarrierService;
use App\Shipping\ShipmentRequest;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $this->registry = app(ProviderRegistry::class);
});

it('keeps the Bangladeshi couriers on their own contract', function () {
    // CourierInterface is Pathao-shaped (city → zone → area) and only makes sense inside
    // Bangladesh; global carriers rate on postcode and weight instead. Both survive.
    foreach ($this->registry->slugs('courier') as $slug) {
        expect($this->registry->driver($slug))->toBeInstanceOf(CourierInterface::class);
    }

    expect($this->registry->slugs('courier'))->toEqualCanonicalizing(['pathao', 'redx', 'steadfast']);
});

it('resolves every carrier against the rate contract', function () {
    foreach (array_keys($this->registry->group('carrier')) as $slug) {
        expect($this->registry->driver($slug))->toBeInstanceOf(ShippingRateInterface::class);
    }
});

it('covers every region we ship from', function () {
    $carriers = $this->registry->group('carrier');

    $market = fn (string $country) => collect($carriers)
        ->filter(fn ($d) => $d->countries === ['*'] || in_array($country, $d->countries, true))
        ->keys();

    expect(count($carriers))->toBeGreaterThanOrEqual(19)
        ->and($market('IN'))->toContain('delhivery', 'shiprocket', 'bluedart')
        ->and($market('NG'))->toContain('sendbox', 'gigl', 'kwik')
        ->and($market('ZA'))->toContain('bobgo')
        ->and($market('BR'))->toContain('melhorenvio', 'correios', 'loggi')
        ->and($market('SA'))->toContain('aramex', 'smsa', 'naqel', 'torod')
        // Worldwide carriers must reach markets with no local option.
        ->and($market('JP'))->toContain('dhl', 'fedex', 'ups', 'shippo', 'easypost');
});

it('returns quotes on the shared shape whatever the carrier answers with', function () {
    Http::fake([
        // Each aggregator names its rate list differently.
        '*shiprocket*' => Http::response(['token' => 't', 'data' => ['available_courier_companies' => [
            ['courier_name' => 'Delhivery Surface', 'rate' => 95, 'estimated_delivery_days' => 4, 'courier_company_id' => 12],
            ['courier_name' => 'Xpressbees',        'rate' => 72, 'estimated_delivery_days' => 5, 'courier_company_id' => 34],
        ]]]),
    ]);

    Integration::create([
        'provider' => 'shiprocket', 'group' => 'carrier', 'label' => 'Shiprocket',
        'credentials' => ['email' => 'a@b.com', 'password' => 'p'],
        'is_active' => true, 'is_default' => true, 'environment' => 'live',
    ]);

    $quotes = $this->registry->driver('shiprocket')->rates(new ShipmentRequest(
        to: ['zip' => '110001', 'country' => 'IN', 'city' => 'Delhi'],
        from: ['zip' => '400001', 'country' => 'IN', 'city' => 'Mumbai'],
        weight: 1.5,
    ));

    expect($quotes)->toHaveCount(2)
        // Cheapest first, so the calculator can take [0] without re-sorting.
        ->and($quotes[0]->amount)->toBe(72.0)
        ->and($quotes[0]->service)->toBe('Xpressbees')
        ->and($quotes[0]->currency)->toBe('INR')
        ->and($quotes[0]->estimatedDays)->toBe(5)
        ->and($quotes[0]->label())->toBe('Shiprocket Xpressbees');
});

it('says so plainly when a carrier books directly instead of from a saved rate', function () {
    Integration::create([
        'provider' => 'delhivery', 'group' => 'carrier', 'label' => 'Delhivery',
        'credentials' => ['api_token' => 't'], 'is_active' => true, 'environment' => 'live',
    ]);

    // Returning a fake tracking code here would look like success.
    expect(fn () => $this->registry->driver('delhivery')->buyLabel('rate_1'))
        ->toThrow(RuntimeException::class, 'books shipments directly');
});

it('converts weight into whatever unit each carrier expects', function () {
    Http::fake(['*' => Http::response([['total_amount' => 60]])]);

    Integration::create([
        'provider' => 'delhivery', 'group' => 'carrier', 'label' => 'Delhivery',
        'credentials' => ['api_token' => 't'], 'is_active' => true, 'environment' => 'live',
    ]);

    $this->registry->driver('delhivery')->rates(new ShipmentRequest(
        to: ['zip' => '110001', 'country' => 'IN'],
        from: ['zip' => '400001', 'country' => 'IN'],
        weight: 2.5,
    ));

    // Delhivery charges in grams while the system carries kilograms.
    Http::assertSent(fn ($request) => str_contains($request->url(), 'cgm=2500'));
});

it('falls through to zone rates when a carrier cannot quote', function () {
    Http::fake(['*' => Http::response([], 500)]);

    Integration::create([
        'provider' => 'delhivery', 'group' => 'carrier', 'label' => 'Delhivery',
        'credentials' => ['api_token' => 't'], 'is_active' => true, 'is_default' => true, 'environment' => 'live',
    ]);

    // A rating outage must not block checkout.
    expect(app(CarrierService::class)->cheapestRate(new ShipmentRequest(
        to: ['zip' => '110001', 'country' => 'IN'],
        from: ['zip' => '400001', 'country' => 'IN'],
        weight: 1.0,
    )))->toBeNull();
});
