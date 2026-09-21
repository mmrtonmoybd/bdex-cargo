<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\SellerListingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class SellerListing extends Model
{
    /** @use HasFactory<SellerListingFactory> */
    use HasFactory;

    protected $guarded = ['id'];

    /** @return BelongsTo<Seller, $this> */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    /** @return BelongsTo<ShippingRoute, $this> */
    public function shippingRoute(): BelongsTo
    {
        return $this->belongsTo(ShippingRoute::class);
    }

    /** @return BelongsTo<ItemCategory, $this> */
    public function itemCategory(): BelongsTo
    {
        return $this->belongsTo(ItemCategory::class);
    }

    /**
     * The seller's own price for a BD-EX total.
     *
     * The base always comes from the pricing engine, so an upstream rate change
     * still reaches every reseller; the listing only adds the seller's margin.
     */
    public function sellPrice(float $baseTotal): float
    {
        $percent = $this->markup_percent ?? $this->seller->markup_percent ?? 0;

        return round($baseTotal * (1 + ((float) $percent / 100)) + (float) ($this->flat_markup_amount ?? 0), 2);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
