<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Resources\DeliveryMethodResource;
use App\Http\Resources\ProductResource;
use App\Models\ContactMessage;
use App\Models\DeliveryMethod;
use App\Models\Product;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    /**
     * Show the storefront: picker, fitment stage, catalogue and forms.
     */
    public function __invoke(): Response
    {
        $products = Product::query()
            ->active()
            ->orderBy('brand')
            ->orderBy('model')
            ->get();

        return Inertia::render('shop/Home', [
            'products' => ProductResource::collection($products)->resolve(),
            'deliveryMethods' => DeliveryMethodResource::collection(
                DeliveryMethod::query()->active()->orderBy('position')->get(),
            )->resolve(),
            'topics' => ContactMessage::TOPICS,
        ]);
    }
}
