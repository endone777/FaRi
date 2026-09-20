<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\CarBrand;
use App\Models\CarModel;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = [
            'q' => $request->string('q')->toString() ?: null,
            'brand' => $request->string('brand')->toString() ?: null,
            'state' => in_array($request->string('state')->toString(), ['hidden', 'out'], true)
                ? $request->string('state')->toString()
                : null,
        ];

        $products = Product::query()
            ->when($filters['q'], fn (Builder $query, string $term) => $query->where(
                fn (Builder $inner) => $inner
                    ->where('name', 'like', "%{$term}%")
                    ->orWhere('oem', 'like', "%{$term}%")
                    ->orWhereRelation('carModel', 'name', 'like', "%{$term}%")
                    ->orWhereHas('carModel.carBrand', fn (Builder $brand) => $brand
                        ->where('name', 'like', "%{$term}%")),
            ))
            ->when($filters['brand'], fn (Builder $query, string $brand) => $query
                ->whereHas('carModel.carBrand', fn (Builder $inner) => $inner->where('name', $brand)))
            ->when($filters['state'] === 'hidden', fn (Builder $query) => $query->where('is_active', false))
            ->when($filters['state'] === 'out', fn (Builder $query) => $query->where('qty', 0))
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString()
            ->through(fn (Product $item): array => (new ProductResource($item))->resolve());

        return Inertia::render('admin/products/Index', [
            'products' => $products,
            'filters' => $filters,
            'brands' => CarBrand::query()->orderBy('position')->pluck('name'),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/products/Form', [
            'product' => null,
            'options' => $this->options(),
        ]);
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        $product = new Product;
        $this->fill($product, $request);
        $product->slug = $this->uniqueSlug($product);
        $product->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Позиция добавлена']);

        return to_route('admin.products.index');
    }

    public function edit(Product $product): Response
    {
        return Inertia::render('admin/products/Form', [
            'product' => (new ProductResource($product))->resolve(),
            'options' => $this->options(),
        ]);
    }

    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        $this->fill($product, $request);
        $product->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Изменения сохранены']);

        return to_route('admin.products.index');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->deleteUpload($product->photo_path);
        $this->deleteUpload($product->photo_old_path);
        $product->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Позиция удалена']);

        return back();
    }

    /**
     * Copy validated input and any uploaded photos onto the model.
     */
    private function fill(Product $product, ProductRequest $request): void
    {
        $product->fill($request->safe()->except(['photo', 'photo_old', 'remove_photo', 'remove_photo_old']));
        $product->is_active = $request->boolean('is_active');
        $product->weight = (float) $request->input('weight', 0);
        $product->warranty_months = $request->integer('warranty_months') ?: 0;

        if ($request->boolean('remove_photo')) {
            $this->deleteUpload($product->photo_path);
            $product->photo_path = null;
        }

        if ($request->boolean('remove_photo_old')) {
            $this->deleteUpload($product->photo_old_path);
            $product->photo_old_path = null;
        }

        $photo = $request->file('photo');

        if ($photo instanceof UploadedFile) {
            $this->deleteUpload($product->photo_path);
            $product->photo_path = $this->storeUpload($photo);
        }

        $photoOld = $request->file('photo_old');

        if ($photoOld instanceof UploadedFile) {
            $this->deleteUpload($product->photo_old_path);
            $product->photo_old_path = $this->storeUpload($photoOld);
        }
    }

    /**
     * Put an uploaded photo on the public disk, or keep the old one on failure.
     */
    private function storeUpload(UploadedFile $file): ?string
    {
        $path = $file->store('products', 'public');

        return $path === false ? null : $path;
    }

    /**
     * Remove an uploaded file, leaving photos that ship with the app alone.
     */
    private function deleteUpload(?string $path): void
    {
        if (filled($path) && ! Str::startsWith($path, ['images/', 'http://', 'https://', '/'])) {
            Storage::disk('public')->delete($path);
        }
    }

    private function uniqueSlug(Product $product): string
    {
        $base = Str::slug($product->brand.' '.$product->model.' '.$product->name) ?: 'headlight';
        $slug = $base;
        $n = 2;

        while (Product::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$n++;
        }

        return $slug;
    }

    /**
     * Form options, including the car directory the product is fitted to.
     *
     * @return array{techs: list<string>, shapes: array<string, string>, cars: array<int, array<string, mixed>>}
     */
    private function options(): array
    {
        $cars = CarBrand::query()
            ->with(['models' => fn ($query) => $query->orderBy('name')])
            ->orderBy('position')
            ->get()
            ->map(fn (CarBrand $brand): array => [
                'id' => $brand->id,
                'name' => $brand->name,
                'models' => $brand->models->map(fn (CarModel $model): array => [
                    'id' => $model->id,
                    'name' => $model->name,
                    'years' => $model->yearsLabel(),
                    'year_from' => $model->year_from,
                    'year_to' => $model->lastYear(),
                ])->all(),
            ])
            ->all();

        return [
            'techs' => Product::TECHS,
            'shapes' => Product::SHAPES,
            'cars' => $cars,
        ];
    }
}
