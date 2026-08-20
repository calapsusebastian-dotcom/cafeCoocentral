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
        Schema::create('rutas', function (Blueprint $table) {
            $table->id();
            $table->string('numero')->nullable()->unique();
            $table->string('nombre');
            $table->date('fecha');
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->string('status')->default('pendiente');
            $table->text('notas')->nullable();
            $table->timestamp('recibida_at')->nullable();
            $table->timestamp('entregada_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rutas');
    }
};
