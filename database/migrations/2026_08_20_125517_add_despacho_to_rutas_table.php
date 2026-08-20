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
        Schema::table('rutas', function (Blueprint $table) {
            $table->string('conductor_nombre')->nullable()->after('recibida_at');
            $table->string('conductor_cc')->nullable()->after('conductor_nombre');
            $table->decimal('costo_ruta', 10, 2)->nullable()->after('conductor_cc');
            $table->timestamp('despachada_at')->nullable()->after('costo_ruta');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rutas', function (Blueprint $table) {
            $table->dropColumn(['conductor_nombre', 'conductor_cc', 'costo_ruta', 'despachada_at']);
        });
    }
};
