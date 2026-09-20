<?php

namespace App\Http\Requests\Admin;

use App\Models\Product;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'brand' => ['required', 'string', 'max:60'],
            'model' => ['required', 'string', 'max:80'],
            'name' => ['required', 'string', 'max:120'],
            'year_from' => ['required', 'integer', 'min:1950', 'max:2100'],
            'year_to' => ['required', 'integer', 'min:1950', 'max:2100', 'gte:year_from'],
            'tech' => ['required', Rule::in(Product::TECHS)],
            'color_temp' => ['required', 'integer', 'min:2000', 'max:8000'],
            'shape' => ['required', Rule::in(array_keys(Product::SHAPES))],
            'oem' => ['required', 'string', 'max:80'],
            'price' => ['required', 'integer', 'min:0', 'max:100000000'],
            'qty' => ['required', 'integer', 'min:0', 'max:9999'],
            'stock_note' => ['nullable', 'string', 'max:120'],
            'weight' => ['nullable', 'numeric', 'min:0', 'max:999'],
            'warranty_months' => ['nullable', 'integer', 'min:0', 'max:600'],
            'description' => ['nullable', 'string', 'max:5000'],
            'is_active' => ['boolean'],
            'photo' => ['nullable', 'image', 'max:5120'],
            'photo_old' => ['nullable', 'image', 'max:5120'],
            'remove_photo' => ['boolean'],
            'remove_photo_old' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'brand' => 'марка',
            'model' => 'модель',
            'name' => 'название',
            'year_from' => 'год с',
            'year_to' => 'год по',
            'oem' => 'артикул',
            'price' => 'цена',
            'qty' => 'остаток',
        ];
    }
}
