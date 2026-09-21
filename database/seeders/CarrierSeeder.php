<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Carrier;
use App\Services\Carrier\Adapters\AramexTrackingAdapter;
use App\Services\Carrier\Adapters\DhlTrackingAdapter;
use App\Services\Carrier\Adapters\FedExTrackingAdapter;
use App\Services\Carrier\Adapters\UpsTrackingAdapter;
use App\Services\Config\DynamicConfig;
use Illuminate\Database\Seeder;

/**
 * The carriers BD-EX ships with, and the credential slots each one needs.
 *
 * The credential rows are created empty: an admin fills them in under
 * System → Configuration, and they are encrypted the moment they are saved.
 * Nothing here contains a secret.
 */
final class CarrierSeeder extends Seeder
{
    public function run(): void
    {
        $carriers = [
            [
                'name' => 'Aramex',
                'code' => 'aramex',
                'driver_class' => AramexTrackingAdapter::class,
                'api_base_url' => 'https://ws.aramex.net/ShippingAPI.V2/Tracking/Service_1_0.svc/json',
                'credentials' => ['username', 'password', 'account_number', 'account_pin', 'account_entity', 'account_country_code'],
            ],
            [
                'name' => 'FedEx',
                'code' => 'fedex',
                'driver_class' => FedExTrackingAdapter::class,
                'api_base_url' => 'https://apis.fedex.com',
                'credentials' => ['client_id', 'client_secret', 'access_token'],
            ],
            [
                'name' => 'DHL',
                'code' => 'dhl',
                'driver_class' => DhlTrackingAdapter::class,
                'api_base_url' => 'https://api-eu.dhl.com',
                'credentials' => ['api_key', 'api_secret'],
            ],
            [
                'name' => 'UPS',
                'code' => 'ups',
                'driver_class' => UpsTrackingAdapter::class,
                'api_base_url' => 'https://onlinetools.ups.com/api',
                'credentials' => ['client_id', 'client_secret', 'access_token'],
            ],
        ];

        foreach ($carriers as $index => $carrier) {
            Carrier::query()->updateOrCreate(
                ['code' => $carrier['code']],
                [
                    'name' => $carrier['name'],
                    'driver_class' => $carrier['driver_class'],
                    'api_base_url' => $carrier['api_base_url'],
                    'poll_interval_hours' => 12,
                    'is_active' => true,
                ],
            );

            foreach ($carrier['credentials'] as $sort => $credential) {
                DynamicConfig::defineSecret(
                    key: "carriers.{$carrier['code']}.{$credential}",
                    group: 'carriers',
                    label: $carrier['name'].' · '.ucwords(str_replace('_', ' ', $credential)),
                    description: 'Used by the '.$carrier['name'].' tracking adapter. Rotating it here takes effect on the next poll.',
                    sortOrder: ($index * 10) + $sort,
                );
            }
        }
    }
}
