<?php

declare(strict_types=1);

namespace App\Events;

use App\Enums\NormalizedStatus;
use App\Models\Order;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired once per order, whether the move came from a carrier sync or from staff.
 */
final class OrderStatusUpdated
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly Order $order,
        public readonly NormalizedStatus $status,
    ) {}
}
