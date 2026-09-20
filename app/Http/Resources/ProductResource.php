<?php

namespace App\Http\Resources;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Product
 */
class ProductResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'brand' => $this->brand,
            'model' => $this->model,
            'year_from' => $this->year_from,
            'year_to' => $this->year_to,
            'name' => $this->name,
            'title' => $this->title(),
            'tech' => $this->tech,
            'color_temp' => $this->color_temp,
            'oem' => $this->oem,
            'shape' => $this->shape,
            'price' => $this->price,
            'qty' => $this->qty,
            'in_stock' => $this->isInStock(),
            'stock_note' => $this->stock_note,
            'weight' => (float) $this->weight,
            'warranty_months' => $this->warranty_months,
            'photo' => $this->photoUrl(),
            'photo_old' => $this->photoOldUrl(),
            'description' => $this->description,
            'is_active' => $this->is_active,
        ];
    }
}
