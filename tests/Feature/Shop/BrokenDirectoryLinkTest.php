<?php

use App\Models\DeliveryMethod;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;

/**
 * Связь позиции со справочником обнуляется, когда модель удаляют из справочника.
 * Ни витрина, ни админка не должны на этом падать.
 */
test('a product with no directory link opens in the admin', function () {
    $this->actingAs(User::factory()->admin()->create());
    $product = Product::factory()->create(['car_model_id' => null]);

    $this->get(route('admin.products.edit', ['product' => $product->id]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/products/Form')
            ->where('product.brand', '')
            ->where('product.model', ''));
});

test('a product with no directory link lists in the admin catalogue', function () {
    $this->actingAs(User::factory()->admin()->create());
    Product::factory()->create(['car_model_id' => null]);

    $this->get(route('admin.products.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->has('products.data', 1));
});

test('a product with no directory link renders on the storefront', function () {
    $product = Product::factory()->create(['car_model_id' => null]);

    $this->get(route('home'))->assertOk();
    $this->get(route('catalog.index'))->assertOk();

    $this->get(route('catalog.show', $product))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('product.brand', ''));
});

test('a product with no directory link is left out of the make filter', function () {
    Product::factory()->forCar('BMW')->create();
    Product::factory()->create(['car_model_id' => null]);

    $this->get(route('catalog.index', ['brand' => 'BMW']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->has('products.data', 1));
});

test('a product with no directory link can still be ordered', function () {
    $product = Product::factory()->create(['car_model_id' => null, 'name' => 'LED в сборе']);
    $method = DeliveryMethod::factory()->create();

    $this->post(route('cart.store'), ['product_id' => $product->id]);
    $this->get(route('cart.index'))->assertOk();

    $this->post(route('checkout.store'), [
        'customer_name' => 'Иван',
        'phone' => '+7 900 000-00-00',
        'email' => 'ivan@example.com',
        'delivery_method_id' => $method->id,
    ]);

    expect(Order::query()->sole()->items()->sole()->title)->toBe('LED в сборе');
});
