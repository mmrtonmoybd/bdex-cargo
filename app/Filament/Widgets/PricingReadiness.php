<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\FxRate;
use App\Models\ItemCategory;
use App\Models\PricingZone;
use App\Models\SurchargeRule;
use App\Models\TvSizeBand;
use Filament\Widgets\Widget;

/**
 * Surfaces pricing data that is seeded but not yet fit to charge against.
 *
 * Some published figures were missing or contradictory in the source material.
 * Rather than inventing a number and letting it quietly ship, those rows are
 * flagged here until someone confirms them.
 */
class PricingReadiness extends Widget
{
    protected string $view = 'filament.widgets.pricing-readiness';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = -1;

    public static function canView(): bool
    {
        return self::issues() !== [];
    }

    /** @return array<int, array{title: string, body: string, url: ?string}> */
    public function getIssues(): array
    {
        return self::issues();
    }

    /** @return array<int, array{title: string, body: string, url: ?string}> */
    private static function issues(): array
    {
        $issues = [];

        $placeholders = SurchargeRule::query()
            ->where('is_placeholder_amount', true)
            ->where('is_active', true)
            ->pluck('label');

        if ($placeholders->isNotEmpty()) {
            $issues[] = [
                'title' => 'Surcharge amounts still marked as placeholders',
                'body' => $placeholders->implode(', ').' — these are charging a stand-in figure. Confirm the real amount and clear the placeholder flag.',
                'url' => null,
            ];
        }

        $incompleteBands = TvSizeBand::query()
            ->where('is_active', false)
            ->count();

        if ($incompleteBands > 0) {
            $issues[] = [
                'title' => "{$incompleteBands} TV size bands are inactive",
                'body' => 'Their government duty is known but the volume fee and local delivery charge were never published. Fill both in, then activate the band to quote it.',
                'url' => null,
            ];
        }

        if (! FxRate::query()->exists()) {
            $issues[] = [
                'title' => 'No exchange rate stored',
                'body' => 'Quotes show the AUD total only until a rate exists for the route\'s currency pair.',
                'url' => null,
            ];
        }

        $zoneCount = PricingZone::query()->where('is_active', true)->count();

        $missingRates = ItemCategory::query()
            ->where('is_active', true)
            ->where('pricing_model', 'per_kg_by_zone')
            ->withCount('zoneRates')
            ->get()
            ->filter(fn (ItemCategory $category): bool => $category->zone_rates_count < $zoneCount)
            ->pluck('name');

        if ($missingRates->isNotEmpty()) {
            $issues[] = [
                'title' => 'Categories missing a rate in at least one zone',
                'body' => $missingRates->implode(', ').' — quoting one of these into a zone with no rate fails rather than guessing.',
                'url' => null,
            ];
        }

        return $issues;
    }
}
