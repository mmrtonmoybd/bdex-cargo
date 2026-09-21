<?php

declare(strict_types=1);

namespace App\Services\Pricing;

use App\Models\TvSizeBand;
use App\Services\Config\DynamicConfig;

/**
 * Landed cost of shipping a television AU → BD.
 *
 * Shape of the calculation:
 *   (per-kg rate × pallet weight) + airport processing fee + volume fee   [AUD]
 *   + Bangladesh government duty + local home delivery                     [BDT]
 *
 * The two shared inputs — per-kg rate and airport fee — are config_entries rows
 * in the `pricing` group, because they are not per-size. Everything else is a
 * column on the size band, so adding a screen size is one row in Filament.
 */
final class TvCostCalculator
{
    public const PER_KG_RATE_KEY = 'pricing.tv.per_kg_rate_aud';

    public const AIRPORT_FEE_KEY = 'pricing.tv.airport_processing_fee_aud';

    public function __construct(private readonly FxConverter $fx = new FxConverter) {}

    public function calculate(TvSizeBand $band, ?float $palletWeightKg = null): TvQuoteResult
    {
        $weightKg = $palletWeightKg ?? (float) $band->default_pallet_weight_kg;
        $perKgRate = DynamicConfig::float(self::PER_KG_RATE_KEY);
        $airportFee = DynamicConfig::float(self::AIRPORT_FEE_KEY);

        $audLines = collect([
            new QuoteLineItem(
                label: 'Air freight',
                amount: round($perKgRate * $weightKg, 2),
                sourceType: 'config',
                sourceCode: self::PER_KG_RATE_KEY,
                detail: sprintf('%s/kg × %skg pallet', number_format($perKgRate, 2), rtrim(rtrim(number_format($weightKg, 3, '.', ''), '0'), '.')),
            ),
            new QuoteLineItem(
                label: 'Airport processing fee',
                amount: round($airportFee, 2),
                sourceType: 'config',
                sourceCode: self::AIRPORT_FEE_KEY,
            ),
            new QuoteLineItem(
                label: sprintf('Volume handling (%s)', $band->size_label),
                amount: round((float) $band->volume_fee_aud, 2),
                sourceType: 'tv_size_band',
                sourceCode: (string) $band->id,
            ),
        ]);

        $bdtLines = collect([
            new QuoteLineItem(
                label: sprintf('Bangladesh government duty (%s)', $band->size_label),
                amount: round((float) $band->bd_govt_duty_bdt, 2),
                sourceType: 'tv_size_band',
                sourceCode: (string) $band->id,
            ),
            new QuoteLineItem(
                label: 'Local home delivery',
                amount: round((float) $band->local_delivery_bdt, 2),
                sourceType: 'tv_size_band',
                sourceCode: (string) $band->id,
            ),
        ]);

        $rate = $this->fx->latest('AUD', 'BDT');

        return new TvQuoteResult(
            audLineItems: $audLines,
            bdtLineItems: $bdtLines,
            bdtPerAudRate: $rate === null ? null : (float) $rate->rate,
            fxFetchedAt: $rate?->fetched_at->toIso8601String(),
        );
    }
}
