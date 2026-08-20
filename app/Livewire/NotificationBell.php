<?php

namespace App\Livewire;

use App\Models\Notificacion;
use Livewire\Attributes\Computed;
use Livewire\Component;

class NotificationBell extends Component
{
    public int $lastSeenId = 0;

    public function mount(): void
    {
        $this->lastSeenId = session('notificaciones_ultima_vista_id', Notificacion::max('id') ?? 0);
    }

    #[Computed]
    public function unreadCount(): int
    {
        return Notificacion::unread()->count();
    }

    public function verificarNuevas(): void
    {
        $nuevas = Notificacion::where('id', '>', $this->lastSeenId)->orderBy('id')->get();

        if ($nuevas->isEmpty()) {
            return;
        }

        foreach ($nuevas as $notificacion) {
            $this->dispatch(
                'notificacion-nueva',
                titulo: $notificacion->titulo,
                mensaje: $notificacion->mensaje,
            );
        }

        $this->lastSeenId = $nuevas->max('id');
        session(['notificaciones_ultima_vista_id' => $this->lastSeenId]);

        unset($this->unreadCount);
    }

    public function render()
    {
        return view('livewire.notification-bell');
    }
}
