<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();
            $table->string('token', 40)->unique();
            $table->string('customer_name');
            $table->string('phone');
            $table->string('email');
            $table->string('vin')->nullable();
            $table->string('city')->nullable();
            $table->text('comment')->nullable();
            $table->foreignId('delivery_method_id')->nullable()->constrained()->nullOnDelete();
            $table->string('delivery_name')->nullable();
            $table->unsignedInteger('delivery_cost')->default(0);
            $table->unsignedInteger('items_total')->default(0);
            $table->unsignedInteger('total')->default(0);
            $table->string('status')->default('new')->index();
            $table->text('admin_note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
