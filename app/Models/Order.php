<?php

namespace App\Models;

use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $number
 * @property string $token
 * @property string $customer_name
 * @property string $phone
 * @property string $email
 * @property string|null $vin
 * @property string|null $city
 * @property string|null $comment
 * @property int|null $delivery_method_id
 * @property string|null $delivery_name
 * @property int $delivery_cost
 * @property int $items_total
 * @property int $total
 * @property string $status
 * @property string|null $admin_note
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, OrderItem> $items
 */
#[Fillable([
    'number', 'token', 'customer_name', 'phone', 'email', 'vin', 'city', 'comment',
    'delivery_method_id', 'delivery_name', 'delivery_cost', 'items_total', 'total',
    'status', 'admin_note',
])]
class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    /**
     * Workflow states an order moves through, keyed by storage value.
     *
     * @var array<string, string>
     */
    public const STATUSES = [
        'new' => 'Новая',
        'confirmed' => 'Подтверждена',
        'invoiced' => 'Счёт выставлен',
        'shipped' => 'Отправлена',
        'done' => 'Завершена',
        'cancelled' => 'Отменена',
    ];

    /**
     * @return HasMany<OrderItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * @return BelongsTo<DeliveryMethod, $this>
     */
    public function deliveryMethod(): BelongsTo
    {
        return $this->belongsTo(DeliveryMethod::class);
    }

    public function getRouteKeyName(): string
    {
        return 'number';
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'delivery_cost' => 'integer',
            'items_total' => 'integer',
            'total' => 'integer',
        ];
    }
}
