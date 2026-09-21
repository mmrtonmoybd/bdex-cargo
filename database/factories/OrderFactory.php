<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\FulfilmentMethod;
use App\Enums\NormalizedStatus;
use App\Enums\PaymentStatus;
use App\Enums\ServiceLevel;
use App\Models\Order;
use App\Models\ShippingRoute;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Order> */
final class OrderFactory extends Factory
{
    protected $model = Order::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'shipping_route_id' => ShippingRoute::factory(),
            'status' => NormalizedStatus::Booked,
            'service_level' => ServiceLevel::Standard,
            'fulfilment_method' => FulfilmentMethod::CourierPickup,
            'sender_name' => $this->faker->name(),
            'sender_phone' => '01'.$this->faker->numerify('#########'),
            'sender_address' => $this->faker->address(),
            'receiver_name' => $this->faker->name(),
            'receiver_phone' => '04'.$this->faker->numerify('########'),
            'receiver_address' => $this->faker->address(),
            'receiver_city' => 'Sydney',
            'declared_value' => 250,
            'payment_status' => PaymentStatus::Pending,
            'payment_currency' => 'AUD',
            'total_cost_aud' => 220,
        ];
    }
}
