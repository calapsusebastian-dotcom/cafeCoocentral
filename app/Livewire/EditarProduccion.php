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

#[Layout('layouts::app', ['title' => 'Editar Producción', 'subtitle' => 'Actualiza este registro antes de trasladarlo', 'icon' => 'fire'])]
class EditarProduccion extends Component
{
    public Produccion $produccion;

    public string $numero = '';

    public string $fecha_produccion = '';

    public string $productoQuery = '';

    public array $cart = [];

    public string $notas = '';

    public function mount(Produccion $produccion): void
    {
        if ($produccion->status !== 'enviado') {
            session()->flash('error', "La producción #{$produccion->numero} ya no se puede editar porque está \"{$produccion->status}\".");

            $this->redirectRoute('produccion.index');

            return;
        }

        $produccion->load('items');

        $this->produccion = $produccion;
        $this->numero = $produccion->numero ?? '';
        $this->fecha_produccion = $produccion->fecha_produccion?->format('Y-m-d') ?? now()->format('Y-m-d');
        $this->notas = $produccion->notas ?? '';

        foreach ($produccion->items as $item) {
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
            'numero' => ['required', 'string', 'max:255', Rule::unique('producciones', 'numero')->ignore($this->produccion->id)],
            'fecha_produccion' => 'required|date',
        ], [], [
            'numero' => 'número de producción',
            'fecha_produccion' => 'fecha de producción',
        ]);

        if (empty($this->cart)) {
            $this->addError('cart', 'La producción debe tener al menos un producto.');

            return;
        }

        DB::transaction(function () {
            $this->produccion->items()->delete();

            foreach ($this->cart as $line) {
                ProduccionItem::create([
                    'produccion_id' => $this->produccion->id,
                    'producto_id' => $line['producto_id'],
                    'producto_nombre' => $line['nombre'],
                    'producto_codigo' => $line['codigo'],
                    'presentacion' => $line['presentacion'],
                    'molienda' => $line['molienda'],
                    'cantidad' => $line['cantidad'],
                ]);
            }

            $this->produccion->update([
                'numero' => $this->numero,
                'fecha_produccion' => $this->fecha_produccion,
                'notas' => $this->notas,
            ]);

            Notificacion::create([
                'user_id' => Auth::id(),
                'titulo' => 'Producción actualizada',
                'mensaje' => "Se actualizó la producción #{$this->numero}.",
                'tipo' => 'sistema',
            ]);
        });

        session()->flash('success', "Producción #{$this->numero} actualizada correctamente.");

        return redirect()->route('produccion.index');
    }

    public function render()
    {
        return view('livewire.editar-produccion');
    }
}
