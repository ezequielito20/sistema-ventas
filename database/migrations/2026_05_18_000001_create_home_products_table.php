<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('home_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('brand')->nullable();
            $table->string('category');
            $table->integer('quantity')->default(0);
            $table->integer('min_quantity')->default(1);
            $table->integer('max_quantity')->nullable();
            $table->string('unit')->default('unidad');
            $table->decimal('purchase_price', 12, 2)->default(0);
            $table->string('barcode', 50)->nullable();
            $table->string('image')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'barcode']);
            $table->index('company_id');
            $table->index('category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('home_products');
    }
};
