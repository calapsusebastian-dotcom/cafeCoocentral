<?php

namespace App\Livewire\Rutas;

use App\Livewire\Concerns\GuardaSoloLectura;
use App\Models\Notificacion;
use App\Models\Ruta;
use App\Models\RutaCliente;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts::app', ['title' => 'Rutas', 'subtitle' => 'Organiza la entrega de pedidos por clientes y productos', 'icon' => 'map'])]
class Index extends Component
{
    use WithPagination, GuardaSoloLectura;

    public string $search = '';

    public string $statusFiltro = '';

    public ?int $verRutaId = null;

    public ?int $facturarRutaClienteId = null;

    public string $numero_factura = '';

    public ?int $despacharRutaId = null;

    public string $conductor_nombre = '';

    public string $conductor_cc = '';

    public string $costo_ruta = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFiltro(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function rutas()
    {
        return Ruta::query()
            ->withCount('clientes')
            ->withCount(['clientes as clientes_facturados_count' => fn ($query) => $query->whereNotNull('numero_factura')])
            ->with('usuario')
            ->when($this->search, fn ($query) => $query->where('numero', 'like', "%{$this->search}%")
                ->orWhere('nombre', 'like', "%{$this->search}%"))
            ->when($this->statusFiltro, fn ($query) => $query->where('status', $this->statusFiltro))
            ->latest('id')
            ->paginate(20);
    }

    #[Computed]
    public function rutaDetalle()
    {
        return $this->verRutaId
            ? Ruta::with(['usuario', 'clientes.cliente', 'clientes.productos'])->find($this->verRutaId)
            : null;
    }

    #[Computed]
    public function rutaClienteAFacturar()
    {
        return $this->facturarRutaClienteId
            ? RutaCliente::with('cliente')->find($this->facturarRutaClienteId)
            : null;
    }

    #[Computed]
    public function rutaADespachar()
    {
        return $this->despacharRutaId
            ? Ruta::find($this->despacharRutaId)
            : null;
    }

    public function verRuta(int $id): void
    {
        $this->verRutaId = $id;
    }

    public function cerrarModal(): void
    {
        $this->verRutaId = null;
    }

    public function marcarRecibida(int $id): void
    {
        if ($this->bloquearSoloLectura()) {
            return;
        }

        $ruta = Ruta::findOrFail($id);

        if ($ruta->status !== 'pendiente') {
            return;
        }

        $ruta->update([
            'status' => 'recibida',
            'recibida_at' => now(),
        ]);

        Notificacion::create([
            'user_id' => Auth::id(),
            'titulo' => 'Ruta recibida',
            'mensaje' => "La ruta #{$ruta->numero} fue marcada como recibida.",
            'tipo' => 'sistema',
        ]);

        session()->flash('success', "Ruta #{$ruta->numero} marcada como recibida.");
    }

    public function abrirDespacho(int $id): void
    {
        $ruta = Ruta::findOrFail($id);
        $this->despacharRutaId = $ruta->id;
        $this->conductor_nombre = $ruta->conductor_nombre ?? '';
        $this->conductor_cc = $ruta->conductor_cc ?? '';
        $this->costo_ruta = $ruta->costo_ruta !== null ? (string) $ruta->costo_ruta : '';
    }

    public function cerrarDespacho(): void
    {
        $this->despacharRutaId = null;
        $this->conductor_nombre = '';
        $this->conductor_cc = '';
        $this->costo_ruta = '';
        $this->resetErrorBag(['conductor_nombre', 'conductor_cc', 'costo_ruta']);
    }

    public function confirmarDespacho(): void
    {
        if ($this->bloquearSoloLectura()) {
            return;
        }

        $this->validate([
            'conductor_nombre' => 'required|string|max:255',
            'conductor_cc' => 'required|string|max:50',
            'costo_ruta' => 'required|numeric|min:0',
        ], [], [
            'conductor_nombre' => 'nombre del conductor',
            'conductor_cc' => 'cédula del conductor',
            'costo_ruta' => 'costo de la ruta',
        ]);

        $ruta = Ruta::findOrFail($this->despacharRutaId);

        if ($ruta->status !== 'recibida') {
            return;
        }

        $ruta->update([
            'status' => 'despachada',
            'conductor_nombre' => $this->conductor_nombre,
            'conductor_cc' => $this->conductor_cc,
            'costo_ruta' => $this->costo_ruta,
            'despachada_at' => now(),
        ]);

        Notificacion::create([
            'user_id' => Auth::id(),
            'titulo' => 'Ruta despachada',
            'mensaje' => "La ruta #{$ruta->numero} fue despachada con {$this->conductor_nombre} (CC {$this->conductor_cc}).",
            'tipo' => 'sistema',
        ]);

        $this->cerrarDespacho();

        session()->flash('success', "Ruta #{$ruta->numero} marcada como despachada.");
    }

    public function marcarEntregada(int $id): void
    {
        if ($this->bloquearSoloLectura()) {
            return;
        }

        $ruta = Ruta::findOrFail($id);

        if ($ruta->status !== 'despachada') {
            return;
        }

        $ruta->update([
            'status' => 'entregada',
            'entregada_at' => now(),
        ]);

        Notificacion::create([
            'user_id' => Auth::id(),
            'titulo' => 'Ruta entregada',
            'mensaje' => "La ruta #{$ruta->numero} fue marcada como entregada.",
            'tipo' => 'sistema',
        ]);

        session()->flash('success', "Ruta #{$ruta->numero} marcada como entregada.");
    }

    public function cancelarRuta(int $id): void
    {
        if (! Auth::user()->is_admin || $this->bloquearSoloLectura()) {
            return;
        }

        $ruta = Ruta::findOrFail($id);

        if ($ruta->status === 'entregada') {
            return;
        }

        $ruta->update(['status' => 'cancelada']);

        session()->flash('success', "Ruta #{$ruta->numero} cancelada.");
    }

    public function eliminarRuta(int $id): void
    {
        if (! Auth::user()->is_admin || $this->bloquearSoloLectura()) {
            return;
        }

        $ruta = Ruta::findOrFail($id);
        $ruta->delete();

        session()->flash('success', "Ruta #{$ruta->numero} eliminada.");
    }

    public function abrirFactura(int $rutaClienteId): void
    {
        $rutaCliente = RutaCliente::findOrFail($rutaClienteId);
        $this->facturarRutaClienteId = $rutaCliente->id;
        $this->numero_factura = $rutaCliente->numero_factura ?? '';
    }

    public function cerrarFactura(): void
    {
        $this->facturarRutaClienteId = null;
        $this->numero_factura = '';
        $this->resetErrorBag('numero_factura');
    }

    public function confirmarFactura(): void
    {
        if ($this->bloquearSoloLectura()) {
            return;
        }

        $this->validate([
            'numero_factura' => 'required|string|max:255',
        ], [], ['numero_factura' => 'número de factura']);

        $rutaCliente = RutaCliente::with(['cliente', 'ruta'])->findOrFail($this->facturarRutaClienteId);
        $rutaCliente->update([
            'numero_factura' => $this->numero_factura,
            'facturado_at' => $rutaCliente->facturado_at ?? now(),
        ]);

        Notificacion::create([
            'user_id' => Auth::id(),
            'titulo' => 'Cliente facturado',
            'mensaje' => "Se facturó a {$rutaCliente->cliente->nombre} en la ruta #{$rutaCliente->ruta->numero} con la factura {$this->numero_factura}.",
            'tipo' => 'sistema',
        ]);

        $this->cerrarFactura();

        session()->flash('success', "Cliente facturado correctamente.");
    }

    public function render()
    {
        return view('livewire.rutas.index');
    }
}
