<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('home_shopping_lists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->timestamp('generated_at')->useCurrent();
            $table->boolean('is_completed')->default(false);
            $table->timestamps();

            $table->index('company_id');
            $table->index(['company_id', 'is_completed']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('home_shopping_lists');
    }
};
