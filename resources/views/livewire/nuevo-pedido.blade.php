<x-slot:headerActions>
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

                <div class="relative mb-5">
                    <div class="flex items-center gap-2">
                        <div class="relative flex-1">
                            <x-heroicon-o-magnifying-glass class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" />
                            <input
                                type="text"
                                wire:model.live.debounce.300ms="clienteQuery"
                                placeholder="Buscar cliente por nombre, documento o código"
                                class="w-full pl-9 pr-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-200 focus:border-brand-400"
                            />
                        </div>
                        <button type="button" class="flex items-center justify-center w-10 h-10 rounded-xl border border-gray-200 text-gray-400 hover:text-brand-600 hover:border-brand-200 transition-colors shrink-0">
                            <x-heroicon-o-user-plus class="w-5 h-5" />
                        </button>
                    </div>

                    @if ($clienteQuery && $this->clientesResultados->isNotEmpty())
                        <div class="absolute z-20 mt-2 w-full bg-white rounded-xl shadow-lg ring-1 ring-gray-100 overflow-hidden">
                            @foreach ($this->clientesResultados as $cliente)
                                <button type="button" wire:click="selectCliente({{ $cliente->id }})" class="w-full text-left px-4 py-2.5 text-sm hover:bg-gray-50 flex flex-col">
                                    <span class="font-medium text-gray-800">{{ $cliente->nombre }}</span>
                                    <span class="text-xs text-gray-400">{{ $cliente->documento }} · {{ $cliente->ciudad }}</span>
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <label class="block">
                        <span class="text-xs text-gray-400">Fecha pedido *</span>
                        <input type="date" wire:model="fecha_pedido" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-200" />
                        @error('fecha_pedido') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </label>
                    <label class="block">
                        <span class="text-xs text-gray-400">Tipo persona *</span>
                        <select wire:model="cliente_tipo_persona" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-200">
                            <option value="natural">Natural</option>
                            <option value="juridica">Jurídica</option>
                        </select>
                    </label>
                </div>

                <div class="mt-4">
                    <label class="block">
                        <span class="text-xs text-gray-400">Nombre / Razón social *</span>
                        <input type="text" wire:model="cliente_nombre" placeholder="Nombre completo o razón social" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-200" />
                        @error('cliente_nombre') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </label>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                    <label class="block">
                        <span class="text-xs text-gray-400">C.C. / NIT *</span>
                        <input type="text" wire:model="cliente_documento" placeholder="000000000" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-200" />
                        @error('cliente_documento') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </label>
                    <label class="block">
                        <span class="text-xs text-gray-400">Email</span>
                        <input type="email" wire:model="cliente_email" placeholder="correo@ejemplo.com" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-200" />
                        @error('cliente_email') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </label>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                    <label class="block">
                        <span class="text-xs text-gray-400">Celular *</span>
                        <input type="text" wire:model="cliente_telefono" placeholder="300 000 0000" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-200" />
                        @error('cliente_telefono') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </label>
                    <label class="block">
                        <span class="text-xs text-gray-400">Ciudad *</span>
                        <input type="text" wire:model="cliente_ciudad" placeholder="Bogotá" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-200" />
                        @error('cliente_ciudad') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </label>
                </div>

                <div class="mt-4">
                    <label class="block">
                        <span class="text-xs text-gray-400">Dirección de entrega *</span>
                        <input type="text" wire:model="direccion_entrega" placeholder="Calle, Carrera, Barrio..." class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-200" />
                        @error('direccion_entrega') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </label>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                    <label class="block">
                        <span class="text-xs text-gray-400">Medio de pago</span>
                        <select wire:model="medio_pago" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-200">
                            <option value="pendiente">⏳ Pendiente</option>
                            <option value="efectivo">💵 Efectivo</option>
                            <option value="transferencia">🏦 Transferencia</option>
                            <option value="credito_30">📅 Crédito 30 días</option>
                            <option value="credito_45">📅 Crédito 45 días</option>
                            <option value="credito_60">📅 Crédito 60 días</option>
                            <option value="credito_90">📅 Crédito 90 días</option>
                        </select>
                    </label>
                    <label class="block">
                        <span class="text-xs text-gray-400">Tipo de precio</span>
                        <select wire:model="cliente_tipo_precio" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-200">
                            <option value="minorista">Minorista</option>
                            <option value="mayorista">Mayorista</option>
                            <option value="distribuidor">Distribuidor</option>
                        </select>
                    </label>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                    <label class="block">
                        <span class="text-xs text-gray-400">No. orden</span>
                        <input type="text" wire:model="numero" placeholder="ORD-0001" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-200" />
                        @error('numero') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </label>
                    <label class="block">
                        <span class="text-xs text-gray-400">Destino del pedido</span>
                        <select wire:model="destino" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-200">
                            <option value="local">Local</option>
                            <option value="web">Web</option>
                        </select>
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
                    <button type="button" class="flex items-center gap-2 px-4 py-2.5 rounded-xl border border-gray-200 text-sm text-gray-500 hover:border-brand-200 hover:text-brand-600 transition-colors shrink-0">
                        <x-heroicon-o-funnel class="w-4 h-4" />
                        Filtrar
                    </button>
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
                                <p class="text-sm font-semibold text-gray-900">${{ number_format($producto->precio, 0, ',', '.') }}</p>
                                <p class="text-[11px] text-gray-400 mb-2">Disponible {{ $producto->stock }}</p>
                                <button
                                    type="button"
                                    wire:click="addProducto({{ $producto->id }})"
                                    @disabled($producto->stock < 1)
                                    class="mt-auto self-end flex items-center justify-center w-8 h-8 rounded-full bg-brand-600 text-white hover:bg-brand-700 disabled:opacity-40 transition-colors"
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
                                <col class="w-[88px]">
                                <col class="w-[88px]">
                                <col class="w-[96px]">
                                <col class="w-[68px]">
                                <col class="w-[84px]">
                                <col class="w-[28px]">
                            </colgroup>
                            <thead>
                                <tr class="text-left text-[11px] text-gray-400 border-b border-gray-100">
                                    <th class="px-1.5 py-2 font-medium">Producto</th>
                                    <th class="px-1.5 py-2 font-medium">Molienda</th>
                                    <th class="px-1.5 py-2 font-medium">Cantidad</th>
                                    <th class="px-1.5 py-2 font-medium">Precio</th>
                                    <th class="px-1.5 py-2 font-medium">Desc.</th>
                                    <th class="px-1.5 py-2 font-medium">Total</th>
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
                                                    <p class="text-[11px] text-gray-400 truncate">{{ $line['codigo'] }} · {{ $line['presentacion'] }}</p>
                                                </div>
                                            </div>
                                        </td>
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
                                                <button type="button" wire:click="decrementar('{{ $id }}')" class="w-6 h-6 flex items-center justify-center text-gray-400 hover:text-brand-600 shrink-0">−</button>
                                                <span class="w-6 text-center text-xs">{{ $line['cantidad'] }}</span>
                                                <button type="button" wire:click="incrementar('{{ $id }}')" class="w-6 h-6 flex items-center justify-center text-gray-400 hover:text-brand-600 shrink-0">+</button>
                                            </div>
                                        </td>
                                        <td class="px-1.5 py-3">
                                            <input
                                                type="number"
                                                step="0.01"
                                                min="0"
                                                wire:model.live.debounce.500ms="cart.{{ $id }}.precio"
                                                class="w-full rounded-lg border border-gray-200 text-[11px] px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-brand-200 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
                                            />
                                        </td>
                                        <td class="px-1.5 py-3">
                                            <select wire:change="actualizarDescuentoLinea('{{ $id }}', $event.target.value)" class="w-full rounded-lg border border-gray-200 text-[11px] px-1 py-1.5">
                                                <option value="0" @selected(($line['descuento_porcentaje'] ?? 0) == 0)>$0</option>
                                                <option value="5" @selected(($line['descuento_porcentaje'] ?? 0) == 5)>5%</option>
                                                <option value="10" @selected(($line['descuento_porcentaje'] ?? 0) == 10)>10%</option>
                                                <option value="15" @selected(($line['descuento_porcentaje'] ?? 0) == 15)>15%</option>
                                            </select>
                                        </td>
                                        <td class="px-1.5 py-3 font-semibold text-gray-900 text-xs">
                                            ${{ number_format($line['precio'] * $line['cantidad'] - $line['descuento_linea'], 0, ',', '.') }}
                                        </td>
                                        <td class="px-1.5 py-3 text-right">
                                            <button type="button" wire:click="removerLinea('{{ $id }}')" class="text-gray-300 hover:text-red-500 transition-colors">
                                                <x-heroicon-o-trash class="w-3.5 h-3.5" />
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

        {{-- Resumen del pedido --}}
        <div class="xl:col-span-1">
            <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 p-6 xl:sticky xl:top-28">
                <h2 class="text-sm font-semibold text-gray-900 mb-4">Resumen del pedido</h2>

                <div class="space-y-3 text-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-gray-500">Subtotal</span>
                        <span class="font-medium text-gray-800">${{ number_format($this->subtotal, 0, ',', '.') }}</span>
                    </div>

                    <div>
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-gray-500 flex items-center gap-1 shrink-0">
                                <x-heroicon-o-truck class="w-4 h-4" />
                                Envío
                            </span>
                            <select wire:model.live="transportadoraId" class="flex-1 rounded-lg border border-gray-200 text-xs px-2 py-1.5 min-w-0">
                                @foreach ($this->transportadoras as $transportadora)
                                    <option value="{{ $transportadora->id }}">{{ $transportadora->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex items-center justify-end gap-1.5 mt-1.5">
                            <span class="text-xs text-gray-400">Costo de envío</span>
                            <span class="text-xs text-gray-400">$</span>
                            <input
                                type="number"
                                step="0.01"
                                min="0"
                                wire:model.live="envioCosto"
                                class="w-24 rounded-lg border border-gray-200 text-xs px-2 py-1 text-right focus:outline-none focus:ring-2 focus:ring-brand-200"
                            />
                        </div>
                        @error('envioCosto') <p class="text-xs text-red-500 mt-1 text-right">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="border-t border-gray-100 mt-4 pt-4 flex items-end justify-between">
                    <span class="text-sm font-medium text-gray-500">Total</span>
                    <div class="text-right">
                        <p class="text-2xl font-bold text-brand-600">${{ number_format($this->total, 0, ',', '.') }}</p>
                        <p class="text-[11px] text-gray-400">COP</p>
                    </div>
                </div>

                <button
                    type="button"
                    wire:click="confirmarPedido"
                    wire:loading.attr="disabled"
                    wire:target="confirmarPedido"
                    class="mt-5 w-full flex items-center justify-center gap-2 rounded-xl bg-brand-600 text-white font-semibold py-3 hover:bg-brand-700 disabled:opacity-60 transition-colors"
                >
                    <x-heroicon-o-check class="w-4 h-4" />
                    <span wire:loading.remove wire:target="confirmarPedido">Confirmar pedido</span>
                    <span wire:loading wire:target="confirmarPedido">Confirmando...</span>
                </button>

                <div class="mt-4 rounded-xl bg-emerald-50 text-emerald-700 text-xs px-4 py-3 flex items-center gap-2">
                    <x-heroicon-o-gift class="w-4 h-4 shrink-0" />
                    <span>Este pedido generará <strong>{{ $this->puntos }} Puntos</strong> para tu cliente</span>
                </div>

                <div class="mt-4 rounded-xl bg-amber-50 p-4">
                    <p class="text-xs font-medium text-amber-800 mb-2">Notas del pedido</p>
                    <textarea
                        wire:model="notas"
                        rows="3"
                        placeholder="Escribe aquí alguna nota especial para este pedido..."
                        class="w-full bg-transparent text-sm text-amber-900 placeholder:text-amber-400 focus:outline-none resize-none"
                    ></textarea>
                </div>

                <p class="mt-4 flex items-center justify-center gap-1.5 text-[11px] text-gray-400">
                    <x-heroicon-o-lock-closed class="w-3.5 h-3.5" />
                    La información de este pedido está segura y encriptada
                </p>
            </div>
        </div>
    </div>
</div>
