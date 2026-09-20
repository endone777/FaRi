<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\CallbackRequest;
use App\Models\ContactMessage;
use App\Models\Order;
use App\Models\Product;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('admin/Dashboard', [
            'stats' => [
                'products' => Product::query()->count(),
                'productsHidden' => Product::query()->where('is_active', false)->count(),
                'outOfStock' => Product::query()->where('qty', 0)->count(),
                'ordersNew' => Order::query()->where('status', 'new')->count(),
                'ordersTotal' => Order::query()->count(),
                'revenue' => (int) Order::query()->whereIn('status', ['confirmed', 'invoiced', 'shipped', 'done'])->sum('total'),
                'callbacksNew' => CallbackRequest::query()->where('status', 'new')->count(),
                'messagesNew' => ContactMessage::query()->where('status', 'new')->count(),
            ],
            'recentOrders' => OrderResource::collection(
                Order::query()->latest()->limit(8)->get(),
            )->resolve(),
            'lowStock' => Product::query()
                ->where('is_active', true)
                ->where('qty', '<=', 2)
                ->orderBy('qty')
                ->limit(6)
                ->get(['id', 'slug', 'brand', 'model', 'name', 'qty']),
        ]);
    }
}
