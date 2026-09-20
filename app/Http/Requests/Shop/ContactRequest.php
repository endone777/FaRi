<?php

namespace App\Http\Requests\Shop;

use App\Models\ContactMessage;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ContactRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'contact' => ['required', 'string', 'max:190'],
            'topic' => ['required', Rule::in(ContactMessage::TOPICS)],
            'message' => ['required', 'string', 'max:4000'],
        ];
    }
}
