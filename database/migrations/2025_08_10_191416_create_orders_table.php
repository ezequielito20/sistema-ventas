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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name')->comment('Nombre completo + departamento');
            $table->string('customer_phone')->comment('Teléfono del cliente');
            $table->unsignedBigInteger('product_id')->comment('ID del producto');
            $table->integer('quantity')->comment('Cantidad solicitada');
            $table->decimal('unit_price', 10, 2)->comment('Precio unitario al momento del pedido');
            $table->decimal('total_price', 10, 2)->comment('Precio total del pedido');
            $table->text('notes')->nullable()->comment('Notas del cliente (especificaciones)');
            $table->enum('status', ['pending', 'processed', 'cancelled'])->default('pending')->comment('Estado del pedido');
            $table->unsignedBigInteger('customer_id')->nullable()->comment('ID del cliente si ya existe');
            $table->unsignedBigInteger('sale_id')->nullable()->comment('ID de la venta generada si se procesó');
            $table->timestamp('processed_at')->nullable()->comment('Fecha de procesamiento');
            $table->unsignedBigInteger('processed_by')->nullable()->comment('Usuario que procesó el pedido');
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('set null');
            $table->foreign('sale_id')->references('id')->on('sales')->onDelete('set null');
            $table->foreign('processed_by')->references('id')->on('users')->onDelete('set null');
            
            $table->index(['status', 'created_at']);
            $table->index('customer_phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
