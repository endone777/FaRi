<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Resources\DeliveryMethodResource;
use App\Http\Resources\ProductResource;
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
            ->when($filters['brand'], fn (Builder $query, string $brand) => $query->where('brand', $brand))
            ->when($filters['model'], fn (Builder $query, string $model) => $query->where('model', $model))
            ->when($filters['tech'], fn (Builder $query, string $tech) => $query->where('tech', $tech))
            ->when($filters['year'], fn (Builder $query, int $year) => $query
                ->where('year_from', '<=', $year)
                ->where('year_to', '>=', $year))
            ->when($filters['q'], fn (Builder $query, string $term) => $query->where(
                fn (Builder $inner) => $inner
                    ->where('brand', 'like', "%{$term}%")
                    ->orWhere('model', 'like', "%{$term}%")
                    ->orWhere('name', 'like', "%{$term}%")
                    ->orWhere('oem', 'like', "%{$term}%"),
            ))
            ->when($filters['sort'] === 'price', fn (Builder $query) => $query->orderBy('price'))
            ->when($filters['sort'] === '-price', fn (Builder $query) => $query->orderByDesc('price'))
            ->when($filters['sort'] === 'brand', fn (Builder $query) => $query->orderBy('brand')->orderBy('model'))
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
            ->where('brand', $product->brand)
            ->whereKeyNot($product->id)
            ->orderBy('model')
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
        $rows = Product::query()->active()->get(['brand', 'model', 'tech']);

        return [
            'brands' => $rows->pluck('brand')->unique()->sort()->values()->all(),
            'models' => $rows->groupBy('brand')
                ->map(fn ($group) => $group->pluck('model')->unique()->sort()->values()->all())
                ->all(),
            'techs' => $rows->pluck('tech')->unique()->values()->all(),
        ];
    }
}
