<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('plan_id')->constrained('plans')->onDelete('restrict');
            $table->string('status')->default('active')->comment('trial, active, grace_period, suspended, cancelled');
            $table->date('started_at');
            $table->date('expires_at')->nullable();
            $table->integer('billing_day')->default(1)->comment('Día del mes de cobro (1-28)');
            $table->date('next_billing_date');
            $table->date('grace_period_end')->nullable();
            $table->date('trial_ends_at')->nullable();
            $table->decimal('amount', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->string('discount_reason')->nullable();
            $table->string('reference_code')->nullable()->comment('Código de referido');
            $table->boolean('auto_renew')->default(true);
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
