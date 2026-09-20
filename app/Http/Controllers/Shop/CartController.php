<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\CartStoreRequest;
use App\Http\Requests\Shop\CartUpdateRequest;
use App\Models\Product;
use App\Support\Cart;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CartController extends Controller
{
    public function __construct(private readonly Cart $cart) {}

    public function index(): Response
    {
        return Inertia::render('shop/Cart');
    }

    public function store(CartStoreRequest $request): RedirectResponse
    {
        $product = Product::query()->findOrFail($request->integer('product_id'));

        $this->cart->add($product, $request->integer('qty') ?: 1);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Добавлено в корзину']);

        return back();
    }

    public function update(CartUpdateRequest $request): RedirectResponse
    {
        $this->cart->setQty($request->string('key')->toString(), $request->integer('qty'));

        return back();
    }

    public function destroy(Request $request, string $key): RedirectResponse
    {
        $this->cart->remove($key);

        return back();
    }

    public function clear(): RedirectResponse
    {
        $this->cart->clear();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Корзина очищена']);

        return back();
    }
}
