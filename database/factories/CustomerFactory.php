<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Customer> */
final class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => '04'.$this->faker->numerify('########'),
            'password' => 'password',
            'preferred_locale' => 'en',
            'preferred_currency' => 'AUD',
            'notification_channels' => ['mail'],
        ];
    }
}
