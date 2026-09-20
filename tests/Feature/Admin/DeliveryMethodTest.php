<?php

use App\Models\DeliveryMethod;
use App\Models\User;

beforeEach(function () {
    $this->actingAs(User::factory()->admin()->create());
});

test('an administrator adds a delivery method that the storefront then offers', function () {
    $this->post(route('admin.delivery.store'), [
        'code' => 'msk',
        'name' => 'Курьер по Москве',
        'cost' => 600,
        'days' => '1–2 дня',
        'note' => 'Бесплатно от 50 000 ₽',
        'free_from' => 50000,
        'is_active' => true,
        'position' => 2,
    ]);

    $method = DeliveryMethod::query()->sole();

    expect($method->code)->toBe('msk')
        ->and($method->free_from)->toBe(50000)
        ->and($method->costFor(60000))->toBe(0)
        ->and($method->costFor(20000))->toBe(600);
});

test('two delivery methods cannot share a code', function () {
    DeliveryMethod::factory()->create(['code' => 'msk']);

    $response = $this->from(route('admin.delivery.index'))->post(route('admin.delivery.store'), [
        'code' => 'msk',
        'name' => 'Другой курьер',
        'cost' => 700,
        'days' => '1 день',
    ]);

    $response->assertSessionHasErrors('code');
    expect(DeliveryMethod::query()->count())->toBe(1);
});

test('a method keeps its own code when edited', function () {
    $method = DeliveryMethod::factory()->create(['code' => 'msk', 'cost' => 600]);

    $this->patch(route('admin.delivery.update', $method), [
        'code' => 'msk',
        'name' => $method->name,
        'cost' => 750,
        'days' => $method->days,
        'is_active' => true,
    ]);

    expect($method->refresh()->cost)->toBe(750);
});

test('a method switched off disappears from the storefront', function () {
    $method = DeliveryMethod::factory()->create(['is_active' => true]);

    $this->patch(route('admin.delivery.update', $method), [
        'code' => $method->code,
        'name' => $method->name,
        'cost' => $method->cost,
        'days' => $method->days,
        'is_active' => false,
    ]);

    expect($method->refresh()->is_active)->toBeFalse();

    $this->get(route('delivery'))
        ->assertInertia(fn ($page) => $page->has('deliveryMethods', 0));
});

test('a delivery method can be deleted', function () {
    $method = DeliveryMethod::factory()->create();

    $this->delete(route('admin.delivery.destroy', $method));

    expect(DeliveryMethod::query()->count())->toBe(0);
});
