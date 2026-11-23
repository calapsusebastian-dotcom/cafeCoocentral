<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->index('created_at', 'pedidos_created_at_index');
            $table->index('status', 'pedidos_status_index');
        });

        Schema::table('rutas', function (Blueprint $table) {
            $table->index('created_at', 'rutas_created_at_index');
            $table->index('status', 'rutas_status_index');
        });
    }

    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $t) {
            $t->dropIndex('pedidos_created_at_index');
            $t->dropIndex('pedidos_status_index');
        });
        Schema::table('rutas', function (Blueprint $t) {
            $t->dropIndex('rutas_created_at_index');
            $t->dropIndex('rutas_status_index');
        });
    }
};
