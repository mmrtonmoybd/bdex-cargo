<?php

declare(strict_types=1);

namespace App\Services\Tracking;

use App\Models\Order;
use Illuminate\Support\Facades\DB;

/**
 * Issues the only tracking identity a customer ever sees.
 *
 * Format: BDX + year + month + a zero-padded sequence within that month, e.g.
 * BDX2026090001. Permanent, unique, and completely independent of any carrier —
 * an order has one of these from the moment it is created, long before anyone
 * decides which carrier will actually fly it.
 */
final class OrderTrackingNumberGenerator
{
    public function generate(): string
    {
        $prefix = 'BDX'.now()->format('Ym');

        // Locking the read keeps two simultaneous bookings from claiming the same
        // sequence; the unique index is the backstop if it ever does.
        return DB::transaction(function () use ($prefix): string {
            $latest = Order::query()
                ->withTrashed()
                ->where('bdex_tracking_number', 'like', $prefix.'%')
                ->orderByDesc('bdex_tracking_number')
                ->lockForUpdate()
                ->value('bdex_tracking_number');

            $sequence = $latest === null ? 1 : ((int) substr($latest, -4)) + 1;

            return $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
        });
    }
}
