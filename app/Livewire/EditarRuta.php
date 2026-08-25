<?php

namespace App\Livewire;

use App\Models\Cliente;
use App\Models\Notificacion;
use App\Models\Producto;
use App\Models\Ruta;
use App\Models\RutaCliente;
use App\Models\RutaClienteProducto;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts::app', ['title' => 'Editar Ruta', 'subtitle' => 'Actualiza esta ruta antes de recibirla', 'icon' => 'map'])]
class EditarRuta extends Component
{
    public Ruta $ruta;

    public string $numero = '';

    public string $nombre = '';

    public string $fecha = '';

    public string $notas = '';

    public array $clientes = [];

    public array $clientesColapsados = [];

    public bool $showClientesModal = false;

    public string $modalClienteQuery = '';

    public array $clientesSeleccionados = [];

    public ?int $productosModalClienteId = null;

    public string $modalProductoQuery = '';

    public array $productosSeleccionados = [];

    public function mount(Ruta $ruta): void
    {
        if (! in_array($ruta->status, ['pendiente', 'recibida'], true)) {
            session()->flash('error', "La ruta #{$ruta->numero} ya no se puede editar porque está \"{$ruta->status}\".");

            $this->redirectRoute('rutas.index');

            return;
        }

        $ruta->load('clientes.cliente', 'clientes.productos');

        $this->ruta = $ruta;
        $this->numero = $ruta->numero ?? '';
        $this->nombre = $ruta->nombre;
        $this->fecha = $ruta->fecha?->format('Y-m-d') ?? now()->format('Y-m-d');
        $this->notas = $ruta->notas ?? '';

        foreach ($ruta->clientes as $rutaCliente) {
            $clienteKey = (string) $rutaCliente->cliente_id;

            $this->clientes[$clienteKey] = [
                'cliente_id' => $rutaCliente->cliente_id,
                'nombre' => $rutaCliente->cliente->nombre,
                'documento' => $rutaCliente->cliente->documento,
                'productos' => [],
            ];

            foreach ($rutaCliente->productos as $producto) {
                $lineKey = (string) Str::uuid();

                $this->clientes[$clienteKey]['productos'][$lineKey] = [
                    'producto_id' => $producto->producto_id,
                    'nombre' => $producto->producto_nombre,
                    'codigo' => $producto->producto_codigo,
                    'presentacion' => $producto->presentacion,
                    'molienda' => $producto->molienda ?? 'entero',
                    'precio_unitario' => (float) $producto->precio_unitario,
                    'cantidad' => $producto->cantidad,
                ];
            }

            if (! empty($this->clientes[$clienteKey]['productos'])) {
                $this->clientesColapsados[$clienteKey] = true;
            }
        }
    }

    #[Computed]
    public function clientesModalResultados()
    {
        return Cliente::query()
            ->when($this->modalClienteQuery, function ($query) {
                $query->where('nombre', 'like', "%{$this->modalClienteQuery}%")
                    ->orWhere('documento', 'like', "%{$this->modalClienteQuery}%")
                    ->orWhere('codigo', 'like', "%{$this->modalClienteQuery}%");
            })
            ->orderBy('nombre')
            ->limit(100)
            ->get();
    }

    #[Computed]
    public function productosModalResultados()
    {
        return Producto::query()
            ->activos()
            ->when($this->modalProductoQuery, fn ($query) => $query->where('nombre', 'like', "%{$this->modalProductoQuery}%"))
            ->orderBy('nombre')
            ->limit(100)
            ->get();
    }

    #[Computed]
    public function totalUnidades(): int
    {
        return collect($this->clientes)->sum(fn ($cliente) => collect($cliente['productos'])->sum('cantidad'));
    }

    #[Computed]
    public function totalValor(): float
    {
        return collect($this->clientes)->sum(
            fn ($cliente) => collect($cliente['productos'])->sum(fn ($p) => $p['precio_unitario'] * $p['cantidad'])
        );
    }

    public function abrirModalClientes(): void
    {
        $this->clientesSeleccionados = [];
        $this->modalClienteQuery = '';
        $this->showClientesModal = true;
    }

    public function cerrarModalClientes(): void
    {
        $this->showClientesModal = false;
    }

    public function toggleClienteSeleccionado(int $clienteId): void
    {
        if (in_array($clienteId, $this->clientesSeleccionados, true)) {
            $this->clientesSeleccionados = array_values(array_diff($this->clientesSeleccionados, [$clienteId]));
        } else {
            $this->clientesSeleccionados[] = $clienteId;
        }
    }

    public function confirmarClientesSeleccionados(): void
    {
        $clientes = Cliente::whereIn('id', $this->clientesSeleccionados)->get();

        foreach ($clientes as $cliente) {
            $key = (string) $cliente->id;

            if (! isset($this->clientes[$key])) {
                $this->clientes[$key] = [
                    'cliente_id' => $cliente->id,
                    'nombre' => $cliente->nombre,
                    'documento' => $cliente->documento,
                    'productos' => [],
                ];
            }
        }

        $this->showClientesModal = false;
        $this->clientesSeleccionados = [];
    }

    public function quitarCliente(int $clienteId): void
    {
        unset($this->clientes[(string) $clienteId]);
        unset($this->clientesColapsados[(string) $clienteId]);
    }

    public function moverClienteArriba(int $clienteId): void
    {
        $this->moverCliente($clienteId, -1);
    }

    public function moverClienteAbajo(int $clienteId): void
    {
        $this->moverCliente($clienteId, 1);
    }

    private function moverCliente(int $clienteId, int $direccion): void
    {
        $keys = array_keys($this->clientes);
        $index = array_search($clienteId, $keys, true);
        $nuevoIndex = $index + $direccion;

        if ($index === false || $nuevoIndex < 0 || $nuevoIndex >= count($keys)) {
            return;
        }

        [$keys[$index], $keys[$nuevoIndex]] = [$keys[$nuevoIndex], $keys[$index]];

        $this->clientes = collect($keys)->mapWithKeys(fn ($key) => [$key => $this->clientes[$key]])->all();
    }

    public function toggleClienteAbierto(int $clienteId): void
    {
        $key = (string) $clienteId;
        $this->clientesColapsados[$key] = ! ($this->clientesColapsados[$key] ?? false);
    }

    public function expandirTodo(): void
    {
        $this->clientesColapsados = [];
    }

    public function colapsarTodo(): void
    {
        foreach (array_keys($this->clientes) as $key) {
            $this->clientesColapsados[$key] = true;
        }
    }

    public function abrirModalProductos(int $clienteId): void
    {
        $this->productosModalClienteId = $clienteId;
        $this->productosSeleccionados = [];
        $this->modalProductoQuery = '';
    }

    public function cerrarModalProductos(): void
    {
        $this->productosModalClienteId = null;
    }

    public function toggleProductoSeleccionado(int $productoId): void
    {
        if (in_array($productoId, $this->productosSeleccionados, true)) {
            $this->productosSeleccionados = array_values(array_diff($this->productosSeleccionados, [$productoId]));
        } else {
            $this->productosSeleccionados[] = $productoId;
        }
    }

    public function confirmarProductosSeleccionados(): void
    {
        $clienteKey = (string) $this->productosModalClienteId;

        if (! isset($this->clientes[$clienteKey])) {
            return;
        }

        $productos = Producto::whereIn('id', $this->productosSeleccionados)->get();

        foreach ($productos as $producto) {
            // Reuse the existing "en grano" line for this product if there's one, so
            // selecting it again just bumps its quantity; otherwise start a new line.
            $lineKey = collect($this->clientes[$clienteKey]['productos'])->search(
                fn ($linea) => $linea['producto_id'] === $producto->id && $linea['molienda'] === 'entero'
            );

            if ($lineKey !== false) {
                $this->clientes[$clienteKey]['productos'][$lineKey]['cantidad']++;
            } else {
                $this->clientes[$clienteKey]['productos'][(string) Str::uuid()] = [
                    'producto_id' => $producto->id,
                    'nombre' => $producto->nombre,
                    'codigo' => $producto->sku,
                    'presentacion' => $producto->presentacion,
                    'molienda' => 'entero',
                    'precio_unitario' => (float) $producto->precio,
                    'cantidad' => 1,
                ];
            }
        }

        $this->productosModalClienteId = null;
        $this->productosSeleccionados = [];
    }

    public function incrementarProducto(int $clienteId, string $key): void
    {
        $clienteKey = (string) $clienteId;

        if (! isset($this->clientes[$clienteKey]['productos'][$key])) {
            return;
        }

        $this->clientes[$clienteKey]['productos'][$key]['cantidad']++;
    }

    public function decrementarProducto(int $clienteId, string $key): void
    {
        $clienteKey = (string) $clienteId;

        if (! isset($this->clientes[$clienteKey]['productos'][$key])) {
            return;
        }

        $cantidad = $this->clientes[$clienteKey]['productos'][$key]['cantidad'] - 1;
        $this->clientes[$clienteKey]['productos'][$key]['cantidad'] = max(1, $cantidad);
    }

    /**
     * Fires for any wire:model-bound "clientes.*" update — used here to
     * clamp a manually typed quantity/precio a valid numbers. Sin esto,
     * borrar el campo de precio deja un string vacío en el array y el
     * cálculo de totales (precio_unitario * cantidad) revienta con un
     * error 500 ("Unsupported operand types: string * int").
     */
    public function updatedClientes($value, $key): void
    {
        foreach (['cantidad', 'precio_unitario'] as $campo) {
            if (! str_ends_with($key, ".{$campo}")) {
                continue;
            }

            $path = substr($key, 0, -strlen(".{$campo}"));
            $segments = explode('.', $path, 3);

            if (count($segments) !== 3 || $segments[1] !== 'productos') {
                return;
            }

            [$clienteKey, , $lineKey] = $segments;

            if (! isset($this->clientes[$clienteKey]['productos'][$lineKey])) {
                return;
            }

            $this->clientes[$clienteKey]['productos'][$lineKey][$campo] = $campo === 'cantidad'
                ? max(1, (int) $value)
                : max(0, (float) $value);

            return;
        }
    }

    public function actualizarMoliendaProducto(int $clienteId, string $key, string $valor): void
    {
        $clienteKey = (string) $clienteId;

        if (! isset($this->clientes[$clienteKey]['productos'][$key])) {
            return;
        }

        $this->clientes[$clienteKey]['productos'][$key]['molienda'] = $valor;
    }

    public function quitarProducto(int $clienteId, string $key): void
    {
        unset($this->clientes[(string) $clienteId]['productos'][$key]);
    }

    public function guardarRuta()
    {
        $this->validate([
            'numero' => ['required', 'string', 'max:255', Rule::unique('rutas', 'numero')->ignore($this->ruta->id)],
            'nombre' => 'required|string|max:255',
            'fecha' => 'required|date',
        ], [], [
            'numero' => 'número de ruta',
            'nombre' => 'nombre de la ruta',
            'fecha' => 'fecha',
        ]);

        if (empty($this->clientes)) {
            $this->addError('clientes', 'Agrega al menos un cliente a la ruta.');

            return;
        }

        foreach ($this->clientes as $cliente) {
            if (empty($cliente['productos'])) {
                $this->addError('clientes', "Agrega al menos un producto para {$cliente['nombre']}.");

                return;
            }
        }

        DB::transaction(function () {
            $facturasPrevias = $this->ruta->clientes()->get(['cliente_id', 'numero_factura', 'facturado_at'])
                ->keyBy('cliente_id');

            $this->ruta->clientes()->delete();

            foreach (array_values($this->clientes) as $orden => $cliente) {
                $facturaPrevia = $facturasPrevias->get($cliente['cliente_id']);

                $rutaCliente = RutaCliente::create([
                    'ruta_id' => $this->ruta->id,
                    'cliente_id' => $cliente['cliente_id'],
                    'orden' => $orden,
                    'numero_factura' => $facturaPrevia?->numero_factura,
                    'facturado_at' => $facturaPrevia?->facturado_at,
                ]);

                foreach ($cliente['productos'] as $producto) {
                    RutaClienteProducto::create([
                        'ruta_cliente_id' => $rutaCliente->id,
                        'producto_id' => $producto['producto_id'],
                        'producto_nombre' => $producto['nombre'],
                        'producto_codigo' => $producto['codigo'],
                        'presentacion' => $producto['presentacion'],
                        'molienda' => $producto['molienda'],
                        'precio_unitario' => $producto['precio_unitario'],
                        'cantidad' => $producto['cantidad'],
                    ]);
                }
            }

            $this->ruta->update([
                'numero' => $this->numero,
                'nombre' => $this->nombre,
                'fecha' => $this->fecha,
                'notas' => $this->notas,
            ]);

            Notificacion::create([
                'user_id' => Auth::id(),
                'titulo' => 'Ruta actualizada',
                'mensaje' => "Se actualizó la ruta #{$this->numero}.",
                'tipo' => 'sistema',
            ]);
        });

        session()->flash('success', "Ruta #{$this->numero} actualizada correctamente.");

        return redirect()->route('rutas.index');
    }

    public function render()
    {
        return view('livewire.editar-ruta');
    }
}
