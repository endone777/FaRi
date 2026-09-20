<?php

namespace Database\Factories;

use App\Models\CarBrand;
use App\Models\CarModel;
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
            'car_model_id' => CarModel::factory(),
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

    /**
     * Fit the product to a car of the given make, creating the directory entry.
     */
    public function forCar(string $brand, ?string $model = null): static
    {
        return $this->state(function () use ($brand, $model): array {
            $carBrand = CarBrand::firstOrCreate(
                ['name' => $brand],
                ['slug' => Str::slug($brand), 'position' => 0],
            );

            $name = $model ?? fake()->unique()->bothify('Model ##?');

            $carModel = CarModel::firstOrCreate(
                ['car_brand_id' => $carBrand->id, 'name' => $name],
                ['slug' => Str::slug($name), 'year_from' => 2010, 'year_to' => null],
            );

            return ['car_model_id' => $carModel->id];
        });
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
