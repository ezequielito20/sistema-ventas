<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('subscription_status')->default('active')->after('ig')->comment('active, suspended, trial');
            $table->integer('billing_day')->default(1)->after('subscription_status')->comment('Día de cobro mensual (1-28)');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['subscription_status', 'billing_day']);
        });
    }
};
