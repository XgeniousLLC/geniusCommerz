<?php

namespace App\Http\Controllers;

use App\Services\MetaCapiService;
use App\Services\PixelLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CapiController extends Controller
{
    /**
     * Receive a client-triggered event (AddToCart, ViewContent) and relay to Meta CAPI.
     * Called fire-and-forget from JS — always returns 200.
     */
    public function event(Request $request, MetaCapiService $capi): JsonResponse
    {
        $allowed = ['AddToCart', 'ViewContent', 'InitiateCheckout'];
        $event   = $request->input('event_name');

        if (! in_array($event, $allowed, true)) {
            return response()->json(['ok' => false], 400);
        }

        if (! $capi->isConfigured()) {
            return response()->json(['ok' => false, 'reason' => 'not_configured']);
        }

        $userData = $capi->buildUserData($request, [
            'user_id' => auth()->id(),
        ]);

        $customData = array_filter([
            'currency'     => $request->input('currency') ?: \App\Models\SiteSetting::get('general.currency', 'BDT'),
            'value'        => $request->input('value') ? (float) $request->input('value') : null,
            'content_ids'  => $request->input('content_ids'),
            'content_type' => $request->input('content_type', 'product'),
            'content_name' => $request->input('content_name'),
            'num_items'    => $request->input('num_items'),
            'contents'     => $request->input('contents'),
        ], fn ($v) => $v !== null);

        $sourceUrl = $request->input('source_url', $request->header('referer', config('app.url')));

        $result = $capi->send($event, $customData, $userData, $sourceUrl);

        PixelLogger::record(
            platform:     'meta',
            event:        $event,
            success:      $result['success'],
            httpStatus:   $result['status'] ?? null,
            responseBody: $result['body']   ?? null,
            error:        $result['success'] ? null : ($result['error'] ?? null),
        );

        return response()->json(['ok' => true]);
    }
}
