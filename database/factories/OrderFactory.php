<?php

namespace Database\Factories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $itemsTotal = fake()->numberBetween(30000, 300000);
        $deliveryCost = fake()->randomElement([0, 600, 900]);

        return [
            'number' => 'FARI-'.fake()->unique()->numberBetween(100000, 999999),
            'token' => Str::random(40),
            'customer_name' => fake()->name(),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->safeEmail(),
            'vin' => null,
            'city' => 'Москва',
            'comment' => null,
            'delivery_method_id' => null,
            'delivery_name' => 'Самовывоз со склада',
            'delivery_cost' => $deliveryCost,
            'items_total' => $itemsTotal,
            'total' => $itemsTotal + $deliveryCost,
            'status' => 'new',
        ];
    }
}
