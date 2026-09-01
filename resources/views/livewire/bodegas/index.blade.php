<div>
    @if (session('success'))
        <div class="mb-6 rounded-xl bg-emerald-50 text-emerald-700 text-sm px-4 py-3">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="mb-6 rounded-xl bg-red-50 text-red-700 text-sm px-4 py-3">{{ session('error') }}</div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 p-6">
        <div class="flex items-center justify-between mb-5">
            <h2 class="text-sm font-semibold text-gray-900">Bodegas</h2>
            @unless (auth()->user()->solo_lectura)
                <button type="button" wire:click="nuevaBodega" class="flex items-center gap-2 px-4 py-2 rounded-xl bg-brand-600 text-white text-sm font-medium hover:bg-brand-700 transition-colors">
                    <x-heroicon-o-plus class="w-4 h-4" />
                    Nueva bodega
                </button>
            @endunless
        </div>

        <div class="space-y-2">
            @forelse ($this->bodegas as $bodega)
                <div class="flex items-center justify-between rounded-xl border border-gray-100 px-4 py-3">
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-gray-800 truncate">{{ $bodega->nombre }}</p>
                        <p class="text-xs text-gray-400 truncate">{{ $bodega->direccion ?: 'Sin dirección' }}</p>
                        @if ($bodega->telefono || $bodega->contacto)
                            <p class="text-xs text-gray-400 truncate">{{ $bodega->contacto }}{{ $bodega->contacto && $bodega->telefono ? ' · ' : '' }}{{ $bodega->telefono }}</p>
                        @endif
                    </div>
                    <div class="flex items-center gap-3 shrink-0">
                        <button type="button" wire:click="toggleBodega({{ $bodega->id }})" @disabled(auth()->user()->solo_lectura) @class([
                            'relative inline-flex h-5 w-9 items-center rounded-full transition-colors shrink-0',
                            'bg-brand-600' => $bodega->activo,
                            'bg-gray-200' => ! $bodega->activo,
                            'opacity-50 cursor-not-allowed' => auth()->user()->solo_lectura,
                        ])>
                            <span @class(['inline-block h-3.5 w-3.5 transform rounded-full bg-white transition-transform', 'translate-x-4' => $bodega->activo, 'translate-x-1' => ! $bodega->activo])></span>
                        </button>
                        @unless (auth()->user()->solo_lectura)
                            <button type="button" wire:click="editarBodega({{ $bodega->id }})" class="text-gray-400 hover:text-brand-600">
                                <x-heroicon-o-pencil-square class="w-4 h-4" />
                            </button>
                            <button type="button" wire:click="eliminarBodega({{ $bodega->id }})" wire:confirm="¿Eliminar esta bodega?" class="text-gray-400 hover:text-red-500">
                                <x-heroicon-o-trash class="w-4 h-4" />
                            </button>
                        @endunless
                    </div>
                </div>
            @empty
                <p class="text-sm text-gray-400 py-10 text-center">No hay bodegas registradas.</p>
            @endforelse
        </div>
    </div>

    @if ($showBodegaModal)
        <div class="fixed inset-0 z-30 flex items-center justify-center bg-gray-900/40 px-4">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ $editingBodegaId ? 'Editar bodega' : 'Nueva bodega' }}</h3>

                <div class="space-y-4">
                    <label class="block">
                        <span class="text-xs text-gray-400">Nombre</span>
                        <input type="text" wire:model="bodega_nombre" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-200" />
                        @error('bodega_nombre') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </label>
                    <label class="block">
                        <span class="text-xs text-gray-400">Dirección</span>
                        <input type="text" wire:model="bodega_direccion" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-200" />
                    </label>
                    <label class="block">
                        <span class="text-xs text-gray-400">Teléfono</span>
                        <input type="text" wire:model="bodega_telefono" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-200" />
                    </label>
                    <label class="block">
                        <span class="text-xs text-gray-400">Contacto</span>
                        <input type="text" wire:model="bodega_contacto" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-200" />
                    </label>
                </div>

                <div class="flex items-center justify-end gap-3 mt-6">
                    <button type="button" wire:click="$set('showBodegaModal', false)" class="px-4 py-2 rounded-xl text-sm text-gray-500 hover:bg-gray-50">Cancelar</button>
                    <button type="button" wire:click="guardarBodega" class="px-4 py-2 rounded-xl bg-brand-600 text-white text-sm font-medium hover:bg-brand-700">Guardar</button>
                </div>
            </div>
        </div>
    @endif
</div>
