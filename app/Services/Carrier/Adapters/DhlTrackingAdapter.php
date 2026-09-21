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

/** DHL Shipment Tracking Unified API. */
final class DhlTrackingAdapter implements CarrierTrackingAdapter
{
    public function __construct(
        private readonly Carrier $carrier,
        private readonly HttpFactory $http,
    ) {}

    public function fetchStatus(string $carrierTrackingNumber): NormalizedTrackingResult
    {
        $key = (string) $this->carrier->credential('api_key');

        if ($key === '') {
            throw new CarrierApiException(
                "No API key stored for {$this->carrier->name}. Set {$this->carrier->credentialKey('api_key')} under System → Configuration."
            );
        }

        try {
            $response = $this->http
                ->withHeaders(['DHL-API-Key' => $key])
                ->timeout(20)
                ->acceptJson()
                ->get(rtrim((string) $this->carrier->api_base_url, '/').'/track/shipments', [
                    'trackingNumber' => $carrierTrackingNumber,
                ])
                ->throw();
        } catch (Throwable $exception) {
            throw new CarrierApiException(
                "DHL tracking request failed: {$exception->getMessage()}",
                previous: $exception,
            );
        }

        $shipment = $response->json('shipments.0');

        if (! is_array($shipment)) {
            throw new CarrierApiException('DHL returned no shipment for '.$carrierTrackingNumber.'.');
        }

        $event = $shipment['status'] ?? [];

        return new NormalizedTrackingResult(
            status: $this->mapStatus((string) ($event['statusCode'] ?? '')),
            rawStatusText: (string) ($event['description'] ?? $event['status'] ?? 'Unknown'),
            location: $event['location']['address']['addressLocality'] ?? null,
            occurredAt: Carbon::parse($event['timestamp'] ?? now()),
            rawPayload: (array) $response->json(),
        );
    }

    private function mapStatus(string $code): NormalizedStatus
    {
        return match (strtolower($code)) {
            'pre-transit' => NormalizedStatus::Booked,
            'collected' => NormalizedStatus::PickedUp,
            'transit' => NormalizedStatus::InTransit,
            'customs' => NormalizedStatus::Customs,
            'out-for-delivery' => NormalizedStatus::OutForDelivery,
            'delivered' => NormalizedStatus::Delivered,
            'failure', 'exception' => NormalizedStatus::Exception,
            default => NormalizedStatus::InWarehouse,
        };
    }
}
