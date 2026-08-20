<x-slot:headerActions>
    <a href="{{ route('pedidos-bodega.index') }}" class="hidden md:inline-flex items-center px-3 py-2 rounded-xl border border-gray-200 text-sm text-gray-500 hover:text-gray-700 hover:bg-gray-50 transition-colors">
        Cancelar
    </a>
    <div class="hidden md:flex flex-col items-end leading-tight px-3 py-2 rounded-xl bg-gray-50">
        <span class="text-[11px] text-gray-400">Pedido #</span>
        <span class="text-sm font-semibold text-brand-600">{{ $numero ?: '—' }}</span>
    </div>
</x-slot:headerActions>

<div>
    @if (session('success'))
        <div class="mb-6 rounded-xl bg-emerald-50 text-emerald-700 text-sm px-4 py-3">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 items-start">
        <div class="xl:col-span-2 space-y-6">
            {{-- 1. Información del pedido --}}
            <section class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 p-6">
                <h2 class="text-sm font-semibold text-gray-900 mb-4">1. Información del pedido</h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <label class="block">
                        <span class="text-xs text-gray-400">Bodega *</span>
                        <select wire:model="bodegaId" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-200">
                            <option value="">Selecciona una bodega</option>
                            @foreach ($this->bodegas as $bodega)
                                <option value="{{ $bodega->id }}">{{ $bodega->nombre }}</option>
                            @endforeach
                        </select>
                        @error('bodegaId') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </label>
                    <label class="block">
                        <span class="text-xs text-gray-400">Fecha pedido *</span>
                        <input type="date" wire:model="fecha_pedido" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-200" />
                        @error('fecha_pedido') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </label>
                </div>

                <div class="mt-4">
                    <label class="block">
                        <span class="text-xs text-gray-400">No. pedido</span>
                        <input type="text" wire:model="numero" placeholder="BOD-0001" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-200" />
                        @error('numero') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </label>
                </div>
            </section>

            {{-- 2. Agregar productos --}}
            <section class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 p-6">
                <h2 class="text-sm font-semibold text-gray-900 mb-4">2. Agregar productos</h2>

                <div class="flex items-center gap-2 mb-4">
                    <div class="relative flex-1">
                        <x-heroicon-o-magnifying-glass class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" />
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="productoQuery"
                            placeholder="Buscar producto..."
                            class="w-full pl-9 pr-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-200"
                        />
                    </div>
                </div>

                <div class="relative" x-data="{ scroll(dir) { $refs.track.scrollBy({ left: dir * 176, behavior: 'smooth' }) } }">
                    <button type="button" @click="scroll(-1)" class="hidden sm:flex absolute -left-3 top-1/2 -translate-y-1/2 z-10 w-8 h-8 rounded-full bg-white shadow ring-1 ring-gray-100 items-center justify-center text-gray-400 hover:text-brand-600">
                        <x-heroicon-o-chevron-left class="w-4 h-4" />
                    </button>

                    <div x-ref="track" class="flex gap-4 overflow-x-auto scroll-smooth pb-2 px-1" style="scrollbar-width: none;">
                        @forelse ($this->productosResultados as $producto)
                            <div class="w-40 shrink-0 rounded-2xl border border-gray-100 p-3 flex flex-col">
                                <div class="w-full h-24 rounded-xl bg-gradient-to-br from-brand-50 to-brand-100 flex items-center justify-center text-brand-400 mb-3">
                                    <x-icon.coffee-bean class="w-10 h-10" />
                                </div>
                                <p class="text-sm font-medium text-gray-800 truncate">{{ $producto->nombre }}</p>
                                <p class="text-xs text-gray-400 mb-1">{{ $producto->presentacion }}</p>
                                <p class="text-[11px] text-gray-400 mb-2">Stock actual {{ $producto->stock }}</p>
                                <button
                                    type="button"
                                    wire:click="addProducto({{ $producto->id }})"
                                    class="mt-auto self-end flex items-center justify-center w-8 h-8 rounded-full bg-brand-600 text-white hover:bg-brand-700 transition-colors"
                                >
                                    <x-heroicon-o-plus class="w-4 h-4" />
                                </button>
                            </div>
                        @empty
                            <p class="text-sm text-gray-400 py-6">No se encontraron productos.</p>
                        @endforelse
                    </div>

                    <button type="button" @click="scroll(1)" class="hidden sm:flex absolute -right-3 top-1/2 -translate-y-1/2 z-10 w-8 h-8 rounded-full bg-white shadow ring-1 ring-gray-100 items-center justify-center text-gray-400 hover:text-brand-600">
                        <x-heroicon-o-chevron-right class="w-4 h-4" />
                    </button>
                </div>
            </section>

            {{-- 3. Detalle del pedido --}}
            <section class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 p-6">
                <h2 class="text-sm font-semibold text-gray-900 mb-4">3. Detalle del pedido</h2>

                @if (empty($cart))
                    <p class="text-sm text-gray-400 py-8 text-center">Agrega productos al pedido para verlos aquí.</p>
                @else
                    <div class="w-full overflow-hidden">
                        <table class="w-full text-sm table-fixed">
                            <colgroup>
                                <col class="w-auto">
                                <col class="w-[100px]">
                                <col class="w-[110px]">
                                <col class="w-[110px]">
                                <col class="w-[28px]">
                            </colgroup>
                            <thead>
                                <tr class="text-left text-xs text-gray-400 border-b border-gray-100">
                                    <th class="px-1.5 py-2 font-medium">Producto</th>
                                    <th class="px-1.5 py-2 font-medium">Presentación</th>
                                    <th class="px-1.5 py-2 font-medium">Molienda</th>
                                    <th class="px-1.5 py-2 font-medium">Cantidad</th>
                                    <th class="px-1.5 py-2"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($cart as $id => $line)
                                    <tr class="border-b border-gray-50 last:border-0" wire:key="cart-line-{{ $id }}">
                                        <td class="px-1.5 py-3">
                                            <div class="flex items-center gap-2 min-w-0">
                                                <span class="w-8 h-8 rounded-lg bg-brand-50 text-brand-400 flex items-center justify-center shrink-0">
                                                    <x-icon.coffee-bean class="w-4 h-4" />
                                                </span>
                                                <div class="min-w-0">
                                                    <p class="font-medium text-gray-800 truncate text-sm">{{ $line['nombre'] }}</p>
                                                    <p class="text-[11px] text-gray-400 truncate">{{ $line['codigo'] }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-1.5 py-3 text-gray-500">{{ $line['presentacion'] }}</td>
                                        <td class="px-1.5 py-3">
                                            <select wire:change="actualizarMoliendaLinea('{{ $id }}', $event.target.value)" class="w-full rounded-lg border border-gray-200 text-[11px] px-1.5 py-1.5">
                                                <option value="entero" @selected(($line['molienda'] ?? 'entero') === 'entero')>En grano</option>
                                                <option value="fina" @selected(($line['molienda'] ?? 'entero') === 'fina')>Fina</option>
                                                <option value="media" @selected(($line['molienda'] ?? 'entero') === 'media')>Media</option>
                                                <option value="gruesa" @selected(($line['molienda'] ?? 'entero') === 'gruesa')>Gruesa</option>
                                            </select>
                                        </td>
                                        <td class="px-1.5 py-3">
                                            <div class="inline-flex items-center rounded-lg border border-gray-200">
                                                <button type="button" wire:click="decrementar('{{ $id }}')" class="w-7 h-7 flex items-center justify-center text-gray-400 hover:text-brand-600">−</button>
                                                <span class="w-8 text-center text-sm">{{ $line['cantidad'] }}</span>
                                                <button type="button" wire:click="incrementar('{{ $id }}')" class="w-7 h-7 flex items-center justify-center text-gray-400 hover:text-brand-600">+</button>
                                            </div>
                                        </td>
                                        <td class="px-1.5 py-3 text-right">
                                            <button type="button" wire:click="removerLinea('{{ $id }}')" class="text-gray-300 hover:text-red-500 transition-colors">
                                                <x-heroicon-o-trash class="w-4 h-4" />
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                @error('cart') <p class="text-xs text-red-500 mt-3">{{ $message }}</p> @enderror

                @if (! empty($cart))
                    <button type="button" wire:click="vaciarPedido" class="mt-4 inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-gray-200 text-sm text-gray-500 hover:border-red-200 hover:text-red-500 transition-colors">
                        <x-heroicon-o-trash class="w-4 h-4" />
                        Vaciar pedido
                    </button>
                @endif
            </section>
        </div>

        {{-- Resumen --}}
        <div class="xl:col-span-1">
            <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 p-6 xl:sticky xl:top-28">
                <h2 class="text-sm font-semibold text-gray-900 mb-4">Resumen del pedido</h2>

                <div class="flex items-center justify-between text-sm mb-4">
                    <span class="text-gray-500">Productos distintos</span>
                    <span class="font-medium text-gray-800">{{ count($cart) }}</span>
                </div>
                <div class="flex items-center justify-between border-t border-gray-100 pt-4">
                    <span class="text-sm font-medium text-gray-500">Total unidades</span>
                    <span class="text-2xl font-bold text-brand-600">{{ $this->totalUnidades }}</span>
                </div>

                <button
                    type="button"
                    wire:click="guardarPedido"
                    wire:loading.attr="disabled"
                    wire:target="guardarPedido"
                    class="mt-5 w-full flex items-center justify-center gap-2 rounded-xl bg-brand-600 text-white font-semibold py-3 hover:bg-brand-700 disabled:opacity-60 transition-colors"
                >
                    <x-heroicon-o-check class="w-4 h-4" />
                    <span wire:loading.remove wire:target="guardarPedido">Guardar pedido</span>
                    <span wire:loading wire:target="guardarPedido">Guardando...</span>
                </button>

                <div class="mt-4 rounded-xl bg-amber-50 p-4">
                    <p class="text-xs font-medium text-amber-800 mb-2">Notas del pedido</p>
                    <textarea
                        wire:model="notas"
                        rows="3"
                        placeholder="Escribe aquí alguna nota especial para este pedido..."
                        class="w-full bg-transparent text-sm text-amber-900 placeholder:text-amber-400 focus:outline-none resize-none"
                    ></textarea>
                </div>
            </div>
        </div>
    </div>
</div>
