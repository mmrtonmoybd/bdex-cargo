<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ConfigEntry;
use Illuminate\Database\Seeder;

/**
 * Operational numbers that are shared rather than per-row.
 *
 * Carrier, SMS, WhatsApp, payment and FX credential rows arrive with their own
 * phases; the Integrations page renders whatever groups exist, so nothing here
 * needs changing when they do.
 */
final class ConfigEntrySeeder extends Seeder
{
    public function run(): void
    {
        $entries = [
            [
                'key' => 'pricing.tax_rate_percent',
                'value' => '0',
                'group' => 'pricing',
                'label' => 'Tax rate (%)',
                'description' => 'Applied to the quote subtotal. Zero means no tax line is shown.',
                'sort_order' => 1,
            ],
            [
                'key' => 'pricing.tv.per_kg_rate_aud',
                'value' => '17',
                'group' => 'pricing',
                'label' => 'TV freight rate (AUD per kg)',
                'description' => 'Multiplied by the pallet weight in the TV landed-cost calculator.',
                'sort_order' => 2,
            ],
            [
                'key' => 'pricing.tv.airport_processing_fee_aud',
                'value' => '35',
                'group' => 'pricing',
                'label' => 'Airport processing fee (AUD)',
                'description' => 'Charged once per TV shipment.',
                'sort_order' => 3,
            ],
            [
                'key' => 'insurance.max_payout_no_cover',
                'value' => '50',
                'group' => 'pricing',
                'label' => 'Maximum payout without Product Cover (AUD)',
                'description' => 'Ceiling for lost, stolen or damaged goods when the customer declined Product Cover Insurance. Full declared value is paid when cover was taken.',
                'sort_order' => 4,
            ],
        ];

        foreach ($entries as $entry) {
            ConfigEntry::query()->updateOrCreate(
                ['key' => $entry['key']],
                $entry + ['is_secret' => false],
            );
        }
    }
}
