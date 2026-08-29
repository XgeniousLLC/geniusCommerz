<?php

use App\Models\Admin;
use App\Models\Integration;
use App\Models\Order;
use App\Models\Product;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Support\Facades\Http;

function activeSms(string $provider, array $credentials): Integration
{
    return Integration::create([
        'provider' => $provider, 'group' => 'sms', 'label' => $provider,
        'credentials' => $credentials, 'is_active' => true, 'is_default' => true,
        'environment' => 'live',
    ]);
}

beforeEach(function () {
    SiteSetting::updateOrCreate(['key' => 'general.store_country'], ['value' => 'BD', 'type' => 'text', 'group' => 'general']);
    SiteSetting::updateOrCreate(['key' => 'general.site_name'], ['value' => 'Acme Store', 'type' => 'text', 'group' => 'general']);
    cache()->flush();
});

it('finds an account whose phone predates E.164 normalisation', function () {
    Http::fake();
    activeSms('bulksmsbd', ['api_key' => 'k', 'sender_id' => 's']);

    // Stored the old way, before phones were normalised on write.
    $user = User::factory()->create(['phone' => '01700000000', 'is_active' => true]);

    // The customer types it either way; both must reach the same account.
    foreach (['01700000000', '+8801700000000'] as $typed) {
        $this->postJson('/login/otp/send', ['phone' => $typed, 'country' => 'BD'])
            ->assertOk()
            ->assertJsonFragment(['message' => 'OTP sent to your phone.']);
    }

    expect($user->fresh()->otp_code)->not->toBeNull();
});

it('does not leak the store brand from a hardcoded OTP message', function () {
    Http::fake();
    activeSms('bulksmsbd', ['api_key' => 'k', 'sender_id' => 's']);
    User::factory()->create(['phone' => '+8801700000000', 'is_active' => true]);

    $this->postJson('/login/otp/send', ['phone' => '01700000000', 'country' => 'BD'])->assertOk();

    Http::assertSent(fn ($request) => str_contains($request['message'] ?? '', 'Acme Store')
        && ! str_contains(strtolower($request['message'] ?? ''), 'klixbd'));
});

it('stores the customer phone in one canonical shape at checkout', function () {
    $product = Product::create([
        'name' => 'P', 'slug' => 'p-'.uniqid(), 'status' => 'active',
        'price' => 100, 'shipping_included' => true,
    ]);

    $this->post('/checkout', [
        'customer_name' => 'Buyer', 'customer_phone' => '01700000000',
        'customer_email' => 'b@example.com',
        'country' => 'BD', 'address' => 'Road 1', 'city' => 'Dhaka', 'postal_code' => '1212',
        'payment_method' => 'cod',
        'items' => [['product_id' => $product->id, 'name' => 'P', 'price' => 100, 'quantity' => 1]],
    ])->assertSessionHasNoErrors();

    $order = Order::latest('id')->first();

    expect($order->customer_phone)->toBe('+8801700000000')
        ->and(User::where('email', 'b@example.com')->first()->phone)->toBe('+8801700000000');
});

it('sends a test message through the same normalisation a real order uses', function () {
    Http::fake();
    $admin = Admin::factory()->create();
    activeSms('bulksmsbd', ['api_key' => 'k', 'sender_id' => 's']);

    $this->actingAs($admin, 'admin')
        ->post(route('admin.sms.test', 'bulksmsbd'), ['phone' => '+8801700000000', 'message' => 'hi'])
        ->assertRedirect();

    // BulkSMSBD wants a local number even though we hold E.164.
    Http::assertSent(fn ($request) => ($request['number'] ?? null) === '01700000000');
});

it('refuses to enable a gateway whose credentials are missing', function () {
    $admin = Admin::factory()->create();

    $this->actingAs($admin, 'admin')
        ->post(route('admin.sms.toggle', 'twilio'))
        ->assertRedirect(route('admin.integrations.edit', 'twilio'));

    expect(Integration::where('provider', 'twilio')->first()?->is_active)->not->toBeTrue();
});

it('renders the SMS page with every gateway and marks the default', function () {
    $admin = Admin::factory()->create();
    activeSms('twilio', ['account_sid' => 'AC', 'auth_token' => 't', 'from_number' => '+15551234567']);

    $this->actingAs($admin, 'admin')->get(route('admin.sms.index'))
        ->assertOk()
        ->assertSee('BulkSMSBD')
        ->assertSee('Amazon SNS')
        ->assertSee('Vonage')
        ->assertSee('Default');
});

it('covers every region we sell into', function () {
    $registry = app(\App\Integrations\ProviderRegistry::class);
    $sms      = $registry->group('sms');

    $market = fn (string $country) => collect($sms)
        ->filter(fn ($d) => $d->countries === ['*'] || in_array($country, $d->countries, true))
        ->keys();

    expect(count($sms))->toBeGreaterThanOrEqual(20)
        // India, Gulf and Africa each have local options beyond the global carriers.
        ->and($market('IN'))->toContain('msg91', 'gupshup', 'fast2sms')
        ->and($market('SA'))->toContain('unifonic', 'taqnyat')
        ->and($market('EG'))->toContain('cequens')
        ->and($market('KE'))->toContain('africastalking')
        ->and($market('NG'))->toContain('termii', 'clickatell')
        ->and($market('BD'))->toContain('bulksmsbd', 'smsbd', 'mram')
        // Global carriers must be offered everywhere, including markets with no local one.
        ->and($market('BR'))->toContain('twilio', 'vonage', 'infobip', 'sinch', 'telnyx');
});

it('hands every new gateway an E.164 number and lets each reshape it', function () {
    Http::fake(['*' => Http::response(['return' => true, 'type' => 'success', 'code' => 'ok',
        'success' => true, 'statusCode' => 201, 'replyCode' => 0,
        'SMSMessageData' => ['Recipients' => [['status' => 'Success']]],
        'messages' => [['status' => ['groupName' => 'PENDING']]]])]);

    $registry = app(\App\Integrations\ProviderRegistry::class);

    // Fast2SMS is domestic-only and takes a bare 10-digit Indian number.
    Integration::create([
        'provider' => 'fast2sms', 'group' => 'sms', 'label' => 'Fast2SMS',
        'credentials' => ['api_key' => 'k'], 'is_active' => true, 'is_default' => true, 'environment' => 'live',
    ]);

    app(\App\Services\SmsService::class)->send('+919876543210', 'hi');

    Http::assertSent(fn ($request) => ($request['numbers'] ?? null) === '9876543210');
});

it('reports a failed send instead of returning success', function () {
    Http::fake(['*' => Http::response(['return' => false, 'message' => 'Invalid API key'])]);

    Integration::create([
        'provider' => 'fast2sms', 'group' => 'sms', 'label' => 'Fast2SMS',
        'credentials' => ['api_key' => 'bad'], 'is_active' => true, 'is_default' => true, 'environment' => 'live',
    ]);

    expect(fn () => app(\App\Services\SmsService::class)->send('+919876543210', 'hi'))
        ->toThrow(RuntimeException::class, 'Invalid API key');
});
