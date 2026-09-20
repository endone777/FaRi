<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Move the make and the model off the product and into the car directory.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('products', 'car_model_id')) {
            Schema::table('products', function (Blueprint $table) {
                $table->foreignId('car_model_id')->nullable()->after('slug')->constrained()->nullOnDelete();
            });
        }

        $this->backfillDirectory();

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['brand']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['brand', 'model']);
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('brand')->default('')->after('slug');
            $table->string('model')->default('')->after('brand');
        });

        foreach (DB::table('products')->whereNotNull('car_model_id')->get() as $product) {
            DB::table('products')->where('id', $product->id)->update([
                'brand' => (string) DB::table('car_models')
                    ->join('car_brands', 'car_brands.id', '=', 'car_models.car_brand_id')
                    ->where('car_models.id', $product->car_model_id)
                    ->value('car_brands.name'),
                'model' => (string) DB::table('car_models')
                    ->where('id', $product->car_model_id)
                    ->value('name'),
            ]);
        }

        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['car_model_id']);
            $table->dropColumn('car_model_id');
        });
    }

    /**
     * Turn the make and model already typed on products into directory entries.
     */
    private function backfillDirectory(): void
    {
        $now = now();

        foreach (DB::table('products')->whereNull('car_model_id')->get() as $product) {
            $brandId = DB::table('car_brands')->where('name', $product->brand)->value('id')
                ?? DB::table('car_brands')->insertGetId([
                    'name' => $product->brand,
                    'slug' => Str::slug($product->brand),
                    'position' => 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

            $modelId = DB::table('car_models')
                ->where('car_brand_id', $brandId)
                ->where('name', $product->model)
                ->value('id')
                ?? DB::table('car_models')->insertGetId([
                    'car_brand_id' => $brandId,
                    'name' => $product->model,
                    'slug' => Str::slug($product->model),
                    'year_from' => $product->year_from,
                    'year_to' => $product->year_to,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

            DB::table('products')->where('id', $product->id)->update(['car_model_id' => $modelId]);
        }
    }
};
