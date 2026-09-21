<?php

declare(strict_types=1);

namespace App\Services\Carrier;

use App\Enums\NormalizedStatus;
use Illuminate\Support\Carbon;

/**
 * What every carrier's answer is translated into before it touches the database.
 *
 * `rawStatusText` is kept for staff and debugging only. It frequently contains
 * the carrier's own wording — and sometimes its name — so it is never rendered
 * on a customer surface.
 */
final readonly class NormalizedTrackingResult
{
    /** @param array<string, mixed> $rawPayload */
    public function __construct(
        public NormalizedStatus $status,
        public string $rawStatusText,
        public ?string $location,
        public Carbon $occurredAt,
        public ?string $proofOfDeliveryUrl = null,
        public array $rawPayload = [],
    ) {}
}
