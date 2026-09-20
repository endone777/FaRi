<?php

use App\Models\Product;

test('a guest can put a set of headlights in the cart', function () {
    $product = Product::factory()->create(['price' => 50000]);

    $response = $this->from(route('home'))->post(route('cart.store'), [
        'product_id' => $product->id,
        'qty' => 2,
    ]);

    $response->assertRedirect(route('home'));
    $this->assertGuest();

    $this->get(route('cart.index'))
        ->assertInertia(fn ($page) => $page
            ->component('shop/Cart')
            ->where('cart.count', 2)
            ->where('cart.items_total', 100000)
            ->where('cart.lines.0.product.slug', $product->slug));
});

test('adding the same product twice raises the quantity of one line', function () {
    $product = Product::factory()->create();

    $this->post(route('cart.store'), ['product_id' => $product->id]);
    $this->post(route('cart.store'), ['product_id' => $product->id]);

    $this->get(route('cart.index'))
        ->assertInertia(fn ($page) => $page
            ->has('cart.lines', 1)
            ->where('cart.count', 2));
});

test('two different products are separate lines', function () {
    $first = Product::factory()->create();
    $second = Product::factory()->create();

    $this->post(route('cart.store'), ['product_id' => $first->id]);
    $this->post(route('cart.store'), ['product_id' => $second->id]);

    $this->get(route('cart.index'))
        ->assertInertia(fn ($page) => $page->has('cart.lines', 2));
});

test('a hidden product cannot be added to the cart', function () {
    $product = Product::factory()->hidden()->create();

    $response = $this->from(route('home'))->post(route('cart.store'), [
        'product_id' => $product->id,
    ]);

    $response->assertSessionHasErrors('product_id');
});

test('setting a line to zero removes it', function () {
    $product = Product::factory()->create();
    $this->post(route('cart.store'), ['product_id' => $product->id]);

    $this->patch(route('cart.update'), ['key' => "p{$product->id}", 'qty' => 0]);

    $this->get(route('cart.index'))
        ->assertInertia(fn ($page) => $page->where('cart.count', 0));
});

test('a line can be removed and the cart emptied', function () {
    $first = Product::factory()->create();
    $second = Product::factory()->create();
    $this->post(route('cart.store'), ['product_id' => $first->id]);
    $this->post(route('cart.store'), ['product_id' => $second->id]);

    $this->delete(route('cart.destroy', ['key' => "p{$first->id}"]));

    $this->get(route('cart.index'))
        ->assertInertia(fn ($page) => $page->has('cart.lines', 1));

    $this->delete(route('cart.clear'));

    $this->get(route('cart.index'))
        ->assertInertia(fn ($page) => $page->has('cart.lines', 0));
});

test('a line whose product was deleted drops out of the cart', function () {
    $product = Product::factory()->create();
    $this->post(route('cart.store'), ['product_id' => $product->id]);

    $product->delete();

    $this->get(route('cart.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('cart.lines', 0)
            ->where('cart.items_total', 0));
});
