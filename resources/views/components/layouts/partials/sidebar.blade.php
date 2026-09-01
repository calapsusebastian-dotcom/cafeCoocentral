@props(['vendor'])

@php
    $navItems = collect(\App\Support\Modulos::todos())
        ->filter(fn ($modulo, $route) => $vendor->puedeVer($route))
        ->map(fn ($modulo, $route) => ['label' => $modulo['label'], 'route' => $route, 'icon' => $modulo['icon']])
        ->values();

    $initials = collect(explode(' ', $vendor->name))->map(fn ($part) => mb_substr($part, 0, 1))->take(2)->implode('');
@endphp

{{-- Fondo oscuro detrás del panel en móvil/tablet --}}
<div
    x-show="sidebarOpen"
    x-cloak
    @click="sidebarOpen = false"
    class="fixed inset-0 z-40 bg-gray-900/50 lg:hidden"
    x-transition:enter="transition-opacity ease-linear duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition-opacity ease-linear duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
></div>

<aside
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
    class="fixed inset-y-0 left-0 z-50 w-72 shrink-0 flex flex-col bg-white border-r border-gray-100 h-screen transition-transform duration-200 ease-in-out lg:sticky lg:top-0"
>
    <div class="px-6 pt-6 pb-5 flex items-center gap-3">
        <span class="flex items-center justify-center w-10 h-10 rounded-2xl bg-brand-50 text-brand-600">
            <x-icon.coffee-bean class="w-6 h-6" />
        </span>
        <div class="min-w-0">
            <p class="font-semibold text-gray-900 leading-tight truncate">Café Coocentral</p>
            <p class="text-xs text-gray-400 leading-tight truncate">Pasión que sabe a origen</p>
        </div>
        <button type="button" @click="sidebarOpen = false" class="ml-auto lg:hidden text-gray-400 hover:text-gray-600 shrink-0">
            <x-heroicon-o-x-mark class="w-5 h-5" />
        </button>
    </div>

    <nav class="flex-1 px-4 py-2 space-y-1 overflow-y-auto" @click="sidebarOpen = false">
        @foreach ($navItems as $item)
            @php $active = request()->routeIs($item['route']); @endphp
            <a
                href="{{ route($item['route']) }}"
                @class([
                    'flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition-colors',
                    'bg-brand-600 text-white shadow-sm shadow-brand-200' => $active,
                    'text-gray-500 hover:bg-gray-50 hover:text-gray-700' => ! $active,
                ])
            >
                <x-dynamic-component :component="'heroicon-' . ($active ? 's' : 'o') . '-' . $item['icon']" class="w-5 h-5 shrink-0" />
                <span>{{ $item['label'] }}</span>
            </a>
        @endforeach

        <a
            href="{{ route('notificaciones.index') }}"
            @class([
                'flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition-colors',
                'bg-brand-600 text-white shadow-sm shadow-brand-200' => request()->routeIs('notificaciones.index'),
                'text-gray-500 hover:bg-gray-50 hover:text-gray-700' => ! request()->routeIs('notificaciones.index'),
            ])
        >
            <x-dynamic-component :component="'heroicon-' . (request()->routeIs('notificaciones.index') ? 's' : 'o') . '-bell'" class="w-5 h-5 shrink-0" />
            <span>Notificaciones</span>
        </a>

        @if ($vendor->is_admin)
            <a
                href="{{ route('usuarios.index') }}"
                @class([
                    'flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition-colors',
                    'bg-brand-600 text-white shadow-sm shadow-brand-200' => request()->routeIs('usuarios.index'),
                    'text-gray-500 hover:bg-gray-50 hover:text-gray-700' => ! request()->routeIs('usuarios.index'),
                ])
            >
                <x-dynamic-component :component="'heroicon-' . (request()->routeIs('usuarios.index') ? 's' : 'o') . '-user-group'" class="w-5 h-5 shrink-0" />
                <span>Usuarios</span>
            </a>
        @endif
    </nav>

    <div class="relative px-4 pb-5 pt-3 border-t border-gray-100" x-data="{ open: false }">
        <button type="button" @click="open = !open" class="w-full flex items-center gap-3 px-2 py-2 rounded-xl hover:bg-gray-50 transition-colors">
            @if ($vendor->avatar_path)
                <img src="{{ asset('storage/' . $vendor->avatar_path) }}" alt="{{ $vendor->name }}" class="w-9 h-9 rounded-full object-cover shrink-0" />
            @else
                <span class="w-9 h-9 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center text-xs font-semibold shrink-0">
                    {{ $initials }}
                </span>
            @endif
            <span class="text-left flex-1 min-w-0">
                <span class="block text-sm font-semibold text-gray-900 truncate">{{ $vendor->name }}</span>
                <span class="block text-xs text-gray-400 truncate">{{ $vendor->role }}</span>
            </span>
            <x-heroicon-o-chevron-up-down class="w-4 h-4 text-gray-400 shrink-0" />
        </button>

        <div
            x-show="open"
            x-cloak
            @click.outside="open = false"
            class="absolute bottom-full left-4 right-4 mb-2 rounded-xl bg-white shadow-lg ring-1 ring-gray-100 overflow-hidden"
        >
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full flex items-center gap-2 px-4 py-3 text-sm text-red-600 hover:bg-red-50 transition-colors">
                    <x-heroicon-o-arrow-right-start-on-rectangle class="w-4 h-4" />
                    Cerrar sesión
                </button>
            </form>
        </div>
    </div>
</aside>
