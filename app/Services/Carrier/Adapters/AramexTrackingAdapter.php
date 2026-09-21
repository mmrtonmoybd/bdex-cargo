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
 * Aramex Shipment Tracking.
 *
 * Aramex authenticates with an account tuple rather than a bearer token, which is
 * why each carrier keeps its own namespaced credentials instead of the system
 * assuming one shape fits every vendor.
 */
final class AramexTrackingAdapter implements CarrierTrackingAdapter
{
    public function __construct(
        private readonly Carrier $carrier,
        private readonly HttpFactory $http,
    ) {}

    public function fetchStatus(string $carrierTrackingNumber): NormalizedTrackingResult
    {
        $credentials = [
            'UserName' => (string) $this->carrier->credential('username'),
            'Password' => (string) $this->carrier->credential('password'),
            'AccountNumber' => (string) $this->carrier->credential('account_number'),
            'AccountPin' => (string) $this->carrier->credential('account_pin'),
            'AccountEntity' => (string) $this->carrier->credential('account_entity'),
            'AccountCountryCode' => (string) $this->carrier->credential('account_country_code'),
        ];

        if ($credentials['UserName'] === '' || $credentials['Password'] === '') {
            throw new CarrierApiException(
                "No credentials stored for {$this->carrier->name}. Set {$this->carrier->credentialKey('username')} and the rest under System → Configuration."
            );
        }

        try {
            $response = $this->http
                ->timeout(20)
                ->acceptJson()
                ->post(rtrim((string) $this->carrier->api_base_url, '/').'/TrackShipments', [
                    'ClientInfo' => $credentials,
                    'Shipments' => [$carrierTrackingNumber],
                    'GetLastTrackingUpdateOnly' => true,
                ])
                ->throw();
        } catch (Throwable $exception) {
            throw new CarrierApiException(
                "Aramex tracking request failed: {$exception->getMessage()}",
                previous: $exception,
            );
        }

        $results = $response->json('TrackingResults.0.Value.0');

        if (! is_array($results)) {
            throw new CarrierApiException('Aramex returned no tracking result for '.$carrierTrackingNumber.'.');
        }

        return new NormalizedTrackingResult(
            status: $this->mapStatus((string) ($results['UpdateCode'] ?? '')),
            rawStatusText: (string) ($results['UpdateDescription'] ?? 'Unknown'),
            location: $results['UpdateLocation'] ?? null,
            occurredAt: Carbon::parse($results['UpdateDateTime'] ?? now()),
            rawPayload: (array) $response->json(),
        );
    }

    private function mapStatus(string $code): NormalizedStatus
    {
        return match ($code) {
            'SH014', 'SH005' => NormalizedStatus::PickedUp,
            'SH002', 'SH003', 'SH006' => NormalizedStatus::InTransit,
            'SH029', 'SH031' => NormalizedStatus::Customs,
            'SH023' => NormalizedStatus::OutForDelivery,
            'SH004', 'SH237' => NormalizedStatus::Delivered,
            'SH011', 'SH043' => NormalizedStatus::Exception,
            default => NormalizedStatus::InWarehouse,
        };
    }
}
