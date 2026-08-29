<?php

use App\Models\Admin;
use App\Models\Product;
use App\Models\ShippingRate;
use App\Models\ShippingZone;
use App\Models\SiteSetting;
use App\Models\TaxRate;
use App\Models\TaxZone;
use App\Support\ShippingPresets;
use App\Support\TaxPresets;
use App\Tax\TaxCalculator;

beforeEach(function () {
    $this->admin = Admin::factory()->create();
    SiteSetting::updateOrCreate(['key' => 'general.store_country'], ['value' => 'GB', 'type' => 'text', 'group' => 'general']);
    SiteSetting::updateOrCreate(['key' => 'general.currency'], ['value' => 'GBP', 'type' => 'text', 'group' => 'general']);
    cache()->flush();
});

it('seeds a whole region of tax zones in one action', function () {
    $this->actingAs($this->admin, 'admin')
        ->post(route('admin.tax.presets'), ['preset' => 'eu_vat'])
        ->assertRedirect();

    expect(TaxZone::count())->toBe(27)
        ->and(TaxRate::count())->toBe(27)
        ->and(TaxZone::where('country', 'DE')->first()->rates->first()->rate)->toEqual('19.0000')
        ->and(TaxZone::where('country', 'HU')->first()->rates->first()->rate)->toEqual('27.0000');
});

it('does not duplicate or overwrite when a template is applied twice', function () {
    $this->actingAs($this->admin, 'admin')->post(route('admin.tax.presets'), ['preset' => 'eu_vat']);

    // The merchant tunes a rate after applying.
    $germany = TaxZone::where('country', 'DE')->first();
    $germany->rates()->first()->update(['rate' => 21.0]);

    $this->actingAs($this->admin, 'admin')->post(route('admin.tax.presets'), ['preset' => 'eu_vat']);

    expect(TaxZone::count())->toBe(27)
        // Their edit survives — re-applying skips rather than resets.
        ->and($germany->fresh()->rates->first()->rate)->toEqual('21.0000');
});

it('stacks Canadian GST and PST as two rates, which is how they are charged', function () {
    $this->actingAs($this->admin, 'admin')->post(route('admin.tax.presets'), ['preset' => 'canada_gst']);

    $bc = TaxZone::where('country', 'CA')->where('state', 'BC')->first();
    $on = TaxZone::where('country', 'CA')->where('state', 'ON')->first();

    expect($bc->rates)->toHaveCount(2)                       // GST 5 + PST 7
        ->and($on->rates)->toHaveCount(1)                    // HST 13, one combined rate
        ->and((float) $on->rates->first()->rate)->toBe(13.0);
});

it('produces tax that actually computes after applying a template', function () {
    SiteSetting::updateOrCreate(['key' => 'tax.enabled'], ['value' => '1', 'type' => 'boolean', 'group' => 'tax']);
    cache()->flush();

    $this->actingAs($this->admin, 'admin')->post(route('admin.tax.presets'), ['preset' => 'eu_vat']);

    $product = Product::create([
        'name' => 'P', 'slug' => 'p-'.uniqid(), 'status' => 'active',
        'price' => 100, 'tax_class' => 'standard', 'shipping_included' => true,
    ]);

    $result = app(TaxCalculator::class)->calculate(
        [['product_id' => $product->id, 'total' => 100.0]],
        ['country' => 'DE'],
    );

    expect($result->total)->toBe(19.0)->and($result->zoneName)->toBe('Germany VAT');
});

it('keeps US state rates separate so a county rate can be added later', function () {
    $this->actingAs($this->admin, 'admin')->post(route('admin.tax.presets'), ['preset' => 'us_sales_tax']);

    $california = TaxZone::where('country', 'US')->where('state', 'CA')->first();

    expect(TaxZone::where('country', 'US')->count())->toBe(46)
        ->and((float) $california->rates->first()->rate)->toBe(7.25)
        // State-level only, so a postal-scoped district zone still wins on specificity.
        ->and($california->postal_pattern)->toBeNull();
});

it('seeds shipping zones with usable weight bands', function () {
    $this->actingAs($this->admin, 'admin')
        ->post(route('admin.shipping.presets'), ['preset' => 'domestic_weight_bands'])
        ->assertRedirect();

    $zone  = ShippingZone::where('country', 'GB')->first();
    $rates = $zone->rates->sortBy('priority')->values();

    expect($zone->name)->toBe('United Kingdom')
        ->and($rates)->toHaveCount(3)
        ->and((float) $rates[0]->max_weight)->toBe(1.0)
        ->and((float) $rates[2]->per_kg)->toBe(1.5)
        // The bands must actually cover a cart without a gap.
        ->and($rates[0]->covers(0.5, 20))->toBeTrue()
        ->and($rates[1]->covers(3.0, 20))->toBeTrue()
        ->and($rates[2]->covers(9.0, 20))->toBeTrue();
});

it('builds shipping templates around the configured store country', function () {
    $gb = ShippingPresets::all('GB', 'GBP');
    $bd = ShippingPresets::all('BD', 'BDT');

    expect($gb['domestic_flat']['zones'][0]['country'])->toBe('GB')
        ->and($gb['domestic_flat']['label'])->toContain('United Kingdom')
        ->and($bd['domestic_flat']['zones'][0]['country'])->toBe('BD')
        ->and($bd['domestic_flat']['label'])->toContain('Bangladesh');
});

it('rejects a template that does not exist', function () {
    $this->actingAs($this->admin, 'admin')
        ->post(route('admin.tax.presets'), ['preset' => 'not-a-template'])
        ->assertNotFound();

    expect(TaxZone::count())->toBe(0);
});

it('offers every template through the registry helpers', function () {
    expect(array_keys(TaxPresets::all()))
        ->toEqualCanonicalizing(['eu_vat', 'uk_vat', 'us_sales_tax', 'canada_gst', 'single_rate_countries'])
        ->and(array_keys(ShippingPresets::all()))
        ->toEqualCanonicalizing(['domestic_flat', 'domestic_weight_bands', 'europe', 'north_america', 'gulf', 'rest_of_world']);
});
