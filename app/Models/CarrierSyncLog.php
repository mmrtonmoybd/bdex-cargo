<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\NormalizedStatus;
use Database\Factories\CarrierSyncLogFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class CarrierSyncLog extends Model
{
    /** @use HasFactory<CarrierSyncLogFactory> */
    use HasFactory;

    protected $guarded = ['id'];

    /** @return BelongsTo<CarrierConsignment, $this> */
    public function carrierConsignment(): BelongsTo
    {
        return $this->belongsTo(CarrierConsignment::class);
    }

    /** @return BelongsTo<Carrier, $this> */
    public function carrier(): BelongsTo
    {
        return $this->belongsTo(Carrier::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'succeeded' => 'boolean',
            'status_changed' => 'boolean',
            'normalized_status' => NormalizedStatus::class,
            'attempted_at' => 'datetime',
            'duration_ms' => 'integer',
            'orders_affected' => 'integer',
        ];
    }
}
