<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Carrier;
use App\Services\Carrier\Adapters\FakeTrackingAdapter;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Carrier> */
final class CarrierFactory extends Factory
{
    protected $model = Carrier::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'name' => 'FedEx',
            'code' => $this->faker->unique()->lexify('carrier_????'),
            // Tests drive the pipeline without a real credential existing.
            'driver_class' => FakeTrackingAdapter::class,
            'api_base_url' => 'https://example.test',
            'poll_interval_hours' => 12,
            'is_active' => true,
        ];
    }
}
