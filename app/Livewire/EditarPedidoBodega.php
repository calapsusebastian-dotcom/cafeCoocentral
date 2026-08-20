<?php

namespace App\Livewire;

use App\Models\Bodega;
use App\Models\Notificacion;
use App\Models\PedidoBodega;
use App\Models\PedidoBodegaItem;
use App\Models\Producto;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts::app', ['title' => 'Editar Pedido a Bodega', 'subtitle' => 'Actualiza esta solicitud antes de recibirla', 'icon' => 'building-storefront'])]
class EditarPedidoBodega extends Component
{
    public PedidoBodega $pedidoBodega;

    public string $numero = '';

    public string $fecha_pedido = '';

    public ?int $bodegaId = null;

    public string $productoQuery = '';

    public array $cart = [];

    public string $notas = '';

    public function mount(PedidoBodega $pedidoBodega): void
    {
        if ($pedidoBodega->status !== 'pendiente') {
            session()->flash('error', "El pedido #{$pedidoBodega->numero} ya no se puede editar porque está \"{$pedidoBodega->status}\".");

            $this->redirectRoute('pedidos-bodega.index');

            return;
        }

        $pedidoBodega->load('items');

        $this->pedidoBodega = $pedidoBodega;
        $this->numero = $pedidoBodega->numero ?? '';
        $this->fecha_pedido = $pedidoBodega->fecha_pedido?->format('Y-m-d') ?? now()->format('Y-m-d');
        $this->bodegaId = $pedidoBodega->bodega_id;
        $this->notas = $pedidoBodega->notas ?? '';

        foreach ($pedidoBodega->items as $item) {
            $this->cart[(string) Str::uuid()] = [
                'producto_id' => $item->producto_id,
                'nombre' => $item->producto_nombre,
                'codigo' => $item->producto_codigo,
                'presentacion' => $item->presentacion,
                'molienda' => $item->molienda ?? 'entero',
                'cantidad' => $item->cantidad,
            ];
        }
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

        $lineKey = collect($this->cart)->search(
            fn ($linea) => $linea['producto_id'] === $producto->id && $linea['molienda'] === 'entero'
        );

        if ($lineKey !== false) {
            $this->cart[$lineKey]['cantidad']++;
        } else {
            $this->cart[(string) Str::uuid()] = [
                'producto_id' => $producto->id,
                'nombre' => $producto->nombre,
                'codigo' => $producto->sku,
                'presentacion' => $producto->presentacion,
                'molienda' => 'entero',
                'cantidad' => 1,
            ];
        }
    }

    public function incrementar(string $key): void
    {
        if (! isset($this->cart[$key])) {
            return;
        }

        $this->cart[$key]['cantidad']++;
    }

    public function decrementar(string $key): void
    {
        if (! isset($this->cart[$key])) {
            return;
        }

        $this->cart[$key]['cantidad'] = max(1, $this->cart[$key]['cantidad'] - 1);
    }

    public function actualizarMoliendaLinea(string $key, string $valor): void
    {
        if (! isset($this->cart[$key])) {
            return;
        }

        $this->cart[$key]['molienda'] = $valor;
    }

    public function removerLinea(string $key): void
    {
        unset($this->cart[$key]);
    }

    public function vaciarPedido(): void
    {
        $this->cart = [];
    }

    public function guardarPedido()
    {
        $this->validate([
            'numero' => ['required', 'string', 'max:255', Rule::unique('pedidos_bodega', 'numero')->ignore($this->pedidoBodega->id)],
            'fecha_pedido' => 'required|date',
            'bodegaId' => 'required|exists:bodegas,id',
        ], [], [
            'numero' => 'número de pedido',
            'fecha_pedido' => 'fecha del pedido',
            'bodegaId' => 'bodega',
        ]);

        if (empty($this->cart)) {
            $this->addError('cart', 'El pedido debe tener al menos un producto.');

            return;
        }

        DB::transaction(function () {
            $this->pedidoBodega->items()->delete();

            foreach ($this->cart as $line) {
                PedidoBodegaItem::create([
                    'pedido_bodega_id' => $this->pedidoBodega->id,
                    'producto_id' => $line['producto_id'],
                    'producto_nombre' => $line['nombre'],
                    'producto_codigo' => $line['codigo'],
                    'presentacion' => $line['presentacion'],
                    'molienda' => $line['molienda'],
                    'cantidad' => $line['cantidad'],
                ]);
            }

            $this->pedidoBodega->update([
                'numero' => $this->numero,
                'fecha_pedido' => $this->fecha_pedido,
                'bodega_id' => $this->bodegaId,
                'notas' => $this->notas,
            ]);

            Notificacion::create([
                'user_id' => Auth::id(),
                'titulo' => 'Pedido a bodega actualizado',
                'mensaje' => "Se actualizó el pedido a bodega #{$this->numero}.",
                'tipo' => 'sistema',
            ]);
        });

        session()->flash('success', "Pedido a bodega #{$this->numero} actualizado correctamente.");

        return redirect()->route('pedidos-bodega.index');
    }

    public function render()
    {
        return view('livewire.editar-pedido-bodega');
    }
}
