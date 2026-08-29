<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Payments\PaymentStatus;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;

/**
 * Where the gateway sends the customer back to.
 *
 * The redirect itself proves nothing — anyone can visit this URL — so arriving here only
 * triggers a server-to-server verify. The order is marked paid by that verify (or by the
 * webhook, whichever lands first), never by the visit.
 */
class PaymentReturnController extends Controller
{
    public function return(string $reference, PaymentService $payments): RedirectResponse
    {
        $payment = $this->find($reference);

        $result = $payment->isSettled()
            ? \App\Payments\PaymentResult::paid($payment->gateway_transaction_id)
            : $payments->verify($payment);

        return match (true) {
            $result->status->isSettled() => redirect()
                ->route('order.confirm', $payment->order->order_number)
                ->with('success', 'Payment received. Thank you!'),

            $result->status === PaymentStatus::Pending => redirect()
                ->route('order.confirm', $payment->order->order_number)
                ->with('info', 'Your payment is being confirmed. We will email you once it clears.'),

            default => redirect()
                ->route('checkout')
                ->with('error', $result->error ?? 'Payment was not completed. Please try again.'),
        };
    }

    public function cancel(string $reference, PaymentService $payments): RedirectResponse
    {
        $payment = $this->find($reference);

        if (! $payment->isSettled()) {
            $payments->apply($payment, \App\Payments\PaymentResult::cancelled());
        }

        return redirect()->route('checkout')->with('info', 'Payment cancelled. Your cart is still here.');
    }

    /**
     * Auto-submitting bridge for gateways that need a signed browser POST. Renders only
     * the fields the driver produced — nothing here is taken from the request.
     */
    public function form(string $reference): \Illuminate\Contracts\View\View
    {
        $payment = $this->find($reference);
        $form    = $payment->payload['_form'] ?? null;

        abort_unless(is_array($form) && ! empty($form['url']), 404);

        return view('payment-form', [
            'action' => $form['url'],
            'fields' => $form['fields'] ?? [],
            'label'  => $payment->provider,
        ]);
    }

    private function find(string $reference): Payment
    {
        return Payment::with('order')->where('idempotency_key', $reference)->firstOrFail();
    }
}
