<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\OrderUpdateRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = [
            'q' => $request->string('q')->toString() ?: null,
            'status' => array_key_exists($request->string('status')->toString(), Order::STATUSES)
                ? $request->string('status')->toString()
                : null,
        ];

        $orders = Order::query()
            ->when($filters['q'], fn (Builder $query, string $term) => $query->where(
                fn (Builder $inner) => $inner
                    ->where('number', 'like', "%{$term}%")
                    ->orWhere('customer_name', 'like', "%{$term}%")
                    ->orWhere('phone', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%"),
            ))
            ->when($filters['status'], fn (Builder $query, string $status) => $query->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString()
            ->through(fn (Order $item): array => (new OrderResource($item))->resolve());

        return Inertia::render('admin/orders/Index', [
            'orders' => $orders,
            'filters' => $filters,
            'statuses' => Order::STATUSES,
        ]);
    }

    public function show(Order $order): Response
    {
        $order->load('items');

        return Inertia::render('admin/orders/Show', [
            'order' => (new OrderResource($order))->resolve(),
            'statuses' => Order::STATUSES,
        ]);
    }

    public function update(OrderUpdateRequest $request, Order $order): RedirectResponse
    {
        $order->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Заявка обновлена']);

        return back();
    }

    public function destroy(Order $order): RedirectResponse
    {
        $order->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Заявка удалена']);

        return to_route('admin.orders.index');
    }
}
