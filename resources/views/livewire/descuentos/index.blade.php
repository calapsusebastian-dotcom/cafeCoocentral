<div>
    @if (session('success'))
        <div class="mb-6 rounded-xl bg-emerald-50 text-emerald-700 text-sm px-4 py-3">{{ session('success') }}</div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-semibold text-gray-900">Descuentos</h2>
                @unless (auth()->user()->solo_lectura)
                    <button type="button" wire:click="nuevoDescuento" class="flex items-center gap-2 px-3 py-2 rounded-xl bg-brand-600 text-white text-xs font-medium hover:bg-brand-700 transition-colors">
                        <x-heroicon-o-plus class="w-4 h-4" />
                        Nuevo
                    </button>
                @endunless
            </div>

            <div class="space-y-2">
                @foreach ($this->descuentos as $descuento)
                    <div class="flex items-center justify-between rounded-xl border border-gray-100 px-4 py-3">
                        <div>
                            <p class="text-sm font-medium text-gray-800">{{ $descuento->nombre }}</p>
                            <p class="text-xs text-gray-400">{{ $descuento->tipo === 'porcentaje' ? $descuento->valor.'%' : '$'.number_format($descuento->valor, 0, ',', '.') }}</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <button type="button" wire:click="toggleDescuento({{ $descuento->id }})" @disabled(auth()->user()->solo_lectura) @class([
                                'relative inline-flex h-5 w-9 items-center rounded-full transition-colors shrink-0',
                                'bg-brand-600' => $descuento->activo,
                                'bg-gray-200' => ! $descuento->activo,
                                'opacity-50 cursor-not-allowed' => auth()->user()->solo_lectura,
                            ])>
                                <span @class(['inline-block h-3.5 w-3.5 transform rounded-full bg-white transition-transform', 'translate-x-4' => $descuento->activo, 'translate-x-1' => ! $descuento->activo])></span>
                            </button>
                            @unless (auth()->user()->solo_lectura)
                                <button type="button" wire:click="editarDescuento({{ $descuento->id }})" class="text-gray-400 hover:text-brand-600">
                                    <x-heroicon-o-pencil-square class="w-4 h-4" />
                                </button>
                                <button type="button" wire:click="eliminarDescuento({{ $descuento->id }})" wire:confirm="¿Eliminar este descuento?" class="text-gray-400 hover:text-red-500">
                                    <x-heroicon-o-trash class="w-4 h-4" />
                                </button>
                            @endunless
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-semibold text-gray-900">Transportadoras</h2>
                @unless (auth()->user()->solo_lectura)
                    <button type="button" wire:click="nuevaTransportadora" class="flex items-center gap-2 px-3 py-2 rounded-xl bg-brand-600 text-white text-xs font-medium hover:bg-brand-700 transition-colors">
                        <x-heroicon-o-plus class="w-4 h-4" />
                        Nueva
                    </button>
                @endunless
            </div>

            <div class="space-y-2">
                @foreach ($this->transportadoras as $transportadora)
                    <div class="flex items-center justify-between rounded-xl border border-gray-100 px-4 py-3">
                        <div>
                            <p class="text-sm font-medium text-gray-800">{{ $transportadora->nombre }}</p>
                            <p class="text-xs text-gray-400">${{ number_format($transportadora->costo, 0, ',', '.') }}</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <button type="button" wire:click="toggleTransportadora({{ $transportadora->id }})" @disabled(auth()->user()->solo_lectura) @class([
                                'relative inline-flex h-5 w-9 items-center rounded-full transition-colors shrink-0',
                                'bg-brand-600' => $transportadora->activo,
                                'bg-gray-200' => ! $transportadora->activo,
                                'opacity-50 cursor-not-allowed' => auth()->user()->solo_lectura,
                            ])>
                                <span @class(['inline-block h-3.5 w-3.5 transform rounded-full bg-white transition-transform', 'translate-x-4' => $transportadora->activo, 'translate-x-1' => ! $transportadora->activo])></span>
                            </button>
                            @unless (auth()->user()->solo_lectura)
                                <button type="button" wire:click="editarTransportadora({{ $transportadora->id }})" class="text-gray-400 hover:text-brand-600">
                                    <x-heroicon-o-pencil-square class="w-4 h-4" />
                                </button>
                                <button type="button" wire:click="eliminarTransportadora({{ $transportadora->id }})" wire:confirm="¿Eliminar esta transportadora?" class="text-gray-400 hover:text-red-500">
                                    <x-heroicon-o-trash class="w-4 h-4" />
                                </button>
                            @endunless
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    @if ($showDescuentoModal)
        <div class="fixed inset-0 z-30 flex items-center justify-center bg-gray-900/40 px-4">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ $editingDescuentoId ? 'Editar descuento' : 'Nuevo descuento' }}</h3>
                <div class="space-y-4">
                    <label class="block">
                        <span class="text-xs text-gray-400">Nombre</span>
                        <input type="text" wire:model="descuento_nombre" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-200" />
                        @error('descuento_nombre') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </label>
                    <label class="block">
                        <span class="text-xs text-gray-400">Tipo</span>
                        <select wire:model="descuento_tipo" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-200">
                            <option value="fijo">Monto fijo</option>
                            <option value="porcentaje">Porcentaje</option>
                        </select>
                    </label>
                    <label class="block">
                        <span class="text-xs text-gray-400">Valor</span>
                        <input type="number" step="0.01" wire:model="descuento_valor" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-200" />
                        @error('descuento_valor') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </label>
                </div>
                <div class="flex items-center justify-end gap-3 mt-6">
                    <button type="button" wire:click="$set('showDescuentoModal', false)" class="px-4 py-2 rounded-xl text-sm text-gray-500 hover:bg-gray-50">Cancelar</button>
                    <button type="button" wire:click="guardarDescuento" class="px-4 py-2 rounded-xl bg-brand-600 text-white text-sm font-medium hover:bg-brand-700">Guardar</button>
                </div>
            </div>
        </div>
    @endif

    @if ($showTransportadoraModal)
        <div class="fixed inset-0 z-30 flex items-center justify-center bg-gray-900/40 px-4">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ $editingTransportadoraId ? 'Editar transportadora' : 'Nueva transportadora' }}</h3>
                <div class="space-y-4">
                    <label class="block">
                        <span class="text-xs text-gray-400">Nombre</span>
                        <input type="text" wire:model="transportadora_nombre" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-200" />
                        @error('transportadora_nombre') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </label>
                    <label class="block">
                        <span class="text-xs text-gray-400">Costo</span>
                        <input type="number" step="0.01" wire:model="transportadora_costo" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-200" />
                        @error('transportadora_costo') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </label>
                </div>
                <div class="flex items-center justify-end gap-3 mt-6">
                    <button type="button" wire:click="$set('showTransportadoraModal', false)" class="px-4 py-2 rounded-xl text-sm text-gray-500 hover:bg-gray-50">Cancelar</button>
                    <button type="button" wire:click="guardarTransportadora" class="px-4 py-2 rounded-xl bg-brand-600 text-white text-sm font-medium hover:bg-brand-700">Guardar</button>
                </div>
            </div>
        </div>
    @endif
</div>
