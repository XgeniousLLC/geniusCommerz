<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class PixelLogger
{
    public static function record(
        string  $platform,
        string  $event,
        bool    $success,
        ?int    $orderId      = null,
        ?string $orderNumber  = null,
        ?int    $httpStatus   = null,
        ?string $responseBody = null,
        ?string $error        = null,
    ): void {
        $entry = json_encode(array_filter([
            'ts'           => now()->toIso8601String(),
            'platform'     => $platform,
            'event'        => $event,
            'success'      => $success,
            'order_id'     => $orderId,
            'order_number' => $orderNumber,
            'http_status'  => $httpStatus,
            'response'     => $responseBody ? mb_substr($responseBody, 0, 1000) : null,
            'error'        => $error,
        ], fn ($v) => $v !== null), JSON_UNESCAPED_UNICODE);

        Log::channel('pixel_events')->info($entry);
    }

    /**
     * Read the pixel_events.log and return parsed entries, newest first.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function read(int $limit = 500): array
    {
        $path = storage_path('logs/pixel_events.log');

        if (! file_exists($path)) {
            return [];
        }

        $lines   = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $entries = [];

        foreach (array_reverse($lines) as $line) {
            // Monolog wraps the message: [timestamp] channel.INFO: {json}  []  []
            if (preg_match('/\] \S+\.INFO: (.+?)  \[\]/', $line, $m)) {
                $raw = json_decode($m[1], true);
            } else {
                $raw = json_decode(trim($line), true);
            }

            if (is_array($raw)) {
                $entries[] = $raw;
            }

            if (count($entries) >= $limit) {
                break;
            }
        }

        return $entries;
    }
}
