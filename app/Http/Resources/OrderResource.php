<?php

namespace App\Http\Resources;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Order
 */
class OrderResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'number' => $this->number,
            'customer_name' => $this->customer_name,
            'phone' => $this->phone,
            'email' => $this->email,
            'vin' => $this->vin,
            'city' => $this->city,
            'comment' => $this->comment,
            'delivery_name' => $this->delivery_name,
            'delivery_cost' => $this->delivery_cost,
            'items_total' => $this->items_total,
            'total' => $this->total,
            'status' => $this->status,
            'status_label' => Order::STATUSES[$this->status] ?? $this->status,
            'admin_note' => $this->admin_note,
            'created_at' => $this->created_at?->toIso8601String(),
            'items' => $this->whenLoaded('items', fn () => $this->items->map(fn ($item): array => [
                'id' => $item->id,
                'title' => $item->title,
                'oem' => $item->oem,
                'unit_price' => $item->unit_price,
                'qty' => $item->qty,
                'line_total' => $item->line_total,
            ])),
        ];
    }
}
