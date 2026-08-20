<?php

namespace App\Livewire\Notificaciones;

use App\Models\Notificacion;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts::app', ['title' => 'Notificaciones', 'subtitle' => 'Mantente al tanto de la actividad de tus pedidos', 'icon' => 'bell'])]
class Index extends Component
{
    use WithPagination;

    public string $filtro = 'todas';

    public function updatingFiltro(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function notificaciones()
    {
        return Notificacion::query()
            ->when($this->filtro === 'no_leidas', fn ($query) => $query->unread())
            ->latest('id')
            ->paginate(20);
    }

    public function marcarLeida(int $id): void
    {
        Notificacion::findOrFail($id)->marcarLeida();
    }

    public function marcarTodasLeidas(): void
    {
        Notificacion::unread()->get()->each->marcarLeida();
    }

    public function render()
    {
        return view('livewire.notificaciones.index');
    }
}
