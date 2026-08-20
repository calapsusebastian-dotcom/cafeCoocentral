<div>
    @if (session('success'))
        <div class="mb-6 rounded-xl bg-emerald-50 text-emerald-700 text-sm px-4 py-3">{{ session('success') }}</div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 p-6">
        <div class="flex flex-col sm:flex-row gap-3 mb-5">
            <div class="relative flex-1">
                <x-heroicon-o-magnifying-glass class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" />
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar por nombre, SKU o categoría..." class="w-full pl-9 pr-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-200" />
            </div>
            <button type="button" wire:click="nuevo" class="flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-brand-600 text-white text-sm font-medium hover:bg-brand-700 transition-colors shrink-0">
                <x-heroicon-o-plus class="w-4 h-4" />
                Nuevo producto
            </button>
        </div>

        <div class="overflow-x-auto -mx-2">
            <table class="w-full text-sm min-w-[700px]">
                <thead>
                    <tr class="text-left text-xs text-gray-400 border-b border-gray-100">
                        <th class="px-2 py-2 font-medium">Producto</th>
                        <th class="px-2 py-2 font-medium">SKU</th>
                        <th class="px-2 py-2 font-medium">Categoría</th>
                        <th class="px-2 py-2 font-medium">Precio</th>
                        <th class="px-2 py-2 font-medium">Stock</th>
                        <th class="px-2 py-2 font-medium">Estado</th>
                        <th class="px-2 py-2"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($this->productos as $producto)
                        <tr class="border-b border-gray-50 last:border-0">
                            <td class="px-2 py-3">
                                <div class="flex items-center gap-3">
                                    <span class="w-8 h-8 rounded-lg bg-brand-50 text-brand-400 flex items-center justify-center shrink-0">
                                        <x-icon.coffee-bean class="w-4 h-4" />
                                    </span>
                                    <div>
                                        <p class="font-medium text-gray-800">{{ $producto->nombre }}</p>
                                        <p class="text-xs text-gray-400">{{ $producto->presentacion }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-2 py-3 text-gray-500">{{ $producto->sku }}</td>
                            <td class="px-2 py-3 text-gray-500">{{ $producto->categoria }}</td>
                            <td class="px-2 py-3 text-gray-700 font-medium">${{ number_format($producto->precio, 0, ',', '.') }}</td>
                            <td class="px-2 py-3 {{ $producto->stock <= 20 ? 'text-amber-600 font-semibold' : 'text-gray-700' }}">{{ $producto->stock }}</td>
                            <td class="px-2 py-3">
                                <span @class([
                                    'inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium',
                                    'bg-emerald-50 text-emerald-700' => $producto->activo,
                                    'bg-gray-100 text-gray-500' => ! $producto->activo,
                                ])>
                                    {{ $producto->activo ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td class="px-2 py-3 text-right space-x-2">
                                <button type="button" wire:click="editar({{ $producto->id }})" class="text-gray-400 hover:text-brand-600">
                                    <x-heroicon-o-pencil-square class="w-4 h-4 inline" />
                                </button>
                                <button type="button" wire:click="eliminar({{ $producto->id }})" wire:confirm="¿Eliminar este producto?" class="text-gray-400 hover:text-red-500">
                                    <x-heroicon-o-trash class="w-4 h-4 inline" />
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-2 py-10 text-center text-gray-400">No hay productos que coincidan con la búsqueda.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $this->productos->links() }}</div>
    </div>

    @if ($showModal)
        <div class="fixed inset-0 z-30 flex items-center justify-center bg-gray-900/40 px-4">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ $editingId ? 'Editar producto' : 'Nuevo producto' }}</h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <label class="block sm:col-span-2">
                        <span class="text-xs text-gray-400">Nombre</span>
                        <input type="text" wire:model="nombre" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-200" />
                        @error('nombre') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </label>
                    <label class="block">
                        <span class="text-xs text-gray-400">Categoría</span>
                        <input type="text" wire:model="categoria" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-200" />
                        @error('categoria') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </label>
                    <label class="block">
                        <span class="text-xs text-gray-400">Presentación</span>
                        <input type="text" wire:model="presentacion" placeholder="250g" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-200" />
                        @error('presentacion') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </label>
                    <label class="block">
                        <span class="text-xs text-gray-400">SKU</span>
                        <input type="text" wire:model="sku" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-200" />
                        @error('sku') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </label>
                    <label class="block">
                        <span class="text-xs text-gray-400">Precio</span>
                        <input type="number" step="0.01" wire:model="precio" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-200" />
                        @error('precio') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </label>
                    <label class="block">
                        <span class="text-xs text-gray-400">Stock</span>
                        <input type="number" wire:model="stock" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-200" />
                        @error('stock') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </label>
                    <label class="flex items-center gap-2 sm:col-span-2">
                        <input type="checkbox" wire:model="activo" class="rounded border-gray-300 text-brand-600 focus:ring-brand-200" />
                        <span class="text-sm text-gray-600">Producto activo</span>
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
