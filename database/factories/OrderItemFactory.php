<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $price = fake()->numberBetween(30000, 300000);
        $qty = fake()->numberBetween(1, 2);

        return [
            'order_id' => Order::factory(),
            'product_id' => null,
            'title' => 'BMW 3 Series (G20) — Adaptive LED в сборе',
            'oem' => fake()->bothify('DEMO-##-##-#-###-###'),
            'unit_price' => $price,
            'qty' => $qty,
            'line_total' => $price * $qty,
        ];
    }
}
