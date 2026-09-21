<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ShippingRoute;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ShippingRoute> */
final class ShippingRouteFactory extends Factory
{
    protected $model = ShippingRoute::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'code' => strtoupper($this->faker->unique()->lexify('RT_????')),
            'name' => 'Australia → Bangladesh',
            'origin_country' => 'AU',
            'destination_country' => 'BD',
            'base_currency' => 'AUD',
            'quote_currency' => 'BDT',
            'transit_time_text' => '10 to 18 working days',
            'is_active' => true,
        ];
    }
}
