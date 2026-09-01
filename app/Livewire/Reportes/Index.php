<?php

namespace App\Livewire\Reportes;

use App\Models\Pedido;
use App\Models\PedidoItem;
use App\Models\Ruta;
use App\Models\RutaClienteProducto;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts::app', ['title' => 'Reportes', 'subtitle' => 'Resumen del desempeño de ventas', 'icon' => 'chart-bar'])]
class Index extends Component
{
    public string $periodo = 'todo';

    protected function desdeFecha()
    {
        return match ($this->periodo) {
            'hoy' => now()->startOfDay(),
            'semana' => now()->subWeek(),
            'mes' => now()->subMonth(),
            default => null,
        };
    }

    protected function pedidosQuery(): Builder
    {
        return Pedido::query()->when($this->periodo !== 'todo', function ($query) {
            $query->when($this->desdeFecha(), fn ($q, $desde) => $q->where('created_at', '>=', $desde));
        });
    }

    protected function rutasQuery(): Builder
    {
        return Ruta::query()->when($this->periodo !== 'todo', function ($query) {
            $query->when($this->desdeFecha(), fn ($q, $desde) => $q->where('created_at', '>=', $desde));
        });
    }

    #[Computed]
    public function totalPedidos(): int
    {
        return $this->pedidosQuery()->count();
    }

    #[Computed]
    public function totalRutas(): int
    {
        return $this->rutasQuery()->count();
    }

    #[Computed]
    public function ventasPedidos(): float
    {
        return (float) $this->pedidosQuery()->sum('total');
    }

    #[Computed]
    public function ventasRutas(): float
    {
        return (float) RutaClienteProducto::query()
            ->whereHas('rutaCliente', fn ($q) => $q->whereIn('ruta_id', $this->rutasQuery()->pluck('id')))
            ->selectRaw('COALESCE(SUM(precio_unitario * cantidad), 0) as total')
            ->value('total');
    }

    #[Computed]
    public function ventasTotales(): float
    {
        return $this->ventasPedidos + $this->ventasRutas;
    }

    #[Computed]
    public function ticketPromedio(): float
    {
        return (float) $this->pedidosQuery()->avg('total');
    }

    #[Computed]
    public function clientesActivos(): int
    {
        $deRutas = \App\Models\RutaCliente::query()
            ->whereIn('ruta_id', $this->rutasQuery()->pluck('id'))
            ->pluck('cliente_id');

        return $this->pedidosQuery()->pluck('cliente_id')->concat($deRutas)->unique()->count();
    }

    #[Computed]
    public function porEstado()
    {
        return $this->pedidosQuery()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');
    }

    #[Computed]
    public function rutasPorEstado()
    {
        return $this->rutasQuery()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');
    }

    #[Computed]
    public function productoMasVendido()
    {
        $dePedidos = PedidoItem::query()
            ->whereIn('pedido_id', $this->pedidosQuery()->pluck('id'))
            ->selectRaw('producto_nombre, sum(cantidad) as unidades')
            ->groupBy('producto_nombre')
            ->pluck('unidades', 'producto_nombre');

        $deRutas = RutaClienteProducto::query()
            ->whereHas('rutaCliente', fn ($q) => $q->whereIn('ruta_id', $this->rutasQuery()->pluck('id')))
            ->selectRaw('producto_nombre, sum(cantidad) as unidades')
            ->groupBy('producto_nombre')
            ->pluck('unidades', 'producto_nombre');

        return $dePedidos->union($deRutas)
            ->mapWithKeys(fn ($unidades, $nombre) => [$nombre => $dePedidos->get($nombre, 0) + $deRutas->get($nombre, 0)])
            ->sortDesc()
            ->take(5)
            ->map(fn ($unidades, $nombre) => (object) ['producto_nombre' => $nombre, 'unidades' => $unidades])
            ->values();
    }

    public function render()
    {
        return view('livewire.reportes.index');
    }
}
