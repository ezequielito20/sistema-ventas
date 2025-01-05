<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('image')->nullable();
            $table->integer('stock');
            $table->integer('min_stock');
            $table->integer('max_stock');
            $table->decimal('purchase_price', 8, 2);
            $table->decimal('sale_price', 8, 2);
            $table->date('entry_date');
            
            // Relación con categoría
            $table->unsignedBigInteger('category_id');
            $table->foreign('category_id')
                  ->references('id')
                  ->on('categories')
                  ->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
