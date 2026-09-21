<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ItemCategoryZoneRateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $item_category_id
 * @property int $pricing_zone_id
 * @property string $rate_per_kg
 */
final class ItemCategoryZoneRate extends Model
{
    /** @use HasFactory<ItemCategoryZoneRateFactory> */
    use HasFactory;

    protected $fillable = [
        'item_category_id',
        'pricing_zone_id',
        'rate_per_kg',
    ];

    /** @return BelongsTo<ItemCategory, $this> */
    public function itemCategory(): BelongsTo
    {
        return $this->belongsTo(ItemCategory::class);
    }

    /** @return BelongsTo<PricingZone, $this> */
    public function pricingZone(): BelongsTo
    {
        return $this->belongsTo(PricingZone::class);
    }
}
