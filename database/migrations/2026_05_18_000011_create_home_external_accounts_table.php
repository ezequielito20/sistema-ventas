<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('home_external_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('home_bank_connection_id')->constrained()->cascadeOnDelete();
            $table->string('external_account_id');
            $table->string('institution_name');
            $table->string('masked_number')->nullable();
            $table->string('currency_code', 3)->default('VED');
            $table->decimal('balance_cached', 14, 2)->default(0);
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            $table->unique(['home_bank_connection_id', 'external_account_id'], 'uniq_home_ext_account');
            $table->index('company_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('home_external_accounts');
    }
};
