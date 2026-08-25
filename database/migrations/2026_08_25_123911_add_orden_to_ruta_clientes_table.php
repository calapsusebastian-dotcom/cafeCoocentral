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
            $table->unsignedInteger('orden')->default(0)->after('cliente_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ruta_clientes', function (Blueprint $table) {
            $table->dropColumn('orden');
        });
    }
};
