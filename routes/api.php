<?php

use App\Http\Controllers\PaymentWebhookController;
use Illuminate\Support\Facades\Route;

/*
 * Gateway webhooks.
 *
 * These live on the api routes (no session, no CSRF) because gateways POST here
 * unauthenticated. Authentication is the provider's own signature check, performed in
 * PaymentService::handleWebhook before anything is written.
 */
Route::post('/payments/webhook/{provider}', PaymentWebhookController::class)
    ->name('payments.webhook');
