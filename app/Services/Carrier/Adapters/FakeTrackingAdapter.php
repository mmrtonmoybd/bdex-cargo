<?php

declare(strict_types=1);

namespace App\Services\Carrier\Adapters;

use App\Enums\NormalizedStatus;
use App\Models\Carrier;
use App\Services\Carrier\CarrierApiException;
use App\Services\Carrier\CarrierTrackingAdapter;
use App\Services\Carrier\NormalizedTrackingResult;
use Illuminate\Support\Carbon;

/**
 * A carrier that answers from memory.
 *
 * Lets the whole pipeline — poll, normalise, fan out, notify — be exercised
 * before a single real credential exists, and lets tests drive a carrier into
 * failure on purpose.
 */
final class FakeTrackingAdapter implements CarrierTrackingAdapter
{
    /** @var array<string, NormalizedTrackingResult> */
    private static array $results = [];

    private static ?string $failWith = null;

    public function __construct(private readonly Carrier $carrier) {}

    public function fetchStatus(string $carrierTrackingNumber): NormalizedTrackingResult
    {
        if (self::$failWith !== null) {
            throw new CarrierApiException(self::$failWith);
        }

        return self::$results[$carrierTrackingNumber] ?? new NormalizedTrackingResult(
            status: NormalizedStatus::InWarehouse,
            rawStatusText: 'Awaiting scan',
            location: null,
            occurredAt: Carbon::now(),
        );
    }

    public static function willReturn(string $trackingNumber, NormalizedTrackingResult $result): void
    {
        self::$results[$trackingNumber] = $result;
    }

    public static function willFail(string $message = 'Carrier unavailable'): void
    {
        self::$failWith = $message;
    }

    public static function reset(): void
    {
        self::$results = [];
        self::$failWith = null;
    }
}
