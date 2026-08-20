<?php

namespace App\Livewire\PedidosWeb;

use App\Livewire\Pedidos;
use App\Models\Pedido;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;

/**
 * Same listing/detail/despacho behavior as Pedidos\Index, scoped to pedidos
 * whose destino is "web" so they can be reviewed as their own section.
 */
#[Layout('layouts::app', ['title' => 'Pedidos Web', 'subtitle' => 'Pedidos recibidos por el canal web', 'icon' => 'globe-alt'])]
class Index extends Pedidos\Index
{
    #[Computed]
    public function pedidos()
    {
        return Pedido::query()
            ->where('destino', 'web')
            ->with(['cliente', 'vendedor'])
            ->when($this->search, function ($query) {
                $query->where('numero', 'like', "%{$this->search}%")
                    ->orWhereHas('cliente', fn ($c) => $c->where('nombre', 'like', "%{$this->search}%"));
            })
            ->when($this->statusFiltro, fn ($query) => $query->where('status', $this->statusFiltro))
            ->when(! Auth::user()->is_admin, fn ($query) => $query->where('user_id', Auth::id()))
            ->latest('id')
            ->paginate(20);
    }

    public function render()
    {
        return view('livewire.pedidos.index');
    }
}
