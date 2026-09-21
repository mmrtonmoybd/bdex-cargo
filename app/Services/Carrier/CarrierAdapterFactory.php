<?php

declare(strict_types=1);

namespace App\Services\Carrier;

use App\Models\Carrier;
use Illuminate\Contracts\Container\Container;

/**
 * Resolves the adapter for a carrier row.
 *
 * The class name is a column, and the credentials are config_entries the adapter
 * reads at call time — so pointing a carrier at a different implementation, or
 * rotating its keys, is a Filament edit with no deploy.
 */
final class CarrierAdapterFactory
{
    public function __construct(private readonly Container $container) {}

    public function make(Carrier $carrier): CarrierTrackingAdapter
    {
        $class = $carrier->driver_class;

        if (! class_exists($class)) {
            throw new CarrierApiException(
                "Carrier '{$carrier->name}' is set to driver {$class}, which does not exist. "
                .'Adding a carrier we have never integrated with needs one adapter class first.'
            );
        }

        $adapter = $this->container->make($class, ['carrier' => $carrier]);

        if (! $adapter instanceof CarrierTrackingAdapter) {
            throw new CarrierApiException(
                "Carrier '{$carrier->name}' is set to driver {$class}, which does not implement "
                .CarrierTrackingAdapter::class.'.'
            );
        }

        return $adapter;
    }
}
