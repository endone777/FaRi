<?php

use App\Models\CallbackRequest;
use App\Models\ContactMessage;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;

beforeEach(function () {
    $this->actingAs(User::factory()->admin()->create());
});

test('an administrator sees an order with its lines', function () {
    $order = Order::factory()->create();
    OrderItem::factory()->for($order)->create(['title' => 'BMW X5 — Laserlight']);

    $this->get(route('admin.orders.show', $order))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/orders/Show')
            ->where('order.number', $order->number)
            ->has('order.items', 1)
            ->where('order.items.0.title', 'BMW X5 — Laserlight'));
});

test('the order list can be narrowed to one status', function () {
    $confirmed = Order::factory()->create(['status' => 'confirmed']);
    Order::factory()->create(['status' => 'new']);

    $this->get(route('admin.orders.index', ['status' => 'confirmed']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('orders.data', 1)
            ->where('orders.data.0.number', $confirmed->number));
});

test('the order list can be searched by phone', function () {
    $wanted = Order::factory()->create(['phone' => '+7 911 111-11-11']);
    Order::factory()->create(['phone' => '+7 922 222-22-22']);

    $this->get(route('admin.orders.index', ['q' => '911']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('orders.data', 1)
            ->where('orders.data.0.number', $wanted->number));
});

test('an administrator moves an order forward and leaves a note', function () {
    $order = Order::factory()->create(['status' => 'new']);

    $this->patch(route('admin.orders.update', $order), [
        'status' => 'invoiced',
        'admin_note' => 'Счёт отправлен',
    ]);

    $order->refresh();

    expect($order->status)->toBe('invoiced')
        ->and($order->admin_note)->toBe('Счёт отправлен');
});

test('an unknown order status is refused', function () {
    $order = Order::factory()->create(['status' => 'new']);

    $response = $this->from(route('admin.orders.show', $order))
        ->patch(route('admin.orders.update', $order), ['status' => 'teleported']);

    $response->assertSessionHasErrors('status');
    expect($order->refresh()->status)->toBe('new');
});

test('deleting an order removes its lines', function () {
    $order = Order::factory()->create();
    OrderItem::factory()->for($order)->create();

    $this->delete(route('admin.orders.destroy', $order))
        ->assertRedirect(route('admin.orders.index'));

    expect(Order::query()->count())->toBe(0)
        ->and(OrderItem::query()->count())->toBe(0);
});

test('an administrator marks a call back as handled', function () {
    $callback = CallbackRequest::factory()->create(['status' => 'new']);

    $this->patch(route('admin.callbacks.update', $callback), ['status' => 'called']);

    expect($callback->refresh()->status)->toBe('called');
});

test('an administrator marks a message as answered and can delete it', function () {
    $message = ContactMessage::factory()->create(['status' => 'new']);

    $this->patch(route('admin.messages.update', $message), ['status' => 'answered']);
    expect($message->refresh()->status)->toBe('answered');

    $this->delete(route('admin.messages.destroy', $message));
    expect(ContactMessage::query()->count())->toBe(0);
});

test('the dashboard counts what needs attention', function () {
    Order::factory()->count(2)->create(['status' => 'new']);
    Order::factory()->create(['status' => 'done', 'total' => 5000]);
    CallbackRequest::factory()->create(['status' => 'new']);
    ContactMessage::factory()->create(['status' => 'new']);

    $this->get(route('admin.dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/Dashboard')
            ->where('stats.ordersNew', 2)
            ->where('stats.ordersTotal', 3)
            ->where('stats.revenue', 5000)
            ->where('stats.callbacksNew', 1)
            ->where('stats.messagesNew', 1));
});
