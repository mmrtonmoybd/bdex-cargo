<?php

declare(strict_types=1);

use App\Enums\CalculationType;
use App\Enums\ConditionField;
use App\Enums\ConditionOperator;
use App\Enums\PricingModel;
use App\Enums\WeightTierScope;
use App\Models\FxRate;
use App\Models\ItemCategory;
use App\Models\ItemCategoryZoneRate;
use App\Models\PricingZone;
use App\Models\ShippingRoute;
use App\Models\SurchargeRule;
use App\Models\WeightTier;
use App\Services\Config\DynamicConfig;
use App\Services\Pricing\PricingEngine;
use App\Services\Pricing\QuoteItem;
use Database\Seeders\ConfigEntrySeeder;

/** Builds a route with one zone, so each test states only what it cares about. */
function makeRoute(string $code = 'AU_TO_BD'): ShippingRoute
{
    return ShippingRoute::query()->create([
        'code' => $code,
        'name' => $code,
        'origin_country' => 'AU',
        'destination_country' => 'BD',
        'base_currency' => 'AUD',
        'quote_currency' => 'BDT',
        'is_active' => true,
    ]);
}

function makeZone(ShippingRoute $route, string $name = 'NSW'): PricingZone
{
    return $route->pricingZones()->create(['name' => $name, 'is_active' => true]);
}

function makeCategory(ShippingRoute $route, PricingModel $model, ?float $flat = null, bool $garment = false, string $name = 'Test category'): ItemCategory
{
    return $route->itemCategories()->create([
        'name' => $name,
        'pricing_model' => $model,
        'flat_fee_amount' => $flat,
        'is_garment' => $garment,
        'is_active' => true,
    ]);
}

function makeRate(ItemCategory $category, PricingZone $zone, float $perKg): ItemCategoryZoneRate
{
    return ItemCategoryZoneRate::query()->create([
        'item_category_id' => $category->id,
        'pricing_zone_id' => $zone->id,
        'rate_per_kg' => $perKg,
    ]);
}

describe('AU → BD base pricing', function (): void {
    it('prices a per-kg category at its zone rate', function (): void {
        $route = makeRoute();
        $zone = makeZone($route);
        $category = makeCategory($route, PricingModel::PerKgByZone, name: 'General goods');
        makeRate($category, $zone, 17);

        $quote = app(PricingEngine::class)->quoteAuToBd(
            collect([new QuoteItem($category, quantity: 1, weightKg: 10)]),
            $zone,
        );

        expect($quote->total())->toBe(170.0);
    });

    it('charges different zones different rates for the same category', function (): void {
        $route = makeRoute();
        $nsw = makeZone($route, 'NSW');
        $other = makeZone($route, 'Other AU states');
        $category = makeCategory($route, PricingModel::PerKgByZone);
        makeRate($category, $nsw, 17);
        makeRate($category, $other, 18);

        $engine = app(PricingEngine::class);
        $items = fn () => collect([new QuoteItem($category, quantity: 1, weightKg: 10)]);

        expect($engine->quoteAuToBd($items(), $nsw)->total())->toBe(170.0)
            ->and($engine->quoteAuToBd($items(), $other)->total())->toBe(180.0);
    });

    it('ignores weight for a flat-fee category', function (): void {
        $route = makeRoute();
        $zone = makeZone($route);
        $category = makeCategory($route, PricingModel::FlatFee, flat: 150, name: 'Laptop');

        $quote = app(PricingEngine::class)->quoteAuToBd(
            collect([new QuoteItem($category, quantity: 1, weightKg: 2.5)]),
            $zone,
        );

        expect($quote->total())->toBe(150.0);
    });

    it('multiplies a per-item flat fee by quantity', function (): void {
        $route = makeRoute();
        $zone = makeZone($route);
        $category = makeCategory($route, PricingModel::FlatFeePerItem, flat: 85, name: 'Smart Watch');

        $quote = app(PricingEngine::class)->quoteAuToBd(
            collect([new QuoteItem($category, quantity: 3)]),
            $zone,
        );

        expect($quote->total())->toBe(255.0);
    });

    it('fails loudly when a category has no rate for the zone', function (): void {
        $route = makeRoute();
        $zone = makeZone($route);
        $category = makeCategory($route, PricingModel::PerKgByZone);

        app(PricingEngine::class)->quoteAuToBd(
            collect([new QuoteItem($category, weightKg: 5)]),
            $zone,
        );
    })->throws(RuntimeException::class, 'has no rate for zone');
});

describe('surcharge rules', function (): void {
    it('adds an unconditional flat fee once per shipment', function (): void {
        $route = makeRoute();
        $zone = makeZone($route);
        $category = makeCategory($route, PricingModel::PerKgByZone);
        makeRate($category, $zone, 17);

        SurchargeRule::query()->create([
            'code' => 'airport',
            'label' => 'Airport processing fee',
            'shipping_route_id' => $route->id,
            'calculation_type' => CalculationType::Flat,
            'amount' => 35,
            'is_active' => true,
        ]);

        $quote = app(PricingEngine::class)->quoteAuToBd(
            collect([
                new QuoteItem($category, weightKg: 5),
                new QuoteItem($category, weightKg: 5),
            ]),
            $zone,
        );

        expect($quote->total())->toBe(205.0)
            ->and($quote->lineItems->where('sourceCode', 'airport'))->toHaveCount(1);
    });

    it('skips a rule whose condition is not met', function (): void {
        $route = makeRoute();
        $zone = makeZone($route);
        $category = makeCategory($route, PricingModel::PerKgByZone);
        makeRate($category, $zone, 10);

        SurchargeRule::query()->create([
            'code' => 'light_box',
            'label' => 'Under-15kg box fee',
            'shipping_route_id' => $route->id,
            'calculation_type' => CalculationType::Flat,
            'amount' => 15,
            'condition_field' => ConditionField::BoxWeightKg,
            'condition_operator' => ConditionOperator::LessThan,
            'condition_value' => '15',
            'is_active' => true,
        ]);

        $engine = app(PricingEngine::class);
        $items = fn () => collect([new QuoteItem($category, weightKg: 20)]);

        $heavy = $engine->quoteAuToBd($items(), $zone, boxCount: 1, lightestBoxWeightKg: 20);
        $light = $engine->quoteAuToBd($items(), $zone, boxCount: 1, lightestBoxWeightKg: 12);

        expect($heavy->total())->toBe(200.0)
            ->and($light->total())->toBe(215.0);
    });

    it('applies a category-scoped per-item rule only to qualifying lines', function (): void {
        $route = makeRoute();
        $zone = makeZone($route);

        $electrical = makeCategory($route, PricingModel::PerKgByZone, name: 'Electrical appliances');
        $general = makeCategory($route, PricingModel::PerKgByZone, name: 'General goods');
        makeRate($electrical, $zone, 17);
        makeRate($general, $zone, 17);

        SurchargeRule::query()->create([
            'code' => 'high_value_electrical',
            'label' => 'High-value electrical item handling',
            'shipping_route_id' => $route->id,
            'item_category_id' => $electrical->id,
            'calculation_type' => CalculationType::PerItem,
            'amount' => 15,
            'condition_field' => ConditionField::DeclaredValue,
            'condition_operator' => ConditionOperator::GreaterThan,
            'condition_value' => '100',
            'is_active' => true,
        ]);

        $quote = app(PricingEngine::class)->quoteAuToBd(
            collect([
                // Qualifies: electrical, declared over $100, two units.
                new QuoteItem($electrical, quantity: 2, weightKg: 1, declaredValue: 250),
                // Same declared value, wrong category.
                new QuoteItem($general, quantity: 2, weightKg: 1, declaredValue: 250),
                // Right category, under the threshold.
                new QuoteItem($electrical, quantity: 1, weightKg: 1, declaredValue: 40),
            ]),
            $zone,
        );

        $surcharges = $quote->lineItems->where('sourceCode', 'high_value_electrical');

        expect($surcharges)->toHaveCount(1)
            ->and($surcharges->first()->amount)->toBe(30.0);
    });

    it('charges a percentage rule against the base subtotal', function (): void {
        $route = makeRoute();
        $zone = makeZone($route);
        $category = makeCategory($route, PricingModel::PerKgByZone);
        makeRate($category, $zone, 20);

        SurchargeRule::query()->create([
            'code' => 'fuel',
            'label' => 'Fuel levy',
            'shipping_route_id' => $route->id,
            'calculation_type' => CalculationType::Percentage,
            'amount' => 10,
            'is_active' => true,
        ]);

        $quote = app(PricingEngine::class)->quoteAuToBd(
            collect([new QuoteItem($category, weightKg: 5)]),
            $zone,
        );

        expect($quote->total())->toBe(110.0);
    });

    it('charges a per-kg rule against the shipment weight', function (): void {
        $route = makeRoute();
        $zone = makeZone($route);
        $category = makeCategory($route, PricingModel::PerKgByZone);
        makeRate($category, $zone, 20);

        SurchargeRule::query()->create([
            'code' => 'remote',
            'label' => 'Remote area loading',
            'shipping_route_id' => $route->id,
            'calculation_type' => CalculationType::PerKg,
            'amount' => 2,
            'is_active' => true,
        ]);

        $quote = app(PricingEngine::class)->quoteAuToBd(
            collect([new QuoteItem($category, weightKg: 5)]),
            $zone,
        );

        expect($quote->total())->toBe(110.0);
    });

    it('applies a route-agnostic rule to every route', function (): void {
        $route = makeRoute();
        $zone = makeZone($route);
        $category = makeCategory($route, PricingModel::PerKgByZone);
        makeRate($category, $zone, 10);

        SurchargeRule::query()->create([
            'code' => 'global_admin_fee',
            'label' => 'Documentation fee',
            'shipping_route_id' => null,
            'calculation_type' => CalculationType::Flat,
            'amount' => 5,
            'is_active' => true,
        ]);

        $quote = app(PricingEngine::class)->quoteAuToBd(
            collect([new QuoteItem($category, weightKg: 1)]),
            $zone,
        );

        expect($quote->total())->toBe(15.0);
    });

    it('ignores an inactive rule', function (): void {
        $route = makeRoute();
        $zone = makeZone($route);
        $category = makeCategory($route, PricingModel::PerKgByZone);
        makeRate($category, $zone, 10);

        SurchargeRule::query()->create([
            'code' => 'retired',
            'label' => 'Retired fee',
            'shipping_route_id' => $route->id,
            'calculation_type' => CalculationType::Flat,
            'amount' => 99,
            'is_active' => false,
        ]);

        $quote = app(PricingEngine::class)->quoteAuToBd(
            collect([new QuoteItem($category, weightKg: 1)]),
            $zone,
        );

        expect($quote->total())->toBe(10.0);
    });
});

describe('BD → AU weight tiers', function (): void {
    beforeEach(function (): void {
        $this->route = makeRoute('BD_TO_AU');

        WeightTier::query()->create([
            'shipping_route_id' => $this->route->id,
            'min_kg' => 1,
            'max_kg' => 10,
            'price_per_kg' => 44,
            'applies_to' => WeightTierScope::GarmentOnly,
            'sort_order' => 1,
            'is_active' => true,
        ]);

        WeightTier::query()->create([
            'shipping_route_id' => $this->route->id,
            'min_kg' => 10.1,
            'max_kg' => 15,
            'price_per_kg' => 21,
            'applies_to' => WeightTierScope::GarmentOnly,
            'sort_order' => 2,
            'is_active' => true,
        ]);
    });

    it('prices a garment shipment from the matching tier', function (): void {
        $quote = app(PricingEngine::class)->quoteBdToAu(5, true, $this->route);

        expect($quote->total())->toBe(220.0);
    });

    it('moves to the next tier as weight crosses the band', function (): void {
        $quote = app(PricingEngine::class)->quoteBdToAu(12, true, $this->route);

        expect($quote->total())->toBe(252.0);
    });

    it('refuses to guess when no tier covers the weight', function (): void {
        app(PricingEngine::class)->quoteBdToAu(40, true, $this->route);
    })->throws(RuntimeException::class, 'No active weight tier');

    it('will not use a garment-only tier for non-garment goods', function (): void {
        app(PricingEngine::class)->quoteBdToAu(5, false, $this->route);
    })->throws(RuntimeException::class, 'No active weight tier');

    it('adds the non-garment handling charge when the shipment is not garments', function (): void {
        WeightTier::query()->create([
            'shipping_route_id' => $this->route->id,
            'min_kg' => 1,
            'max_kg' => 20,
            'price_per_kg' => 30,
            'applies_to' => WeightTierScope::All,
            'sort_order' => 3,
            'is_active' => true,
        ]);

        SurchargeRule::query()->create([
            'code' => 'non_garment',
            'label' => 'Non-garment handling',
            'shipping_route_id' => $this->route->id,
            'calculation_type' => CalculationType::Flat,
            'amount' => 25,
            'condition_field' => ConditionField::IsNonGarment,
            'condition_operator' => ConditionOperator::IsTrue,
            'is_active' => true,
        ]);

        $engine = app(PricingEngine::class);

        expect($engine->quoteBdToAu(5, false, $this->route)->total())->toBe(175.0)
            ->and($engine->quoteBdToAu(5, true, $this->route)->total())->toBe(220.0);
    });
});

describe('tax and currency', function (): void {
    it('applies the tax rate from dynamic config', function (): void {
        $this->seed(ConfigEntrySeeder::class);
        DynamicConfig::set('pricing.tax_rate_percent', '10');

        $route = makeRoute();
        $zone = makeZone($route);
        $category = makeCategory($route, PricingModel::PerKgByZone);
        makeRate($category, $zone, 10);

        $quote = app(PricingEngine::class)->quoteAuToBd(
            collect([new QuoteItem($category, weightKg: 10)]),
            $zone,
        );

        expect($quote->subtotal())->toBe(100.0)
            ->and($quote->tax())->toBe(10.0)
            ->and($quote->total())->toBe(110.0);
    });

    it('converts the total using the newest stored rate', function (): void {
        $route = makeRoute();
        $zone = makeZone($route);
        $category = makeCategory($route, PricingModel::PerKgByZone);
        makeRate($category, $zone, 10);

        FxRate::query()->create([
            'from_currency' => 'AUD',
            'to_currency' => 'BDT',
            'rate' => 70,
            'fetched_at' => now()->subDay(),
        ]);

        FxRate::query()->create([
            'from_currency' => 'AUD',
            'to_currency' => 'BDT',
            'rate' => 80,
            'fetched_at' => now(),
        ]);

        $quote = app(PricingEngine::class)->quoteAuToBd(
            collect([new QuoteItem($category, weightKg: 1)]),
            $zone,
        );

        expect($quote->fxRateUsed)->toBe(80.0)
            ->and($quote->convertedTotal())->toBe(800.0);
    });

    it('reports no converted total when no rate is stored', function (): void {
        $route = makeRoute();
        $zone = makeZone($route);
        $category = makeCategory($route, PricingModel::PerKgByZone);
        makeRate($category, $zone, 10);

        $quote = app(PricingEngine::class)->quoteAuToBd(
            collect([new QuoteItem($category, weightKg: 1)]),
            $zone,
        );

        expect($quote->convertedTotal())->toBeNull();
    });
});

describe('quote traceability', function (): void {
    it('itemises every applied rule back to its source', function (): void {
        $route = makeRoute();
        $zone = makeZone($route);
        $category = makeCategory($route, PricingModel::PerKgByZone, name: 'General goods');
        makeRate($category, $zone, 17);

        SurchargeRule::query()->create([
            'code' => 'airport',
            'label' => 'Airport processing fee',
            'shipping_route_id' => $route->id,
            'calculation_type' => CalculationType::Flat,
            'amount' => 35,
            'is_active' => true,
        ]);

        $breakdown = app(PricingEngine::class)->quoteAuToBd(
            collect([new QuoteItem($category, weightKg: 10)]),
            $zone,
        )->toArray();

        expect($breakdown['line_items'])->toHaveCount(2)
            ->and($breakdown['line_items'][0]['label'])->toBe('General goods')
            ->and($breakdown['line_items'][0]['source_type'])->toBe('item_category')
            ->and($breakdown['line_items'][1]['source_code'])->toBe('airport')
            ->and($breakdown['total'])->toBe(205.0);
    });
});
