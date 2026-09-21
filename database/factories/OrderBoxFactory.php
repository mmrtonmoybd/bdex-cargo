<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderBox;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<OrderBox> */
final class OrderBoxFactory extends Factory
{
    protected $model = OrderBox::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'position' => 1,
            'weight_kg' => 12.5,
            'length_cm' => 60,
            'width_cm' => 40,
            'height_cm' => 40,
            'barcode' => strtoupper($this->faker->unique()->bothify('BX########')),
        ];
    }
}
