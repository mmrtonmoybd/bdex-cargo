<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\PricingZoneFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $shipping_route_id
 * @property string $name
 * @property string|null $code
 * @property bool $is_active
 */
final class PricingZone extends Model
{
    /** @use HasFactory<PricingZoneFactory> */
    use HasFactory;

    protected $fillable = [
        'shipping_route_id',
        'name',
        'code',
        'description',
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
