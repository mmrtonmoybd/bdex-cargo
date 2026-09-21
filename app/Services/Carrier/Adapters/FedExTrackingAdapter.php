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

/**
 * FedEx Track API.
 *
 * Credentials are read per call from config_entries, never injected from .env, so
 * a rotated key takes effect on the next poll.
 */
final class FedExTrackingAdapter implements CarrierTrackingAdapter
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
                ->timeout(20)
                ->acceptJson()
                ->post(rtrim((string) $this->carrier->api_base_url, '/').'/track/v1/trackingnumbers', [
                    'trackingInfo' => [
                        ['trackingNumberInfo' => ['trackingNumber' => $carrierTrackingNumber]],
                    ],
                    'includeDetailedScans' => false,
                ])
                ->throw();
        } catch (Throwable $exception) {
            throw new CarrierApiException(
                "FedEx tracking request failed: {$exception->getMessage()}",
                previous: $exception,
            );
        }

        $latest = $response->json('output.completeTrackResults.0.trackResults.0.latestStatusDetail');

        if (! is_array($latest)) {
            throw new CarrierApiException('FedEx returned no status detail for '.$carrierTrackingNumber.'.');
        }

        return new NormalizedTrackingResult(
            status: $this->mapStatus((string) ($latest['code'] ?? '')),
            rawStatusText: (string) ($latest['description'] ?? 'Unknown'),
            location: $latest['scanLocation']['city'] ?? null,
            occurredAt: Carbon::parse($latest['derivedStatusTime'] ?? now()),
            rawPayload: (array) $response->json(),
        );
    }

    /** FedEx's vocabulary, translated into ours. */
    private function mapStatus(string $code): NormalizedStatus
    {
        return match ($code) {
            'PU', 'OC' => NormalizedStatus::PickedUp,
            'AR', 'DP', 'IT' => NormalizedStatus::InTransit,
            'CC', 'CD' => NormalizedStatus::Customs,
            'OD' => NormalizedStatus::OutForDelivery,
            'DL' => NormalizedStatus::Delivered,
            'DE', 'SE' => NormalizedStatus::Exception,
            default => NormalizedStatus::InWarehouse,
        };
    }
}
