<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\CheckoutRequest;
use App\Http\Resources\DeliveryMethodResource;
use App\Http\Resources\OrderResource;
use App\Models\DeliveryMethod;
use App\Models\Order;
use App\Support\Cart;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class CheckoutController extends Controller
{
    public function __construct(private readonly Cart $cart) {}

    /**
     * Show the order form. No account is needed to place an order.
     */
    public function create(): Response|RedirectResponse
    {
        if ($this->cart->isEmpty()) {
            return to_route('catalog.index');
        }

        return Inertia::render('shop/Checkout', [
            'deliveryMethods' => DeliveryMethodResource::collection(
                DeliveryMethod::query()->active()->orderBy('position')->get(),
            )->resolve(),
        ]);
    }

    /**
     * Turn the session cart into an order and hand back a tracking link.
     */
    public function store(CheckoutRequest $request): RedirectResponse
    {
        $lines = $this->cart->detailed();

        if ($lines->isEmpty()) {
            return to_route('catalog.index')->withErrors(['cart' => 'Корзина пуста']);
        }

        $method = DeliveryMethod::query()->findOrFail($request->integer('delivery_method_id'));
        $itemsTotal = (int) $lines->sum('line_total');
        $deliveryCost = $method->costFor($itemsTotal);

        $order = DB::transaction(function () use ($request, $lines, $method, $itemsTotal, $deliveryCost): Order {
            $order = Order::create([
                ...$request->safe()->except('delivery_method_id'),
                'number' => $this->nextNumber(),
                'token' => Str::random(40),
                'delivery_method_id' => $method->id,
                'delivery_name' => $method->name,
                'delivery_cost' => $deliveryCost,
                'items_total' => $itemsTotal,
                'total' => $itemsTotal + $deliveryCost,
                'status' => 'new',
            ]);

            foreach ($lines as $line) {
                $order->items()->create([
                    'product_id' => $line['product']->id,
                    'title' => $line['product']->title(),
                    'oem' => $line['product']->oem,
                    'unit_price' => $line['product']->price,
                    'qty' => $line['qty'],
                    'line_total' => $line['line_total'],
                ]);
            }

            return $order;
        });

        $this->cart->clear();

        return to_route('checkout.show', ['order' => $order->number, 'token' => $order->token]);
    }

    /**
     * Confirmation page, readable by anyone holding the order's token.
     */
    public function show(Request $request, Order $order): Response
    {
        abort_unless(hash_equals($order->token, $request->string('token')->toString()), 404);

        $order->load('items');

        return Inertia::render('shop/OrderPlaced', [
            'order' => (new OrderResource($order))->resolve(),
        ]);
    }

    /**
     * A short, human-readable order number that is unique per day.
     */
    private function nextNumber(): string
    {
        do {
            $number = 'FARI-'.now()->format('ymd').'-'.str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);
        } while (Order::query()->where('number', $number)->exists());

        return $number;
    }
}
