<?php

use App\Models\DeliveryMethod;
use App\Models\Product;

test('the storefront lists only products that are on show', function () {
    $visible = Product::factory()->forCar('BMW')->create();
    $hidden = Product::factory()->forCar('Audi')->hidden()->create();

    $response = $this->get(route('home'));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('shop/Home')
            ->has('products', 1)
            ->where('products.0.slug', $visible->slug)
            ->where('products.0.brand', 'BMW'));

    $this->assertNotSame($hidden->slug, $visible->slug);
});

test('the catalogue filters by brand', function () {
    Product::factory()->forCar('BMW')->create();
    Product::factory()->forCar('Audi')->create();

    $response = $this->get(route('catalog.index', ['brand' => 'Audi']));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('shop/Catalog')
            ->has('products.data', 1)
            ->where('products.data.0.brand', 'Audi'));
});

test('the catalogue keeps only products whose year range covers the chosen year', function () {
    $matching = Product::factory()->create(['year_from' => 2015, 'year_to' => 2020]);
    Product::factory()->create(['year_from' => 2005, 'year_to' => 2010]);

    $response = $this->get(route('catalog.index', ['year' => 2018]));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('products.data', 1)
            ->where('products.data.0.slug', $matching->slug));
});

test('the catalogue search matches the part number', function () {
    $matching = Product::factory()->create(['oem' => 'DEMO-63-11-8-092-471']);
    Product::factory()->create(['oem' => 'DEMO-81150-06D40']);

    $response = $this->get(route('catalog.index', ['q' => '8-092']));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('products.data', 1)
            ->where('products.data.0.slug', $matching->slug));
});

test('the catalogue sorts by price when asked', function () {
    Product::factory()->create(['price' => 90000, 'slug' => 'pricey']);
    Product::factory()->create(['price' => 10000, 'slug' => 'cheap']);

    $response = $this->get(route('catalog.index', ['sort' => 'price']));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('products.data.0.slug', 'cheap')
            ->where('products.data.1.slug', 'pricey'));
});

test('a product page shows the item and others of the same brand', function () {
    $product = Product::factory()->forCar('BMW')->create();
    $sibling = Product::factory()->forCar('BMW', 'X5 (G05)')->create();
    Product::factory()->forCar('Audi')->create();

    $response = $this->get(route('catalog.show', $product));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('shop/Product')
            ->where('product.slug', $product->slug)
            ->has('related', 1)
            ->where('related.0.slug', $sibling->slug));
});

test('a hidden product is not reachable by its address', function () {
    $product = Product::factory()->hidden()->create();

    $this->get(route('catalog.show', $product))->assertNotFound();
});

test('the delivery page lists only active methods', function () {
    $shown = DeliveryMethod::factory()->create(['position' => 1]);
    DeliveryMethod::factory()->create(['is_active' => false]);

    $response = $this->get(route('delivery'));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('shop/Delivery')
            ->has('deliveryMethods', 1)
            ->where('deliveryMethods.0.code', $shown->code));
});
