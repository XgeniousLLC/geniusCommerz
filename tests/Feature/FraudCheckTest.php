<?php

use App\Contracts\FraudInterface;
use App\Integrations\ProviderRegistry;
use App\Models\Admin;
use App\Models\Integration;
use App\Models\Order;
use App\Models\Product;
use App\Models\SiteSetting;
use App\Services\FraudScorer;
use App\Services\FraudService;
use Illuminate\Support\Facades\Http;

function activeFraud(string $provider, array $credentials): Integration
{
    return Integration::create([
        'provider' => $provider, 'group' => 'fraud', 'label' => $provider,
        'credentials' => $credentials, 'is_active' => true, 'is_default' => true,
        'environment' => 'live',
    ]);
}

beforeEach(function () {
    $this->registry = app(ProviderRegistry::class);
});

it('covers every region we sell into', function () {
    $checkers = $this->registry->group('fraud');

    $market = fn (string $country) => collect($checkers)
        ->filter(fn ($d) => $d->countries === ['*'] || in_array($country, $d->countries, true))
        ->keys();

    expect(count($checkers))->toBeGreaterThanOrEqual(11)
        ->and($market('IN'))->toContain('bureau')
        ->and($market('AE'))->toContain('uqudo')
        ->and($market('NG'))->toContain('smileid', 'youverify')
        ->and($market('GB'))->toContain('ravelin')
        ->and($market('BD'))->toContain('fraudbd', 'bdcourier')
        // Global checkers must reach markets with no local option.
        ->and($market('BR'))->toContain('seon', 'sift', 'minfraud', 'ipqualityscore');
});

it('resolves every checker against the fraud contract', function () {
    foreach (array_keys($this->registry->group('fraud')) as $slug) {
        expect($this->registry->driver($slug))->toBeInstanceOf(FraudInterface::class);
    }
});

it('reports unconfigured rather than firing a doomed request', function () {
    Http::fake();

    foreach (['seon', 'sift', 'minfraud', 'ravelin', 'bureau', 'uqudo', 'smileid', 'youverify'] as $slug) {
        $driver = $this->registry->driver($slug);

        expect($driver->isConfigured())->toBeFalse("{$slug}")
            ->and($driver->check('+15551234567'))->toHaveKey('error');
    }

    Http::assertNothingSent();
});

it('normalises every provider onto one risk vocabulary', function () {
    $levels = ['safe', 'low_risk', 'mid_risk', 'high_risk', 'unknown'];

    Http::fake([
        '*seon.io*'     => Http::response(['success' => true, 'data' => ['fraud_score' => 90]]),
        '*sift.com*'    => Http::response(['scores' => ['payment_abuse' => ['score' => 0.9]]]),
        '*maxmind.com*' => Http::response(['risk_score' => 12]),
    ]);

    activeFraud('seon', ['licence_key' => 'k']);
    $seon = $this->registry->driver('seon')->check('+15551234567');

    expect($seon['risk_level'])->toBeIn($levels)
        ->and($seon['risk_level'])->toBe('high_risk')
        ->and($seon['risk_score'])->toBe(10)          // inverted: their 90 risk = our 10 safety
        ->and($seon['provider'])->toBe('seon');

    // A provider scoring 0-1 must land on the same scale as one scoring 0-100.
    Integration::query()->delete();
    activeFraud('sift', ['api_key' => 'k']);
    $sift = $this->registry->driver('sift')->check('+15551234567');

    expect($sift['risk_level'])->toBe('high_risk')->and($sift['risk_score'])->toBe(10);

    Integration::query()->delete();
    activeFraud('minfraud', ['account_id' => '1', 'licence_key' => 'k']);
    $mf = $this->registry->driver('minfraud')->check('+15551234567', ['ip' => '1.2.3.4']);

    expect($mf['risk_level'])->toBe('safe')->and($mf['risk_score'])->toBe(88);
});

it('will not send an international customer to a market-specific checker', function () {
    activeFraud('bdcourier', ['api_key' => 'k']);
    $fraud = app(FraudService::class);

    // BDCourier is Bangladesh-only; a US order must not reach it.
    expect($fraud->servesCountry('BD'))->toBeTrue()
        ->and($fraud->servesCountry('US'))->toBeFalse()
        ->and($fraud->active('US'))->toBeNull()
        ->and($fraud->active('BD'))->not->toBeNull();
});

it('blocks purchase pixels when the configured checker flags the customer', function () {
    SiteSetting::updateOrCreate(['key' => 'tracking.meta_pixel_id'], ['value' => '1', 'type' => 'text', 'group' => 'tracking']);
    activeFraud('seon', ['licence_key' => 'k']);

    Http::fake(['*seon.io*' => Http::response(['success' => true, 'data' => ['fraud_score' => 95]])]);

    $product = Product::create([
        'name' => 'P', 'slug' => 'p-'.uniqid(), 'status' => 'active',
        'price' => 100, 'shipping_included' => true,
    ]);

    $this->post('/checkout', [
        'customer_name' => 'Buyer', 'customer_phone' => '5551234567',
        'customer_email' => 'risky@example.com',
        'country' => 'US', 'address' => '1 Main St', 'city' => 'Austin',
        'state' => 'TX', 'postal_code' => '78701', 'payment_method' => 'cod',
        'items' => [['product_id' => $product->id, 'name' => 'P', 'price' => 100, 'quantity' => 1]],
    ])->assertSessionHasNoErrors();

    // The gate now works for international orders, not just Bangladeshi ones.
    // Pixel activity is written to a log channel rather than a table.
    $blocked = collect(\App\Services\PixelLogger::read())
        ->filter(fn ($entry) => ($entry['success'] ?? true) === false);

    expect($blocked)->not->toBeEmpty()
        ->and($blocked->first()['error'])->toContain('SEON')
        // All three platforms are blocked together, not just Meta.
        ->and($blocked->pluck('platform')->unique()->values()->all())
            ->toEqualCanonicalizing(['meta', 'tiktok', 'ga4']);
});

it('renders the fraud page with every checker', function () {
    $admin = Admin::factory()->create();

    $this->actingAs($admin, 'admin')->get(route('admin.fraud.index'))
        ->assertOk()
        ->assertSee('SEON')
        ->assertSee('Bureau')
        ->assertSee('Smile ID')
        ->assertSee('Ravelin');
});
