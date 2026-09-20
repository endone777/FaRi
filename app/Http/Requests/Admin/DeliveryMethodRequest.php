<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DeliveryMethodRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'code' => [
                'required', 'string', 'max:40', 'alpha_dash',
                Rule::unique('delivery_methods', 'code')->ignore($this->route('deliveryMethod')),
            ],
            'name' => ['required', 'string', 'max:120'],
            'cost' => ['required', 'integer', 'min:0', 'max:1000000'],
            'days' => ['required', 'string', 'max:60'],
            'note' => ['nullable', 'string', 'max:2000'],
            'free_from' => ['nullable', 'integer', 'min:0', 'max:100000000'],
            'is_active' => ['boolean'],
            'position' => ['nullable', 'integer', 'min:0', 'max:999'],
        ];
    }
}
