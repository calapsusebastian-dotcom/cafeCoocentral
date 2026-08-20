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
        Schema::table('ruta_cliente_productos', function (Blueprint $table) {
            $table->string('molienda')->default('entero')->after('presentacion');
            $table->decimal('precio_unitario', 10, 2)->default(0)->after('molienda');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ruta_cliente_productos', function (Blueprint $table) {
            $table->dropColumn(['molienda', 'precio_unitario']);
        });
    }
};
