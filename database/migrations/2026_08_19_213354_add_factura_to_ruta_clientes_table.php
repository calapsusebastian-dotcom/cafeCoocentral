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
        Schema::table('ruta_clientes', function (Blueprint $table) {
            $table->string('numero_factura')->nullable()->after('cliente_id');
            $table->timestamp('facturado_at')->nullable()->after('numero_factura');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ruta_clientes', function (Blueprint $table) {
            $table->dropColumn(['numero_factura', 'facturado_at']);
        });
    }
};
