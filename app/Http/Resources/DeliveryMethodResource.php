<?php

namespace App\Http\Resources;

use App\Models\DeliveryMethod;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin DeliveryMethod
 */
class DeliveryMethodResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'cost' => $this->cost,
            'days' => $this->days,
            'note' => $this->note,
            'free_from' => $this->free_from,
            'is_active' => $this->is_active,
            'position' => $this->position,
        ];
    }
}
