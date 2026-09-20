<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\CallbackFormRequest;
use App\Models\CallbackRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class CallbackController extends Controller
{
    public function store(CallbackFormRequest $request): RedirectResponse
    {
        CallbackRequest::create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Заявка на звонок принята']);

        return back();
    }
}
