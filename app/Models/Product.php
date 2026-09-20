<?php

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $slug
 * @property string $brand
 * @property string $model
 * @property int $year_from
 * @property int $year_to
 * @property string $name
 * @property string $tech
 * @property int $color_temp
 * @property string $oem
 * @property string $shape
 * @property int $price
 * @property int $qty
 * @property string|null $stock_note
 * @property string $weight
 * @property int $warranty_months
 * @property string|null $photo_path
 * @property string|null $photo_old_path
 * @property string|null $description
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'slug', 'brand', 'model', 'year_from', 'year_to', 'name', 'tech', 'color_temp',
    'oem', 'shape', 'price', 'qty', 'stock_note', 'weight', 'warranty_months',
    'photo_path', 'photo_old_path', 'description', 'is_active',
])]
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    /**
     * The light source technologies a product may use.
     *
     * @var list<string>
     */
    public const TECHS = ['LED', 'Ксенон', 'Галоген'];

    /**
     * The housing silhouettes drawn on the fitment stage.
     *
     * @var array<string, string>
     */
    public const SHAPES = [
        'sharp' => 'Угловатая',
        'swept' => 'Каплевидная',
        'slim' => 'Узкая',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function isInStock(): bool
    {
        return $this->qty > 0;
    }

    /**
     * @param  Builder<Product>  $query
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
            'price' => 'integer',
            'qty' => 'integer',
            'color_temp' => 'integer',
            'year_from' => 'integer',
            'year_to' => 'integer',
            'warranty_months' => 'integer',
            'weight' => 'float',
        ];
    }
}
