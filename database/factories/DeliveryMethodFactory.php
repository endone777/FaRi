<?php

namespace Database\Factories;

use App\Models\DeliveryMethod;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<DeliveryMethod>
 */
class DeliveryMethodFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => Str::random(8),
            'name' => 'Доставка '.fake()->word(),
            'cost' => fake()->numberBetween(0, 1500),
            'days' => '1–3 дня',
            'note' => fake()->sentence(),
            'free_from' => null,
            'is_active' => true,
            'position' => fake()->numberBetween(0, 10),
        ];
    }
}
