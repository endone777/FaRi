<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DeliveryMethodRequest;
use App\Http\Resources\DeliveryMethodResource;
use App\Models\DeliveryMethod;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class DeliveryMethodController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/Delivery', [
            'methods' => DeliveryMethodResource::collection(
                DeliveryMethod::query()->orderBy('position')->get(),
            )->resolve(),
        ]);
    }

    public function store(DeliveryMethodRequest $request): RedirectResponse
    {
        DeliveryMethod::create([
            ...$request->validated(),
            'is_active' => $request->boolean('is_active'),
            'position' => $request->integer('position'),
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Способ доставки добавлен']);

        return back();
    }

    public function update(DeliveryMethodRequest $request, DeliveryMethod $deliveryMethod): RedirectResponse
    {
        $deliveryMethod->update([
            ...$request->validated(),
            'is_active' => $request->boolean('is_active'),
            'position' => $request->integer('position'),
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Способ доставки обновлён']);

        return back();
    }

    public function destroy(DeliveryMethod $deliveryMethod): RedirectResponse
    {
        $deliveryMethod->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Способ доставки удалён']);

        return back();
    }
}
