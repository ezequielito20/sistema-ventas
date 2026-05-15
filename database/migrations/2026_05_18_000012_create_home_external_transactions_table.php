<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('home_external_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('home_external_account_id')->constrained()->cascadeOnDelete();
            $table->string('external_transaction_id');
            $table->decimal('amount', 14, 2);
            $table->string('currency_code', 3)->default('VED');
            $table->timestamp('posted_at');
            $table->text('description')->nullable();
            $table->string('raw_category')->nullable();
            $table->timestamps();

            $table->unique(['home_external_account_id', 'external_transaction_id'], 'uniq_ext_txn');
            $table->index(['home_external_account_id', 'posted_at']);
            $table->index('company_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('home_external_transactions');
    }
};
