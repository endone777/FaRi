<?php

namespace App\Models;

use Database\Factories\DeliveryMethodFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $code
 * @property string $name
 * @property int $cost
 * @property string $days
 * @property string|null $note
 * @property int|null $free_from
 * @property bool $is_active
 * @property int $position
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['code', 'name', 'cost', 'days', 'note', 'free_from', 'is_active', 'position'])]
class DeliveryMethod extends Model
{
    /** @use HasFactory<DeliveryMethodFactory> */
    use HasFactory;

    /**
     * Delivery cost for an order of the given item subtotal.
     */
    public function costFor(int $itemsTotal): int
    {
        if ($this->free_from !== null && $itemsTotal >= $this->free_from) {
            return 0;
        }

        return $this->cost;
    }

    /**
     * @param  Builder<DeliveryMethod>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'cost' => 'integer',
            'free_from' => 'integer',
            'position' => 'integer',
        ];
    }
}
