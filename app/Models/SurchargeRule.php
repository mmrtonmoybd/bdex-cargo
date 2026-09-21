<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CalculationType;
use App\Enums\ConditionField;
use App\Enums\ConditionOperator;
use Database\Factories\SurchargeRuleFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $code
 * @property string $label
 * @property int|null $shipping_route_id
 * @property int|null $item_category_id
 * @property CalculationType $calculation_type
 * @property string $amount
 * @property ConditionField|null $condition_field
 * @property ConditionOperator|null $condition_operator
 * @property string|null $condition_value
 * @property bool $is_placeholder_amount
 * @property bool $is_active
 */
final class SurchargeRule extends Model
{
    /** @use HasFactory<SurchargeRuleFactory> */
    use HasFactory;

    protected $fillable = [
        'code',
        'label',
        'description',
        'shipping_route_id',
        'item_category_id',
        'calculation_type',
        'amount',
        'condition_field',
        'condition_operator',
        'condition_value',
        'is_placeholder_amount',
        'sort_order',
        'is_active',
    ];

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

    /** A rule with no condition triple always applies. */
    public function isUnconditional(): bool
    {
        return $this->condition_field === null || $this->condition_operator === null;
    }

    /** @param Builder<self> $query */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * Rules that apply to a route: route-specific ones plus the route-agnostic ones.
     *
     * @param  Builder<self>  $query
     */
    public function scopeForRoute(Builder $query, int $routeId): void
    {
        $query->where(function (Builder $query) use ($routeId): void {
            $query->whereNull('shipping_route_id')->orWhere('shipping_route_id', $routeId);
        });
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'calculation_type' => CalculationType::class,
            'condition_field' => ConditionField::class,
            'condition_operator' => ConditionOperator::class,
            'is_placeholder_amount' => 'boolean',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
