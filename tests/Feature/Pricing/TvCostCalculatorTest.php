<?php

declare(strict_types=1);

use App\Models\FxRate;
use App\Models\ShippingRoute;
use App\Models\TvSizeBand;
use App\Services\Config\DynamicConfig;
use App\Services\Pricing\TvCostCalculator;

beforeEach(function (): void {
    $this->route = ShippingRoute::query()->create([
        'code' => 'AU_TO_BD',
        'name' => 'Australia → Bangladesh',
        'origin_country' => 'AU',
        'destination_country' => 'BD',
        'base_currency' => 'AUD',
        'quote_currency' => 'BDT',
        'is_active' => true,
    ]);

    DynamicConfig::set(TvCostCalculator::PER_KG_RATE_KEY, '17');
    DynamicConfig::set(TvCostCalculator::AIRPORT_FEE_KEY, '35');

    $this->band = TvSizeBand::query()->create([
        'shipping_route_id' => $this->route->id,
        'size_label' => '55"',
        'min_inches' => 53,
        'max_inches' => 60,
        'bd_govt_duty_bdt' => 70000,
        'volume_fee_aud' => 145,
        'local_delivery_bdt' => 2000,
        'default_pallet_weight_kg' => 12,
        'is_active' => true,
    ]);
});

it('adds freight, airport fee and volume fee on the Australian side', function (): void {
    // 17/kg × 12kg = 204, + 35 airport, + 145 volume.
    $result = app(TvCostCalculator::class)->calculate($this->band);

    expect($result->totalAud())->toBe(384.0);
});

it('keeps duty and local delivery on the Bangladesh side', function (): void {
    $result = app(TvCostCalculator::class)->calculate($this->band);

    expect($result->totalBdt())->toBe(72000.0);
});

it('honours an overridden pallet weight', function (): void {
    // 17/kg × 15kg = 255, + 35 + 145.
    $result = app(TvCostCalculator::class)->calculate($this->band, palletWeightKg: 15);

    expect($result->totalAud())->toBe(435.0);
});

it('reads the per-kg rate from dynamic config rather than a constant', function (): void {
    DynamicConfig::set(TvCostCalculator::PER_KG_RATE_KEY, '20');

    $result = app(TvCostCalculator::class)->calculate($this->band);

    expect($result->totalAud())->toBe(420.0);
});

it('combines both sides only when a rate is stored', function (): void {
    expect(app(TvCostCalculator::class)->calculate($this->band)->combinedBdt())->toBeNull();

    FxRate::query()->create([
        'from_currency' => 'AUD',
        'to_currency' => 'BDT',
        'rate' => 80,
        'fetched_at' => now(),
    ]);

    $result = app(TvCostCalculator::class)->calculate($this->band);

    // 72,000 BDT + (384 AUD × 80).
    expect($result->combinedBdt())->toBe(102720.0)
        ->and($result->combinedAud())->toBe(1284.0);
});
