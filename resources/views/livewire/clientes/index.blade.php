<div>
    @if (session('success'))
        <div class="mb-6 rounded-xl bg-emerald-50 text-emerald-700 text-sm px-4 py-3">{{ session('success') }}</div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 p-6">
        <div class="flex flex-col sm:flex-row gap-3 mb-5">
            <div class="relative flex-1">
                <x-heroicon-o-magnifying-glass class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" />
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar por nombre, documento, código, email o ciudad..." class="w-full pl-9 pr-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-200" />
            </div>
            @unless (auth()->user()->solo_lectura)
                <button type="button" wire:click="nuevo" class="flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-brand-600 text-white text-sm font-medium hover:bg-brand-700 transition-colors shrink-0">
                    <x-heroicon-o-plus class="w-4 h-4" />
                    Nuevo cliente
                </button>
            @endunless
        </div>

        <div class="overflow-x-auto -mx-2">
            <table class="w-full text-sm min-w-[700px]">
                <thead>
                    <tr class="text-left text-xs text-gray-400 border-b border-gray-100">
                        <th class="px-2 py-2 font-medium">Cliente</th>
                        <th class="px-2 py-2 font-medium">Documento</th>
                        <th class="px-2 py-2 font-medium">Contacto</th>
                        <th class="px-2 py-2 font-medium">Ciudad</th>
                        <th class="px-2 py-2 font-medium">Tipo de precio</th>
                        <th class="px-2 py-2 font-medium">Puntos</th>
                        <th class="px-2 py-2"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($this->clientes as $cliente)
                        <tr class="border-b border-gray-50 last:border-0">
                            <td class="px-2 py-3">
                                <p class="font-medium text-gray-800">{{ $cliente->nombre }}</p>
                                <p class="text-xs text-gray-400 capitalize">{{ $cliente->tipo_persona }}</p>
                            </td>
                            <td class="px-2 py-3 text-gray-500">{{ $cliente->documento }}</td>
                            <td class="px-2 py-3 text-gray-500">
                                <p>{{ $cliente->telefono ?: '—' }}</p>
                                <p class="text-xs text-gray-400">{{ $cliente->email ?: '—' }}</p>
                            </td>
                            <td class="px-2 py-3 text-gray-500">{{ $cliente->ciudad }}</td>
                            <td class="px-2 py-3 text-gray-500 capitalize">{{ $cliente->tipo_precio }}</td>
                            <td class="px-2 py-3 text-gray-700 font-medium">{{ $cliente->puntos }}</td>
                            <td class="px-2 py-3 text-right space-x-2">
                                @unless (auth()->user()->solo_lectura)
                                    <button type="button" wire:click="editar({{ $cliente->id }})" class="text-gray-400 hover:text-brand-600">
                                        <x-heroicon-o-pencil-square class="w-4 h-4 inline" />
                                    </button>
                                    <button type="button" wire:click="eliminar({{ $cliente->id }})" wire:confirm="¿Eliminar este cliente?" class="text-gray-400 hover:text-red-500">
                                        <x-heroicon-o-trash class="w-4 h-4 inline" />
                                    </button>
                                @endunless
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-2 py-10 text-center text-gray-400">No hay clientes que coincidan con la búsqueda.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $this->clientes->links() }}</div>
    </div>

    @if ($showModal)
        <div class="fixed inset-0 z-30 flex items-center justify-center bg-gray-900/40 px-4">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ $editingId ? 'Editar cliente' : 'Nuevo cliente' }}</h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <label class="block sm:col-span-2">
                        <span class="text-xs text-gray-400">Nombre / Razón social</span>
                        <input type="text" wire:model="nombre" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-200" />
                        @error('nombre') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </label>
                    <label class="block">
                        <span class="text-xs text-gray-400">Tipo persona</span>
                        <select wire:model="tipo_persona" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-200">
                            <option value="natural">Natural</option>
                            <option value="juridica">Jurídica</option>
                        </select>
                    </label>
                    <label class="block">
                        <span class="text-xs text-gray-400">C.C. / NIT</span>
                        <input type="text" wire:model="documento" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-200" />
                        @error('documento') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </label>
                    <label class="block">
                        <span class="text-xs text-gray-400">Código</span>
                        <input type="text" wire:model="codigo" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-200" />
                        @error('codigo') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </label>
                    <label class="block">
                        <span class="text-xs text-gray-400">Celular</span>
                        <input type="text" wire:model="telefono" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-200" />
                    </label>
                    <label class="block">
                        <span class="text-xs text-gray-400">Email</span>
                        <input type="email" wire:model="email" placeholder="correo@ejemplo.com" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-200" />
                        @error('email') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </label>
                    <label class="block">
                        <span class="text-xs text-gray-400">Tipo de precio</span>
                        <select wire:model="tipo_precio" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-200">
                            <option value="minorista">Minorista</option>
                            <option value="mayorista">Mayorista</option>
                            <option value="distribuidor">Distribuidor</option>
                        </select>
                    </label>
                    <label class="block">
                        <span class="text-xs text-gray-400">Ciudad</span>
                        <input type="text" wire:model="ciudad" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-200" />
                    </label>
                    <label class="block sm:col-span-2">
                        <span class="text-xs text-gray-400">Dirección</span>
                        <input type="text" wire:model="direccion" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-200" />
                    </label>
                </div>

                <div class="flex items-center justify-end gap-3 mt-6">
                    <button type="button" wire:click="$set('showModal', false)" class="px-4 py-2 rounded-xl text-sm text-gray-500 hover:bg-gray-50">Cancelar</button>
                    <button type="button" wire:click="guardar" class="px-4 py-2 rounded-xl bg-brand-600 text-white text-sm font-medium hover:bg-brand-700">Guardar</button>
                </div>
            </div>
        </div>
    @endif
</div>
