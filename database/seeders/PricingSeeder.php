<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\CalculationType;
use App\Enums\ConditionField;
use App\Enums\ConditionOperator;
use App\Enums\PricingModel;
use App\Enums\WeightTierScope;
use App\Models\ItemCategory;
use App\Models\ItemCategoryZoneRate;
use App\Models\PricingZone;
use App\Models\ShippingRoute;
use App\Models\SurchargeRule;
use App\Models\TvSizeBand;
use App\Models\WeightTier;
use Illuminate\Database\Seeder;

/**
 * Day-one pricing data.
 *
 * Everything written here is an ordinary row an admin can edit, reorder or
 * deactivate afterwards. Re-running the seeder refreshes these rows without
 * touching anything staff have added.
 */
final class PricingSeeder extends Seeder
{
    public function run(): void
    {
        $auToBd = $this->route(
            code: 'AU_TO_BD',
            name: 'Australia → Bangladesh',
            origin: 'AU',
            destination: 'BD',
            base: 'AUD',
            quote: 'BDT',
            // Published materials disagree — one graphic says 10-18 working days.
            // Staff own this wording per route rather than the code choosing.
            transit: '10 to 18 working days',
            sort: 1,
        );

        $bdToAu = $this->route(
            code: 'BD_TO_AU',
            name: 'Bangladesh → Australia',
            origin: 'BD',
            destination: 'AU',
            base: 'AUD',
            quote: 'BDT',
            // Source materials disagree here too: one graphic says "4+ estimated
            // business days", a separate document says 6-9 business days.
            transit: '4+ estimated business days',
            sort: 2,
        );

        $zones = $this->zones($auToBd);

        $this->categories($auToBd, $zones);
        $this->weightTiers($bdToAu);
        $this->tvSizeBands($auToBd);
        $this->surchargeRules($auToBd, $bdToAu);
    }

    private function route(
        string $code,
        string $name,
        string $origin,
        string $destination,
        string $base,
        string $quote,
        string $transit,
        int $sort,
    ): ShippingRoute {
        return ShippingRoute::query()->updateOrCreate(
            ['code' => $code],
            [
                'name' => $name,
                'origin_country' => $origin,
                'destination_country' => $destination,
                'base_currency' => $base,
                'quote_currency' => $quote,
                'transit_time_text' => $transit,
                'sort_order' => $sort,
                'is_active' => true,
            ],
        );
    }

    /** @return array<string, PricingZone> */
    private function zones(ShippingRoute $route): array
    {
        $definitions = [
            'NSW' => ['code' => 'NSW', 'sort' => 1, 'description' => 'New South Wales'],
            'OTHER' => ['code' => 'OTHER', 'sort' => 2, 'description' => 'ACT, VIC, QLD, SA, WA and TAS'],
        ];

        $zones = [];

        foreach ($definitions as $key => $definition) {
            $zones[$key] = PricingZone::query()->updateOrCreate(
                ['shipping_route_id' => $route->id, 'name' => $key === 'NSW' ? 'NSW' : 'Other AU states'],
                [
                    'code' => $definition['code'],
                    'description' => $definition['description'],
                    'sort_order' => $definition['sort'],
                    'is_active' => true,
                ],
            );
        }

        return $zones;
    }

    /** @param array<string, PricingZone> $zones */
    private function categories(ShippingRoute $route, array $zones): void
    {
        // [name, model, nsw rate, other rate, flat amount, max kg, description]
        $categories = [
            ['General goods', PricingModel::PerKgByZone, 17, 18, null, null, 'Household items, baby food, powder milk, clothing, shoes, chocolates, dry foods, leather goods and books.'],
            ['Electrical appliances', PricingModel::PerKgByZone, 17, 18, null, null, 'Items declared above $100 attract an additional per-item fee.'],
            ['Blood sugar / BP device', PricingModel::PerKgByZone, 17, 18, null, null, 'Carries an additional per-item handling fee.'],
            ['Toiletries', PricingModel::PerKgByZone, 20, 20, null, null, 'Shampoo, lotion, toothpaste, face wash, cream and conditioner.'],
            ['Makeup & beauty', PricingModel::PerKgByZone, 25, 25, null, null, 'Lipstick, serum, eyeshadow, makeup palettes, eyeliner and foundation.'],
            ['Vitamin & supplement', PricingModel::PerKgByZone, 27, 27, null, null, null],
            ['Automobile parts & accessories', PricingModel::PerKgByZone, 30, 30, null, null, null],
            ['Tech items & computer equipment', PricingModel::PerKgByZone, 35, 35, null, null, null],
            ['DSLR camera, iPad & Tab', PricingModel::FlatFee, null, null, 100, 1, 'Flat rate for units up to 1kg.'],
            ['Gaming console – Xbox / PlayStation', PricingModel::FlatFee, null, null, 120, 2.5, 'Flat rate for units up to 2.5kg.'],
            ['Laptop', PricingModel::FlatFee, null, null, 150, 2.5, 'Flat rate for units up to 2.5kg.'],
            ['Smart Watch', PricingModel::FlatFeePerItem, null, null, 85, null, 'Charged per piece.'],
            ['AirPods / Buds', PricingModel::FlatFeePerItem, null, null, 50, null, 'Charged per piece.'],
        ];

        foreach ($categories as $index => [$name, $model, $nsw, $other, $flat, $maxKg, $description]) {
            $category = ItemCategory::query()->updateOrCreate(
                ['shipping_route_id' => $route->id, 'name' => $name],
                [
                    'description' => $description,
                    'pricing_model' => $model,
                    'flat_fee_amount' => $flat,
                    'max_weight_kg' => $maxKg,
                    'is_garment' => $name === 'General goods',
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ],
            );

            if (! $model->usesZoneRates()) {
                continue;
            }

            foreach (['NSW' => $nsw, 'OTHER' => $other] as $zoneKey => $rate) {
                ItemCategoryZoneRate::query()->updateOrCreate(
                    ['item_category_id' => $category->id, 'pricing_zone_id' => $zones[$zoneKey]->id],
                    ['rate_per_kg' => $rate],
                );
            }
        }
    }

    private function weightTiers(ShippingRoute $route): void
    {
        // The published BD → AU tier sheet, in full. Every band is a row an admin
        // can reprice, split or retire.
        $tiers = [
            ['min' => 0.1, 'max' => 1, 'price' => 44],
            ['min' => 1.1, 'max' => 2, 'price' => 33],
            ['min' => 2.1, 'max' => 3, 'price' => 29],
            ['min' => 3.1, 'max' => 4, 'price' => 27],
            ['min' => 4.1, 'max' => 5, 'price' => 26],
            ['min' => 5.1, 'max' => 6, 'price' => 25],
            ['min' => 6.1, 'max' => 7, 'price' => 24],
            ['min' => 7.1, 'max' => 8, 'price' => 23.5],
            ['min' => 8.1, 'max' => 9, 'price' => 22.8],
            ['min' => 9.1, 'max' => 10, 'price' => 22.5],
            ['min' => 10.1, 'max' => 15, 'price' => 21],
            ['min' => 15.1, 'max' => 20, 'price' => 20],
            ['min' => 20.1, 'max' => 30, 'price' => 19],
            ['min' => 30.1, 'max' => 50, 'price' => 18],
            ['min' => 50.1, 'max' => 70, 'price' => 17],
            ['min' => 70.1, 'max' => null, 'price' => 15],
        ];

        // The published sheet is the whole sheet: drop any band it no longer lists so
        // a reseed cannot leave a stale row quietly underpricing a shipment.
        WeightTier::query()
            ->where('shipping_route_id', $route->id)
            ->whereNotIn('min_kg', array_column($tiers, 'min'))
            ->delete();

        foreach ($tiers as $index => $tier) {
            WeightTier::query()->updateOrCreate(
                ['shipping_route_id' => $route->id, 'min_kg' => $tier['min']],
                [
                    'max_kg' => $tier['max'],
                    'price_per_kg' => $tier['price'],
                    'applies_to' => WeightTierScope::All,
                    'note' => '*For garment items only',
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ],
            );
        }
    }

    private function tvSizeBands(ShippingRoute $route): void
    {
        // Duty comes from the published BD government table; volume fee and local
        // delivery were published for four sizes only. The remaining duty bands are
        // seeded inactive so staff can see them and fill in the missing two figures
        // rather than having a zero quietly underprice a shipment.
        $bands = [
            ['label' => '28-32"', 'min' => 28, 'max' => 32, 'duty' => 10000, 'volume' => null, 'delivery' => null],
            ['label' => '33-37"', 'min' => 33, 'max' => 37, 'duty' => 15000, 'volume' => null, 'delivery' => null],
            ['label' => '38-42"', 'min' => 38, 'max' => 42, 'duty' => 20000, 'volume' => null, 'delivery' => null],
            ['label' => '43-47"', 'min' => 43, 'max' => 47, 'duty' => 30000, 'volume' => null, 'delivery' => null],
            ['label' => '48-52"', 'min' => 48, 'max' => 52, 'duty' => 50000, 'volume' => null, 'delivery' => null],
            ['label' => '55"', 'min' => 53, 'max' => 60, 'duty' => 70000, 'volume' => 145, 'delivery' => 2000],
            ['label' => '65"', 'min' => 61, 'max' => 65, 'duty' => 70000, 'volume' => 170, 'delivery' => 2000],
            ['label' => '75"', 'min' => 66, 'max' => 80, 'duty' => 90000, 'volume' => 220, 'delivery' => 5000],
            ['label' => '85"', 'min' => 81, 'max' => null, 'duty' => 90000, 'volume' => 250, 'delivery' => 5000],
        ];

        foreach ($bands as $index => $band) {
            $complete = $band['volume'] !== null && $band['delivery'] !== null;

            TvSizeBand::query()->updateOrCreate(
                ['shipping_route_id' => $route->id, 'size_label' => $band['label']],
                [
                    'min_inches' => $band['min'],
                    'max_inches' => $band['max'],
                    'bd_govt_duty_bdt' => $band['duty'],
                    'volume_fee_aud' => $band['volume'] ?? 0,
                    'local_delivery_bdt' => $band['delivery'] ?? 0,
                    'default_pallet_weight_kg' => 12,
                    'sort_order' => $index + 1,
                    'is_active' => $complete,
                ],
            );
        }
    }

    private function surchargeRules(ShippingRoute $auToBd, ShippingRoute $bdToAu): void
    {
        $electrical = ItemCategory::query()
            ->where('shipping_route_id', $auToBd->id)
            ->where('name', 'Electrical appliances')
            ->first();

        $medical = ItemCategory::query()
            ->where('shipping_route_id', $auToBd->id)
            ->where('name', 'Blood sugar / BP device')
            ->first();

        $rules = [
            [
                'code' => 'au_bd_airport_fee',
                'label' => 'Airport processing fee',
                'description' => 'Charged once per shipment leaving Australia.',
                'shipping_route_id' => $auToBd->id,
                'item_category_id' => null,
                'calculation_type' => CalculationType::Flat,
                'amount' => 35,
                'condition_field' => null,
                'condition_operator' => null,
                'condition_value' => null,
                'is_placeholder_amount' => false,
                'sort_order' => 1,
            ],
            [
                'code' => 'au_bd_electrical_high_value',
                'label' => 'High-value electrical item handling',
                'description' => 'Published rate card shows $15/$20 per item without saying which applies when — confirm the correct figure before launch.',
                'shipping_route_id' => $auToBd->id,
                'item_category_id' => $electrical?->id,
                'calculation_type' => CalculationType::PerItem,
                'amount' => 15,
                'condition_field' => ConditionField::DeclaredValue,
                'condition_operator' => ConditionOperator::GreaterThan,
                'condition_value' => '100',
                'is_placeholder_amount' => true,
                'sort_order' => 2,
            ],
            [
                'code' => 'au_bd_medical_device',
                'label' => 'Medical device handling',
                'description' => 'Additional charge per blood sugar or blood pressure device.',
                'shipping_route_id' => $auToBd->id,
                'item_category_id' => $medical?->id,
                'calculation_type' => CalculationType::PerItem,
                'amount' => 20,
                'condition_field' => null,
                'condition_operator' => null,
                'condition_value' => null,
                'is_placeholder_amount' => false,
                'sort_order' => 3,
            ],
            [
                'code' => 'au_bd_light_box_fee',
                'label' => 'Under-15kg box fee',
                'description' => 'Applies to Australia Post drop-off boxes weighing less than 15kg.',
                'shipping_route_id' => $auToBd->id,
                'item_category_id' => null,
                'calculation_type' => CalculationType::Flat,
                'amount' => 15,
                'condition_field' => ConditionField::BoxWeightKg,
                'condition_operator' => ConditionOperator::LessThan,
                'condition_value' => '15',
                'is_placeholder_amount' => false,
                'sort_order' => 4,
            ],
            [
                'code' => 'bd_au_non_garment_handling',
                'label' => 'Non-garments handling charge',
                'description' => 'Charged per kg with a floor, as published on the rate sheet.',
                'shipping_route_id' => $bdToAu->id,
                'item_category_id' => null,
                'calculation_type' => CalculationType::PerKg,
                'amount' => 2,
                'minimum_amount' => 20,
                'condition_field' => ConditionField::IsNonGarment,
                'condition_operator' => ConditionOperator::IsTrue,
                'condition_value' => null,
                'is_placeholder_amount' => false,
                'sort_order' => 5,
            ],
        ];

        foreach ($rules as $rule) {
            SurchargeRule::query()->updateOrCreate(
                ['code' => $rule['code']],
                $rule + ['is_active' => true],
            );
        }
    }
}
