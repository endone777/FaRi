<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Resources\DeliveryMethodResource;
use App\Models\DeliveryMethod;
use Inertia\Inertia;
use Inertia\Response;

class DeliveryController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('shop/Delivery', [
            'deliveryMethods' => DeliveryMethodResource::collection(
                DeliveryMethod::query()->active()->orderBy('position')->get(),
            )->resolve(),
        ]);
    }
}
