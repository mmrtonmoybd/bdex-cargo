<?php

declare(strict_types=1);

use App\Filament\Widgets\PricingReadiness;
use App\Models\FxRate;
use App\Models\SurchargeRule;
use Database\Seeders\PricingSeeder;

it('stays hidden when nothing needs attention', function (): void {
    FxRate::query()->create([
        'from_currency' => 'AUD',
        'to_currency' => 'BDT',
        'rate' => 80,
        'fetched_at' => now(),
    ]);

    expect(PricingReadiness::canView())->toBeFalse();
});

it('flags the seeded placeholder amounts', function (): void {
    $this->seed(PricingSeeder::class);

    expect(PricingReadiness::canView())->toBeTrue();

    $titles = collect((new PricingReadiness)->getIssues())->pluck('title');

    expect($titles)->toContain('Surcharge amounts still marked as placeholders')
        ->and($titles)->toContain('No exchange rate stored');
});

it('stops flagging a rule once its amount is confirmed', function (): void {
    $this->seed(PricingSeeder::class);

    SurchargeRule::query()->update(['is_placeholder_amount' => false]);

    $titles = collect((new PricingReadiness)->getIssues())->pluck('title');

    expect($titles)->not->toContain('Surcharge amounts still marked as placeholders');
});
