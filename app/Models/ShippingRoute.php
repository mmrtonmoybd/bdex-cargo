<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ShippingRouteFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $code
 * @property string $name
 * @property string $origin_country
 * @property string $destination_country
 * @property string $base_currency
 * @property string $quote_currency
 * @property string|null $transit_time_text
 * @property bool $is_active
 */
final class ShippingRoute extends Model
{
    /** @use HasFactory<ShippingRouteFactory> */
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'origin_country',
        'destination_country',
        'base_currency',
        'quote_currency',
        'transit_time_text',
        'sort_order',
        'is_active',
    ];

    /** @return HasMany<PricingZone, $this> */
    public function pricingZones(): HasMany
    {
        return $this->hasMany(PricingZone::class);
    }

    /** @return HasMany<ItemCategory, $this> */
    public function itemCategories(): HasMany
    {
        return $this->hasMany(ItemCategory::class);
    }

    /** @return HasMany<WeightTier, $this> */
    public function weightTiers(): HasMany
    {
        return $this->hasMany(WeightTier::class);
    }

    /** @return HasMany<TvSizeBand, $this> */
    public function tvSizeBands(): HasMany
    {
        return $this->hasMany(TvSizeBand::class);
    }

    /** @return HasMany<SurchargeRule, $this> */
    public function surchargeRules(): HasMany
    {
        return $this->hasMany(SurchargeRule::class);
    }

    /** @param Builder<self> $query */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
