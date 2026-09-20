<?php

namespace App\Http\Middleware;

use App\Http\Resources\ProductResource;
use App\Support\Cart;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * The guest cart, shared with every page so the drawer is always in sync.
     *
     * @return array{lines: list<array<string, mixed>>, count: int, items_total: int, weight: float}
     */
    private function cart(Request $request): array
    {
        $cart = $request->getSession()->isStarted() ? app(Cart::class) : null;

        if ($cart === null) {
            return ['lines' => [], 'count' => 0, 'items_total' => 0, 'weight' => 0.0];
        }

        $lines = $cart->detailed();

        return [
            'lines' => $lines->map(fn (array $line): array => [
                'key' => $line['key'],
                'side' => $line['side'],
                'qty' => $line['qty'],
                'line_total' => $line['line_total'],
                'product' => new ProductResource($line['product']),
            ])->all(),
            'count' => (int) $lines->sum('qty'),
            'items_total' => (int) $lines->sum('line_total'),
            'weight' => round((float) $lines->sum(fn (array $line): float => $line['product']->weight * $line['qty']), 2),
        ];
    }

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $request->user(),
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'isAdmin' => (bool) $request->user()?->isAdmin(),
            'cart' => fn (): array => $this->cart($request),
        ];
    }
}
