<?php

namespace App\Livewire\Clientes;

use App\Livewire\Concerns\GuardaSoloLectura;
use App\Models\Cliente;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts::app', ['title' => 'Clientes', 'subtitle' => 'Administra la información de tus clientes', 'icon' => 'users'])]
class Index extends Component
{
    use WithPagination, GuardaSoloLectura;

    public string $search = '';

    public bool $showModal = false;

    public ?int $editingId = null;

    public string $nombre = '';

    public string $tipo_persona = 'natural';

    public string $documento = '';

    public string $codigo = '';

    public string $telefono = '';

    public string $email = '';

    public string $direccion = '';

    public string $ciudad = '';

    public string $tipo_precio = 'minorista';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function clientes()
    {
        return Cliente::query()
            ->when($this->search, function ($query) {
                $query->where('nombre', 'like', "%{$this->search}%")
                    ->orWhere('documento', 'like', "%{$this->search}%")
                    ->orWhere('codigo', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%")
                    ->orWhere('ciudad', 'like', "%{$this->search}%");
            })
            ->latest('id')
            ->paginate(20);
    }

    public function nuevo(): void
    {
        $this->reset(['editingId', 'nombre', 'documento', 'codigo', 'telefono', 'email', 'direccion', 'ciudad']);
        $this->tipo_persona = 'natural';
        $this->tipo_precio = 'minorista';
        $this->showModal = true;
    }

    public function editar(int $id): void
    {
        $cliente = Cliente::findOrFail($id);
        $this->editingId = $cliente->id;
        $this->nombre = $cliente->nombre;
        $this->tipo_persona = $cliente->tipo_persona;
        $this->documento = $cliente->documento;
        $this->codigo = $cliente->codigo ?? '';
        $this->telefono = $cliente->telefono ?? '';
        $this->email = $cliente->email ?? '';
        $this->direccion = $cliente->direccion ?? '';
        $this->ciudad = $cliente->ciudad ?? '';
        $this->tipo_precio = $cliente->tipo_precio;
        $this->showModal = true;
    }

    public function guardar(): void
    {
        if ($this->bloquearSoloLectura()) {
            return;
        }

        $data = $this->validate([
            'nombre' => 'required|string|max:255',
            'tipo_persona' => 'required|in:natural,juridica',
            'documento' => ['required', 'string', 'max:255', Rule::unique('clientes', 'documento')->ignore($this->editingId)],
            'codigo' => ['nullable', 'string', 'max:255', Rule::unique('clientes', 'codigo')->ignore($this->editingId)],
            'telefono' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'direccion' => 'nullable|string|max:255',
            'ciudad' => 'nullable|string|max:255',
            'tipo_precio' => 'required|in:minorista,mayorista,distribuidor',
        ]);

        Cliente::updateOrCreate(['id' => $this->editingId], $data);

        $this->showModal = false;
        session()->flash('success', 'Cliente guardado correctamente.');
    }

    public function eliminar(int $id): void
    {
        if ($this->bloquearSoloLectura()) {
            return;
        }

        Cliente::whereKey($id)->delete();
    }

    public function render()
    {
        return view('livewire.clientes.index');
    }
}
