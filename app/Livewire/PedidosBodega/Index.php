<?php

namespace App\Livewire\PedidosBodega;

use App\Models\Bodega;
use App\Models\MovimientoInventario;
use App\Models\Notificacion;
use App\Models\PedidoBodega;
use App\Models\Producto;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts::app', ['title' => 'Pedidos a Bodega', 'subtitle' => 'Solicitudes de reabastecimiento a tus bodegas', 'icon' => 'building-storefront'])]
class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public string $statusFiltro = '';

    public ?int $verPedidoId = null;

    public bool $showBodegaModal = false;

    public ?int $editingBodegaId = null;

    public string $bodega_nombre = '';

    public string $bodega_direccion = '';

    public string $bodega_telefono = '';

    public string $bodega_contacto = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFiltro(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function pedidos()
    {
        return PedidoBodega::query()
            ->with(['bodega', 'usuario', 'items'])
            ->when($this->search, function ($query) {
                $query->where('numero', 'like', "%{$this->search}%")
                    ->orWhereHas('bodega', fn ($b) => $b->where('nombre', 'like', "%{$this->search}%"));
            })
            ->when($this->statusFiltro, fn ($query) => $query->where('status', $this->statusFiltro))
            ->latest('id')
            ->paginate(20);
    }

    #[Computed]
    public function pedidoDetalle()
    {
        return $this->verPedidoId
            ? PedidoBodega::with(['items', 'bodega', 'usuario'])->find($this->verPedidoId)
            : null;
    }

    #[Computed]
    public function bodegas()
    {
        return Bodega::orderBy('nombre')->get();
    }

    public function verPedido(int $id): void
    {
        $this->verPedidoId = $id;
    }

    public function cerrarModal(): void
    {
        $this->verPedidoId = null;
    }

    public function marcarRecibido(int $id): void
    {
        $pedido = PedidoBodega::with('items')->findOrFail($id);

        if ($pedido->status === 'recibido') {
            return;
        }

        DB::transaction(function () use ($pedido) {
            $vendedor = Auth::user();

            foreach ($pedido->items as $item) {
                if (! $item->producto_id) {
                    continue;
                }

                $producto = Producto::find($item->producto_id);

                if (! $producto) {
                    continue;
                }

                $producto->increment('stock', $item->cantidad);

                MovimientoInventario::create([
                    'producto_id' => $producto->id,
                    'tipo' => 'entrada',
                    'cantidad' => $item->cantidad,
                    'motivo' => "Recepción pedido a bodega #{$pedido->numero}",
                    'user_id' => $vendedor->id,
                ]);
            }

            $pedido->update([
                'status' => 'recibido',
                'recibido_at' => now(),
            ]);

            Notificacion::create([
                'user_id' => $vendedor->id,
                'titulo' => 'Pedido a bodega recibido',
                'mensaje' => "Se recibió el pedido #{$pedido->numero} de {$pedido->bodega->nombre} y se actualizó el inventario.",
                'tipo' => 'stock',
            ]);
        });

        session()->flash('success', "Pedido #{$pedido->numero} marcado como recibido. Inventario actualizado.");
    }

    public function cancelarPedido(int $id): void
    {
        $pedido = PedidoBodega::findOrFail($id);

        if ($pedido->status === 'recibido') {
            return;
        }

        $pedido->update(['status' => 'cancelado']);

        session()->flash('success', "Pedido #{$pedido->numero} cancelado.");
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
        $bodega = Bodega::findOrFail($id);
        $bodega->update(['activo' => ! $bodega->activo]);
    }

    public function eliminarBodega(int $id): void
    {
        Bodega::whereKey($id)->delete();
    }

    public function render()
    {
        return view('livewire.pedidos-bodega.index');
    }
}
