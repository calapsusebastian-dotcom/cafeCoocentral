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
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->string('numero')->nullable()->unique();
            $table->foreignId('cliente_id')->constrained('clientes')->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->decimal('subtotal', 10, 2);
            $table->foreignId('descuento_id')->nullable()->constrained('descuentos')->nullOnDelete();
            $table->decimal('descuento_monto', 10, 2)->default(0);
            $table->foreignId('transportadora_id')->nullable()->constrained('transportadoras')->nullOnDelete();
            $table->decimal('envio_costo', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            $table->unsignedInteger('puntos_generados')->default(0);
            $table->boolean('pago_contra_entrega')->default(true);
            $table->text('notas')->nullable();
            $table->string('status')->default('pendiente');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
};
