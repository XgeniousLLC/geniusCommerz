<?php

use App\Models\Currency;
use App\Models\SiteSetting;
use App\Services\PriceBook;
use Illuminate\Support\Facades\Http;

function fxCurrency(string $code, float $rate, string $source = 'api', float $markup = 0): Currency
{
    return Currency::create([
        'code' => $code, 'symbol' => $code, 'name' => $code, 'rate' => $rate,
        'rate_source' => $source, 'rate_markup_percent' => $markup,
        'is_active' => true, 'is_default' => false,
    ]);
}

beforeEach(function () {
    SiteSetting::updateOrCreate(['key' => 'general.currency'], ['value' => 'BDT', 'type' => 'text', 'group' => 'general']);
    SiteSetting::updateOrCreate(['key' => 'currencies.enabled'], ['value' => '1', 'type' => 'text', 'group' => 'currencies']);
    cache()->flush();
});

it('refreshes only the currencies set to update automatically', function () {
    $auto   = fxCurrency('USD', 0.0090);
    $manual = fxCurrency('EUR', 0.0085, 'manual');

    Http::fake(['*' => Http::response(['result' => 'success', 'rates' => ['USD' => 0.0092, 'EUR' => 0.0080]])]);

    $this->artisan('currency:refresh-rates')->assertSuccessful();

    expect((float) $auto->fresh()->rate)->toBe(0.0092)
        ->and($auto->fresh()->rate_updated_at)->not->toBeNull()
        // a manually maintained rate is never overwritten
        ->and((float) $manual->fresh()->rate)->toBe(0.0085);
});

it('never leaves a currency without a rate when the source is down', function () {
    $usd = fxCurrency('USD', 0.0090);

    Http::fake(['*' => Http::response(['result' => 'error'], 500)]);

    $this->artisan('currency:refresh-rates')->assertFailed();

    expect((float) $usd->fresh()->rate)->toBe(0.0090);
});

it('rejects an implausible rate move instead of repricing the catalogue', function () {
    $usd = fxCurrency('USD', 0.0090);

    // A garbage response an order of magnitude out.
    Http::fake(['*' => Http::response(['result' => 'success', 'rates' => ['USD' => 0.09]])]);

    $this->artisan('currency:refresh-rates')->assertSuccessful();

    expect((float) $usd->fresh()->rate)->toBe(0.0090);
});

it('applies the move when it is forced', function () {
    $usd = fxCurrency('USD', 0.0090);
    Http::fake(['*' => Http::response(['result' => 'success', 'rates' => ['USD' => 0.09]])]);

    $this->artisan('currency:refresh-rates', ['--force' => true])->assertSuccessful();

    expect((float) $usd->fresh()->rate)->toBe(0.09);
});

it('leaves a locked rate alone', function () {
    $usd = fxCurrency('USD', 0.0090);
    $usd->update(['rate_locked' => true]);

    Http::fake(['*' => Http::response(['result' => 'success', 'rates' => ['USD' => 0.0092]])]);
    $this->artisan('currency:refresh-rates')->assertSuccessful();

    expect((float) $usd->fresh()->rate)->toBe(0.0090);
});

it('adds the configured markup to cover FX spread', function () {
    $usd = fxCurrency('USD', 0.0090, 'api', markup: 2.0);

    Http::fake(['*' => Http::response(['result' => 'success', 'rates' => ['USD' => 0.0092]])]);
    $this->artisan('currency:refresh-rates')->assertSuccessful();

    expect(round((float) $usd->fresh()->rate, 6))->toBe(round(0.0092 * 1.02, 6));
});

it('flags a rate that has not refreshed in over a day', function () {
    $fresh = fxCurrency('USD', 0.009);
    $fresh->update(['rate_updated_at' => now()]);

    $stale = fxCurrency('EUR', 0.008);
    $stale->update(['rate_updated_at' => now()->subDays(3)]);

    expect($fresh->fresh()->isRateStale())->toBeFalse()
        ->and($stale->fresh()->isRateStale())->toBeTrue();
});

it('pins the rate for a session so totals do not move while browsing', function () {
    fxCurrency('USD', 0.0090);
    $book = app(PriceBook::class);

    $first = $book->sessionRate('USD');

    // A refresh lands mid-visit.
    Currency::where('code', 'USD')->update(['rate' => 0.0200]);

    expect($book->sessionRate('USD'))->toBe($first);
});

it('detects a rate that drifted between page render and submit', function () {
    fxCurrency('USD', 0.0100);
    $book = app(PriceBook::class);

    expect($book->rateHasDrifted('USD', 0.0100))->toBeFalse()
        ->and($book->rateHasDrifted('USD', 0.01003))->toBeFalse()   // within tolerance
        ->and($book->rateHasDrifted('USD', 0.0120))->toBeTrue()     // 20% out
        ->and($book->rateHasDrifted('USD', null))->toBeFalse();     // nothing was quoted
});
