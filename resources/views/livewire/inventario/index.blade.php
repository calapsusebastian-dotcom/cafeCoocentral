<div>
    @if (session('success'))
        <div class="mb-6 rounded-xl bg-emerald-50 text-emerald-700 text-sm px-4 py-3">{{ session('success') }}</div>
    @endif

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 items-start">
        <div class="xl:col-span-2 space-y-6">
            <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-sm font-semibold text-gray-900">Stock actual</h2>
                    <div class="relative w-56">
                        <x-heroicon-o-magnifying-glass class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" />
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar producto..." class="w-full pl-9 pr-3 py-2 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-200" />
                    </div>
                </div>

                <div class="overflow-x-auto -mx-2">
                    <table class="w-full text-sm min-w-[500px]">
                        <thead>
                            <tr class="text-left text-xs text-gray-400 border-b border-gray-100">
                                <th class="px-2 py-2 font-medium">Producto</th>
                                <th class="px-2 py-2 font-medium">Categoría</th>
                                <th class="px-2 py-2 font-medium">Presentación</th>
                                <th class="px-2 py-2 font-medium">Stock</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($this->stock as $producto)
                                <tr class="border-b border-gray-50 last:border-0">
                                    <td class="px-2 py-3 font-medium text-gray-800">{{ $producto->nombre }}</td>
                                    <td class="px-2 py-3 text-gray-500">{{ $producto->categoria }}</td>
                                    <td class="px-2 py-3 text-gray-500">{{ $producto->presentacion }}</td>
                                    <td class="px-2 py-3 {{ $producto->stock <= 20 ? 'text-amber-600 font-semibold' : 'text-gray-700 font-medium' }}">{{ $producto->stock }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 p-6">
                <h2 class="text-sm font-semibold text-gray-900 mb-4">Historial de movimientos</h2>
                <div class="overflow-x-auto -mx-2">
                    <table class="w-full text-sm min-w-[600px]">
                        <thead>
                            <tr class="text-left text-xs text-gray-400 border-b border-gray-100">
                                <th class="px-2 py-2 font-medium">Producto</th>
                                <th class="px-2 py-2 font-medium">Tipo</th>
                                <th class="px-2 py-2 font-medium">Cantidad</th>
                                <th class="px-2 py-2 font-medium">Motivo</th>
                                <th class="px-2 py-2 font-medium">Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($this->movimientos as $movimiento)
                                <tr class="border-b border-gray-50 last:border-0">
                                    <td class="px-2 py-3 font-medium text-gray-800">{{ $movimiento->producto?->nombre ?? '—' }}</td>
                                    <td class="px-2 py-3">
                                        <span @class([
                                            'inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium',
                                            'bg-emerald-50 text-emerald-700' => $movimiento->tipo === 'entrada',
                                            'bg-red-50 text-red-700' => $movimiento->tipo === 'salida',
                                        ])>
                                            {{ ucfirst($movimiento->tipo) }}
                                        </span>
                                    </td>
                                    <td class="px-2 py-3 text-gray-700">{{ $movimiento->cantidad }}</td>
                                    <td class="px-2 py-3 text-gray-500">{{ $movimiento->motivo }}</td>
                                    <td class="px-2 py-3 text-gray-400">{{ $movimiento->created_at->format('d/m/Y') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-2 py-8 text-center text-gray-400">Sin movimientos registrados.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">{{ $this->movimientos->links() }}</div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 p-6">
            <h2 class="text-sm font-semibold text-gray-900 mb-4">Registrar movimiento</h2>

            <div class="space-y-4">
                <label class="block">
                    <span class="text-xs text-gray-400">Producto</span>
                    <select wire:model="productoId" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-200">
                        <option value="">Selecciona un producto</option>
                        @foreach ($this->productos as $producto)
                            <option value="{{ $producto->id }}">{{ $producto->nombre }} ({{ $producto->presentacion }})</option>
                        @endforeach
                    </select>
                    @error('productoId') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </label>

                <label class="block">
                    <span class="text-xs text-gray-400">Tipo</span>
                    <select wire:model="tipo" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-200">
                        <option value="entrada">Entrada</option>
                        <option value="salida">Salida</option>
                    </select>
                </label>

                <label class="block">
                    <span class="text-xs text-gray-400">Cantidad</span>
                    <input type="number" wire:model="cantidad" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-200" />
                    @error('cantidad') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </label>

                <label class="block">
                    <span class="text-xs text-gray-400">Motivo</span>
                    <input type="text" wire:model="motivo" placeholder="Ej. Ajuste de inventario" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-200" />
                    @error('motivo') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </label>

                <button type="button" wire:click="registrarMovimiento" class="w-full flex items-center justify-center gap-2 rounded-xl bg-brand-600 text-white font-medium py-2.5 hover:bg-brand-700 transition-colors">
                    <x-heroicon-o-plus class="w-4 h-4" />
                    Registrar movimiento
                </button>
            </div>
        </div>
    </div>
</div>
