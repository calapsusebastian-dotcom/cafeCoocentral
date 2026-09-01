<?php

namespace App\Livewire;

use App\Models\Notificacion;
use App\Models\Produccion;
use App\Models\ProduccionItem;
use App\Models\Producto;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts::app', ['title' => 'Nueva Producción', 'subtitle' => 'Registra los productos producidos hoy', 'icon' => 'fire'])]
class NuevaProduccion extends Component
{
    public string $numero = '';

    public string $fecha_produccion = '';

    public string $productoQuery = '';

    public array $cart = [];

    public string $notas = '';

    public function mount(): void
    {
        $this->fecha_produccion = now()->format('Y-m-d');
        $this->numero = 'PROD-'.str_pad((string) ((Produccion::max('id') ?? 0) + 1), 4, '0', STR_PAD_LEFT);
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

        // Reuse the existing "en grano" line for this product if there's one, so repeated
        // clicks just bump its quantity; otherwise start a new line (e.g. se produjo el
        // mismo producto tanto en grano como molido, lo que requiere dos líneas).
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
                'stock_actual' => $producto->stock,
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

    /**
     * Fires for any wire:model-bound "cart.*" update — used here to clamp a
     * manually typed quantity to a minimum of 1.
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

        $this->cart[$lineKey]['cantidad'] = max(1, (int) $value);
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

    public function vaciarProduccion(): void
    {
        $this->cart = [];
    }

    public function guardarProduccion()
    {
        $this->validate([
            'numero' => ['required', 'string', 'max:255', Rule::unique('producciones', 'numero')],
            'fecha_produccion' => 'required|date',
        ], [], [
            'numero' => 'número de producción',
            'fecha_produccion' => 'fecha de producción',
        ]);

        if (empty($this->cart)) {
            $this->addError('cart', 'Agrega al menos un producto a la producción.');

            return;
        }

        $produccion = DB::transaction(function () {
            $usuario = Auth::user();

            $produccion = Produccion::create([
                'numero' => $this->numero,
                'fecha_produccion' => $this->fecha_produccion,
                'user_id' => $usuario->id,
                'status' => 'enviado',
                'notas' => $this->notas,
            ]);

            foreach ($this->cart as $line) {
                ProduccionItem::create([
                    'produccion_id' => $produccion->id,
                    'producto_id' => $line['producto_id'],
                    'producto_nombre' => $line['nombre'],
                    'producto_codigo' => $line['codigo'],
                    'presentacion' => $line['presentacion'],
                    'molienda' => $line['molienda'],
                    'cantidad' => $line['cantidad'],
                ]);
            }

            Notificacion::create([
                'user_id' => $usuario->id,
                'titulo' => 'Producción registrada',
                'mensaje' => "Se registró la producción #{$produccion->numero} con ".collect($this->cart)->sum('cantidad')." unidades.",
                'tipo' => 'sistema',
            ]);

            return $produccion;
        });

        session()->flash('success', "Producción #{$produccion->numero} registrada correctamente.");

        return redirect()->route('produccion.index');
    }

    public function render()
    {
        return view('livewire.nueva-produccion');
    }
}
