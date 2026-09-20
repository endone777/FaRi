<?php

use App\Models\DeliveryMethod;
use App\Models\Order;
use App\Models\Product;

function fillCart(object $test, Product $product, int $qty = 1): void
{
    $test->post(route('cart.store'), [
        'product_id' => $product->id,
        'qty' => $qty,
    ]);
}

test('a guest can place an order without an account', function () {
    $product = Product::factory()->create(['price' => 60000, 'oem' => 'DEMO-1']);
    $method = DeliveryMethod::factory()->create(['cost' => 900, 'free_from' => null]);
    fillCart($this, $product, 2);

    $response = $this->post(route('checkout.store'), [
        'customer_name' => 'Иван',
        'phone' => '+7 900 000-00-00',
        'email' => 'ivan@example.com',
        'city' => 'Казань',
        'delivery_method_id' => $method->id,
    ]);

    $this->assertGuest();

    $order = Order::query()->sole();

    expect($order->items_total)->toBe(120000)
        ->and($order->delivery_cost)->toBe(900)
        ->and($order->total)->toBe(120900)
        ->and($order->status)->toBe('new')
        ->and($order->delivery_name)->toBe($method->name);

    $item = $order->items()->sole();

    expect($item->qty)->toBe(2)
        ->and($item->unit_price)->toBe(60000)
        ->and($item->line_total)->toBe(120000)
        ->and($item->oem)->toBe('DEMO-1')
        ->and($item->product_id)->toBe($product->id);

    $response->assertRedirect(route('checkout.show', [
        'order' => $order->number,
        'token' => $order->token,
    ]));
});

test('delivery is free once the order passes the threshold', function () {
    $product = Product::factory()->create(['price' => 60000]);
    $method = DeliveryMethod::factory()->create(['cost' => 600, 'free_from' => 50000]);
    fillCart($this, $product);

    $this->post(route('checkout.store'), [
        'customer_name' => 'Иван',
        'phone' => '+7 900 000-00-00',
        'email' => 'ivan@example.com',
        'delivery_method_id' => $method->id,
    ]);

    $order = Order::query()->sole();

    expect($order->delivery_cost)->toBe(0)
        ->and($order->total)->toBe(60000);
});

test('the cart is emptied once the order is placed', function () {
    $product = Product::factory()->create();
    $method = DeliveryMethod::factory()->create();
    fillCart($this, $product);

    $this->post(route('checkout.store'), [
        'customer_name' => 'Иван',
        'phone' => '+7 900 000-00-00',
        'email' => 'ivan@example.com',
        'delivery_method_id' => $method->id,
    ]);

    $this->get(route('cart.index'))
        ->assertInertia(fn ($page) => $page->where('cart.count', 0));
});

test('an order needs a name, a phone, an email and a delivery method', function () {
    $product = Product::factory()->create();
    fillCart($this, $product);

    $response = $this->from(route('checkout.create'))->post(route('checkout.store'), []);

    $response->assertSessionHasErrors([
        'customer_name',
        'phone',
        'email',
        'delivery_method_id',
    ]);

    expect(Order::query()->count())->toBe(0);
});

test('a VIN is refused unless it is seventeen characters', function () {
    $product = Product::factory()->create();
    $method = DeliveryMethod::factory()->create();
    fillCart($this, $product);

    $response = $this->from(route('checkout.create'))->post(route('checkout.store'), [
        'customer_name' => 'Иван',
        'phone' => '+7 900 000-00-00',
        'email' => 'ivan@example.com',
        'vin' => 'SHORT',
        'delivery_method_id' => $method->id,
    ]);

    $response->assertSessionHasErrors('vin');
});

test('an inactive delivery method is refused', function () {
    $product = Product::factory()->create();
    $method = DeliveryMethod::factory()->create(['is_active' => false]);
    fillCart($this, $product);

    $response = $this->from(route('checkout.create'))->post(route('checkout.store'), [
        'customer_name' => 'Иван',
        'phone' => '+7 900 000-00-00',
        'email' => 'ivan@example.com',
        'delivery_method_id' => $method->id,
    ]);

    $response->assertSessionHasErrors('delivery_method_id');
});

test('the checkout page sends an empty cart back to the catalogue', function () {
    $this->get(route('checkout.create'))->assertRedirect(route('catalog.index'));
});

test('an order cannot be placed with an empty cart', function () {
    $method = DeliveryMethod::factory()->create();

    $response = $this->post(route('checkout.store'), [
        'customer_name' => 'Иван',
        'phone' => '+7 900 000-00-00',
        'email' => 'ivan@example.com',
        'delivery_method_id' => $method->id,
    ]);

    $response->assertRedirect(route('catalog.index'));
    expect(Order::query()->count())->toBe(0);
});

test('the confirmation page opens only with the order token', function () {
    $order = Order::factory()->create();

    $this->get(route('checkout.show', ['order' => $order->number, 'token' => $order->token]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('shop/OrderPlaced')
            ->where('order.number', $order->number));

    $this->get(route('checkout.show', ['order' => $order->number, 'token' => 'wrong']))
        ->assertNotFound();
});
