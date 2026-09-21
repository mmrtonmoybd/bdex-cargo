<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\FulfilmentMethod;
use App\Enums\NormalizedStatus;
use App\Enums\PaymentStatus;
use App\Enums\ServiceLevel;
use App\Enums\StatusSource;
use App\Services\Tracking\CustomerFacingOrder;
use App\Services\Tracking\OrderTrackingNumberGenerator;
use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * A customer's shipment.
 *
 * Note what is NOT on the customer's side of this model: the carrier and its
 * tracking number are reachable only through `carrierConsignment`, and no
 * customer-facing code path is allowed to touch that relation. See
 * {@see CustomerFacingOrder}.
 *
 * @property string $bdex_tracking_number
 * @property NormalizedStatus $status
 * @property array<string, mixed>|null $cost_breakdown
 */
final class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $guarded = ['id'];

    /**
     * Every order gets its BD-EX number the moment it is created.
     *
     * Issued here rather than by whoever happens to create the order, so the
     * invariant "a customer always has a number to quote" holds for the booking
     * wizard, the admin panel, an import and a test alike.
     */
    protected static function booted(): void
    {
        self::creating(function (self $order): void {
            if (blank($order->bdex_tracking_number)) {
                $order->bdex_tracking_number = app(OrderTrackingNumberGenerator::class)->generate();
            }
        });
    }

    /** @return BelongsTo<ShippingRoute, $this> */
    public function shippingRoute(): BelongsTo
    {
        return $this->belongsTo(ShippingRoute::class);
    }

    /** @return BelongsTo<PricingZone, $this> */
    public function pricingZone(): BelongsTo
    {
        return $this->belongsTo(PricingZone::class);
    }

    /** @return BelongsTo<Customer, $this> */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * The real carrier booking this order was consolidated onto.
     *
     * Internal only. Never eager-load this in a customer-facing query — the
     * public tracking path is built so it cannot reach here at all.
     *
     * @return BelongsTo<CarrierConsignment, $this>
     */
    public function carrierConsignment(): BelongsTo
    {
        return $this->belongsTo(CarrierConsignment::class);
    }

    /** @return HasMany<OrderBox, $this> */
    public function boxes(): HasMany
    {
        return $this->hasMany(OrderBox::class)->orderBy('position');
    }

    /** @return HasMany<OrderItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /** @return HasMany<OrderStatusHistory, $this> */
    public function statusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class)->orderBy('occurred_at');
    }

    /** @return HasMany<Payment, $this> */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class)->latest();
    }

    /** @return HasOne<Invoice, $this> */
    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    /** @return MorphMany<Document, $this> */
    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    /** @return BelongsTo<Seller, $this> */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    /**
     * Records a status change and its history entry together.
     *
     * Everything that moves an order — the carrier sync fan-out, a manual
     * correction, the booking itself — comes through here, so the timeline can
     * never disagree with the order.
     */
    public function recordStatus(
        NormalizedStatus $status,
        StatusSource $source,
        ?Carbon $occurredAt = null,
        ?string $location = null,
        ?string $note = null,
    ): OrderStatusHistory {
        $history = $this->statusHistories()->create([
            'status' => $status,
            'source' => $source,
            'location' => $location,
            'note' => $note,
            'occurred_at' => $occurredAt ?? now(),
        ]);

        $this->forceFill(['status' => $status])->save();

        return $history;
    }

    public function totalWeightKg(): float
    {
        return (float) $this->boxes->sum('weight_kg');
    }

    /** @param Builder<self> $query */
    public function scopeAwaitingConsolidation(Builder $query): void
    {
        $query->whereNull('carrier_consignment_id');
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'status' => NormalizedStatus::class,
            'service_level' => ServiceLevel::class,
            'fulfilment_method' => FulfilmentMethod::class,
            'payment_status' => PaymentStatus::class,
            'cost_breakdown' => 'array',
            'pickup_preferred_date' => 'date',
            'pickup_confirmed_at' => 'datetime',
            'has_product_cover_insurance' => 'boolean',
            'is_fragile' => 'boolean',
            'is_cold_storage' => 'boolean',
        ];
    }
}
