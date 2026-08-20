<?php

namespace App\Livewire\Productos;

use App\Models\Producto;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts::app', ['title' => 'Productos', 'subtitle' => 'Gestiona tu catálogo de café', 'icon' => 'cube'])]
class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public bool $showModal = false;

    public ?int $editingId = null;

    public string $nombre = '';

    public string $categoria = '';

    public string $presentacion = '';

    public string $sku = '';

    public string $precio = '';

    public string $stock = '';

    public bool $activo = true;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function productos()
    {
        return Producto::query()
            ->when($this->search, function ($query) {
                $query->where('nombre', 'like', "%{$this->search}%")
                    ->orWhere('sku', 'like', "%{$this->search}%")
                    ->orWhere('categoria', 'like', "%{$this->search}%");
            })
            ->orderBy('nombre')
            ->paginate(8);
    }

    public function nuevo(): void
    {
        $this->reset(['editingId', 'nombre', 'categoria', 'presentacion', 'sku', 'precio', 'stock']);
        $this->activo = true;
        $this->showModal = true;
    }

    public function editar(int $id): void
    {
        $producto = Producto::findOrFail($id);
        $this->editingId = $producto->id;
        $this->nombre = $producto->nombre;
        $this->categoria = $producto->categoria;
        $this->presentacion = $producto->presentacion;
        $this->sku = $producto->sku;
        $this->precio = (string) $producto->precio;
        $this->stock = (string) $producto->stock;
        $this->activo = $producto->activo;
        $this->showModal = true;
    }

    public function guardar(): void
    {
        $data = $this->validate([
            'nombre' => 'required|string|max:255',
            'categoria' => 'required|string|max:255',
            'presentacion' => 'required|string|max:255',
            'sku' => ['required', 'string', 'max:255', Rule::unique('productos', 'sku')->ignore($this->editingId)],
            'precio' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'activo' => 'boolean',
        ]);

        Producto::updateOrCreate(['id' => $this->editingId], $data);

        $this->showModal = false;
        session()->flash('success', 'Producto guardado correctamente.');
    }

    public function eliminar(int $id): void
    {
        Producto::whereKey($id)->delete();
    }

    public function render()
    {
        return view('livewire.productos.index');
    }
}
