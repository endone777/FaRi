<?php

use App\Models\User;

dataset('admin pages', [
    'dashboard' => fn () => route('admin.dashboard'),
    'catalogue' => fn () => route('admin.products.index'),
    'orders' => fn () => route('admin.orders.index'),
    'callbacks' => fn () => route('admin.callbacks.index'),
    'messages' => fn () => route('admin.messages.index'),
    'delivery' => fn () => route('admin.delivery.index'),
]);

test('guests are sent to the login page', function (string $url) {
    $this->get($url)->assertRedirect(route('login'));
})->with('admin pages');

test('a signed in customer may not reach the admin panel', function (string $url) {
    $this->actingAs(User::factory()->create());

    $this->get($url)->assertForbidden();
})->with('admin pages');

test('an administrator reaches every admin page', function (string $url) {
    $this->actingAs(User::factory()->admin()->create());

    $this->get($url)->assertOk();
})->with('admin pages');

test('a customer may not change the catalogue', function () {
    $this->actingAs(User::factory()->create());

    $this->post(route('admin.products.store'), [])->assertForbidden();
});
