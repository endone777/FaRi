<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CallbackRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class CallbackRequestController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/Callbacks', [
            'callbacks' => CallbackRequest::query()->latest()->paginate(20),
            'statuses' => CallbackRequest::STATUSES,
        ]);
    }

    public function update(Request $request, CallbackRequest $callback): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(array_keys(CallbackRequest::STATUSES))],
        ]);

        $callback->update($validated);

        return back();
    }

    public function destroy(CallbackRequest $callback): RedirectResponse
    {
        $callback->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Запись удалена']);

        return back();
    }
}
