<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<OrderItem> */
final class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'description' => 'Cotton shirts',
            'quantity' => 3,
            'unit' => 'pcs',
            'weight_kg' => 2.5,
            'declared_value' => 80,
        ];
    }
}
