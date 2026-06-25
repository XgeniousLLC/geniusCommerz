<?php

namespace Database\Seeders;

use App\Models\Integration;
use Illuminate\Database\Seeder;

class IntegrationSeeder extends Seeder
{
    public const PROVIDERS = [
        // Payments
        ['provider' => 'bkash',       'label' => 'bKash Tokenized API'],
        ['provider' => 'nagad',        'label' => 'Nagad PGW'],
        ['provider' => 'sslcommerz',   'label' => 'SSLCOMMERZ v4'],
        // Couriers
        ['provider' => 'pathao',       'label' => 'Pathao Courier'],
        ['provider' => 'steadfast',    'label' => 'Steadfast Courier'],
        ['provider' => 'redx',         'label' => 'RedX Courier'],
        // Fraud screening
        ['provider' => 'fraudbd',      'label' => 'FraudBD'],
        ['provider' => 'bdcourier',    'label' => 'BDCourier'],
        // SMS Gateways
        ['provider' => 'bulksmsbd',    'label' => 'BulkSMSBD'],
        ['provider' => 'smsbd',        'label' => 'SMS.BD'],
        ['provider' => 'mram',         'label' => 'MRAM SMS'],
        ['provider' => 'twilio',       'label' => 'Twilio'],
        // AI Providers
        ['provider' => 'openai',       'label' => 'OpenAI'],
        ['provider' => 'gemini',       'label' => 'Google Gemini'],
        ['provider' => 'claude',       'label' => 'Anthropic Claude'],
        ['provider' => 'deepseek',     'label' => 'DeepSeek'],
    ];

    public function run(): void
    {
        foreach (self::PROVIDERS as $provider) {
            Integration::firstOrCreate(
                ['provider' => $provider['provider']],
                ['label' => $provider['label'], 'credentials' => [], 'is_active' => false]
            );
        }
    }
}
