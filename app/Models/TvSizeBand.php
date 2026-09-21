<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\TvSizeBandFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $shipping_route_id
 * @property string $size_label
 * @property int $min_inches
 * @property int|null $max_inches
 * @property string $bd_govt_duty_bdt
 * @property string $volume_fee_aud
 * @property string $local_delivery_bdt
 * @property string $default_pallet_weight_kg
 * @property bool $is_active
 */
final class TvSizeBand extends Model
{
    /** @use HasFactory<TvSizeBandFactory> */
    use HasFactory;

    protected $fillable = [
        'shipping_route_id',
        'size_label',
        'min_inches',
        'max_inches',
        'bd_govt_duty_bdt',
        'volume_fee_aud',
        'local_delivery_bdt',
        'default_pallet_weight_kg',
        'sort_order',
        'is_active',
    ];

    /** @return BelongsTo<ShippingRoute, $this> */
    public function shippingRoute(): BelongsTo
    {
        return $this->belongsTo(ShippingRoute::class);
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
            'min_inches' => 'integer',
            'max_inches' => 'integer',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
