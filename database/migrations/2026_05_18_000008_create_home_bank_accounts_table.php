<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('home_bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('bank_name');
            $table->string('account_type');
            $table->text('account_number_encrypted')->nullable();
            $table->decimal('balance', 14, 2)->default(0);
            $table->string('currency_code', 3)->default('VED');
            $table->timestamps();

            $table->index('company_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('home_bank_accounts');
    }
};
