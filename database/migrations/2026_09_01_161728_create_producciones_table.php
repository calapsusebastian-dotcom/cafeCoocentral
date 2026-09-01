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
        Schema::create('producciones', function (Blueprint $table) {
            $table->id();
            $table->string('numero')->nullable()->unique();
            $table->date('fecha_produccion');
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->string('status')->default('enviado');
            $table->string('numero_imov')->nullable();
            $table->text('notas')->nullable();
            $table->timestamp('trasladado_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('producciones');
    }
};
