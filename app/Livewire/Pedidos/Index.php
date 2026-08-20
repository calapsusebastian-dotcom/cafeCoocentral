<?php

namespace App\Livewire\Pedidos;

use App\Models\Notificacion;
use App\Models\Pedido;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts::app', ['title' => 'Pedidos', 'subtitle' => 'Consulta y da seguimiento a los pedidos de tus clientes', 'icon' => 'clipboard-document-list'])]
class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public string $statusFiltro = '';

    public ?int $verPedidoId = null;

    public ?int $despacharPedidoId = null;

    public string $numero_guia = '';

    public ?int $facturarPedidoId = null;

    public string $numero_factura = '';

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
        return Pedido::query()
            ->where('destino', 'local')
            ->with(['cliente', 'vendedor'])
            ->when($this->search, function ($query) {
                $query->where('numero', 'like', "%{$this->search}%")
                    ->orWhereHas('cliente', fn ($c) => $c->where('nombre', 'like', "%{$this->search}%"));
            })
            ->when($this->statusFiltro, fn ($query) => $query->where('status', $this->statusFiltro))
            ->latest('id')
            ->paginate(8);
    }

    #[Computed]
    public function pedidoDetalle()
    {
        return $this->verPedidoId
            ? Pedido::with(['items', 'cliente', 'vendedor', 'descuento', 'transportadora'])->find($this->verPedidoId)
            : null;
    }

    #[Computed]
    public function pedidoADespachar()
    {
        return $this->despacharPedidoId
            ? Pedido::with(['cliente', 'transportadora'])->find($this->despacharPedidoId)
            : null;
    }

    #[Computed]
    public function pedidoAFacturar()
    {
        return $this->facturarPedidoId
            ? Pedido::with('cliente')->find($this->facturarPedidoId)
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

    public function abrirDespacho(int $id): void
    {
        $pedido = Pedido::findOrFail($id);
        $this->despacharPedidoId = $pedido->id;
        $this->numero_guia = $pedido->numero_guia ?? '';
    }

    public function cerrarDespacho(): void
    {
        $this->despacharPedidoId = null;
        $this->numero_guia = '';
        $this->resetErrorBag('numero_guia');
    }

    public function confirmarDespacho(): void
    {
        $this->validate([
            'numero_guia' => 'required|string|max:255',
        ], [], ['numero_guia' => 'número de guía']);

        $pedido = Pedido::with('cliente')->findOrFail($this->despacharPedidoId);
        $pedido->update([
            'status' => 'enviado',
            'numero_guia' => $this->numero_guia,
            'despachado_at' => $pedido->despachado_at ?? now(),
        ]);

        Notificacion::create([
            'user_id' => Auth::id(),
            'titulo' => 'Pedido despachado',
            'mensaje' => "El pedido #{$pedido->numero} de {$pedido->cliente->nombre} fue despachado con guía {$this->numero_guia}.",
            'tipo' => 'pedido',
        ]);

        $this->cerrarDespacho();

        session()->flash('success', "Pedido #{$pedido->numero} marcado como despachado.");
    }

    public function abrirFactura(int $id): void
    {
        $pedido = Pedido::findOrFail($id);
        $this->facturarPedidoId = $pedido->id;
        $this->numero_factura = $pedido->numero_factura ?? '';
    }

    public function cerrarFactura(): void
    {
        $this->facturarPedidoId = null;
        $this->numero_factura = '';
        $this->resetErrorBag('numero_factura');
    }

    public function confirmarFactura(): void
    {
        $this->validate([
            'numero_factura' => 'required|string|max:255',
        ], [], ['numero_factura' => 'número de factura']);

        $pedido = Pedido::with('cliente')->findOrFail($this->facturarPedidoId);
        $pedido->update([
            'numero_factura' => $this->numero_factura,
            'facturado_at' => $pedido->facturado_at ?? now(),
        ]);

        Notificacion::create([
            'user_id' => Auth::id(),
            'titulo' => 'Pedido facturado',
            'mensaje' => "El pedido #{$pedido->numero} de {$pedido->cliente->nombre} fue facturado con la factura {$this->numero_factura}.",
            'tipo' => 'pedido',
        ]);

        $this->cerrarFactura();

        session()->flash('success', "Pedido #{$pedido->numero} facturado correctamente.");
    }

    public function render()
    {
        return view('livewire.pedidos.index');
    }
}
