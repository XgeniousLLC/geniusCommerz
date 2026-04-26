<?php

namespace Database\Seeders;

use App\Models\LoyaltySetting;
use Illuminate\Database\Seeder;

class LoyaltySettingSeeder extends Seeder
{
    public function run(): void
    {
        LoyaltySetting::set('enabled',            false, 'boolean');
        LoyaltySetting::set('points_per_taka',    0.1,   'decimal');
        LoyaltySetting::set('taka_per_point',     0.5,   'decimal');
        LoyaltySetting::set('min_redemption',     100,   'number');
        LoyaltySetting::set('max_redemption_pct', 20,    'number');
        LoyaltySetting::set('tiers', [
            ['name' => 'Silver',   'min' => 0,     'max' => 4999,  'bonus_pct' => 0],
            ['name' => 'Gold',     'min' => 5000,  'max' => 19999, 'bonus_pct' => 5],
            ['name' => 'Platinum', 'min' => 20000, 'max' => null,  'bonus_pct' => 10],
        ], 'json');
    }
}
