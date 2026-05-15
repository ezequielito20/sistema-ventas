<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('home_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('home_bank_account_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type');
            $table->string('category');
            $table->decimal('amount', 12, 2);
            $table->text('description')->nullable();
            $table->date('transaction_date');
            $table->string('receipt_image_path')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'transaction_date']);
            $table->index(['company_id', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('home_transactions');
    }
};
