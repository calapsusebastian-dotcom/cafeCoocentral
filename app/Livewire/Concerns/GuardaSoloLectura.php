<?php

namespace App\Livewire\Concerns;

use Illuminate\Support\Facades\Auth;

/**
 * Bloquea acciones de escritura (crear/editar/eliminar/cambiar estado) para
 * usuarios marcados como "solo lectura". Cada método que modifica datos
 * debe empezar con `if ($this->bloquearSoloLectura()) { return; }`.
 */
trait GuardaSoloLectura
{
    protected function bloquearSoloLectura(): bool
    {
        if (Auth::user()?->puedeEscribir()) {
            return false;
        }

        session()->flash('error', 'Tu usuario es de solo lectura y no puede realizar esta acción.');

        return true;
    }
}
