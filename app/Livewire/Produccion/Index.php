<?php

namespace App\Livewire\Produccion;

use App\Livewire\Concerns\GuardaSoloLectura;
use App\Models\MovimientoInventario;
use App\Models\Notificacion;
use App\Models\Produccion;
use App\Models\Producto;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts::app', ['title' => 'Producción', 'subtitle' => 'Registra la producción diaria y su traslado a bodega', 'icon' => 'fire'])]
class Index extends Component
{
    use WithPagination, GuardaSoloLectura;

    public string $search = '';

    public string $statusFiltro = '';

    public ?int $verProduccionId = null;

    public ?int $trasladarProduccionId = null;

    public string $numero_imov = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFiltro(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function producciones()
    {
        return Produccion::query()
            ->with(['usuario', 'items'])
            ->when($this->search, fn ($query) => $query->where('numero', 'like', "%{$this->search}%"))
            ->when($this->statusFiltro, fn ($query) => $query->where('status', $this->statusFiltro))
            ->latest('id')
            ->paginate(20);
    }

    #[Computed]
    public function produccionDetalle()
    {
        return $this->verProduccionId
            ? Produccion::with(['items', 'usuario'])->find($this->verProduccionId)
            : null;
    }

    #[Computed]
    public function produccionATrasladar()
    {
        return $this->trasladarProduccionId
            ? Produccion::find($this->trasladarProduccionId)
            : null;
    }

    public function verProduccion(int $id): void
    {
        $this->verProduccionId = $id;
    }

    public function cerrarModal(): void
    {
        $this->verProduccionId = null;
    }

    public function abrirTraslado(int $id): void
    {
        $produccion = Produccion::findOrFail($id);
        $this->trasladarProduccionId = $produccion->id;
        $this->numero_imov = $produccion->numero_imov ?? '';
    }

    public function cerrarTraslado(): void
    {
        $this->trasladarProduccionId = null;
        $this->numero_imov = '';
        $this->resetErrorBag('numero_imov');
    }

    public function confirmarTraslado(): void
    {
        if ($this->bloquearSoloLectura()) {
            return;
        }

        $this->validate([
            'numero_imov' => 'required|string|max:255',
        ], [], [
            'numero_imov' => 'número de IMOV',
        ]);

        $produccion = Produccion::with('items')->findOrFail($this->trasladarProduccionId);

        if ($produccion->status !== 'enviado') {
            return;
        }

        DB::transaction(function () use ($produccion) {
            $usuario = Auth::user();

            foreach ($produccion->items as $item) {
                if (! $item->producto_id) {
                    continue;
                }

                $producto = Producto::find($item->producto_id);

                if (! $producto) {
                    continue;
                }

                $producto->increment('stock', $item->cantidad);

                MovimientoInventario::create([
                    'producto_id' => $producto->id,
                    'tipo' => 'entrada',
                    'cantidad' => $item->cantidad,
                    'motivo' => "Traslado de producción #{$produccion->numero} (IMOV {$this->numero_imov})",
                    'user_id' => $usuario->id,
                ]);
            }

            $produccion->update([
                'status' => 'trasladado',
                'numero_imov' => $this->numero_imov,
                'trasladado_at' => now(),
            ]);

            Notificacion::create([
                'user_id' => $usuario->id,
                'titulo' => 'Producción trasladada',
                'mensaje' => "La producción #{$produccion->numero} fue trasladada (IMOV {$this->numero_imov}) y se actualizó el inventario.",
                'tipo' => 'stock',
            ]);
        });

        session()->flash('success', "Producción #{$produccion->numero} trasladada correctamente. Inventario actualizado.");

        $this->cerrarTraslado();
    }

    public function eliminarProduccion(int $id): void
    {
        if (! Auth::user()->is_admin || $this->bloquearSoloLectura()) {
            return;
        }

        $produccion = Produccion::with('items')->findOrFail($id);

        DB::transaction(function () use ($produccion) {
            // Si ya se trasladó, el inventario ya se incrementó — hay que revertirlo
            // antes de borrar el registro para que el stock no quede inflado.
            if ($produccion->status === 'trasladado') {
                foreach ($produccion->items as $item) {
                    if (! $item->producto_id) {
                        continue;
                    }

                    $producto = Producto::find($item->producto_id);

                    if (! $producto) {
                        continue;
                    }

                    $producto->decrement('stock', min($item->cantidad, $producto->stock));

                    MovimientoInventario::create([
                        'producto_id' => $producto->id,
                        'tipo' => 'salida',
                        'cantidad' => $item->cantidad,
                        'motivo' => "Eliminación de la producción #{$produccion->numero}",
                        'user_id' => Auth::id(),
                    ]);
                }
            }

            $produccion->delete();
        });

        session()->flash('success', "Producción #{$produccion->numero} eliminada.");
    }

    public function render()
    {
        return view('livewire.produccion.index');
    }
}
