<?php

namespace App\Livewire;

use App\Models\Cliente;
use App\Models\Descuento;
use App\Models\MovimientoInventario;
use App\Models\Notificacion;
use App\Models\Pedido;
use App\Models\PedidoItem;
use App\Models\Producto;
use App\Models\Transportadora;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts::app', ['title' => 'Editar Pedido', 'subtitle' => 'Actualiza la información de este pedido', 'icon' => 'pencil-square'])]
class EditarPedido extends Component
{
    public Pedido $pedido;

    public array $originalItems = [];

    public string $numero = '';

    public string $destino = 'local';

    public string $fecha_pedido = '';

    public string $status = 'pendiente';

    public string $clienteQuery = '';

    public ?int $clienteId = null;

    public string $cliente_tipo_persona = 'natural';

    public string $cliente_nombre = '';

    public string $cliente_documento = '';

    public string $cliente_telefono = '';

    public string $cliente_email = '';

    public string $cliente_ciudad = '';

    public string $direccion_entrega = '';

    public string $cliente_tipo_precio = 'mayorista';

    public string $medio_pago = 'pendiente';

    public string $productoQuery = '';

    public array $cart = [];

    public ?int $descuentoId = null;

    public ?int $transportadoraId = null;

    public string $envioCosto = '0';

    public string $numero_guia = '';

    public string $notas = '';

    public function mount(Pedido $pedido): void
    {
        $pedido->load(['items', 'cliente']);

        $this->pedido = $pedido;
        $this->numero = $pedido->numero ?? '';
        $this->destino = $pedido->destino ?? 'local';
        $this->fecha_pedido = $pedido->fecha_pedido?->format('Y-m-d') ?? now()->format('Y-m-d');
        $this->status = $pedido->status;
        $this->direccion_entrega = $pedido->direccion_entrega ?? '';
        $this->medio_pago = $pedido->medio_pago;
        $this->notas = $pedido->notas ?? '';
        $this->descuentoId = $pedido->descuento_id;
        $this->transportadoraId = $pedido->transportadora_id;
        $this->envioCosto = (string) $pedido->envio_costo;
        $this->numero_guia = $pedido->numero_guia ?? '';

        $cliente = $pedido->cliente;
        $this->clienteId = $cliente->id;
        $this->cliente_tipo_persona = $cliente->tipo_persona;
        $this->cliente_nombre = $cliente->nombre;
        $this->cliente_documento = $cliente->documento;
        $this->cliente_telefono = $cliente->telefono ?? '';
        $this->cliente_email = $cliente->email ?? '';
        $this->cliente_ciudad = $cliente->ciudad ?? '';
        $this->cliente_tipo_precio = $cliente->tipo_precio;

        foreach ($pedido->items as $item) {
            if ($item->producto_id) {
                $this->originalItems[$item->producto_id] = ($this->originalItems[$item->producto_id] ?? 0) + $item->cantidad;
            }
        }

        foreach ($pedido->items as $item) {
            $producto = $item->producto;
            $lineaSubtotal = $item->precio_unitario * $item->cantidad;
            $reservadoOriginal = $this->originalItems[$item->producto_id] ?? $item->cantidad;

            $this->cart[(string) Str::uuid()] = [
                'producto_id' => $item->producto_id,
                'nombre' => $item->producto_nombre,
                'codigo' => $item->producto_codigo,
                'presentacion' => $item->presentacion,
                'molienda' => $item->molienda ?? 'entero',
                'precio' => (float) $item->precio_unitario,
                'cantidad' => $item->cantidad,
                'descuento_linea' => (float) $item->descuento_linea,
                'descuento_porcentaje' => $lineaSubtotal > 0 ? round(((float) $item->descuento_linea / $lineaSubtotal) * 100) : 0,
                'stock_disponible' => $producto ? $producto->stock + $reservadoOriginal : $item->cantidad,
            ];
        }
    }

    #[Computed]
    public function clientesResultados()
    {
        if (mb_strlen($this->clienteQuery) < 2) {
            return collect();
        }

        return Cliente::query()
            ->where(function ($query) {
                $query->where('nombre', 'like', "%{$this->clienteQuery}%")
                    ->orWhere('documento', 'like', "%{$this->clienteQuery}%")
                    ->orWhere('codigo', 'like', "%{$this->clienteQuery}%");
            })
            ->limit(6)
            ->get();
    }

    #[Computed]
    public function productosResultados()
    {
        return Producto::query()
            ->activos()
            ->when($this->productoQuery, fn ($query) => $query->where('nombre', 'like', "%{$this->productoQuery}%"))
            ->orderBy('nombre')
            ->get();
    }

    #[Computed]
    public function descuentos()
    {
        return Descuento::where('activo', true)->get();
    }

    #[Computed]
    public function transportadoras()
    {
        return Transportadora::where('activo', true)->get();
    }

    #[Computed]
    public function subtotal(): float
    {
        return collect($this->cart)->sum(fn ($line) => $line['precio'] * $line['cantidad'] - $line['descuento_linea']);
    }

    #[Computed]
    public function descuentoMonto(): float
    {
        $descuento = $this->descuentoId ? Descuento::find($this->descuentoId) : null;

        return $descuento ? $descuento->montoPara($this->subtotal) : 0.0;
    }

    #[Computed]
    public function total(): float
    {
        return max(0, $this->subtotal - $this->descuentoMonto + (float) $this->envioCosto);
    }

    public function updatedTransportadoraId($value): void
    {
        $transportadora = Transportadora::find($value);
        $this->envioCosto = (string) ($transportadora->costo ?? 0);
    }

    #[Computed]
    public function puntos(): int
    {
        return intdiv((int) $this->total, 1000);
    }

    public function selectCliente(int $id): void
    {
        $cliente = Cliente::findOrFail($id);

        $this->clienteId = $cliente->id;
        $this->cliente_tipo_persona = $cliente->tipo_persona;
        $this->cliente_nombre = $cliente->nombre;
        $this->cliente_documento = $cliente->documento;
        $this->cliente_telefono = $cliente->telefono ?? '';
        $this->cliente_email = $cliente->email ?? '';
        $this->direccion_entrega = $cliente->direccion ?? '';
        $this->cliente_ciudad = $cliente->ciudad ?? '';
        $this->cliente_tipo_precio = $cliente->tipo_precio;
        $this->clienteQuery = '';
    }

    public function addProducto(int $id): void
    {
        $producto = Producto::findOrFail($id);
        $reservadoOriginal = $this->originalItems[$producto->id] ?? 0;
        $disponible = $producto->stock + $reservadoOriginal;

        $totalEnCarrito = collect($this->cart)->where('producto_id', $producto->id)->sum('cantidad');

        if ($totalEnCarrito >= $disponible) {
            return;
        }

        // Reuse the existing "en grano" line for this product if there's one, so repeated
        // clicks just bump its quantity; otherwise start a new line (e.g. the client wants
        // the same product both en grano and molido, which needs two separate lines).
        $key = collect($this->cart)->search(
            fn ($line) => $line['producto_id'] === $producto->id && $line['molienda'] === 'entero'
        );

        if ($key !== false) {
            $this->cart[$key]['cantidad']++;

            return;
        }

        $this->cart[(string) Str::uuid()] = [
            'producto_id' => $producto->id,
            'nombre' => $producto->nombre,
            'codigo' => $producto->sku,
            'presentacion' => $producto->presentacion,
            'molienda' => 'entero',
            'precio' => (float) $producto->precio,
            'cantidad' => 1,
            'descuento_linea' => 0.0,
            'descuento_porcentaje' => 0,
            'stock_disponible' => $disponible,
        ];
    }

    public function incrementar(string $key): void
    {
        if (! isset($this->cart[$key])) {
            return;
        }

        $productoId = $this->cart[$key]['producto_id'];
        $totalEnCarrito = collect($this->cart)->where('producto_id', $productoId)->sum('cantidad');

        if ($totalEnCarrito < $this->cart[$key]['stock_disponible']) {
            $this->cart[$key]['cantidad']++;
        }
    }

    public function decrementar(string $key): void
    {
        if (! isset($this->cart[$key])) {
            return;
        }

        $this->cart[$key]['cantidad'] = max(1, $this->cart[$key]['cantidad'] - 1);
    }

    /**
     * Fires for any wire:model-bound "cart.*" update — used here to clamp a
     * manually typed quantity between 1 and the stock still available for
     * that product once the other lines of it are accounted for.
     */
    public function updatedCart($value, $key): void
    {
        if (! str_ends_with($key, '.cantidad')) {
            return;
        }

        $lineKey = substr($key, 0, -strlen('.cantidad'));

        if (! isset($this->cart[$lineKey])) {
            return;
        }

        $productoId = $this->cart[$lineKey]['producto_id'];
        $otrasLineas = collect($this->cart)->except($lineKey)->where('producto_id', $productoId)->sum('cantidad');
        $maximo = max(1, $this->cart[$lineKey]['stock_disponible'] - $otrasLineas);

        $this->cart[$lineKey]['cantidad'] = max(1, min((int) $value, $maximo));
    }

    public function actualizarDescuentoLinea(string $key, $valor): void
    {
        if (! isset($this->cart[$key])) {
            return;
        }

        $porcentaje = (float) $valor;
        $lineaSubtotal = $this->cart[$key]['precio'] * $this->cart[$key]['cantidad'];
        $this->cart[$key]['descuento_porcentaje'] = $porcentaje;
        $this->cart[$key]['descuento_linea'] = round($lineaSubtotal * ($porcentaje / 100), 2);
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
            'numero' => ['required', 'string', 'max:255', Rule::unique('pedidos', 'numero')->ignore($this->pedido->id)],
            'fecha_pedido' => 'required|date',
            'status' => 'required|in:pendiente,confirmado,enviado,entregado,cancelado',
            'cliente_tipo_persona' => 'required|in:natural,juridica',
            'cliente_nombre' => 'required|string|max:255',
            'cliente_documento' => 'required|string|max:255',
            'cliente_telefono' => 'required|string|max:50',
            'cliente_email' => 'nullable|email|max:255',
            'cliente_ciudad' => 'required|string|max:255',
            'direccion_entrega' => 'required|string|max:255',
            'envioCosto' => 'required|numeric|min:0',
        ], [], [
            'numero' => 'número de orden',
            'fecha_pedido' => 'fecha del pedido',
            'cliente_nombre' => 'nombre del cliente',
            'cliente_documento' => 'documento del cliente',
            'cliente_telefono' => 'celular',
            'cliente_ciudad' => 'ciudad',
            'direccion_entrega' => 'dirección de entrega',
        ]);

        if (empty($this->cart)) {
            $this->addError('cart', 'El pedido debe tener al menos un producto.');

            return;
        }

        DB::transaction(function () {
            $vendedor = Auth::user();

            $cliente = $this->clienteId
                ? Cliente::find($this->clienteId)
                : Cliente::firstOrCreate(
                    ['documento' => $this->cliente_documento],
                    ['nombre' => $this->cliente_nombre, 'tipo_precio' => $this->cliente_tipo_precio]
                );

            $cliente->update([
                'nombre' => $this->cliente_nombre,
                'tipo_persona' => $this->cliente_tipo_persona,
                'telefono' => $this->cliente_telefono,
                'email' => $this->cliente_email,
                'direccion' => $this->direccion_entrega,
                'ciudad' => $this->cliente_ciudad,
                'tipo_precio' => $this->cliente_tipo_precio,
            ]);

            $nuevasCantidades = collect($this->cart)
                ->filter(fn ($line) => $line['producto_id'])
                ->groupBy('producto_id')
                ->map(fn ($lineas) => $lineas->sum('cantidad'));

            $productoIds = collect($this->originalItems)->keys()
                ->merge($nuevasCantidades->keys())
                ->unique()
                ->filter();

            foreach ($productoIds as $productoId) {
                $anterior = $this->originalItems[$productoId] ?? 0;
                $nueva = $nuevasCantidades[$productoId] ?? 0;
                $delta = $nueva - $anterior;

                if ($delta === 0) {
                    continue;
                }

                $producto = Producto::find($productoId);

                if (! $producto) {
                    continue;
                }

                if ($delta > 0) {
                    $producto->decrement('stock', $delta);
                    MovimientoInventario::create([
                        'producto_id' => $productoId,
                        'tipo' => 'salida',
                        'cantidad' => $delta,
                        'motivo' => "Ajuste pedido #{$this->numero}",
                        'pedido_id' => $this->pedido->id,
                        'user_id' => $vendedor->id,
                    ]);
                } else {
                    $producto->increment('stock', abs($delta));
                    MovimientoInventario::create([
                        'producto_id' => $productoId,
                        'tipo' => 'entrada',
                        'cantidad' => abs($delta),
                        'motivo' => "Ajuste pedido #{$this->numero}",
                        'pedido_id' => $this->pedido->id,
                        'user_id' => $vendedor->id,
                    ]);
                }
            }

            $this->pedido->items()->delete();

            foreach ($this->cart as $line) {
                PedidoItem::create([
                    'pedido_id' => $this->pedido->id,
                    'producto_id' => $line['producto_id'],
                    'producto_nombre' => $line['nombre'],
                    'producto_codigo' => $line['codigo'],
                    'presentacion' => $line['presentacion'],
                    'molienda' => $line['molienda'],
                    'precio_unitario' => $line['precio'],
                    'cantidad' => $line['cantidad'],
                    'descuento_linea' => $line['descuento_linea'],
                    'total' => $line['precio'] * $line['cantidad'] - $line['descuento_linea'],
                ]);
            }

            $this->pedido->update([
                'numero' => $this->numero,
                'destino' => $this->destino,
                'fecha_pedido' => $this->fecha_pedido,
                'cliente_id' => $cliente->id,
                'direccion_entrega' => $this->direccion_entrega,
                'subtotal' => $this->subtotal,
                'descuento_id' => $this->descuentoId,
                'descuento_monto' => $this->descuentoMonto,
                'transportadora_id' => $this->transportadoraId,
                'envio_costo' => (float) $this->envioCosto,
                'total' => $this->total,
                'puntos_generados' => $this->puntos,
                'medio_pago' => $this->medio_pago,
                'numero_guia' => $this->numero_guia ?: null,
                'despachado_at' => $this->status === 'enviado' ? ($this->pedido->despachado_at ?? now()) : $this->pedido->despachado_at,
                'notas' => $this->notas,
                'status' => $this->status,
            ]);

            Notificacion::create([
                'user_id' => $vendedor->id,
                'titulo' => 'Pedido actualizado',
                'mensaje' => "Se actualizó el pedido #{$this->numero} de {$cliente->nombre}.",
                'tipo' => 'pedido',
            ]);
        });

        session()->flash('success', "Pedido #{$this->numero} actualizado correctamente.");

        return redirect()->route('pedidos.index');
    }

    public function render()
    {
        return view('livewire.editar-pedido');
    }
}
