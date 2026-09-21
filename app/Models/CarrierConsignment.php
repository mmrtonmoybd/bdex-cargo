<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\NormalizedStatus;
use Database\Factories\CarrierConsignmentFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * One booking with one carrier, shared by every order consolidated into it.
 *
 * @property string $carrier_tracking_number
 * @property NormalizedStatus $internal_status
 * @property array<string, mixed>|null $raw_payload
 */
final class CarrierConsignment extends Model
{
    /** @use HasFactory<CarrierConsignmentFactory> */
    use HasFactory;

    protected $guarded = ['id'];

    /** @return BelongsTo<Carrier, $this> */
    public function carrier(): BelongsTo
    {
        return $this->belongsTo(Carrier::class);
    }

    /**
     * Every customer order riding on this consignment.
     *
     * When this consignment's status changes, all of these move at once and see
     * the identical milestone at the identical time. That is the intended
     * behaviour of consolidation, not a bug.
     *
     * @return HasMany<Order, $this>
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /** @return HasMany<CarrierSyncLog, $this> */
    public function syncLogs(): HasMany
    {
        return $this->hasMany(CarrierSyncLog::class)->latest('attempted_at');
    }

    /** Consignments still worth polling: active carrier, not yet delivered. */
    public function scopeSyncable(Builder $query): void
    {
        $query->whereNot('internal_status', NormalizedStatus::Delivered->value)
            ->whereHas('carrier', fn (Builder $carrier) => $carrier->where('is_active', true));
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'internal_status' => NormalizedStatus::class,
            'raw_payload' => 'array',
            'booked_at' => 'datetime',
            'last_synced_at' => 'datetime',
            'status_changed_at' => 'datetime',
        ];
    }
}
