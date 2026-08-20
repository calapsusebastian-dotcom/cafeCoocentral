<div>
    <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 p-6">
        <div class="flex items-center justify-between mb-5">
            <div class="flex items-center gap-2">
                <button type="button" wire:click="$set('filtro', 'todas')" @class(['px-3 py-1.5 rounded-lg text-sm font-medium', 'bg-brand-600 text-white' => $filtro === 'todas', 'text-gray-500 hover:bg-gray-50' => $filtro !== 'todas'])>Todas</button>
                <button type="button" wire:click="$set('filtro', 'no_leidas')" @class(['px-3 py-1.5 rounded-lg text-sm font-medium', 'bg-brand-600 text-white' => $filtro === 'no_leidas', 'text-gray-500 hover:bg-gray-50' => $filtro !== 'no_leidas'])>No leídas</button>
            </div>
            <button type="button" wire:click="marcarTodasLeidas" class="text-sm text-brand-600 hover:text-brand-700 font-medium">Marcar todas como leídas</button>
        </div>

        <div class="space-y-2">
            @forelse ($this->notificaciones as $notificacion)
                <div @class([
                    'flex items-start gap-3 rounded-xl px-4 py-3 transition-colors',
                    'bg-brand-50/50' => ! $notificacion->leida,
                ])>
                    <span @class([
                        'flex items-center justify-center w-9 h-9 rounded-full shrink-0',
                        'bg-brand-100 text-brand-600' => $notificacion->tipo === 'pedido',
                        'bg-amber-100 text-amber-600' => $notificacion->tipo === 'stock',
                        'bg-gray-100 text-gray-500' => $notificacion->tipo === 'sistema',
                    ])>
                        @if ($notificacion->tipo === 'pedido')
                            <x-heroicon-o-shopping-cart class="w-4 h-4" />
                        @elseif ($notificacion->tipo === 'stock')
                            <x-heroicon-o-archive-box class="w-4 h-4" />
                        @else
                            <x-heroicon-o-cog-6-tooth class="w-4 h-4" />
                        @endif
                    </span>

                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-800">{{ $notificacion->titulo }}</p>
                        <p class="text-xs text-gray-500 mt-0.5">{{ $notificacion->mensaje }}</p>
                        <p class="text-[11px] text-gray-400 mt-1">{{ $notificacion->created_at->locale('es')->diffForHumans() }}</p>
                    </div>

                    @unless ($notificacion->leida)
                        <button type="button" wire:click="marcarLeida({{ $notificacion->id }})" class="text-xs font-medium text-brand-600 hover:text-brand-700 shrink-0">Marcar leída</button>
                    @endunless
                </div>
            @empty
                <p class="text-sm text-gray-400 py-10 text-center">No hay notificaciones para mostrar.</p>
            @endforelse
        </div>

        <div class="mt-4">{{ $this->notificaciones->links() }}</div>
    </div>
</div>
