<?php

namespace App\Http\Requests\Admin;

use App\Models\CarModel;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CarModelRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'car_brand_id' => ['required', 'integer', Rule::exists('car_brands', 'id')],
            'name' => [
                'required', 'string', 'max:80',
                Rule::unique('car_models', 'name')
                    ->where('car_brand_id', $this->integer('car_brand_id'))
                    ->ignore($this->route('carModel')),
            ],
            'year_from' => [
                'required', 'integer',
                'min:'.CarModel::EARLIEST_YEAR,
                'max:'.(now()->year + 1),
            ],
            'year_to' => [
                'nullable', 'integer',
                'gte:year_from',
                'max:'.(now()->year + 1),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'car_brand_id' => 'марка',
            'name' => 'модель',
            'year_from' => 'год начала выпуска',
            'year_to' => 'год окончания выпуска',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'year_from.min' => 'Справочник ведётся с :min года.',
            'year_to.gte' => 'Год окончания не может быть раньше года начала.',
        ];
    }
}
