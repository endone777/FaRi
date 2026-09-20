<?php

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $slug
 * @property int|null $car_model_id
 * @property-read string $brand
 * @property-read string $model
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
 * @property float $weight
 * @property int $warranty_months
 * @property string|null $photo_path
 * @property string|null $photo_old_path
 * @property string|null $description
 * @property bool $is_active
 * @property-read CarModel|null $carModel
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'slug', 'car_model_id', 'year_from', 'year_to', 'name', 'tech', 'color_temp',
    'oem', 'shape', 'price', 'qty', 'stock_note', 'weight', 'warranty_months',
    'photo_path', 'photo_old_path', 'description', 'is_active',
])]
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    /**
     * The make and the model come from the directory, so they load with every product.
     *
     * @var list<string>
     */
    protected $with = ['carModel.carBrand'];

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

    /**
     * @return BelongsTo<CarModel, $this>
     */
    public function carModel(): BelongsTo
    {
        return $this->belongsTo(CarModel::class);
    }

    /**
     * The make, read from the directory entry this product is fitted to.
     *
     * @return Attribute<string, never>
     */
    protected function brand(): Attribute
    {
        return Attribute::get(function (): string {
            $carModel = $this->carModel;

            return $carModel === null ? '' : $carModel->carBrand->name;
        });
    }

    /**
     * The model generation, read from the directory entry.
     *
     * @return Attribute<string, never>
     */
    protected function model(): Attribute
    {
        return Attribute::get(function (): string {
            $carModel = $this->carModel;

            return $carModel === null ? '' : $carModel->name;
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function isInStock(): bool
    {
        return $this->qty > 0;
    }

    public function photoUrl(): ?string
    {
        return $this->urlFor($this->photo_path);
    }

    public function photoOldUrl(): ?string
    {
        return $this->urlFor($this->photo_old_path);
    }

    public function title(): string
    {
        return trim($this->brand.' '.$this->model).' — '.$this->name;
    }

    /**
     * Seeded photos ship with the app in public/, uploaded ones live on the public disk.
     */
    private function urlFor(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://', '/'])) {
            return $path;
        }

        if (Str::startsWith($path, 'images/')) {
            return asset($path);
        }

        return Storage::disk('public')->url($path);
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
