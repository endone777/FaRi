<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $from = fake()->numberBetween(2010, 2019);

        return [
            'slug' => Str::random(10),
            'brand' => fake()->randomElement(['BMW', 'Audi', 'Volkswagen', 'Toyota']),
            'model' => fake()->bothify('Model ##'),
            'year_from' => $from,
            'year_to' => $from + fake()->numberBetween(2, 6),
            'name' => 'Фара в сборе',
            'tech' => fake()->randomElement(Product::TECHS),
            'color_temp' => fake()->randomElement([3200, 4300, 5000, 5500]),
            'oem' => fake()->bothify('DEMO-##-##-#-###-###'),
            'shape' => fake()->randomElement(array_keys(Product::SHAPES)),
            'price' => fake()->numberBetween(30000, 300000),
            'qty' => fake()->numberBetween(0, 9),
            'stock_note' => 'на складе',
            'weight' => fake()->randomFloat(1, 3, 7),
            'warranty_months' => 12,
            'photo_path' => null,
            'photo_old_path' => null,
            'description' => fake()->paragraph(),
            'is_active' => true,
        ];
    }

    public function outOfStock(): static
    {
        return $this->state(fn (): array => [
            'qty' => 0,
            'stock_note' => 'под заказ, 5–7 дней',
        ]);
    }

    public function hidden(): static
    {
        return $this->state(fn (): array => ['is_active' => false]);
    }
}
