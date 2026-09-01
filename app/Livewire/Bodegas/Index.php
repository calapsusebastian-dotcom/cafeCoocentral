<?php

namespace App\Livewire\Bodegas;

use App\Livewire\Concerns\GuardaSoloLectura;
use App\Models\Bodega;
use Illuminate\Database\QueryException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts::app', ['title' => 'Bodegas', 'subtitle' => 'Administra las bodegas que abastecen tu inventario', 'icon' => 'building-office-2'])]
class Index extends Component
{
    use GuardaSoloLectura;

    public bool $showBodegaModal = false;

    public ?int $editingBodegaId = null;

    public string $bodega_nombre = '';

    public string $bodega_direccion = '';

    public string $bodega_telefono = '';

    public string $bodega_contacto = '';

    #[Computed]
    public function bodegas()
    {
        return Bodega::orderBy('nombre')->get();
    }

    public function nuevaBodega(): void
    {
        $this->reset(['editingBodegaId', 'bodega_nombre', 'bodega_direccion', 'bodega_telefono', 'bodega_contacto']);
        $this->showBodegaModal = true;
    }

    public function editarBodega(int $id): void
    {
        $bodega = Bodega::findOrFail($id);
        $this->editingBodegaId = $bodega->id;
        $this->bodega_nombre = $bodega->nombre;
        $this->bodega_direccion = $bodega->direccion ?? '';
        $this->bodega_telefono = $bodega->telefono ?? '';
        $this->bodega_contacto = $bodega->contacto ?? '';
        $this->showBodegaModal = true;
    }

    public function guardarBodega(): void
    {
        if ($this->bloquearSoloLectura()) {
            return;
        }

        $data = $this->validate([
            'bodega_nombre' => 'required|string|max:255',
            'bodega_direccion' => 'nullable|string|max:255',
            'bodega_telefono' => 'nullable|string|max:50',
            'bodega_contacto' => 'nullable|string|max:255',
        ]);

        Bodega::updateOrCreate(['id' => $this->editingBodegaId], [
            'nombre' => $data['bodega_nombre'],
            'direccion' => $data['bodega_direccion'],
            'telefono' => $data['bodega_telefono'],
            'contacto' => $data['bodega_contacto'],
        ]);

        $this->showBodegaModal = false;
        session()->flash('success', 'Bodega guardada correctamente.');
    }

    public function toggleBodega(int $id): void
    {
        if ($this->bloquearSoloLectura()) {
            return;
        }

        $bodega = Bodega::findOrFail($id);
        $bodega->update(['activo' => ! $bodega->activo]);
    }

    public function eliminarBodega(int $id): void
    {
        if ($this->bloquearSoloLectura()) {
            return;
        }

        try {
            Bodega::whereKey($id)->delete();
            session()->flash('success', 'Bodega eliminada correctamente.');
        } catch (QueryException) {
            session()->flash('error', 'No se puede eliminar esta bodega porque tiene pedidos a bodega asociados. Desactívala en su lugar.');
        }
    }

    public function render()
    {
        return view('livewire.bodegas.index');
    }
}
