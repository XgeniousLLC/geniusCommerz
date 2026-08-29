<?php

namespace App\Http\Controllers;

use App\Integrations\ProviderRegistry;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentWebhookController extends Controller
{
    public function __invoke(
        Request $request,
        string $provider,
        PaymentService $payments,
        ProviderRegistry $registry,
    ): JsonResponse {
        $definition = $registry->find($provider);

        if (! $definition || $definition->group !== 'payment' || ! $definition->isImplemented()) {
            return response()->json(['error' => 'Unknown provider'], 404);
        }

        // A rejected signature must be a hard failure, not a silent 200 — otherwise a
        // misconfigured signing secret looks identical to a healthy endpoint.
        if (! $payments->handleWebhook($provider, $request)) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        return response()->json(['received' => true]);
    }
}
