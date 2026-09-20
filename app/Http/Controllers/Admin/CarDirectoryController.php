<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CarBrandRequest;
use App\Http\Requests\Admin\CarModelRequest;
use App\Models\CarBrand;
use App\Models\CarModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The car directory: the makes and generations headlights are fitted to.
 */
class CarDirectoryController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->string('q')->toString() ?: null;

        $brands = CarBrand::query()
            ->withCount('models')
            ->with(['models' => fn ($query) => $query
                ->withCount('products')
                ->when($search, fn (Builder $inner, string $term) => $inner->where('name', 'like', "%{$term}%"))
                ->orderBy('name')])
            ->orderBy('position')
            ->get()
            ->map(fn (CarBrand $brand): array => [
                'id' => $brand->id,
                'name' => $brand->name,
                'position' => $brand->position,
                'models_count' => $brand->models_count,
                'models' => $brand->models->map(fn (CarModel $model): array => [
                    'id' => $model->id,
                    'car_brand_id' => $model->car_brand_id,
                    'name' => $model->name,
                    'year_from' => $model->year_from,
                    'year_to' => $model->year_to,
                    'years' => $model->yearsLabel(),
                    'products_count' => $model->products_count,
                ])->all(),
            ])
            ->all();

        return Inertia::render('admin/Cars', [
            'brands' => $brands,
            'filters' => ['q' => $search],
            'earliestYear' => CarModel::EARLIEST_YEAR,
            'currentYear' => (int) now()->year,
        ]);
    }

    public function storeBrand(CarBrandRequest $request): RedirectResponse
    {
        CarBrand::create([
            'name' => $request->string('name')->toString(),
            'slug' => Str::slug($request->string('name')->toString()),
            'position' => $request->integer('position'),
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Марка добавлена']);

        return back();
    }

    public function updateBrand(CarBrandRequest $request, CarBrand $carBrand): RedirectResponse
    {
        $carBrand->update([
            'name' => $request->string('name')->toString(),
            'slug' => Str::slug($request->string('name')->toString()),
            'position' => $request->integer('position'),
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Марка обновлена']);

        return back();
    }

    public function destroyBrand(CarBrand $carBrand): RedirectResponse
    {
        $inUse = CarModel::query()
            ->where('car_brand_id', $carBrand->id)
            ->whereHas('products')
            ->exists();

        if ($inUse) {
            return back()->withErrors([
                'brand' => 'Нельзя удалить марку: на её модели заведены позиции каталога.',
            ]);
        }

        $carBrand->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Марка удалена']);

        return back();
    }

    public function storeModel(CarModelRequest $request): RedirectResponse
    {
        CarModel::create([
            ...$request->validated(),
            'slug' => Str::slug($request->string('name')->toString()),
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Модель добавлена']);

        return back();
    }

    public function updateModel(CarModelRequest $request, CarModel $carModel): RedirectResponse
    {
        $carModel->update([
            ...$request->validated(),
            'slug' => Str::slug($request->string('name')->toString()),
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Модель обновлена']);

        return back();
    }

    public function destroyModel(CarModel $carModel): RedirectResponse
    {
        if ($carModel->products()->exists()) {
            return back()->withErrors([
                'model' => 'Нельзя удалить модель: на неё заведены позиции каталога.',
            ]);
        }

        $carModel->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Модель удалена']);

        return back();
    }
}
