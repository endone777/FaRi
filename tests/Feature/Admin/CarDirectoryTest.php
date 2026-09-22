<?php

use App\Models\CarBrand;
use App\Models\CarModel;
use App\Models\Product;
use App\Models\User;

beforeEach(function () {
    $this->actingAs(User::factory()->admin()->create());
});

test('the directory lists makes with their generations and product counts', function () {
    $brand = CarBrand::factory()->create(['name' => 'BMW']);
    $model = CarModel::factory()->for($brand, 'carBrand')->create(['name' => 'X5 (G05)']);
    Product::factory()->create(['car_model_id' => $model->id]);

    $this->get(route('admin.cars.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/Cars')
            ->has('brands', 1)
            ->where('brands.0.name', 'BMW')
            ->where('brands.0.models.0.name', 'X5 (G05)')
            ->where('brands.0.models.0.products_count', 1));
});

test('an administrator adds a make', function () {
    $this->post(route('admin.cars.brands.store'), ['name' => 'Skoda', 'position' => 5]);

    $brand = CarBrand::query()->sole();

    expect($brand->name)->toBe('Skoda')
        ->and($brand->slug)->toBe('skoda')
        ->and($brand->position)->toBe(5);
});

test('two makes cannot share a name', function () {
    CarBrand::factory()->create(['name' => 'BMW']);

    $response = $this->from(route('admin.cars.index'))
        ->post(route('admin.cars.brands.store'), ['name' => 'BMW']);

    $response->assertSessionHasErrors('name');
    expect(CarBrand::query()->count())->toBe(1);
});

test('an administrator adds a generation that is still in production', function () {
    $brand = CarBrand::factory()->create();

    $this->post(route('admin.cars.models.store'), [
        'car_brand_id' => $brand->id,
        'name' => '5 Series (G60)',
        'year_from' => 2023,
        'year_to' => null,
    ]);

    $model = CarModel::query()->sole();

    expect($model->name)->toBe('5 Series (G60)')
        ->and($model->year_to)->toBeNull()
        ->and($model->yearsLabel())->toBe('2023–н. в.')
        ->and($model->lastYear())->toBe((int) now()->year);
});

test('the directory starts at 2010', function () {
    $brand = CarBrand::factory()->create();

    $response = $this->from(route('admin.cars.index'))->post(route('admin.cars.models.store'), [
        'car_brand_id' => $brand->id,
        'name' => 'E39',
        'year_from' => 1998,
    ]);

    $response->assertSessionHasErrors(['year_from' => 'Справочник ведётся с 2010 года.']);
    expect(CarModel::query()->count())->toBe(0);
});

test('a generation cannot end before it starts', function () {
    $brand = CarBrand::factory()->create();

    $response = $this->from(route('admin.cars.index'))->post(route('admin.cars.models.store'), [
        'car_brand_id' => $brand->id,
        'name' => 'X5 (G05)',
        'year_from' => 2018,
        'year_to' => 2015,
    ]);

    $response->assertSessionHasErrors('year_to');
});

test('one make cannot hold the same generation twice', function () {
    $brand = CarBrand::factory()->create();
    CarModel::factory()->for($brand, 'carBrand')->create(['name' => 'X5 (G05)']);

    $response = $this->from(route('admin.cars.index'))->post(route('admin.cars.models.store'), [
        'car_brand_id' => $brand->id,
        'name' => 'X5 (G05)',
        'year_from' => 2018,
    ]);

    $response->assertSessionHasErrors('name');
});

test('the same generation name may repeat under a different make', function () {
    $bmw = CarBrand::factory()->create(['name' => 'BMW']);
    $audi = CarBrand::factory()->create(['name' => 'Audi']);
    CarModel::factory()->for($bmw, 'carBrand')->create(['name' => 'Q5']);

    $this->post(route('admin.cars.models.store'), [
        'car_brand_id' => $audi->id,
        'name' => 'Q5',
        'year_from' => 2016,
    ]);

    expect(CarModel::query()->where('name', 'Q5')->count())->toBe(2);
});

test('an administrator corrects the years of a generation', function () {
    $model = CarModel::factory()->create(['year_from' => 2016, 'year_to' => 2020]);

    $this->patch(route('admin.cars.models.update', $model), [
        'car_brand_id' => $model->car_brand_id,
        'name' => $model->name,
        'year_from' => 2016,
        'year_to' => 2023,
    ]);

    expect($model->refresh()->year_to)->toBe(2023);
});

test('a generation used by the catalogue cannot be deleted', function () {
    $model = CarModel::factory()->create();
    Product::factory()->create(['car_model_id' => $model->id]);

    $response = $this->from(route('admin.cars.index'))
        ->delete(route('admin.cars.models.destroy', $model));

    $response->assertSessionHasErrors('model');
    expect(CarModel::query()->count())->toBe(1);
});

test('a make whose generations are used by the catalogue cannot be deleted', function () {
    $brand = CarBrand::factory()->create();
    $model = CarModel::factory()->for($brand, 'carBrand')->create();
    Product::factory()->create(['car_model_id' => $model->id]);

    $response = $this->from(route('admin.cars.index'))
        ->delete(route('admin.cars.brands.destroy', $brand));

    $response->assertSessionHasErrors('brand');
    expect(CarBrand::query()->count())->toBe(1);
});

test('an unused make is deleted with its generations', function () {
    $brand = CarBrand::factory()->create();
    CarModel::factory()->for($brand, 'carBrand')->create();

    $this->delete(route('admin.cars.brands.destroy', $brand));

    expect(CarBrand::query()->count())->toBe(0)
        ->and(CarModel::query()->count())->toBe(0);
});

test('deleting a make leaves no models behind it', function () {
    $brand = CarBrand::factory()->create();
    CarModel::factory()->count(3)->for($brand, 'carBrand')->create();
    $other = CarModel::factory()->create();

    $this->delete(route('admin.cars.brands.destroy', $brand));

    expect(CarModel::query()->where('car_brand_id', $brand->id)->count())->toBe(0)
        ->and(CarModel::query()->whereKey($other->id)->exists())->toBeTrue();
});

test('a customer may not touch the directory', function () {
    $this->actingAs(User::factory()->create());

    $this->get(route('admin.cars.index'))->assertForbidden();
    $this->post(route('admin.cars.brands.store'), ['name' => 'Skoda'])->assertForbidden();
});
