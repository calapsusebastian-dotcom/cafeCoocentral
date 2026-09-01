<?php

namespace App\Livewire\PedidosBodega;

use App\Livewire\Concerns\GuardaSoloLectura;
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
    use WithPagination, GuardaSoloLectura;

    public string $search = '';

    public string $statusFiltro = '';

    public ?int $verPedidoId = null;

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
        if ($this->bloquearSoloLectura()) {
            return;
        }

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
        if (! Auth::user()->is_admin || $this->bloquearSoloLectura()) {
            return;
        }

        $pedido = PedidoBodega::findOrFail($id);

        if ($pedido->status === 'recibido') {
            return;
        }

        $pedido->update(['status' => 'cancelado']);

        session()->flash('success', "Pedido #{$pedido->numero} cancelado.");
    }

    public function eliminarPedido(int $id): void
    {
        if (! Auth::user()->is_admin || $this->bloquearSoloLectura()) {
            return;
        }

        $pedido = PedidoBodega::findOrFail($id);
        $pedido->delete();

        session()->flash('success', "Pedido #{$pedido->numero} eliminado.");
    }

    public function render()
    {
        return view('livewire.pedidos-bodega.index');
    }
}
