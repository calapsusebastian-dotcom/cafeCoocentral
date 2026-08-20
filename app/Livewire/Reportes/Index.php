<?php

namespace App\Livewire\Reportes;

use App\Models\Pedido;
use App\Models\PedidoItem;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts::app', ['title' => 'Reportes', 'subtitle' => 'Resumen del desempeño de ventas', 'icon' => 'chart-bar'])]
class Index extends Component
{
    public string $periodo = 'todo';

    protected function pedidosQuery(): Builder
    {
        return Pedido::query()->when($this->periodo !== 'todo', function ($query) {
            $desde = match ($this->periodo) {
                'hoy' => now()->startOfDay(),
                'semana' => now()->subWeek(),
                'mes' => now()->subMonth(),
                default => null,
            };

            $query->when($desde, fn ($q) => $q->where('created_at', '>=', $desde));
        });
    }

    #[Computed]
    public function totalPedidos(): int
    {
        return $this->pedidosQuery()->count();
    }

    #[Computed]
    public function ventasTotales(): float
    {
        return (float) $this->pedidosQuery()->sum('total');
    }

    #[Computed]
    public function ticketPromedio(): float
    {
        return (float) $this->pedidosQuery()->avg('total');
    }

    #[Computed]
    public function clientesActivos(): int
    {
        return $this->pedidosQuery()->distinct('cliente_id')->count('cliente_id');
    }

    #[Computed]
    public function porEstado()
    {
        return $this->pedidosQuery()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');
    }

    #[Computed]
    public function productoMasVendido()
    {
        return PedidoItem::query()
            ->whereIn('pedido_id', $this->pedidosQuery()->pluck('id'))
            ->selectRaw('producto_nombre, sum(cantidad) as unidades')
            ->groupBy('producto_nombre')
            ->orderByDesc('unidades')
            ->limit(5)
            ->get();
    }

    public function render()
    {
        return view('livewire.reportes.index');
    }
}
