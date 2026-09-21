<?php

declare(strict_types=1);

namespace App\Services\Pricing;

use App\Models\FxRate;

/**
 * Reads the newest stored rate for a currency pair.
 *
 * Deliberately a read of `fx_rates`, not a live API call: quoting must not depend
 * on a third party being up. Whatever writes those rows — staff by hand today, a
 * scheduled FxRateSource driver later — is a separate concern.
 */
final class FxConverter
{
    public function latest(string $from, string $to): ?FxRate
    {
        if ($from === $to) {
            return null;
        }

        return FxRate::query()
            ->forPair($from, $to)
            ->latest('fetched_at')
            ->first();
    }
}
