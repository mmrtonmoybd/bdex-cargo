<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\PricingAgreementFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A volume tier or a customer's contract rate.
 *
 * @property string $discount_percent
 */
final class PricingAgreement extends Model
{
    /** @use HasFactory<PricingAgreementFactory> */
    use HasFactory;

    protected $guarded = ['id'];

    /** @return BelongsTo<Customer, $this> */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /** @return BelongsTo<ShippingRoute, $this> */
    public function shippingRoute(): BelongsTo
    {
        return $this->belongsTo(ShippingRoute::class);
    }

    public function isContract(): bool
    {
        return $this->customer_id !== null;
    }

    /** Whether a shipment meets this agreement's thresholds. */
    public function qualifies(float $weightKg, int $shipmentsThisMonth): bool
    {
        if ($this->min_weight_kg !== null && $weightKg < (float) $this->min_weight_kg) {
            return false;
        }

        return $this->min_shipments_per_month === null
            || $shipmentsThisMonth >= $this->min_shipments_per_month;
    }

    /** @param Builder<self> $query */
    public function scopeActive(Builder $query): void
    {
        $today = now()->toDateString();

        $query->where('is_active', true)
            ->where(fn (Builder $q) => $q->whereNull('valid_from')->orWhere('valid_from', '<=', $today))
            ->where(fn (Builder $q) => $q->whereNull('valid_until')->orWhere('valid_until', '>=', $today));
    }

    /**
     * Agreements that could apply to a shipment: the customer's own, plus the
     * published tiers anyone can earn.
     *
     * @param  Builder<self>  $query
     */
    public function scopeAvailableTo(Builder $query, ?int $customerId, int $routeId): void
    {
        $query
            ->where(fn (Builder $q) => $q->whereNull('customer_id')->when(
                $customerId !== null,
                fn (Builder $inner) => $inner->orWhere('customer_id', $customerId)
            ))
            ->where(fn (Builder $q) => $q->whereNull('shipping_route_id')->orWhere('shipping_route_id', $routeId));
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'valid_from' => 'date',
            'valid_until' => 'date',
            'min_shipments_per_month' => 'integer',
            'priority' => 'integer',
        ];
    }
}
