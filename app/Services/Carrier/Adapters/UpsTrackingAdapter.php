<?php

declare(strict_types=1);

namespace App\Services\Carrier\Adapters;

use App\Enums\NormalizedStatus;
use App\Models\Carrier;
use App\Services\Carrier\CarrierApiException;
use App\Services\Carrier\CarrierTrackingAdapter;
use App\Services\Carrier\NormalizedTrackingResult;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\Carbon;
use Throwable;

/** UPS Tracking API. */
final class UpsTrackingAdapter implements CarrierTrackingAdapter
{
    public function __construct(
        private readonly Carrier $carrier,
        private readonly HttpFactory $http,
    ) {}

    public function fetchStatus(string $carrierTrackingNumber): NormalizedTrackingResult
    {
        $token = (string) $this->carrier->credential('access_token');

        if ($token === '') {
            throw new CarrierApiException(
                "No access token stored for {$this->carrier->name}. Set {$this->carrier->credentialKey('access_token')} under System → Configuration."
            );
        }

        try {
            $response = $this->http
                ->withToken($token)
                ->withHeaders(['transId' => (string) now()->timestamp, 'transactionSrc' => 'bdex'])
                ->timeout(20)
                ->acceptJson()
                ->get(rtrim((string) $this->carrier->api_base_url, '/')."/track/v1/details/{$carrierTrackingNumber}")
                ->throw();
        } catch (Throwable $exception) {
            throw new CarrierApiException(
                "UPS tracking request failed: {$exception->getMessage()}",
                previous: $exception,
            );
        }

        $activity = $response->json('trackResponse.shipment.0.package.0.activity.0');

        if (! is_array($activity)) {
            throw new CarrierApiException('UPS returned no activity for '.$carrierTrackingNumber.'.');
        }

        $status = $activity['status'] ?? [];
        $date = (string) ($activity['date'] ?? now()->format('Ymd'));
        $time = (string) ($activity['time'] ?? '000000');

        return new NormalizedTrackingResult(
            status: $this->mapStatus((string) ($status['type'] ?? '')),
            rawStatusText: (string) ($status['description'] ?? 'Unknown'),
            location: $activity['location']['address']['city'] ?? null,
            occurredAt: Carbon::createFromFormat('YmdHis', $date.str_pad($time, 6, '0')) ?: now(),
            rawPayload: (array) $response->json(),
        );
    }

    private function mapStatus(string $type): NormalizedStatus
    {
        return match (strtoupper($type)) {
            'M' => NormalizedStatus::Booked,
            'P' => NormalizedStatus::PickedUp,
            'I' => NormalizedStatus::InTransit,
            'O' => NormalizedStatus::OutForDelivery,
            'D' => NormalizedStatus::Delivered,
            'X' => NormalizedStatus::Exception,
            default => NormalizedStatus::InWarehouse,
        };
    }
}
