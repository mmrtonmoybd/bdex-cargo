<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\NormalizedStatus;
use App\Models\Carrier;
use App\Models\CarrierConsignment;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<CarrierConsignment> */
final class CarrierConsignmentFactory extends Factory
{
    protected $model = CarrierConsignment::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'carrier_id' => Carrier::factory(),
            'carrier_tracking_number' => strtoupper($this->faker->unique()->bothify('7#########')),
            'internal_status' => NormalizedStatus::Booked,
            'booked_at' => now(),
        ];
    }
}
