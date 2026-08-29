<?php

use App\Models\Integration;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\WebhookEvent;
use App\Payments\PaymentStatus;
use App\Services\PaymentService;
use Illuminate\Support\Facades\Http;

function payProduct(): Product
{
    return Product::create([
        'name' => 'Paid Product', 'slug' => 'paid-'.uniqid(),
        'status' => 'active', 'price' => 100, 'shipping_included' => true,
    ]);
}

function enableGateway(string $provider, array $credentials = [], string $env = 'live'): Integration
{
    $row = Integration::forSlug($provider);
    $row->environment = $env;
    $row->is_active   = true;
    if ($credentials) {
        $row->mergeCredentials($credentials, array_fill_keys(array_keys($credentials), true), $env);
    }
    $row->save();

    return $row;
}

function placeOrder(Product $product, string $method = 'cod'): \Illuminate\Testing\TestResponse
{
    return test()->post('/checkout', [
        'customer_name' => 'Buyer', 'customer_phone' => '5551234567',
        'customer_email' => 'b'.uniqid().'@example.com',
        'country' => 'US', 'address' => '1 Main St', 'city' => 'Austin',
        'state' => 'TX', 'postal_code' => '78701',
        'payment_method' => $method,
        'items' => [['product_id' => $product->id, 'name' => $product->name, 'price' => 100, 'quantity' => 1]],
    ]);
}

it('rejects a payment method that is not an enabled gateway', function () {
    $product = payProduct();

    placeOrder($product, 'free')->assertSessionHasErrors('payment_method');
    placeOrder($product, 'stripe')->assertSessionHasErrors('payment_method');

    expect(Order::count())->toBe(0);
});

it('leaves a cash-on-delivery order unpaid and goes straight to confirmation', function () {
    enableGateway('cod');
    $product = payProduct();

    placeOrder($product, 'cod')->assertRedirect();

    $order = Order::latest('id')->first();

    expect($order->payment_status)->toBe('unpaid')
        ->and($order->paid_at)->toBeNull()
        ->and($order->payments ?? Payment::where('order_id', $order->id)->get())->toHaveCount(1)
        ->and(Payment::first()->status)->toBe(PaymentStatus::Deferred->value);
});

it('sends the customer to the gateway and holds the order pending', function () {
    enableGateway('stripe', ['secret_key' => 'sk_test_x', 'webhook_secret' => 'whsec_x']);
    $product = payProduct();

    Http::fake(['api.stripe.com/v1/checkout/sessions' => Http::response([
        'id' => 'cs_test_123', 'url' => 'https://checkout.stripe.com/pay/cs_test_123',
    ])]);

    placeOrder($product, 'stripe')->assertRedirect('https://checkout.stripe.com/pay/cs_test_123');

    $order   = Order::latest('id')->first();
    $payment = Payment::first();

    // The order must NOT be paid yet — the customer has only been sent to Stripe.
    expect($order->payment_status)->toBe('pending')
        ->and($order->paid_at)->toBeNull()
        ->and($payment->status)->toBe(PaymentStatus::Redirect->value)
        ->and($payment->gateway_transaction_id)->toBe('cs_test_123')
        ->and($payment->currency)->toBe($order->presentment_currency);
});

it('charges the gateway in integer minor units of the presentment currency', function () {
    enableGateway('stripe', ['secret_key' => 'sk_test_x', 'webhook_secret' => 'whsec_x']);
    $product = payProduct();

    Http::fake(['api.stripe.com/*' => Http::response(['id' => 'cs_1', 'url' => 'https://stripe.test/pay'])]);

    placeOrder($product, 'stripe');

    $order = Order::latest('id')->first();

    Http::assertSent(function ($request) use ($order) {
        $expected = (int) round((float) $order->presentment_total * 100); // BDT has 2 decimals
        return $request['line_items'][0]['price_data']['unit_amount'] === $expected;
    });
});

it('marks the order paid only from a signed webhook', function () {
    enableGateway('stripe', ['secret_key' => 'sk_test_x', 'webhook_secret' => 'whsec_test']);
    $product = payProduct();
    Http::fake(['api.stripe.com/*' => Http::response(['id' => 'cs_1', 'url' => 'https://stripe.test/pay'])]);
    placeOrder($product, 'stripe');

    $payment = Payment::first();
    $body = json_encode([
        'id' => 'evt_1', 'type' => 'checkout.session.completed',
        'data' => ['object' => [
            'id' => 'cs_1', 'payment_status' => 'paid',
            'client_reference_id' => $payment->idempotency_key,
        ]],
    ]);

    $t   = time();
    $sig = hash_hmac('sha256', "{$t}.{$body}", 'whsec_test');

    $this->call('POST', '/api/payments/webhook/stripe', [], [], [], [
        'HTTP_STRIPE_SIGNATURE' => "t={$t},v1={$sig}",
        'CONTENT_TYPE'          => 'application/json',
    ], $body)->assertOk();

    $order = Order::latest('id')->first();

    expect($order->payment_status)->toBe('paid')
        ->and($order->paid_at)->not->toBeNull()
        ->and(Payment::first()->status)->toBe(PaymentStatus::Paid->value);
});

it('refuses a webhook with a bad signature and leaves the order alone', function () {
    enableGateway('stripe', ['secret_key' => 'sk_test_x', 'webhook_secret' => 'whsec_test']);
    $product = payProduct();
    Http::fake(['api.stripe.com/*' => Http::response(['id' => 'cs_1', 'url' => 'https://stripe.test/pay'])]);
    placeOrder($product, 'stripe');

    $body = json_encode(['id' => 'evt_bad', 'type' => 'checkout.session.completed',
        'data' => ['object' => ['id' => 'cs_1', 'payment_status' => 'paid',
            'client_reference_id' => Payment::first()->idempotency_key]]]);

    $t = time();
    $this->call('POST', '/api/payments/webhook/stripe', [], [], [], [
        'HTTP_STRIPE_SIGNATURE' => "t={$t},v1=deadbeef",
        'CONTENT_TYPE'          => 'application/json',
    ], $body)->assertStatus(400);

    expect(Order::latest('id')->first()->payment_status)->toBe('pending')
        ->and(WebhookEvent::count())->toBe(0);
});

it('rejects a replayed signature from outside the tolerance window', function () {
    enableGateway('stripe', ['secret_key' => 'sk_test_x', 'webhook_secret' => 'whsec_test']);
    $body = json_encode(['id' => 'evt_old', 'type' => 'checkout.session.completed', 'data' => ['object' => []]]);

    $t   = time() - 3600;                       // an hour old
    $sig = hash_hmac('sha256', "{$t}.{$body}", 'whsec_test');

    $this->call('POST', '/api/payments/webhook/stripe', [], [], [], [
        'HTTP_STRIPE_SIGNATURE' => "t={$t},v1={$sig}",
        'CONTENT_TYPE'          => 'application/json',
    ], $body)->assertStatus(400);
});

it('does not pay an order twice when the same webhook is delivered again', function () {
    enableGateway('stripe', ['secret_key' => 'sk_test_x', 'webhook_secret' => 'whsec_test']);
    $product = payProduct();
    Http::fake(['api.stripe.com/*' => Http::response(['id' => 'cs_1', 'url' => 'https://stripe.test/pay'])]);
    placeOrder($product, 'stripe');

    $payment = Payment::first();
    $body = json_encode(['id' => 'evt_dup', 'type' => 'checkout.session.completed',
        'data' => ['object' => ['id' => 'cs_1', 'payment_status' => 'paid',
            'client_reference_id' => $payment->idempotency_key]]]);
    $t   = time();
    $sig = hash_hmac('sha256', "{$t}.{$body}", 'whsec_test');
    $headers = ['HTTP_STRIPE_SIGNATURE' => "t={$t},v1={$sig}", 'CONTENT_TYPE' => 'application/json'];

    $this->call('POST', '/api/payments/webhook/stripe', [], [], [], $headers, $body)->assertOk();
    $firstPaidAt = Order::latest('id')->first()->paid_at;

    // same event id again — the unique index must drop it
    $this->call('POST', '/api/payments/webhook/stripe', [], [], [], $headers, $body)->assertOk();

    $order = Order::latest('id')->first();

    expect(WebhookEvent::count())->toBe(1)
        ->and(Payment::count())->toBe(1)
        ->and($order->paid_at->eq($firstPaidAt))->toBeTrue()
        ->and($order->activities()->where('type', 'payment_paid')->count())->toBe(1);
});

it('never marks an order paid from the browser return url alone', function () {
    enableGateway('stripe', ['secret_key' => 'sk_test_x', 'webhook_secret' => 'whsec_test']);
    $product = payProduct();
    Http::fake(['api.stripe.com/v1/checkout/sessions' => Http::response(['id' => 'cs_1', 'url' => 'https://stripe.test/pay'])]);
    placeOrder($product, 'stripe');

    $payment = Payment::first();

    // Stripe says the session is still unpaid, whatever the customer's browser claims.
    Http::fake(['api.stripe.com/v1/checkout/sessions/cs_1' => Http::response([
        'id' => 'cs_1', 'status' => 'open', 'payment_status' => 'unpaid',
    ])]);

    $this->get(route('payment.return', ['reference' => $payment->idempotency_key]))->assertRedirect();

    expect(Order::latest('id')->first()->payment_status)->toBe('pending');
});

it('settles from the return url when the gateway confirms it server-side', function () {
    enableGateway('stripe', ['secret_key' => 'sk_test_x', 'webhook_secret' => 'whsec_test']);
    $product = payProduct();
    Http::fake(['api.stripe.com/v1/checkout/sessions' => Http::response(['id' => 'cs_1', 'url' => 'https://stripe.test/pay'])]);
    placeOrder($product, 'stripe');

    $payment = Payment::first();

    Http::fake(['api.stripe.com/v1/checkout/sessions/cs_1' => Http::response([
        'id' => 'cs_1', 'status' => 'complete', 'payment_status' => 'paid',
    ])]);

    $this->get(route('payment.return', ['reference' => $payment->idempotency_key]))
        ->assertRedirect(route('order.confirm', Order::latest('id')->first()->order_number));

    expect(Order::latest('id')->first()->payment_status)->toBe('paid');
});

it('will not un-pay an order if a later attempt fails', function () {
    enableGateway('cod');
    $product = payProduct();
    placeOrder($product, 'cod');

    $order = Order::latest('id')->first();
    $order->update(['payment_status' => 'paid', 'paid_at' => now()]);

    $payments = app(PaymentService::class);
    $second   = Payment::create([
        'order_id' => $order->id, 'provider' => 'cod', 'status' => 'pending',
        'amount_minor' => 100, 'currency' => 'BDT', 'base_currency' => 'BDT',
        'idempotency_key' => 'later-attempt',
    ]);
    $payments->apply($second, \App\Payments\PaymentResult::failed('card declined'));

    expect($order->fresh()->payment_status)->toBe('paid');
});

it('404s a webhook for a provider that is not a payment gateway', function () {
    $this->postJson('/api/payments/webhook/nonsense')->assertNotFound();
    // A real provider from another group must not accept payment webhooks either.
    $this->postJson('/api/payments/webhook/twilio')->assertNotFound();
});
