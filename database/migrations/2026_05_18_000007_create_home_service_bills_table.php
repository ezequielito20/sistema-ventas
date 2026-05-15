<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('home_service_bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('home_service_id')->constrained()->cascadeOnDelete();
            $table->string('period', 7);
            $table->decimal('amount', 12, 2);
            $table->date('due_date');
            $table->date('cutoff_date')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->string('bill_image_path')->nullable();
            $table->string('ocr_status')->nullable();
            $table->json('ocr_payload')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['home_service_id', 'due_date']);
            $table->index('due_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('home_service_bills');
    }
};
