<?php

namespace App\Livewire\Facturacion;

use App\Livewire\Pedidos;
use App\Models\Pedido;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;

/**
 * Restricted view for users who should only be able to attach an invoice
 * number to an order — reuses the factura/detalle logic from Pedidos\Index
 * but splits the listing into Pedidos (local) and Pedidos Web tabs and
 * exposes no edit/despacho actions in its view.
 */
#[Layout('layouts::app', ['title' => 'Facturación', 'subtitle' => 'Registra el número de factura de los pedidos', 'icon' => 'document-text'])]
class Index extends Pedidos\Index
{
    public string $destino = 'local';

    public function updatingDestino(): void
    {
        $this->resetPage();
    }

    public function cambiarDestino(string $destino): void
    {
        $this->destino = $destino;
        $this->resetPage();
    }

    #[Computed]
    public function pedidos()
    {
        return Pedido::query()
            ->where('destino', $this->destino)
            ->with(['cliente', 'vendedor'])
            ->when($this->search, function ($query) {
                $query->where('numero', 'like', "%{$this->search}%")
                    ->orWhereHas('cliente', fn ($c) => $c->where('nombre', 'like', "%{$this->search}%"));
            })
            ->when($this->statusFiltro, fn ($query) => $query->where('status', $this->statusFiltro))
            ->latest('id')
            ->paginate(20);
    }

    public function render()
    {
        return view('livewire.facturacion.index');
    }
}
