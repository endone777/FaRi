<?php

namespace App\Models;

use Database\Factories\CarModelFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * One generation of a car: the unit headlights are matched against.
 *
 * @property int $id
 * @property int $car_brand_id
 * @property string $name
 * @property string $slug
 * @property int $year_from
 * @property int|null $year_to
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read CarBrand|null $carBrand
 */
#[Fillable(['car_brand_id', 'name', 'slug', 'year_from', 'year_to'])]
class CarModel extends Model
{
    /** @use HasFactory<CarModelFactory> */
    use HasFactory;

    /**
     * The earliest model year the directory covers.
     */
    public const EARLIEST_YEAR = 2010;

    /**
     * @return BelongsTo<CarBrand, $this>
     */
    public function carBrand(): BelongsTo
    {
        return $this->belongsTo(CarBrand::class);
    }

    /**
     * @return HasMany<Product, $this>
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * The last model year, treating an open-ended generation as still in production.
     */
    public function lastYear(): int
    {
        return $this->year_to ?? (int) now()->year;
    }

    public function yearsLabel(): string
    {
        return $this->year_from.'–'.($this->year_to ?? 'н. в.');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'year_from' => 'integer',
            'year_to' => 'integer',
        ];
    }
}
