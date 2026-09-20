<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRequest;
use App\Http\Resources\ProductResource;
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
                    ->where('brand', 'like', "%{$term}%")
                    ->orWhere('model', 'like', "%{$term}%")
                    ->orWhere('name', 'like', "%{$term}%")
                    ->orWhere('oem', 'like', "%{$term}%"),
            ))
            ->when($filters['brand'], fn (Builder $query, string $brand) => $query->where('brand', $brand))
            ->when($filters['state'] === 'hidden', fn (Builder $query) => $query->where('is_active', false))
            ->when($filters['state'] === 'out', fn (Builder $query) => $query->where('qty', 0))
            ->orderBy('brand')
            ->orderBy('model')
            ->paginate(15)
            ->withQueryString()
            ->through(fn (Product $item): array => (new ProductResource($item))->resolve());

        return Inertia::render('admin/products/Index', [
            'products' => $products,
            'filters' => $filters,
            'brands' => Product::query()->distinct()->orderBy('brand')->pluck('brand'),
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

        if ($request->file('photo') instanceof UploadedFile) {
            $this->deleteUpload($product->photo_path);
            $product->photo_path = $request->file('photo')->store('products', 'public');
        }

        if ($request->file('photo_old') instanceof UploadedFile) {
            $this->deleteUpload($product->photo_old_path);
            $product->photo_old_path = $request->file('photo_old')->store('products', 'public');
        }
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
     * @return array{techs: list<string>, shapes: array<string, string>}
     */
    private function options(): array
    {
        return [
            'techs' => Product::TECHS,
            'shapes' => Product::SHAPES,
        ];
    }
}
