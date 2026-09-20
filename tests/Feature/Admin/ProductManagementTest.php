<?php

use App\Models\CarBrand;
use App\Models\CarModel;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->actingAs(User::factory()->admin()->create());
});

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function productPayload(CarModel $carModel, array $overrides = []): array
{
    return [
        'car_model_id' => $carModel->id,
        'name' => 'Adaptive LED в сборе',
        'year_from' => 2018,
        'year_to' => 2022,
        'tech' => 'LED',
        'color_temp' => 5000,
        'shape' => 'sharp',
        'oem' => 'DEMO-63-11-8-092-471',
        'price' => 124900,
        'qty' => 4,
        'stock_note' => 'на складе',
        'weight' => 5.4,
        'warranty_months' => 12,
        'is_active' => true,
        ...$overrides,
    ];
}

test('an administrator adds a headlight for a car from the directory', function () {
    Storage::fake('public');
    $carModel = CarModel::factory()
        ->for(CarBrand::factory()->state(['name' => 'BMW']), 'carBrand')
        ->state(['name' => '3 Series (G20)'])
        ->create();

    $response = $this->post(route('admin.products.store'), productPayload($carModel, [
        'photo' => UploadedFile::fake()->image('headlight.jpg'),
    ]));

    $response->assertRedirect(route('admin.products.index'));

    $product = Product::query()->sole();

    expect($product->car_model_id)->toBe($carModel->id)
        ->and($product->brand)->toBe('BMW')
        ->and($product->model)->toBe('3 Series (G20)')
        ->and($product->slug)->toBe('bmw-3-series-g20-adaptive-led-v-sbore')
        ->and($product->price)->toBe(124900)
        ->and($product->photo_path)->not->toBeNull();

    Storage::disk('public')->assertExists($product->photo_path);
});

test('a product must point at a car that exists in the directory', function () {
    $carModel = CarModel::factory()->create();

    $response = $this->from(route('admin.products.create'))->post(
        route('admin.products.store'),
        productPayload($carModel, ['car_model_id' => $carModel->id + 999]),
    );

    $response->assertSessionHasErrors('car_model_id');
    expect(Product::query()->count())->toBe(0);
});

test('two products for the same car get different addresses', function () {
    $carModel = CarModel::factory()->create();

    $this->post(route('admin.products.store'), productPayload($carModel));
    $this->post(route('admin.products.store'), productPayload($carModel, ['oem' => 'DEMO-2']));

    expect(Product::query()->pluck('slug')->unique())->toHaveCount(2);
});

test('the year range must not run backwards', function () {
    $carModel = CarModel::factory()->create();

    $response = $this->from(route('admin.products.create'))->post(
        route('admin.products.store'),
        productPayload($carModel, ['year_from' => 2020, 'year_to' => 2015]),
    );

    $response->assertSessionHasErrors('year_to');
    expect(Product::query()->count())->toBe(0);
});

test('a light source outside the offered list is refused', function () {
    $carModel = CarModel::factory()->create();

    $response = $this->from(route('admin.products.create'))->post(
        route('admin.products.store'),
        productPayload($carModel, ['tech' => 'Плазма']),
    );

    $response->assertSessionHasErrors('tech');
});

test('an administrator moves a headlight to another car and hides it', function () {
    $product = Product::factory()->forCar('BMW')->create(['is_active' => true]);
    $other = CarModel::factory()->create();

    $response = $this->put(
        route('admin.products.update', ['product' => $product->id]),
        productPayload($other, ['price' => 77000, 'qty' => 0, 'is_active' => false]),
    );

    $response->assertRedirect(route('admin.products.index'));

    $product->refresh();

    expect($product->car_model_id)->toBe($other->id)
        ->and($product->price)->toBe(77000)
        ->and($product->is_active)->toBeFalse()
        ->and($product->isInStock())->toBeFalse();
});

test('an uploaded photo can be removed without replacing it', function () {
    Storage::fake('public');
    $product = Product::factory()->create(['photo_path' => 'products/old.jpg']);
    Storage::disk('public')->put('products/old.jpg', 'x');

    $this->put(
        route('admin.products.update', ['product' => $product->id]),
        productPayload($product->carModel, ['remove_photo' => true]),
    );

    expect($product->refresh()->photo_path)->toBeNull();
    Storage::disk('public')->assertMissing('products/old.jpg');
});

test('deleting a product keeps the photos that ship with the app', function () {
    Storage::fake('public');
    $product = Product::factory()->create(['photo_path' => 'images/catalog/bm1.jpg']);

    $this->delete(route('admin.products.destroy', ['product' => $product->id]));

    expect(Product::query()->count())->toBe(0);
});

test('the catalogue list can be narrowed to hidden products', function () {
    Product::factory()->forCar('BMW')->create();
    $hidden = Product::factory()->forCar('Audi')->hidden()->create();

    $this->get(route('admin.products.index', ['state' => 'hidden']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/products/Index')
            ->has('products.data', 1)
            ->where('products.data.0.slug', $hidden->slug));
});

test('the product form offers the whole directory', function () {
    CarModel::factory()
        ->for(CarBrand::factory()->state(['name' => 'BMW']), 'carBrand')
        ->state(['name' => 'X5 (G05)'])
        ->create();

    $this->get(route('admin.products.create'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/products/Form')
            ->has('options.cars', 1)
            ->where('options.cars.0.name', 'BMW')
            ->where('options.cars.0.models.0.name', 'X5 (G05)'));
});
