<x-slot:headerActions>
    <a href="{{ route('produccion.nueva') }}" class="flex items-center gap-2 px-4 py-2.5 rounded-xl bg-brand-600 text-white text-sm font-medium hover:bg-brand-700 transition-colors">
        <x-heroicon-o-plus class="w-4 h-4" />
        Nueva producción
    </a>
</x-slot:headerActions>

<div>
    @if (session('success'))
        <div class="mb-6 rounded-xl bg-emerald-50 text-emerald-700 text-sm px-4 py-3">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="mb-6 rounded-xl bg-red-50 text-red-700 text-sm px-4 py-3">{{ session('error') }}</div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 p-6">
        <div class="flex flex-col sm:flex-row gap-3 mb-5">
            <div class="relative flex-1">
                <x-heroicon-o-magnifying-glass class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" />
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar por número de producción..." class="w-full pl-9 pr-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-200" />
            </div>
            <select wire:model.live="statusFiltro" class="rounded-xl border border-gray-200 text-sm px-3 py-2.5">
                <option value="">Todos los estados</option>
                <option value="enviado">Enviado</option>
                <option value="trasladado">Trasladado</option>
            </select>
        </div>

        <div class="overflow-x-auto -mx-2">
            <table class="w-full text-sm min-w-[600px]">
                <thead>
                    <tr class="text-left text-xs text-gray-400 border-b border-gray-100">
                        <th class="px-2 py-2 font-medium">Producción</th>
                        <th class="px-2 py-2 font-medium">Fecha</th>
                        <th class="px-2 py-2 font-medium">Unidades</th>
                        <th class="px-2 py-2 font-medium">N.° IMOV</th>
                        <th class="px-2 py-2 font-medium">Estado</th>
                        <th class="px-2 py-2"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($this->producciones as $produccion)
                        @php
                            $estilos = $produccion->status === 'trasladado' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700';
                            $etiqueta = ucfirst($produccion->status);
                        @endphp
                        <tr class="border-b border-gray-50 last:border-0">
                            <td class="px-2 py-3 font-medium text-brand-600">#{{ $produccion->numero }}</td>
                            <td class="px-2 py-3 text-gray-500">{{ $produccion->fecha_produccion?->format('d/m/Y') }}</td>
                            <td class="px-2 py-3 text-gray-700">{{ $produccion->items->sum('cantidad') }}</td>
                            <td class="px-2 py-3 text-gray-500">{{ $produccion->numero_imov ?: '—' }}</td>
                            <td class="px-2 py-3">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $estilos }}">{{ $etiqueta }}</span>
                            </td>
                            <td class="px-2 py-3 text-right space-x-2">
                                <button type="button" wire:click="verProduccion({{ $produccion->id }})" class="text-gray-400 hover:text-brand-600">
                                    <x-heroicon-o-eye class="w-4 h-4 inline" />
                                </button>
                                <a href="{{ route('produccion.imprimir', $produccion) }}" target="_blank" title="Imprimir" class="text-gray-400 hover:text-brand-600">
                                    <x-heroicon-o-printer class="w-4 h-4 inline" />
                                </a>
                                @if ($produccion->status === 'enviado')
                                    <a href="{{ route('produccion.editar', $produccion) }}" title="Editar" class="text-gray-400 hover:text-brand-600">
                                        <x-heroicon-o-pencil-square class="w-4 h-4 inline" />
                                    </a>
                                    <button type="button" wire:click="abrirTraslado({{ $produccion->id }})" title="Marcar como trasladado" class="text-gray-400 hover:text-emerald-600">
                                        <x-heroicon-o-truck class="w-4 h-4 inline" />
                                    </button>
                                @endif
                                @if (auth()->user()->is_admin)
                                    <button
                                        type="button"
                                        wire:click="eliminarProduccion({{ $produccion->id }})"
                                        wire:confirm="¿Eliminar la producción #{{ $produccion->numero }}? Esta acción no se puede deshacer."
                                        title="Eliminar producción"
                                        class="text-gray-400 hover:text-red-500"
                                    >
                                        <x-heroicon-o-trash class="w-4 h-4 inline" />
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-2 py-10 text-center text-gray-400">No hay producciones que coincidan con la búsqueda.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $this->producciones->links() }}
        </div>
    </div>

    {{-- Detalle de la producción --}}
    @if ($this->produccionDetalle)
        <div class="fixed inset-0 z-30 flex items-center justify-center bg-gray-900/40 px-4" wire:click.self="cerrarModal">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg p-6 max-h-[85vh] overflow-y-auto">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <p class="text-xs text-gray-400">Producción</p>
                        <h3 class="text-lg font-semibold text-gray-900">#{{ $this->produccionDetalle->numero }}</h3>
                    </div>
                    <div class="flex items-center gap-3">
                        <a href="{{ route('produccion.imprimir', $this->produccionDetalle) }}" target="_blank" class="flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700 font-medium">
                            <x-heroicon-o-printer class="w-4 h-4" />
                            Imprimir
                        </a>
                        @if ($this->produccionDetalle->status === 'enviado')
                            <a href="{{ route('produccion.editar', $this->produccionDetalle) }}" class="flex items-center gap-1.5 text-sm text-brand-600 hover:text-brand-700 font-medium">
                                <x-heroicon-o-pencil-square class="w-4 h-4" />
                                Editar
                            </a>
                        @endif
                        @if (auth()->user()->is_admin)
                            <button type="button" wire:click="eliminarProduccion({{ $this->produccionDetalle->id }})" wire:confirm="¿Eliminar la producción #{{ $this->produccionDetalle->numero }}? Esta acción no se puede deshacer." class="flex items-center gap-1.5 text-sm text-gray-500 hover:text-red-500 font-medium">
                                <x-heroicon-o-trash class="w-4 h-4" />
                                Eliminar
                            </button>
                        @endif
                        <button type="button" wire:click="cerrarModal" class="text-gray-400 hover:text-gray-600">
                            <x-heroicon-o-x-mark class="w-5 h-5" />
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 text-sm mb-4">
                    <div><p class="text-xs text-gray-400">Fecha</p><p class="text-gray-800 font-medium">{{ $this->produccionDetalle->fecha_produccion?->format('d/m/Y') }}</p></div>
                    <div>
                        <p class="text-xs text-gray-400">Estado</p>
                        @php
                            $estilos = $this->produccionDetalle->status === 'trasladado' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700';
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $estilos }}">{{ ucfirst($this->produccionDetalle->status) }}</span>
                    </div>
                    <div><p class="text-xs text-gray-400">Registrado por</p><p class="text-gray-800 font-medium">{{ $this->produccionDetalle->usuario->name }}</p></div>
                    <div><p class="text-xs text-gray-400">N.° IMOV</p><p class="text-gray-800 font-medium">{{ $this->produccionDetalle->numero_imov ?: '—' }}</p></div>
                </div>

                <div class="border-t border-gray-100 pt-4 space-y-2">
                    @foreach ($this->produccionDetalle->items as $item)
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600">{{ $item->producto_nombre }} <span class="text-gray-400">({{ $item->presentacion }} · {{ \App\Models\PedidoItem::MOLIENDAS[$item->molienda] ?? $item->molienda }})</span></span>
                            <span class="font-medium text-gray-800">{{ $item->cantidad }} uds.</span>
                        </div>
                    @endforeach
                </div>

                <div class="border-t border-gray-100 mt-4 pt-4 flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-500">Total unidades</span>
                    <span class="text-lg font-bold text-brand-600">{{ $this->produccionDetalle->items->sum('cantidad') }}</span>
                </div>

                @if ($this->produccionDetalle->notas)
                    <div class="mt-4 rounded-xl bg-amber-50 p-3 text-xs text-amber-800">{{ $this->produccionDetalle->notas }}</div>
                @endif
            </div>
        </div>
    @endif

    {{-- Marcar como trasladado --}}
    @if ($this->produccionATrasladar)
        <div class="fixed inset-0 z-40 flex items-center justify-center bg-gray-900/40 px-4" wire:click.self="cerrarTraslado">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
                <div class="flex items-start justify-between mb-1">
                    <div class="flex items-center gap-3">
                        <span class="flex items-center justify-center w-10 h-10 rounded-xl bg-brand-50 text-brand-600 shrink-0">
                            <x-heroicon-o-truck class="w-5 h-5" />
                        </span>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Marcar como trasladado</h3>
                            <p class="text-xs text-gray-400">Producción #{{ $this->produccionATrasladar->numero }}</p>
                        </div>
                    </div>
                    <button type="button" wire:click="cerrarTraslado" class="text-gray-400 hover:text-gray-600">
                        <x-heroicon-o-x-mark class="w-5 h-5" />
                    </button>
                </div>

                <p class="text-xs text-gray-400 mt-4">Al confirmar, las cantidades de esta producción se sumarán al inventario.</p>

                <div class="mt-4">
                    <label class="block">
                        <span class="text-xs text-gray-400">Número de IMOV *</span>
                        <input
                            type="text"
                            wire:model="numero_imov"
                            placeholder="Ej. IMOV-00123"
                            class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-200"
                        />
                        @error('numero_imov') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </label>
                </div>

                <div class="flex items-center justify-end gap-3 mt-6">
                    <button type="button" wire:click="cerrarTraslado" class="px-4 py-2 rounded-xl text-sm text-gray-500 hover:bg-gray-50">Cancelar</button>
                    <button type="button" wire:click="confirmarTraslado" class="flex items-center gap-2 px-4 py-2 rounded-xl bg-brand-600 text-white text-sm font-medium hover:bg-brand-700">
                        <x-heroicon-o-truck class="w-4 h-4" />
                        Confirmar traslado
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
