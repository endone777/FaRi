<?php

namespace App\Http\Requests\Shop;

use App\Support\Cart;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CartStoreRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', Rule::exists('products', 'id')->where('is_active', true)],
            'side' => ['required', Rule::in(array_keys(Cart::SIDES))],
            'qty' => ['nullable', 'integer', 'min:1', 'max:99'],
        ];
    }
}
