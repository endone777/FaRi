<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('brand')->index();
            $table->string('model');
            $table->unsignedSmallInteger('year_from');
            $table->unsignedSmallInteger('year_to');
            $table->string('name');
            $table->string('tech')->index();
            $table->unsignedSmallInteger('color_temp')->default(5000);
            $table->string('oem')->index();
            $table->string('shape')->default('sharp');
            $table->unsignedInteger('price');
            $table->unsignedInteger('qty')->default(0);
            $table->string('stock_note')->nullable();
            $table->decimal('weight', 5, 2)->default(0);
            $table->unsignedSmallInteger('warranty_months')->default(12);
            $table->string('photo_path')->nullable();
            $table->string('photo_old_path')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
