<?php

use App\Models\CallbackRequest;
use App\Models\ContactMessage;

test('a visitor can ask for a call back', function () {
    $response = $this->from(route('home'))->post(route('callback.store'), [
        'name' => 'Иван',
        'phone' => '+7 900 000-00-00',
        'preferred_time' => 'Завтра',
    ]);

    $response->assertRedirect(route('home'));

    $callback = CallbackRequest::query()->sole();

    expect($callback->name)->toBe('Иван')
        ->and($callback->preferred_time)->toBe('Завтра')
        ->and($callback->status)->toBe('new');
});

test('a call back needs a name and a phone', function () {
    $response = $this->from(route('home'))->post(route('callback.store'), []);

    $response->assertSessionHasErrors(['name', 'phone']);
    expect(CallbackRequest::query()->count())->toBe(0);
});

test('a visitor can send a message from the contacts page', function () {
    $response = $this->from(route('contacts.index'))->post(route('contacts.store'), [
        'name' => 'Иван',
        'contact' => 'ivan@example.com',
        'topic' => 'Подбор по VIN',
        'message' => 'Нужна левая фара на Golf VII',
    ]);

    $response->assertRedirect(route('contacts.index'));

    $message = ContactMessage::query()->sole();

    expect($message->topic)->toBe('Подбор по VIN')
        ->and($message->status)->toBe('new');
});

test('a message topic outside the offered list is refused', function () {
    $response = $this->from(route('contacts.index'))->post(route('contacts.store'), [
        'name' => 'Иван',
        'contact' => 'ivan@example.com',
        'topic' => 'Что угодно',
        'message' => 'Текст',
    ]);

    $response->assertSessionHasErrors('topic');
    expect(ContactMessage::query()->count())->toBe(0);
});
