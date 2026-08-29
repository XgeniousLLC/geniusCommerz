<?php

namespace App\Console\Commands;

use App\Models\Currency;
use App\Models\SiteSetting;
use App\Services\FxService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RefreshExchangeRates extends Command
{
    protected $signature = 'currency:refresh-rates {--force : Apply rates even if they fail the sanity check}';

    protected $description = 'Refresh exchange rates for currencies set to update automatically';

    /**
     * Reject a rate that moves more than this in one refresh.
     *
     * One malformed API response would otherwise reprice the entire catalogue; a genuine
     * move this large is rare enough to be worth a human look.
     */
    private const MAX_MOVE = 0.15;

    public function handle(FxService $fx): int
    {
        $base = strtoupper((string) SiteSetting::get('general.currency', 'BDT'));

        $currencies = Currency::where('rate_source', 'api')->where('rate_locked', false)->get();

        if ($currencies->isEmpty()) {
            $this->info('No currencies are set to refresh automatically.');

            return self::SUCCESS;
        }

        try {
            $source = $fx->source();
            $rates  = $source->rates($base);
        } catch (\Throwable $e) {
            // Never clear or zero a rate on failure — a stale rate beats a broken one.
            Log::warning('Exchange rate refresh failed', ['error' => $e->getMessage()]);
            $this->error('Rate refresh failed: '.$e->getMessage());

            return self::FAILURE;
        }

        $updated = $skipped = 0;

        foreach ($currencies as $currency) {
            $code = strtoupper($currency->code);

            if ($code === $base) {
                $currency->update(['rate' => 1, 'rate_updated_at' => now()]);

                continue;
            }

            $fresh = $rates[$code] ?? null;

            if (! $fresh || $fresh <= 0) {
                $this->warn("  {$code}: not quoted by ".$source->name());
                $skipped++;

                continue;
            }

            $fresh *= 1 + ((float) $currency->rate_markup_percent / 100);
            $previous = (float) $currency->rate;

            if (! $this->option('force') && $previous > 0 && abs($fresh - $previous) / $previous > self::MAX_MOVE) {
                $move = round(abs($fresh - $previous) / $previous * 100, 1);
                $this->warn("  {$code}: rejected a {$move}% move ({$previous} → {$fresh}) — re-run with --force to accept");
                Log::warning('Exchange rate move rejected', compact('code', 'previous', 'fresh'));
                $skipped++;

                continue;
            }

            $currency->update(['rate' => $fresh, 'rate_updated_at' => now()]);
            $updated++;
        }

        $this->info("Rates refreshed from {$source->name()}: {$updated} updated, {$skipped} skipped.");

        return self::SUCCESS;
    }
}
