<?php

namespace App\Livewire;

use App\Models\Bodega;
use App\Models\Notificacion;
use App\Models\PedidoBodega;
use App\Models\PedidoBodegaItem;
use App\Models\Producto;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts::app', ['title' => 'Nuevo Pedido a Bodega', 'subtitle' => 'Solicita productos a una bodega para reabastecer inventario', 'icon' => 'building-storefront'])]
class NuevoPedidoBodega extends Component
{
    public string $numero = '';

    public string $fecha_pedido = '';

    public ?int $bodegaId = null;

    public string $productoQuery = '';

    public array $cart = [];

    public string $notas = '';

    public function mount(): void
    {
        $this->fecha_pedido = now()->format('Y-m-d');
        $this->numero = 'BOD-'.str_pad((string) ((PedidoBodega::max('id') ?? 0) + 1), 4, '0', STR_PAD_LEFT);
        $this->bodegaId = Bodega::where('activo', true)->first()?->id;
    }

    #[Computed]
    public function bodegas()
    {
        return Bodega::where('activo', true)->orderBy('nombre')->get();
    }

    #[Computed]
    public function productosResultados()
    {
        return Producto::query()
            ->when($this->productoQuery, fn ($query) => $query->where('nombre', 'like', "%{$this->productoQuery}%"))
            ->orderBy('nombre')
            ->get();
    }

    #[Computed]
    public function totalUnidades(): int
    {
        return collect($this->cart)->sum('cantidad');
    }

    public function addProducto(int $id): void
    {
        $producto = Producto::findOrFail($id);
        $key = (string) $producto->id;

        if (isset($this->cart[$key])) {
            $this->cart[$key]['cantidad']++;
        } else {
            $this->cart[$key] = [
                'producto_id' => $producto->id,
                'nombre' => $producto->nombre,
                'codigo' => $producto->sku,
                'presentacion' => $producto->presentacion,
                'cantidad' => 1,
                'stock_actual' => $producto->stock,
            ];
        }
    }

    public function incrementar(int $id): void
    {
        $key = (string) $id;

        if (! isset($this->cart[$key])) {
            return;
        }

        $this->cart[$key]['cantidad']++;
    }

    public function decrementar(int $id): void
    {
        $key = (string) $id;

        if (! isset($this->cart[$key])) {
            return;
        }

        $this->cart[$key]['cantidad'] = max(1, $this->cart[$key]['cantidad'] - 1);
    }

    public function actualizarCantidadLinea(int $id, $valor): void
    {
        $key = (string) $id;

        if (! isset($this->cart[$key])) {
            return;
        }

        $this->cart[$key]['cantidad'] = max(1, (int) $valor);
    }

    public function removerLinea(int $id): void
    {
        unset($this->cart[(string) $id]);
    }

    public function vaciarPedido(): void
    {
        $this->cart = [];
    }

    public function guardarPedido()
    {
        $this->validate([
            'numero' => ['required', 'string', 'max:255', Rule::unique('pedidos_bodega', 'numero')],
            'fecha_pedido' => 'required|date',
            'bodegaId' => 'required|exists:bodegas,id',
        ], [], [
            'numero' => 'número de pedido',
            'fecha_pedido' => 'fecha del pedido',
            'bodegaId' => 'bodega',
        ]);

        if (empty($this->cart)) {
            $this->addError('cart', 'Agrega al menos un producto al pedido.');

            return;
        }

        $pedido = DB::transaction(function () {
            $vendedor = Auth::user();

            $pedidoBodega = PedidoBodega::create([
                'numero' => $this->numero,
                'fecha_pedido' => $this->fecha_pedido,
                'bodega_id' => $this->bodegaId,
                'user_id' => $vendedor->id,
                'status' => 'pendiente',
                'notas' => $this->notas,
            ]);

            foreach ($this->cart as $line) {
                PedidoBodegaItem::create([
                    'pedido_bodega_id' => $pedidoBodega->id,
                    'producto_id' => $line['producto_id'],
                    'producto_nombre' => $line['nombre'],
                    'producto_codigo' => $line['codigo'],
                    'presentacion' => $line['presentacion'],
                    'cantidad' => $line['cantidad'],
                ]);
            }

            $bodega = Bodega::find($this->bodegaId);

            Notificacion::create([
                'user_id' => $vendedor->id,
                'titulo' => 'Pedido a bodega creado',
                'mensaje' => "Se creó el pedido #{$pedidoBodega->numero} a la bodega {$bodega->nombre} con ".collect($this->cart)->sum('cantidad')." unidades.",
                'tipo' => 'sistema',
            ]);

            return $pedidoBodega;
        });

        session()->flash('success', "Pedido a bodega #{$pedido->numero} creado correctamente.");

        return redirect()->route('pedidos-bodega.index');
    }

    public function render()
    {
        return view('livewire.nuevo-pedido-bodega');
    }
}
