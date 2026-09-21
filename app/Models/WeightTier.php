<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\WeightTierScope;
use Database\Factories\WeightTierFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $shipping_route_id
 * @property string $min_kg
 * @property string|null $max_kg
 * @property string $price_per_kg
 * @property WeightTierScope $applies_to
 * @property string|null $note
 * @property bool $is_active
 */
final class WeightTier extends Model
{
    /** @use HasFactory<WeightTierFactory> */
    use HasFactory;

    protected $fillable = [
        'shipping_route_id',
        'min_kg',
        'max_kg',
        'price_per_kg',
        'applies_to',
        'note',
        'sort_order',
        'is_active',
    ];

    /** @return BelongsTo<ShippingRoute, $this> */
    public function shippingRoute(): BelongsTo
    {
        return $this->belongsTo(ShippingRoute::class);
    }

    /** Whether a weight falls inside this tier. A null `max_kg` means "and above". */
    public function covers(float $weightKg): bool
    {
        if ($weightKg < (float) $this->min_kg) {
            return false;
        }

        return $this->max_kg === null || $weightKg <= (float) $this->max_kg;
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
            'applies_to' => WeightTierScope::class,
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
