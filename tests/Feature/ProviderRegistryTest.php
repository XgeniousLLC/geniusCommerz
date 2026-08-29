<?php

use App\Contracts\AiInterface;
use App\Contracts\CourierInterface;
use App\Contracts\PaymentInterface;
use App\Contracts\ShippingRateInterface;
use App\Contracts\SmsInterface;
use App\Integrations\ProviderRegistry;
use App\Models\Admin;
use App\Models\Integration;
use App\Services\AiService;
use App\Services\CourierService;
use App\Services\SmsService;

beforeEach(function () {
    $this->registry = app(ProviderRegistry::class);
});

/**
 * Phase 2 is a refactor: every provider that resolved before must still resolve, and the
 * public API of the three services must be unchanged.
 */
it('still knows every provider that used to live in the model constants', function () {
    expect($this->registry->slugs('courier'))->toEqualCanonicalizing(['pathao', 'redx', 'steadfast'])
        // The SMS catalogue grows too; assert the shape rather than a fixed list.
        ->and($this->registry->slugs('sms'))->toContain('bulksmsbd', 'smsbd', 'mram', 'twilio')
        ->and(count($this->registry->group('sms')))->toBeGreaterThanOrEqual(20)
        ->and($this->registry->slugs('ai'))->toEqualCanonicalizing(['openai', 'gemini', 'claude', 'deepseek'])
        ->and($this->registry->slugs('fraud'))->toContain('fraudbd', 'bdcourier', 'ipqualityscore')
        ->and(count($this->registry->group('fraud')))->toBeGreaterThanOrEqual(11)
        ->and($this->registry->slugs('carrier'))->toContain('easypost', 'dhl', 'fedex', 'ups')
        ->and(count($this->registry->group('carrier')))->toBeGreaterThanOrEqual(19)
        ->and($this->registry->slugs('fx'))->toEqualCanonicalizing(['open_er_api', 'exchangerate_api'])
        // The payment catalogue grows continuously, so assert the shape rather than a
        // fixed list that would need editing with every new gateway.
        ->and($this->registry->slugs('payment'))->toContain('cod', 'stripe', 'paypal')
        ->and($this->registry->slugs('payment'))->toContain('sslcommerz', 'aamarpay', 'shurjopay')
        ->and(count($this->registry->group('payment')))->toBeGreaterThanOrEqual(20);
});

it('resolves every implemented driver against its contract', function () {
    $contracts = [
        'courier' => CourierInterface::class,
        'sms'     => SmsInterface::class,
        'ai'      => AiInterface::class,
        'carrier' => ShippingRateInterface::class,
    ];

    foreach ($contracts as $group => $contract) {
        foreach ($this->registry->slugs($group) as $slug) {
            expect($this->registry->driver($slug))->toBeInstanceOf($contract);
        }
    }
});

it('resolves drivers with no saved row, so nothing has to be seeded first', function () {
    expect(Integration::count())->toBe(0)
        ->and($this->registry->driver('pathao'))->toBeInstanceOf(CourierInterface::class);
});

it('refuses a provider that is not in the catalogue', function () {
    expect(fn () => $this->registry->driver('not-a-provider'))
        ->toThrow(RuntimeException::class, 'Unknown provider');
});

it('has a driver for every catalogued provider', function () {
    // Nothing is catalog-only any more; a definition without a driver would still be
    // refused rather than resolved, which the isImplemented() gate covers.
    $unimplemented = collect($this->registry->all())
        ->reject(fn ($definition) => $definition->isImplemented())
        ->keys();

    expect($unimplemented)->toBeEmpty();
});

it('keeps the old default lookups working', function () {
    Integration::create([
        'provider' => 'pathao', 'group' => 'courier', 'label' => 'Pathao Courier',
        'credentials' => [], 'is_active' => true, 'is_default' => true,
    ]);

    expect(Integration::defaultCourier()?->provider)->toBe('pathao')
        ->and(Integration::defaultFor('courier')?->provider)->toBe('pathao')
        ->and(app(CourierService::class)->hasDefault())->toBeTrue()
        ->and(app(CourierService::class)->driver())->toBeInstanceOf(CourierInterface::class);
});

it('reports the same message as before when no default is configured', function () {
    expect(fn () => app(CourierService::class)->driver())
        ->toThrow(RuntimeException::class, 'No active default courier is configured.');

    expect(fn () => app(SmsService::class)->driver())
        ->toThrow(RuntimeException::class, 'No active default SMS gateway is configured.');

    expect(fn () => app(AiService::class)->driver())
        ->toThrow(RuntimeException::class, 'No active default AI provider configured.');
});

it('will not resolve a provider from the wrong group', function () {
    // twilio is a real provider, but asking the courier service for it must fail
    expect(fn () => app(CourierService::class)->driver('twilio'))
        ->toThrow(RuntimeException::class, 'No active default courier is configured.');
});

it('keeps sandbox and live secrets apart so debugging cannot wipe live keys', function () {
    $row = Integration::forSlug('pathao');
    $row->environment = 'live';
    $row->mergeCredentials(['client_secret' => 'LIVE'], ['client_secret' => true], 'live');
    $row->mergeCredentials(['client_secret' => 'TEST'], ['client_secret' => true], 'sandbox');
    $row->mergeCredentials(['base_url' => 'https://shared'], ['base_url' => false], 'live');
    $row->save();

    $row->environment = 'live';
    expect($row->getCredential('client_secret'))->toBe('LIVE');

    $row->environment = 'sandbox';
    expect($row->getCredential('client_secret'))->toBe('TEST')
        // shared values resolve from either environment
        ->and($row->getCredential('base_url'))->toBe('https://shared');
});

it('still reads credentials saved in the old flat shape', function () {
    $row = Integration::forSlug('twilio');
    $row->credentials = ['account_sid' => 'AC_LEGACY'];   // pre-environment layout
    $row->save();

    expect($row->fresh()->getCredential('account_sid'))->toBe('AC_LEGACY');
});

it('only offers payment gateways that can charge the order currency', function () {
    Integration::create([
        'provider' => 'bkash', 'group' => 'payment', 'label' => 'bKash',
        'credentials' => [], 'is_active' => true,
    ]);

    // bKash is BDT/Bangladesh only: offered at home, never for a USD order.
    expect($this->registry->forCheckout('USD', 'US'))->toBeEmpty()
        ->and(array_keys($this->registry->forCheckout('BDT', 'BD')))->toBe(['bkash']);
});

it('creates the row lazily when credentials are first saved', function () {
    $admin = Admin::factory()->create();

    expect(Integration::where('provider', 'twilio')->exists())->toBeFalse();

    $this->actingAs($admin, 'admin')
        ->put(route('admin.integrations.update', 'twilio'), [
            'environment' => 'live',
            'is_active'   => '1',
            'credentials' => ['account_sid' => 'AC123', 'auth_token' => 'tok', 'from_number' => '+15551234567'],
        ])->assertRedirect();

    $row = Integration::where('provider', 'twilio')->first();

    expect($row)->not->toBeNull()
        ->and($row->group)->toBe('sms')
        ->and($row->label)->toBe('Twilio')
        ->and($row->is_active)->toBeTrue()
        ->and($row->getCredential('account_sid'))->toBe('AC123');
});

it('does not wipe a stored secret when the field is submitted blank', function () {
    $admin = Admin::factory()->create();

    $this->actingAs($admin, 'admin')->put(route('admin.integrations.update', 'twilio'), [
        'environment' => 'live', 'is_active' => '1',
        'credentials' => ['account_sid' => 'AC123', 'auth_token' => 'SECRET'],
    ]);

    // re-save with the password field left empty, as the form renders it
    $this->actingAs($admin, 'admin')->put(route('admin.integrations.update', 'twilio'), [
        'environment' => 'live', 'is_active' => '1',
        'credentials' => ['account_sid' => 'AC123', 'auth_token' => ''],
    ]);

    expect(Integration::forProvider('twilio')->getCredential('auth_token'))->toBe('SECRET');
});

it('renders the integrations and AI settings pages from the registry', function () {
    $admin = Admin::factory()->create();

    $this->actingAs($admin, 'admin')->get(route('admin.integrations.index'))
        ->assertOk()
        ->assertSee('Pathao Courier')
        ->assertSee('bKash');

    $this->actingAs($admin, 'admin')->get(route('admin.integrations.edit', 'pathao'))
        ->assertOk()
        ->assertSee('Client Secret');

    $this->actingAs($admin, 'admin')->get(route('admin.ai-settings.index'))
        ->assertOk()
        ->assertSee('Anthropic Claude');
});

it('404s on a provider that is not in the catalog', function () {
    $admin = Admin::factory()->create();

    $this->actingAs($admin, 'admin')
        ->get(route('admin.integrations.edit', 'not-a-provider'))
        ->assertNotFound();
});
