<?php

namespace Database\Factories;

use App\Models\CarBrand;
use App\Models\CarModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<CarModel>
 */
class CarModelFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->bothify('Model ##?');
        $from = fake()->numberBetween(2010, 2020);

        return [
            'car_brand_id' => CarBrand::factory(),
            'name' => $name,
            'slug' => Str::slug($name),
            'year_from' => $from,
            'year_to' => $from + fake()->numberBetween(3, 8),
        ];
    }

    public function stillMade(): static
    {
        return $this->state(fn (): array => ['year_to' => null]);
    }
}
