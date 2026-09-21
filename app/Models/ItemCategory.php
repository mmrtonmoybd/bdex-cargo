<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PricingModel;
use Database\Factories\ItemCategoryFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $shipping_route_id
 * @property string $name
 * @property PricingModel $pricing_model
 * @property string|null $flat_fee_amount
 * @property string|null $max_weight_kg
 * @property bool $is_garment
 * @property bool $is_active
 */
final class ItemCategory extends Model
{
    /** @use HasFactory<ItemCategoryFactory> */
    use HasFactory;

    protected $fillable = [
        'shipping_route_id',
        'name',
        'description',
        'pricing_model',
        'flat_fee_amount',
        'max_weight_kg',
        'is_garment',
        'sort_order',
        'is_active',
    ];

    /** @return BelongsTo<ShippingRoute, $this> */
    public function shippingRoute(): BelongsTo
    {
        return $this->belongsTo(ShippingRoute::class);
    }

    /** @return HasMany<ItemCategoryZoneRate, $this> */
    public function zoneRates(): HasMany
    {
        return $this->hasMany(ItemCategoryZoneRate::class);
    }

    /** Rate per kg for a zone, or null when this category has no rate there. */
    public function rateForZone(PricingZone $zone): ?float
    {
        $rate = $this->zoneRates->firstWhere('pricing_zone_id', $zone->id);

        return $rate === null ? null : (float) $rate->rate_per_kg;
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
            'pricing_model' => PricingModel::class,
            'is_garment' => 'boolean',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
