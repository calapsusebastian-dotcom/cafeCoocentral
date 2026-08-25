<a
    href="{{ route('notificaciones.index') }}"
    wire:poll.30s="verificarNuevas"
    class="relative flex items-center justify-center w-10 h-10 rounded-xl bg-gray-50 text-gray-500 hover:bg-gray-100 transition-colors"
>
    <x-heroicon-o-bell class="w-5 h-5" />
    @if ($this->unreadCount > 0)
        <span class="absolute -top-1 -right-1 flex items-center justify-center min-w-[18px] h-[18px] px-1 rounded-full bg-red-500 text-white text-[10px] font-semibold">
            {{ $this->unreadCount }}
        </span>
    @endif
</a>
