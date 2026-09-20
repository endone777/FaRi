<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\ContactRequest;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ContactController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('shop/Contacts', [
            'topics' => ContactMessage::TOPICS,
        ]);
    }

    public function store(ContactRequest $request): RedirectResponse
    {
        ContactMessage::create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Сообщение отправлено, ответим в рабочее время']);

        return back();
    }
}
