<?php

declare(strict_types=1);

namespace App\Services\Pricing;

use App\Enums\PricingModel;
use App\Enums\WeightTierScope;
use App\Models\Customer;
use App\Models\PricingAgreement;
use App\Models\PricingZone;
use App\Models\ShippingRoute;
use App\Models\SurchargeRule;
use App\Models\WeightTier;
use App\Services\Config\DynamicConfig;
use Illuminate\Support\Collection;
use RuntimeException;

/**
 * Every price the customer ever sees comes out of this class.
 *
 * It holds no rates of its own: base prices come from item_category_zone_rates or
 * weight_tiers, fees come from surcharge_rules, the tax rate comes from
 * config_entries, and the conversion rate comes from fx_rates. Changing any of
 * them is Filament data entry.
 *
 * Reads are deliberately uncached. Prices change rarely, but a stale quote on an
 * active booking is worse than the query.
 */
final class PricingEngine
{
    public function __construct(
        private readonly SurchargeEvaluator $surcharges = new SurchargeEvaluator,
        private readonly FxConverter $fx = new FxConverter,
    ) {}

    /**
     * AU → BD: priced per item category, at the rate for the sender's zone.
     *
     * @param  Collection<int, QuoteItem>  $items
     */
    public function quoteAuToBd(
        Collection $items,
        PricingZone $zone,
        int $boxCount = 1,
        float $lightestBoxWeightKg = 0.0,
        ?Customer $customer = null,
    ): QuoteResult {
        $route = $zone->shippingRoute()->firstOrFail();

        $lineItems = $items
            ->map(fn (QuoteItem $item): QuoteLineItem => $this->baseLineFor($item, $zone))
            ->values();

        $context = $this->contextFor($items, $boxCount, $lightestBoxWeightKg);

        return $this->applySurcharges($lineItems, $items, $context, $route, customer: $customer);
    }

    /**
     * BD → AU: priced from the shipment's total weight, banded by weight tier.
     */
    public function quoteBdToAu(
        float $totalWeightKg,
        bool $isGarment,
        ShippingRoute $route,
        int $boxCount = 1,
        ?Customer $customer = null,
    ): QuoteResult {
        $tier = $this->weightTierFor($route, $totalWeightKg, $isGarment);

        if ($tier === null) {
            throw new RuntimeException(
                "No active weight tier on route {$route->code} covers {$totalWeightKg}kg. Add one in Pricing → Weight tiers."
            );
        }

        $lineItems = collect([
            new QuoteLineItem(
                label: sprintf('%s freight — %skg', $route->name, rtrim(rtrim(number_format($totalWeightKg, 3, '.', ''), '0'), '.')),
                amount: round((float) $tier->price_per_kg * $totalWeightKg, 2),
                sourceType: 'weight_tier',
                sourceCode: (string) $tier->id,
                detail: sprintf('%s per kg', number_format((float) $tier->price_per_kg, 2)),
            ),
        ]);

        $context = new QuoteContext(
            totalWeightKg: $totalWeightKg,
            isNonGarment: ! $isGarment,
            boxCount: $boxCount,
            itemCount: $boxCount,
        );

        return $this->applySurcharges($lineItems, collect(), $context, $route, $totalWeightKg, $customer);
    }

    /** The active tier covering a weight, honouring garment-only scoping. */
    public function weightTierFor(ShippingRoute $route, float $weightKg, bool $isGarment): ?WeightTier
    {
        return $route->weightTiers()
            ->active()
            ->when(
                ! $isGarment,
                fn ($query) => $query->where('applies_to', WeightTierScope::All->value)
            )
            ->orderBy('sort_order')
            ->orderBy('min_kg')
            ->get()
            ->first(fn (WeightTier $tier): bool => $tier->covers($weightKg));
    }

    /** Base price for one line, by its category's pricing model. */
    private function baseLineFor(QuoteItem $item, PricingZone $zone): QuoteLineItem
    {
        $category = $item->category;

        $amount = match ($category->pricing_model) {
            PricingModel::PerKgByZone => $this->perKgByZone($item, $zone),
            PricingModel::PerKgFlat => (float) $category->flat_fee_amount * $item->weightKg,
            PricingModel::FlatFee => (float) $category->flat_fee_amount,
            PricingModel::FlatFeePerItem => (float) $category->flat_fee_amount * $item->quantity,
        };

        return new QuoteLineItem(
            label: $category->name,
            amount: round($amount, 2),
            sourceType: 'item_category',
            sourceCode: (string) $category->id,
            detail: $this->baseLineDetail($item, $zone),
        );
    }

    private function perKgByZone(QuoteItem $item, PricingZone $zone): float
    {
        $rate = $item->category->rateForZone($zone);

        if ($rate === null) {
            throw new RuntimeException(
                "Item category '{$item->category->name}' has no rate for zone '{$zone->name}'. Add one in Pricing → Zone rates."
            );
        }

        return $rate * $item->weightKg;
    }

    private function baseLineDetail(QuoteItem $item, PricingZone $zone): string
    {
        return match ($item->category->pricing_model) {
            PricingModel::PerKgByZone => sprintf(
                '%s/kg (%s) × %skg',
                number_format((float) ($item->category->rateForZone($zone) ?? 0), 2),
                $zone->name,
                rtrim(rtrim(number_format($item->weightKg, 3, '.', ''), '0'), '.'),
            ),
            PricingModel::PerKgFlat => sprintf(
                '%s/kg × %skg',
                number_format((float) $item->category->flat_fee_amount, 2),
                rtrim(rtrim(number_format($item->weightKg, 3, '.', ''), '0'), '.'),
            ),
            PricingModel::FlatFee => 'Flat rate',
            PricingModel::FlatFeePerItem => sprintf('%s × %d', number_format((float) $item->category->flat_fee_amount, 2), $item->quantity),
        };
    }

    /** @param Collection<int, QuoteItem> $items */
    private function contextFor(Collection $items, int $boxCount, float $lightestBoxWeightKg): QuoteContext
    {
        return new QuoteContext(
            declaredValue: (float) $items->max(fn (QuoteItem $item): float => $item->declaredValue) ?: 0.0,
            boxWeightKg: $lightestBoxWeightKg,
            totalWeightKg: (float) $items->sum(fn (QuoteItem $item): float => $item->totalWeightKg()),
            isNonGarment: $items->contains(fn (QuoteItem $item): bool => ! $item->category->is_garment),
            boxCount: $boxCount,
            itemCount: (int) $items->sum(fn (QuoteItem $item): int => $item->quantity),
        );
    }

    /**
     * Appends every active surcharge rule that applies, then wraps the result.
     *
     * Shipment-wide rules are evaluated once against the shipment context;
     * category-scoped rules are evaluated per matching line against that line's
     * own context, so "per item over $100" means per qualifying item.
     *
     * @param  Collection<int, QuoteLineItem>  $lineItems
     * @param  Collection<int, QuoteItem>  $items
     */
    private function applySurcharges(
        Collection $lineItems,
        Collection $items,
        QuoteContext $context,
        ShippingRoute $route,
        ?float $weightOverrideKg = null,
        ?Customer $customer = null,
    ): QuoteResult {
        $weightKg = $weightOverrideKg ?? $context->totalWeightKg;
        $subtotal = (float) $lineItems->sum(fn (QuoteLineItem $line): float => $line->amount);

        $rules = SurchargeRule::query()
            ->active()
            ->forRoute($route->id)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        foreach ($rules as $rule) {
            if ($rule->item_category_id !== null) {
                $lineItems = $lineItems->concat(
                    $this->categoryScopedLines($rule, $items, $context, $subtotal)
                );

                continue;
            }

            $line = $this->surcharges->evaluate($rule, $context, $subtotal, $weightKg, $context->itemCount);

            if ($line !== null) {
                $lineItems->push($line);
            }
        }

        // Volume tiers and contract rates discount the engine's own price, so an
        // upstream rate change still reaches every agreement.
        $discount = $this->agreementDiscount(
            $route,
            $customer,
            $weightKg,
            (float) $lineItems->sum(fn (QuoteLineItem $line): float => $line->amount),
        );

        if ($discount !== null) {
            $lineItems->push($discount);
        }

        $fxRate = $this->fx->latest($route->base_currency, $route->quote_currency);

        return new QuoteResult(
            lineItems: $lineItems->values(),
            baseCurrency: $route->base_currency,
            quoteCurrency: $route->quote_currency,
            // The corridor's own rate; the config value remains the fallback for a
            // route nobody has set one on yet.
            taxRatePercent: (float) $route->tax_rate_percent > 0
                ? (float) $route->tax_rate_percent
                : DynamicConfig::float('pricing.tax_rate_percent'),
            taxLabel: $route->tax_label ?? 'Tax',
            fxRateUsed: $fxRate === null ? null : (float) $fxRate->rate,
            fxFetchedAt: $fxRate?->fetched_at->toIso8601String(),
        );
    }

    /**
     * The best agreement this shipment qualifies for, as a discount line.
     *
     * A customer's own contract wins over a published volume tier, and among
     * equals the higher priority wins — so a negotiated rate is never silently
     * beaten by a promotion.
     */
    private function agreementDiscount(
        ShippingRoute $route,
        ?Customer $customer,
        float $weightKg,
        float $subtotal,
    ): ?QuoteLineItem {
        if ($subtotal <= 0.0) {
            return null;
        }

        $shipmentsThisMonth = $customer === null
            ? 0
            : $customer->orders()->where('created_at', '>=', now()->startOfMonth())->count();

        $agreement = PricingAgreement::query()
            ->active()
            ->availableTo($customer?->id, $route->id)
            ->orderByDesc('priority')
            ->orderByDesc('discount_percent')
            ->get()
            ->filter(fn (PricingAgreement $a): bool => $a->qualifies($weightKg, $shipmentsThisMonth))
            ->sortByDesc(fn (PricingAgreement $a): array => [$a->isContract() ? 1 : 0, $a->priority, (float) $a->discount_percent])
            ->first();

        if ($agreement === null || (float) $agreement->discount_percent <= 0.0) {
            return null;
        }

        return new QuoteLineItem(
            label: $agreement->label,
            amount: -round($subtotal * ((float) $agreement->discount_percent / 100), 2),
            sourceType: 'pricing_agreement',
            sourceCode: (string) $agreement->id,
            detail: rtrim(rtrim(number_format((float) $agreement->discount_percent, 2), '0'), '.').'% off',
        );
    }

    /**
     * @param  Collection<int, QuoteItem>  $items
     * @return Collection<int, QuoteLineItem>
     */
    private function categoryScopedLines(
        SurchargeRule $rule,
        Collection $items,
        QuoteContext $context,
        float $subtotal,
    ): Collection {
        return $items
            ->filter(fn (QuoteItem $item): bool => $item->category->id === $rule->item_category_id)
            ->map(fn (QuoteItem $item): ?QuoteLineItem => $this->surcharges->evaluate(
                $rule,
                $context->forItem($item),
                $subtotal,
                $item->totalWeightKg(),
                $item->quantity,
            ))
            ->filter()
            ->values();
    }
}
