<?php

declare(strict_types=1);

namespace App\Services\Documents;

use App\Models\Order;
use Illuminate\Support\Facades\DB;

/**
 * BD-EX's own air waybill numbers.
 *
 * Format: BDEX-<year><sequence>. Ours, not the carrier's — customs paperwork
 * needs a waybill reference, and printing the carrier's would hand the customer
 * the one fact the system is built to withhold.
 */
final class AirWaybillNumberGenerator
{
    public function generate(): string
    {
        $prefix = 'BDEX-'.now()->format('Y');

        return DB::transaction(function () use ($prefix): string {
            $latest = Order::query()
                ->withTrashed()
                ->where('air_waybill_number', 'like', $prefix.'%')
                ->orderByDesc('air_waybill_number')
                ->lockForUpdate()
                ->value('air_waybill_number');

            $sequence = $latest === null ? 1 : ((int) substr($latest, -6)) + 1;

            return $prefix.str_pad((string) $sequence, 6, '0', STR_PAD_LEFT);
        });
    }
}
