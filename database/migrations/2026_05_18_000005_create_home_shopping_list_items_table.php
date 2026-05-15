<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('home_shopping_list_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('home_shopping_list_id')->constrained()->cascadeOnDelete();
            $table->foreignId('home_product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name_snapshot');
            $table->integer('suggested_quantity');
            $table->integer('actual_purchased_quantity')->nullable();
            $table->boolean('is_purchased')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('home_shopping_list_items');
    }
};
