<?php

use App\Models\CarBrand;
use App\Models\CarModel;
use App\Models\Product;

/**
 * Марка и модель товара читаются из справочника. Связь может оборваться —
 * позиция обязана остаться пригодной к показу.
 */
function productLinkedTo(?CarModel $carModel): Product
{
    $product = new Product(['name' => 'Adaptive LED в сборе']);
    $product->setRelation('carModel', $carModel);

    return $product;
}

function carModelNamed(string $name, ?CarBrand $brand): CarModel
{
    $carModel = new CarModel(['name' => $name]);
    $carModel->setRelation('carBrand', $brand);

    return $carModel;
}

test('the make and model come from the directory entry', function () {
    $product = productLinkedTo(
        carModelNamed('X5 (G05)', new CarBrand(['name' => 'BMW'])),
    );

    expect($product->brand)->toBe('BMW')
        ->and($product->model)->toBe('X5 (G05)')
        ->and($product->title())->toBe('BMW X5 (G05) — Adaptive LED в сборе');
});

test('a directory entry without a make leaves the product readable', function () {
    $product = productLinkedTo(carModelNamed('X5 (G05)', null));

    expect($product->brand)->toBe('')
        ->and($product->model)->toBe('X5 (G05)')
        ->and($product->title())->toBe('X5 (G05) — Adaptive LED в сборе');
});

test('a product with no directory entry falls back to its own name', function () {
    $product = productLinkedTo(null);

    expect($product->brand)->toBe('')
        ->and($product->model)->toBe('')
        ->and($product->title())->toBe('Adaptive LED в сборе');
});
