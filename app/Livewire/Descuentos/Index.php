<?php

namespace App\Livewire\Descuentos;

use App\Models\Descuento;
use App\Models\Transportadora;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts::app', ['title' => 'Descuentos', 'subtitle' => 'Administra reglas de descuento y transportadoras', 'icon' => 'tag'])]
class Index extends Component
{
    public bool $showDescuentoModal = false;

    public ?int $editingDescuentoId = null;

    public string $descuento_nombre = '';

    public string $descuento_tipo = 'fijo';

    public string $descuento_valor = '';

    public bool $showTransportadoraModal = false;

    public ?int $editingTransportadoraId = null;

    public string $transportadora_nombre = '';

    public string $transportadora_costo = '';

    #[Computed]
    public function descuentos()
    {
        return Descuento::orderBy('nombre')->get();
    }

    #[Computed]
    public function transportadoras()
    {
        return Transportadora::orderBy('nombre')->get();
    }

    public function nuevoDescuento(): void
    {
        $this->reset(['editingDescuentoId', 'descuento_nombre', 'descuento_valor']);
        $this->descuento_tipo = 'fijo';
        $this->showDescuentoModal = true;
    }

    public function editarDescuento(int $id): void
    {
        $descuento = Descuento::findOrFail($id);
        $this->editingDescuentoId = $descuento->id;
        $this->descuento_nombre = $descuento->nombre;
        $this->descuento_tipo = $descuento->tipo;
        $this->descuento_valor = (string) $descuento->valor;
        $this->showDescuentoModal = true;
    }

    public function guardarDescuento(): void
    {
        $data = $this->validate([
            'descuento_nombre' => 'required|string|max:255',
            'descuento_tipo' => 'required|in:fijo,porcentaje',
            'descuento_valor' => 'required|numeric|min:0',
        ]);

        Descuento::updateOrCreate(['id' => $this->editingDescuentoId], [
            'nombre' => $data['descuento_nombre'],
            'tipo' => $data['descuento_tipo'],
            'valor' => $data['descuento_valor'],
        ]);

        $this->showDescuentoModal = false;
        session()->flash('success', 'Descuento guardado correctamente.');
    }

    public function toggleDescuento(int $id): void
    {
        $descuento = Descuento::findOrFail($id);
        $descuento->update(['activo' => ! $descuento->activo]);
    }

    public function eliminarDescuento(int $id): void
    {
        Descuento::whereKey($id)->delete();
    }

    public function nuevaTransportadora(): void
    {
        $this->reset(['editingTransportadoraId', 'transportadora_nombre', 'transportadora_costo']);
        $this->showTransportadoraModal = true;
    }

    public function editarTransportadora(int $id): void
    {
        $transportadora = Transportadora::findOrFail($id);
        $this->editingTransportadoraId = $transportadora->id;
        $this->transportadora_nombre = $transportadora->nombre;
        $this->transportadora_costo = (string) $transportadora->costo;
        $this->showTransportadoraModal = true;
    }

    public function guardarTransportadora(): void
    {
        $data = $this->validate([
            'transportadora_nombre' => 'required|string|max:255',
            'transportadora_costo' => 'required|numeric|min:0',
        ]);

        Transportadora::updateOrCreate(['id' => $this->editingTransportadoraId], [
            'nombre' => $data['transportadora_nombre'],
            'costo' => $data['transportadora_costo'],
        ]);

        $this->showTransportadoraModal = false;
        session()->flash('success', 'Transportadora guardada correctamente.');
    }

    public function toggleTransportadora(int $id): void
    {
        $transportadora = Transportadora::findOrFail($id);
        $transportadora->update(['activo' => ! $transportadora->activo]);
    }

    public function eliminarTransportadora(int $id): void
    {
        Transportadora::whereKey($id)->delete();
    }

    public function render()
    {
        return view('livewire.descuentos.index');
    }
}
