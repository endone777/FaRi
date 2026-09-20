<?php

use App\Http\Controllers\Admin\CallbackRequestController;
use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DeliveryMethodController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Shop\CallbackController;
use App\Http\Controllers\Shop\CartController;
use App\Http\Controllers\Shop\CatalogController;
use App\Http\Controllers\Shop\CheckoutController;
use App\Http\Controllers\Shop\ContactController;
use App\Http\Controllers\Shop\DeliveryController;
use App\Http\Controllers\Shop\HomeController;
use App\Http\Middleware\EnsureUserIsAdmin;
use Illuminate\Support\Facades\Route;

/*
 * Storefront — ordering never requires an account.
 */
Route::get('/', HomeController::class)->name('home');

Route::get('catalog', [CatalogController::class, 'index'])->name('catalog.index');
Route::get('catalog/{product}', [CatalogController::class, 'show'])->name('catalog.show');

Route::get('delivery', DeliveryController::class)->name('delivery');

Route::get('contacts', [ContactController::class, 'index'])->name('contacts.index');
Route::post('contacts', [ContactController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('contacts.store');

Route::post('callback', [CallbackController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('callback.store');

Route::get('cart', [CartController::class, 'index'])->name('cart.index');
Route::post('cart', [CartController::class, 'store'])->name('cart.store');
Route::patch('cart', [CartController::class, 'update'])->name('cart.update');
Route::delete('cart', [CartController::class, 'clear'])->name('cart.clear');
Route::delete('cart/{key}', [CartController::class, 'destroy'])->name('cart.destroy');

Route::get('checkout', [CheckoutController::class, 'create'])->name('checkout.create');
Route::post('checkout', [CheckoutController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('checkout.store');
Route::get('checkout/{order}', [CheckoutController::class, 'show'])->name('checkout.show');

/*
 * Admin panel.
 */
Route::middleware(['auth', EnsureUserIsAdmin::class])
    ->prefix('admin')
    ->name('admin.')
    ->group(function (): void {
        Route::get('/', DashboardController::class)->name('dashboard');

        Route::resource('products', ProductController::class)->except('show');

        Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::patch('orders/{order}', [OrderController::class, 'update'])->name('orders.update');
        Route::delete('orders/{order}', [OrderController::class, 'destroy'])->name('orders.destroy');

        Route::get('callbacks', [CallbackRequestController::class, 'index'])->name('callbacks.index');
        Route::patch('callbacks/{callback}', [CallbackRequestController::class, 'update'])->name('callbacks.update');
        Route::delete('callbacks/{callback}', [CallbackRequestController::class, 'destroy'])->name('callbacks.destroy');

        Route::get('messages', [ContactMessageController::class, 'index'])->name('messages.index');
        Route::patch('messages/{message}', [ContactMessageController::class, 'update'])->name('messages.update');
        Route::delete('messages/{message}', [ContactMessageController::class, 'destroy'])->name('messages.destroy');

        Route::get('delivery', [DeliveryMethodController::class, 'index'])->name('delivery.index');
        Route::post('delivery', [DeliveryMethodController::class, 'store'])->name('delivery.store');
        Route::patch('delivery/{deliveryMethod}', [DeliveryMethodController::class, 'update'])->name('delivery.update');
        Route::delete('delivery/{deliveryMethod}', [DeliveryMethodController::class, 'destroy'])->name('delivery.destroy');
    });

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';
