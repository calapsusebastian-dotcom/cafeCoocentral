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

#[Layout('layouts::app', ['title' => 'Nuevo Pedido', 'subtitle' => 'Crea un nuevo pedido para tu cliente', 'icon' => 'shopping-cart'])]
class NuevoPedido extends Component
{
    public string $numero = '';

    public string $destino = 'local';

    public string $fecha_pedido = '';

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

    public string $centro_costo = '';

    public string $notas = '';

    public function mount(): void
    {
        $this->transportadoraId = Transportadora::where('activo', true)->first()?->id;
        $this->envioCosto = (string) (Transportadora::find($this->transportadoraId)?->costo ?? 0);
        $this->descuentoId = Descuento::where('nombre', 'Sin descuento')->first()?->id
            ?? Descuento::where('activo', true)->first()?->id;
        $this->fecha_pedido = now()->format('Y-m-d');
        $this->numero = 'ORD-'.str_pad((string) ((Pedido::max('id') ?? 0) + 1), 4, '0', STR_PAD_LEFT);
    }

    public function updatedTransportadoraId($value): void
    {
        $transportadora = Transportadora::find($value);
        $this->envioCosto = (string) ($transportadora->costo ?? 0);
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

        $totalEnCarrito = collect($this->cart)->where('producto_id', $producto->id)->sum('cantidad');

        if ($totalEnCarrito >= $producto->stock) {
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
            'stock_disponible' => $producto->stock,
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

    public function confirmarPedido()
    {
        $this->validate([
            'numero' => ['required', 'string', 'max:255', Rule::unique('pedidos', 'numero')],
            'fecha_pedido' => 'required|date',
            'cliente_tipo_persona' => 'required|in:natural,juridica',
            'cliente_nombre' => 'required|string|max:255',
            'cliente_documento' => 'required|string|max:255',
            'cliente_telefono' => 'required|string|max:50',
            'cliente_email' => 'nullable|email|max:255',
            'cliente_ciudad' => 'required|string|max:255',
            'direccion_entrega' => 'required|string|max:255',
            'envioCosto' => 'required|numeric|min:0',
            'centro_costo' => 'required|string|in:Garzón,Bogotá,Neiva,Producción',
        ], [], [
            'numero' => 'número de orden',
            'fecha_pedido' => 'fecha del pedido',
            'cliente_nombre' => 'nombre del cliente',
            'cliente_documento' => 'documento del cliente',
            'cliente_telefono' => 'celular',
            'cliente_ciudad' => 'ciudad',
            'direccion_entrega' => 'dirección de entrega',
            'centro_costo' => 'centro de costos',
        ]);

        if (empty($this->cart)) {
            $this->addError('cart', 'Agrega al menos un producto al pedido.');

            return;
        }

        $pedido = DB::transaction(function () {
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

            $pedido = Pedido::create([
                'numero' => $this->numero,
                'destino' => $this->destino,
                'fecha_pedido' => $this->fecha_pedido,
                'cliente_id' => $cliente->id,
                'direccion_entrega' => $this->direccion_entrega,
                'user_id' => $vendedor->id,
                'subtotal' => $this->subtotal,
                'descuento_id' => $this->descuentoId,
                'descuento_monto' => $this->descuentoMonto,
                'transportadora_id' => $this->transportadoraId,
                'envio_costo' => (float) $this->envioCosto,
                'centro_costo' => $this->centro_costo,
                'total' => $this->total,
                'puntos_generados' => $this->puntos,
                'medio_pago' => $this->medio_pago,
                'notas' => $this->notas,
                'status' => 'pendiente',
            ]);

            foreach ($this->cart as $line) {
                PedidoItem::create([
                    'pedido_id' => $pedido->id,
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

                $producto = Producto::find($line['producto_id']);
                $producto?->decrement('stock', $line['cantidad']);

                MovimientoInventario::create([
                    'producto_id' => $line['producto_id'],
                    'tipo' => 'salida',
                    'cantidad' => $line['cantidad'],
                    'motivo' => "Venta pedido #{$pedido->numero}",
                    'pedido_id' => $pedido->id,
                    'user_id' => $vendedor->id,
                ]);
            }

            Notificacion::create([
                'user_id' => $vendedor->id,
                'titulo' => 'Nuevo pedido confirmado',
                'mensaje' => "Se confirmó el pedido #{$pedido->numero} de {$cliente->nombre} por $".number_format($pedido->total, 0, ',', '.'),
                'tipo' => 'pedido',
            ]);

            return $pedido;
        });

        $this->reset([
            'clienteId', 'cliente_tipo_persona', 'cliente_nombre', 'cliente_documento', 'cliente_telefono',
            'cliente_email', 'direccion_entrega', 'cliente_ciudad', 'cliente_tipo_precio', 'medio_pago', 'destino', 'cart', 'notas',
        ]);
        $this->fecha_pedido = now()->format('Y-m-d');
        $this->numero = 'ORD-'.str_pad((string) ((Pedido::max('id') ?? 0) + 1), 4, '0', STR_PAD_LEFT);

        session()->flash('success', "Pedido #{$pedido->numero} confirmado correctamente.");

        return redirect()->route('pedidos.index');
    }

    public function render()
    {
        return view('livewire.nuevo-pedido');
    }
}
