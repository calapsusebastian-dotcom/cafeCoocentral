<x-slot:headerActions>
    <div class="hidden md:flex flex-col items-end leading-tight px-3 py-2 rounded-xl bg-gray-50">
        <span class="text-[11px] text-gray-400">Ruta #</span>
        <span class="text-sm font-semibold text-brand-600">{{ $numero ?: '—' }}</span>
    </div>
</x-slot:headerActions>

<div>
    @if (session('success'))
        <div class="mb-6 rounded-xl bg-emerald-50 text-emerald-700 text-sm px-4 py-3">{{ session('success') }}</div>
    @endif

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 items-start">
        <div class="xl:col-span-2 space-y-6">
            {{-- 1. Información de la ruta --}}
            <section class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 p-6">
                <h2 class="text-sm font-semibold text-gray-900 mb-4">1. Información de la ruta</h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <label class="block">
                        <span class="text-xs text-gray-400">Nombre de la ruta *</span>
                        <input type="text" wire:model="nombre" placeholder="Ej. Zona Norte" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-200" />
                        @error('nombre') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </label>
                    <label class="block">
                        <span class="text-xs text-gray-400">Fecha *</span>
                        <input type="date" wire:model="fecha" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-200" />
                        @error('fecha') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </label>
                </div>

                <label class="block mt-4">
                    <span class="text-xs text-gray-400">Notas</span>
                    <textarea wire:model="notas" rows="2" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-200"></textarea>
                </label>
            </section>

            {{-- 2. Clientes de la ruta --}}
            <section class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-sm font-semibold text-gray-900">2. Clientes de la ruta</h2>
                    <button type="button" wire:click="abrirModalClientes" class="flex items-center gap-1.5 px-3 py-2 rounded-xl bg-brand-50 text-brand-700 text-xs font-medium hover:bg-brand-100 transition-colors">
                        <x-heroicon-o-plus class="w-4 h-4" />
                        Agregar clientes
                    </button>
                </div>

                @error('clientes') <p class="text-xs text-red-500 mb-3">{{ $message }}</p> @enderror

                @if (empty($clientes))
                    <p class="text-sm text-gray-400 py-8 text-center">Agrega los clientes que hacen parte de esta ruta.</p>
                @else
                    <div class="mb-4 flex items-center gap-3 text-xs text-gray-400">
                        <button type="button" wire:click="expandirTodo" class="hover:text-brand-600">Expandir todo</button>
                        <span>·</span>
                        <button type="button" wire:click="colapsarTodo" class="hover:text-brand-600">Colapsar todo</button>
                    </div>

                    <div
                        class="space-y-3"
                        x-data="{
                            dragId: null,
                            dragEl: null,
                            iniciarArrastre(id, event) {
                                this.dragId = id;
                                this.dragEl = event.currentTarget.closest('[data-cliente-card]');
                                event.preventDefault();
                            },
                            duranteArrastre(event) {
                                if (this.dragId === null || ! this.dragEl) return;
                                const el = document.elementFromPoint(event.clientX, event.clientY);
                                const card = el ? el.closest('[data-cliente-card]') : null;
                                if (! card || card === this.dragEl || card.parentNode !== this.dragEl.parentNode) return;
                                const rect = card.getBoundingClientRect();
                                const antesDelCentro = (event.clientY - rect.top) < rect.height / 2;
                                card.parentNode.insertBefore(this.dragEl, antesDelCentro ? card : card.nextSibling);
                            },
                            soltar() {
                                if (this.dragId !== null && this.dragEl) {
                                    const orden = Array.from(this.dragEl.parentNode.querySelectorAll('[data-cliente-card]')).map(el => parseInt(el.dataset.clienteCard, 10));
                                    $wire.reordenarTodos(orden);
                                }
                                this.dragId = null;
                                this.dragEl = null;
                            },
                        }"
                        x-on:pointermove.window="duranteArrastre($event)"
                        x-on:pointerup.window="soltar()"
                        x-on:pointercancel.window="dragId = null; dragEl = null"
                    >
                        @foreach ($clientes as $clienteId => $cliente)
                            @php
                                $numProductos = count($cliente['productos']);
                                $subtotalCliente = collect($cliente['productos'])->sum(fn ($p) => $p['precio_unitario'] * $p['cantidad']);
                                $abierto = ! ($clientesColapsados[$clienteId] ?? false);
                            @endphp
                            <div
                                data-cliente-card="{{ $clienteId }}"
                                class="rounded-xl border border-gray-100 overflow-hidden transition-all bg-white"
                                :class="{ 'opacity-60 shadow-lg': dragId === {{ $clienteId }} }"
                                wire:key="ruta-cliente-{{ $clienteId }}"
                            >
                                <div class="flex items-center justify-between gap-2 p-4 cursor-pointer hover:bg-gray-50 transition-colors" wire:click="toggleClienteAbierto({{ $clienteId }})">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <span
                                            x-on:pointerdown="iniciarArrastre({{ $clienteId }}, $event)"
                                            x-on:click.stop
                                            title="Arrastrar para reordenar"
                                            class="cursor-grab active:cursor-grabbing text-gray-300 hover:text-gray-500 shrink-0 touch-none"
                                        >
                                            <x-heroicon-o-bars-3 class="w-4 h-4" />
                                        </span>
                                        <x-heroicon-o-chevron-right class="w-4 h-4 text-gray-400 shrink-0 transition-transform {{ $abierto ? 'rotate-90' : '' }}" />
                                        <div class="min-w-0">
                                            <p class="text-sm font-semibold text-gray-900 truncate">{{ $cliente['nombre'] }}</p>
                                            <p class="text-xs text-gray-400 truncate">{{ $cliente['documento'] }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3 shrink-0">
                                        <span class="text-xs text-gray-400 hidden sm:inline">{{ $numProductos }} {{ Str::plural('producto', $numProductos) }}</span>
                                        <span class="text-xs font-medium text-gray-700">${{ number_format($subtotalCliente, 0, ',', '.') }}</span>
                                        <div class="flex items-center">
                                            <button type="button" wire:click.stop="moverClienteArriba({{ $clienteId }})" @disabled($loop->first) title="Subir cliente" class="text-gray-300 hover:text-brand-600 disabled:opacity-30 disabled:hover:text-gray-300 transition-colors">
                                                <x-heroicon-o-chevron-up class="w-4 h-4" />
                                            </button>
                                            <button type="button" wire:click.stop="moverClienteAbajo({{ $clienteId }})" @disabled($loop->last) title="Bajar cliente" class="text-gray-300 hover:text-brand-600 disabled:opacity-30 disabled:hover:text-gray-300 transition-colors">
                                                <x-heroicon-o-chevron-down class="w-4 h-4" />
                                            </button>
                                        </div>
                                        <button type="button" wire:click.stop="quitarCliente({{ $clienteId }})" class="text-gray-300 hover:text-red-500 transition-colors">
                                            <x-heroicon-o-x-mark class="w-4 h-4" />
                                        </button>
                                    </div>
                                </div>

                                <div class="px-4 pb-3 flex items-center gap-2">
                                    <span class="text-xs text-gray-400 shrink-0">Medio de pago</span>
                                    <select wire:change="actualizarMedioPago({{ $clienteId }}, $event.target.value)" class="rounded-lg border border-gray-200 text-xs px-2 py-1.5 bg-white focus:outline-none focus:ring-2 focus:ring-brand-200">
                                        <option value="pendiente" @selected(($cliente['medio_pago'] ?? 'pendiente') === 'pendiente')>⏳ Pendiente</option>
                                        <option value="efectivo" @selected(($cliente['medio_pago'] ?? 'pendiente') === 'efectivo')>💵 Efectivo</option>
                                        <option value="transferencia" @selected(($cliente['medio_pago'] ?? 'pendiente') === 'transferencia')>🏦 Transferencia</option>
                                        <option value="credito_30" @selected(($cliente['medio_pago'] ?? 'pendiente') === 'credito_30')>📅 Crédito 30 días</option>
                                        <option value="credito_45" @selected(($cliente['medio_pago'] ?? 'pendiente') === 'credito_45')>📅 Crédito 45 días</option>
                                        <option value="credito_60" @selected(($cliente['medio_pago'] ?? 'pendiente') === 'credito_60')>📅 Crédito 60 días</option>
                                        <option value="credito_90" @selected(($cliente['medio_pago'] ?? 'pendiente') === 'credito_90')>📅 Crédito 90 días</option>
                                    </select>
                                    <span class="text-xs text-gray-400 shrink-0">N.° de orden</span>
                                    <input
                                        type="text"
                                        wire:model.live.debounce.500ms="clientes.{{ $clienteId }}.numero_orden"
                                        placeholder="Opcional"
                                        class="w-28 rounded-lg border border-gray-200 text-xs px-2 py-1.5 bg-white focus:outline-none focus:ring-2 focus:ring-brand-200"
                                    />
                                </div>

                                @if ($abierto)
                                <div class="px-4">
                                @if (empty($cliente['productos']))
                                    <p class="text-xs text-gray-400 py-2 mb-2">Sin productos agregados para este cliente.</p>
                                @else
                                    <div class="space-y-2 mb-3">
                                        @foreach ($cliente['productos'] as $lineKey => $producto)
                                            <div class="bg-gray-50 rounded-lg px-3 py-2.5" wire:key="ruta-cliente-{{ $clienteId }}-producto-{{ $lineKey }}">
                                                <div class="flex items-center justify-between gap-2 mb-2">
                                                    <div class="min-w-0">
                                                        <p class="font-medium text-gray-800 truncate text-xs">{{ $producto['nombre'] }}</p>
                                                        <p class="text-gray-400 text-[11px]">{{ $producto['presentacion'] }}</p>
                                                    </div>
                                                    <button type="button" wire:click="quitarProducto({{ $clienteId }}, '{{ $lineKey }}')" class="text-gray-300 hover:text-red-500 shrink-0">
                                                        <x-heroicon-o-trash class="w-3.5 h-3.5" />
                                                    </button>
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    <select wire:change="actualizarMoliendaProducto({{ $clienteId }}, '{{ $lineKey }}', $event.target.value)" class="flex-1 rounded-lg border border-gray-200 text-[11px] px-1.5 py-1.5 bg-white">
                                                        <option value="entero" @selected(($producto['molienda'] ?? 'entero') === 'entero')>En grano</option>
                                                        <option value="fina" @selected(($producto['molienda'] ?? 'entero') === 'fina')>Fina</option>
                                                        <option value="media" @selected(($producto['molienda'] ?? 'entero') === 'media')>Media</option>
                                                        <option value="gruesa" @selected(($producto['molienda'] ?? 'entero') === 'gruesa')>Gruesa</option>
                                                    </select>
                                                    <div class="relative w-24 shrink-0">
                                                        <span class="absolute left-2 top-1/2 -translate-y-1/2 text-[11px] text-gray-400">$</span>
                                                        <input
                                                            type="number"
                                                            step="0.01"
                                                            min="0"
                                                            wire:model.live.debounce.500ms="clientes.{{ $clienteId }}.productos.{{ $lineKey }}.precio_unitario"
                                                            class="w-full rounded-lg border border-gray-200 text-[11px] pl-4 pr-1.5 py-1.5 bg-white focus:outline-none focus:ring-2 focus:ring-brand-200 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
                                                        />
                                                    </div>
                                                    <div class="inline-flex items-center rounded-lg border border-gray-200 bg-white shrink-0">
                                                        <button type="button" wire:click="decrementarProducto({{ $clienteId }}, '{{ $lineKey }}')" class="w-6 h-6 flex items-center justify-center text-gray-400 hover:text-brand-600">−</button>
                                                        <input
                                                            type="number"
                                                            min="1"
                                                            wire:model.live.debounce.500ms="clientes.{{ $clienteId }}.productos.{{ $lineKey }}.cantidad"
                                                            class="w-9 text-center text-xs border-0 bg-transparent focus:outline-none focus:ring-0 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
                                                        />
                                                        <button type="button" wire:click="incrementarProducto({{ $clienteId }}, '{{ $lineKey }}')" class="w-6 h-6 flex items-center justify-center text-gray-400 hover:text-brand-600">+</button>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                </div>
                                @endif

                                <div class="px-4 pb-4 {{ $abierto ? '' : 'pt-3' }}">
                                    <button type="button" wire:click="abrirModalProductos({{ $clienteId }})" class="flex items-center gap-1.5 text-xs font-medium text-brand-600 hover:text-brand-700">
                                        <x-heroicon-o-plus class="w-3.5 h-3.5" />
                                        Agregar productos
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>
        </div>

        <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 p-6 xl:sticky xl:top-28">
            <h2 class="text-sm font-semibold text-gray-900 mb-4">Resumen de la ruta</h2>

            <div class="space-y-3 text-sm">
                <div class="flex items-center justify-between">
                    <span class="text-gray-500">Clientes</span>
                    <span class="font-medium text-gray-800">{{ count($clientes) }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-gray-500">Unidades totales</span>
                    <span class="font-medium text-gray-800">{{ $this->totalUnidades }}</span>
                </div>
                <div class="flex items-center justify-between border-t border-gray-100 pt-3">
                    <span class="text-gray-500">Valor total</span>
                    <span class="font-semibold text-brand-600">${{ number_format($this->totalValor, 0, ',', '.') }}</span>
                </div>
            </div>

            <button
                type="button"
                wire:click="guardarRuta"
                wire:loading.attr="disabled"
                wire:target="guardarRuta"
                class="mt-6 w-full flex items-center justify-center gap-2 px-4 py-3 rounded-xl bg-brand-600 text-white text-sm font-medium hover:bg-brand-700 disabled:opacity-60 transition-colors"
            >
                <x-heroicon-o-map class="w-4 h-4" />
                <span wire:loading.remove wire:target="guardarRuta">Guardar cambios</span>
                <span wire:loading wire:target="guardarRuta">Guardando...</span>
            </button>
        </div>
    </div>

    {{-- Selector de clientes --}}
    @if ($showClientesModal)
        <div class="fixed inset-0 z-40 flex items-center justify-center bg-gray-900/40 px-4" wire:click.self="cerrarModalClientes">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 max-h-[80vh] flex flex-col">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Agregar clientes</h3>
                    <button type="button" wire:click="cerrarModalClientes" class="text-gray-400 hover:text-gray-600">
                        <x-heroicon-o-x-mark class="w-5 h-5" />
                    </button>
                </div>

                <div class="relative mb-3">
                    <x-heroicon-o-magnifying-glass class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" />
                    <input type="text" wire:model.live.debounce.300ms="modalClienteQuery" placeholder="Buscar cliente..." class="w-full pl-9 pr-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-200" />
                </div>

                <div class="flex-1 overflow-y-auto -mx-2 px-2 space-y-1">
                    @forelse ($this->clientesModalResultados as $cliente)
                        <label class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-gray-50 cursor-pointer">
                            <input
                                type="checkbox"
                                wire:click="toggleClienteSeleccionado({{ $cliente->id }})"
                                @checked(in_array($cliente->id, $clientesSeleccionados))
                                @disabled(isset($clientes[$cliente->id]))
                                class="w-4 h-4 rounded border-gray-300 text-brand-600 focus:ring-brand-200 disabled:opacity-40"
                            />
                            <span class="min-w-0 flex-1">
                                <span class="block text-sm font-medium text-gray-800 truncate">{{ $cliente->nombre }}</span>
                                <span class="block text-xs text-gray-400 truncate">{{ $cliente->documento }} · {{ $cliente->ciudad }}</span>
                            </span>
                            @if (isset($clientes[$cliente->id]))
                                <span class="text-[11px] text-emerald-600 font-medium shrink-0">Agregado</span>
                            @endif
                        </label>
                    @empty
                        <p class="text-sm text-gray-400 py-8 text-center">No se encontraron clientes.</p>
                    @endforelse
                </div>

                <div class="flex items-center justify-end gap-3 mt-4 pt-4 border-t border-gray-100">
                    <button type="button" wire:click="cerrarModalClientes" class="px-4 py-2 rounded-xl text-sm text-gray-500 hover:bg-gray-50">Cancelar</button>
                    <button type="button" wire:click="confirmarClientesSeleccionados" class="px-4 py-2 rounded-xl bg-brand-600 text-white text-sm font-medium hover:bg-brand-700">
                        Agregar seleccionados ({{ count($clientesSeleccionados) }})
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Selector de productos --}}
    @if ($productosModalClienteId)
        <div class="fixed inset-0 z-40 flex items-center justify-center bg-gray-900/40 px-4" wire:click.self="cerrarModalProductos">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 max-h-[80vh] flex flex-col">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Agregar productos</h3>
                    <button type="button" wire:click="cerrarModalProductos" class="text-gray-400 hover:text-gray-600">
                        <x-heroicon-o-x-mark class="w-5 h-5" />
                    </button>
                </div>

                <div class="relative mb-3">
                    <x-heroicon-o-magnifying-glass class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" />
                    <input type="text" wire:model.live.debounce.300ms="modalProductoQuery" placeholder="Buscar producto..." class="w-full pl-9 pr-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-200" />
                </div>

                <div class="flex-1 overflow-y-auto -mx-2 px-2 space-y-1">
                    @forelse ($this->productosModalResultados as $producto)
                        <label class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-gray-50 cursor-pointer">
                            <input
                                type="checkbox"
                                wire:click="toggleProductoSeleccionado({{ $producto->id }})"
                                @checked(in_array($producto->id, $productosSeleccionados))
                                class="w-4 h-4 rounded border-gray-300 text-brand-600 focus:ring-brand-200"
                            />
                            <span class="min-w-0 flex-1">
                                <span class="block text-sm font-medium text-gray-800 truncate">{{ $producto->nombre }}</span>
                                <span class="block text-xs text-gray-400 truncate">{{ $producto->presentacion }} · ${{ number_format($producto->precio, 0, ',', '.') }}</span>
                            </span>
                        </label>
                    @empty
                        <p class="text-sm text-gray-400 py-8 text-center">No se encontraron productos.</p>
                    @endforelse
                </div>

                <div class="flex items-center justify-end gap-3 mt-4 pt-4 border-t border-gray-100">
                    <button type="button" wire:click="cerrarModalProductos" class="px-4 py-2 rounded-xl text-sm text-gray-500 hover:bg-gray-50">Cancelar</button>
                    <button type="button" wire:click="confirmarProductosSeleccionados" class="px-4 py-2 rounded-xl bg-brand-600 text-white text-sm font-medium hover:bg-brand-700">
                        Agregar seleccionados ({{ count($productosSeleccionados) }})
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
