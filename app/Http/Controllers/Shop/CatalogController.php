<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Resources\DeliveryMethodResource;
use App\Http\Resources\ProductResource;
use App\Models\CarModel;
use App\Models\DeliveryMethod;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CatalogController extends Controller
{
    /**
     * List the catalogue with brand, model, year, light source and text filters.
     */
    public function index(Request $request): Response
    {
        $filters = [
            'brand' => $request->string('brand')->toString() ?: null,
            'model' => $request->string('model')->toString() ?: null,
            'year' => $request->integer('year') ?: null,
            'tech' => $request->string('tech')->toString() ?: null,
            'q' => $request->string('q')->toString() ?: null,
            'sort' => in_array($request->string('sort')->toString(), ['price', '-price', 'brand'], true)
                ? $request->string('sort')->toString()
                : 'brand',
        ];

        $products = Product::query()
            ->active()
            ->when($filters['brand'], fn (Builder $query, string $brand) => $query
                ->whereHas('carModel.carBrand', fn (Builder $inner) => $inner->where('name', $brand)))
            ->when($filters['model'], fn (Builder $query, string $model) => $query
                ->whereRelation('carModel', 'name', $model))
            ->when($filters['tech'], fn (Builder $query, string $tech) => $query->where('tech', $tech))
            ->when($filters['year'], fn (Builder $query, int $year) => $query
                ->where('year_from', '<=', $year)
                ->where('year_to', '>=', $year))
            ->when($filters['q'], fn (Builder $query, string $term) => $query->where(
                fn (Builder $inner) => $inner
                    ->where('name', 'like', "%{$term}%")
                    ->orWhere('oem', 'like', "%{$term}%")
                    ->orWhereRelation('carModel', 'name', 'like', "%{$term}%")
                    ->orWhereHas('carModel.carBrand', fn (Builder $brand) => $brand
                        ->where('name', 'like', "%{$term}%")),
            ))
            ->when($filters['sort'] === 'price', fn (Builder $query) => $query->orderBy('price'))
            ->when($filters['sort'] === '-price', fn (Builder $query) => $query->orderByDesc('price'))
            ->when($filters['sort'] === 'brand', fn (Builder $query) => $query->orderBy(
                CarModel::query()
                    ->select('car_brands.name')
                    ->join('car_brands', 'car_brands.id', '=', 'car_models.car_brand_id')
                    ->whereColumn('car_models.id', 'products.car_model_id'),
            ))
            ->paginate(12)
            ->withQueryString()
            ->through(fn (Product $item): array => (new ProductResource($item))->resolve());

        return Inertia::render('shop/Catalog', [
            'products' => $products,
            'filters' => $filters,
            'facets' => $this->facets(),
        ]);
    }

    /**
     * Show one product with the parts that fit the same car.
     */
    public function show(Product $product): Response
    {
        abort_unless($product->is_active, 404);

        $related = Product::query()
            ->active()
            ->whereHas('carModel', fn (Builder $query) => $query
                ->where('car_brand_id', $product->carModel?->car_brand_id))
            ->whereKeyNot($product->id)
            ->limit(3)
            ->get();

        return Inertia::render('shop/Product', [
            'product' => (new ProductResource($product))->resolve(),
            'related' => ProductResource::collection($related)->resolve(),
            'deliveryMethods' => DeliveryMethodResource::collection(
                DeliveryMethod::query()->active()->orderBy('position')->get(),
            )->resolve(),
        ]);
    }

    /**
     * Distinct filter values built from the live catalogue.
     *
     * @return array{brands: list<string>, models: array<string, list<string>>, techs: list<string>}
     */
    private function facets(): array
    {
        $brands = [];
        $models = [];
        $techs = [];

        foreach (Product::query()->active()->get() as $row) {
            if ($row->brand !== '') {
                $brands[$row->brand] = true;
                $models[$row->brand][$row->model] = true;
            }

            $techs[$row->tech] = true;
        }

        ksort($brands);
        ksort($techs);

        return [
            'brands' => array_keys($brands),
            'models' => array_map(
                function (array $group): array {
                    ksort($group);

                    return array_keys($group);
                },
                $models,
            ),
            'techs' => array_keys($techs),
        ];
    }
}
