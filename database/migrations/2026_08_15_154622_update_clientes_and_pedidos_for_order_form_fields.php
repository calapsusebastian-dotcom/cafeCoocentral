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
        Schema::table('clientes', function (Blueprint $table) {
            $table->string('email')->nullable()->after('telefono');
            $table->string('tipo_persona')->default('natural')->after('nombre');
        });

        Schema::table('clientes', function (Blueprint $table) {
            $table->renameColumn('tipo', 'tipo_precio');
        });

        Schema::table('pedidos', function (Blueprint $table) {
            $table->date('fecha_pedido')->nullable()->after('numero');
            $table->string('direccion_entrega')->nullable()->after('cliente_id');
            $table->string('medio_pago')->default('pendiente')->after('pago_contra_entrega');
        });

        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropColumn('pago_contra_entrega');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->boolean('pago_contra_entrega')->default(true)->after('medio_pago');
        });

        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropColumn(['fecha_pedido', 'direccion_entrega', 'medio_pago']);
        });

        Schema::table('clientes', function (Blueprint $table) {
            $table->renameColumn('tipo_precio', 'tipo');
        });

        Schema::table('clientes', function (Blueprint $table) {
            $table->dropColumn(['email', 'tipo_persona']);
        });
    }
};
