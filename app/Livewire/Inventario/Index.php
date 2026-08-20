<?php

namespace App\Livewire\Inventario;

use App\Models\MovimientoInventario;
use App\Models\Producto;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts::app', ['title' => 'Inventario', 'subtitle' => 'Consulta el stock y los movimientos de tus productos', 'icon' => 'archive-box'])]
class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public ?int $productoId = null;

    public string $tipo = 'entrada';

    public string $cantidad = '';

    public string $motivo = '';

    #[Computed]
    public function stock()
    {
        return Producto::query()
            ->when($this->search, fn ($query) => $query->where('nombre', 'like', "%{$this->search}%"))
            ->orderBy('nombre')
            ->get();
    }

    #[Computed]
    public function movimientos()
    {
        return MovimientoInventario::with('producto')->latest('id')->paginate(20);
    }

    #[Computed]
    public function productos()
    {
        return Producto::orderBy('nombre')->get();
    }

    public function registrarMovimiento(): void
    {
        $data = $this->validate([
            'productoId' => 'required|exists:productos,id',
            'tipo' => 'required|in:entrada,salida',
            'cantidad' => 'required|integer|min:1',
            'motivo' => 'required|string|max:255',
        ]);

        $producto = Producto::findOrFail($data['productoId']);

        if ($data['tipo'] === 'salida' && $data['cantidad'] > $producto->stock) {
            $this->addError('cantidad', 'No hay suficiente stock disponible.');

            return;
        }

        $producto->increment('stock', $data['tipo'] === 'entrada' ? $data['cantidad'] : -$data['cantidad']);

        MovimientoInventario::create([
            'producto_id' => $producto->id,
            'tipo' => $data['tipo'],
            'cantidad' => $data['cantidad'],
            'motivo' => $data['motivo'],
            'user_id' => Auth::id(),
        ]);

        $this->reset(['productoId', 'cantidad', 'motivo']);
        $this->tipo = 'entrada';
        session()->flash('success', 'Movimiento registrado correctamente.');
    }

    public function render()
    {
        return view('livewire.inventario.index');
    }
}
