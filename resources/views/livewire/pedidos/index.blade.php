<div>
    @if (session('success'))
        <div class="mb-6 rounded-xl bg-emerald-50 text-emerald-700 text-sm px-4 py-3">{{ session('success') }}</div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 p-6">
        <div class="flex flex-col sm:flex-row gap-3 mb-5">
            <div class="relative flex-1">
                <x-heroicon-o-magnifying-glass class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" />
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar por número de pedido o cliente..." class="w-full pl-9 pr-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-200" />
            </div>
            <select wire:model.live="statusFiltro" class="rounded-xl border border-gray-200 text-sm px-3 py-2.5">
                <option value="">Todos los estados</option>
                <option value="pendiente">Pendiente</option>
                <option value="confirmado">Confirmado</option>
                <option value="enviado">Despachado</option>
                <option value="entregado">Entregado</option>
                <option value="cancelado">Cancelado</option>
            </select>
        </div>

        <div class="overflow-x-auto -mx-2">
            <table class="w-full text-sm min-w-[700px]">
                <thead>
                    <tr class="text-left text-xs text-gray-400 border-b border-gray-100">
                        <th class="px-2 py-2 font-medium">Pedido</th>
                        <th class="px-2 py-2 font-medium">Cliente</th>
                        <th class="px-2 py-2 font-medium">Vendedor</th>
                        <th class="px-2 py-2 font-medium">Fecha</th>
                        <th class="px-2 py-2 font-medium">Total</th>
                        <th class="px-2 py-2 font-medium">Estado</th>
                        <th class="px-2 py-2"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($this->pedidos as $pedido)
                        <tr class="border-b border-gray-50 last:border-0">
                            <td class="px-2 py-3 font-medium text-brand-600">#{{ $pedido->numero }}</td>
                            <td class="px-2 py-3 text-gray-700">{{ $pedido->cliente->nombre }}</td>
                            <td class="px-2 py-3 text-gray-500">{{ $pedido->vendedor->name }}</td>
                            <td class="px-2 py-3 text-gray-500">{{ $pedido->created_at->format('d/m/Y') }}</td>
                            <td class="px-2 py-3 font-semibold text-gray-900">${{ number_format($pedido->total, 0, ',', '.') }}</td>
                            <td class="px-2 py-3">
                                <div class="flex items-center gap-2">
                                    <x-pedido-status :status="$pedido->status" />
                                    @if ($pedido->numero_factura)
                                        <span title="Factura #{{ $pedido->numero_factura }}" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700">
                                            Facturado
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-2 py-3 text-right space-x-2">
                                <button type="button" wire:click="verPedido({{ $pedido->id }})" class="text-gray-400 hover:text-brand-600">
                                    <x-heroicon-o-eye class="w-4 h-4 inline" />
                                </button>
                                <a href="{{ route('pedidos.editar', $pedido) }}" class="text-gray-400 hover:text-brand-600">
                                    <x-heroicon-o-pencil-square class="w-4 h-4 inline" />
                                </a>
                                @unless ($pedido->status === 'cancelado')
                                    <button type="button" wire:click="abrirDespacho({{ $pedido->id }})" title="Marcar como despachado" class="text-gray-400 hover:text-brand-600">
                                        <x-heroicon-o-truck class="w-4 h-4 inline" />
                                    </button>
                                    <button type="button" wire:click="abrirFactura({{ $pedido->id }})" title="{{ $pedido->numero_factura ? 'Editar factura' : 'Facturar pedido' }}" class="text-gray-400 hover:text-brand-600">
                                        <x-heroicon-o-document-text class="w-4 h-4 inline" />
                                    </button>
                                @endunless
                                @if (auth()->user()->is_admin)
                                    <button
                                        type="button"
                                        wire:click="eliminarPedido({{ $pedido->id }})"
                                        wire:confirm="¿Eliminar el pedido #{{ $pedido->numero }}? Esta acción no se puede deshacer y restaurará el stock de los productos."
                                        title="Eliminar pedido"
                                        class="text-gray-400 hover:text-red-500"
                                    >
                                        <x-heroicon-o-trash class="w-4 h-4 inline" />
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-2 py-10 text-center text-gray-400">No hay pedidos que coincidan con la búsqueda.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $this->pedidos->links() }}
        </div>
    </div>

    @if ($this->pedidoDetalle)
        <div class="fixed inset-0 z-30 flex items-center justify-center bg-gray-900/40 px-4" wire:click.self="cerrarModal">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg p-6 max-h-[85vh] overflow-y-auto">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <p class="text-xs text-gray-400">Pedido</p>
                        <h3 class="text-lg font-semibold text-gray-900">#{{ $this->pedidoDetalle->numero }}</h3>
                    </div>
                    <div class="flex items-center gap-3">
                        <a href="{{ route('pedidos.imprimir', $this->pedidoDetalle) }}" target="_blank" class="flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700 font-medium">
                            <x-heroicon-o-printer class="w-4 h-4" />
                            Imprimir
                        </a>
                        <a href="{{ route('pedidos.editar', $this->pedidoDetalle) }}" class="flex items-center gap-1.5 text-sm text-brand-600 hover:text-brand-700 font-medium">
                            <x-heroicon-o-pencil-square class="w-4 h-4" />
                            Editar
                        </a>
                        <button type="button" wire:click="cerrarModal" class="text-gray-400 hover:text-gray-600">
                            <x-heroicon-o-x-mark class="w-5 h-5" />
                        </button>
                    </div>
                </div>

                <div class="rounded-xl bg-gray-50 p-4 mb-4">
                    <div class="flex items-start justify-between mb-3">
                        <div>
                            <p class="text-sm font-semibold text-gray-900">{{ $this->pedidoDetalle->cliente->nombre }}</p>
                            <p class="text-xs text-gray-400">{{ $this->pedidoDetalle->cliente->documento }}</p>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-200 text-gray-700 capitalize">
                                {{ $this->pedidoDetalle->cliente->tipo_persona }}
                            </span>
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-brand-50 text-brand-700 capitalize">
                                {{ $this->pedidoDetalle->cliente->tipo_precio }}
                            </span>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <div><p class="text-xs text-gray-400">Celular</p><p class="text-gray-700">{{ $this->pedidoDetalle->cliente->telefono ?: '—' }}</p></div>
                        <div><p class="text-xs text-gray-400">Email</p><p class="text-gray-700">{{ $this->pedidoDetalle->cliente->email ?: '—' }}</p></div>
                        <div><p class="text-xs text-gray-400">Ciudad</p><p class="text-gray-700">{{ $this->pedidoDetalle->cliente->ciudad ?: '—' }}</p></div>
                        <div><p class="text-xs text-gray-400">Código</p><p class="text-gray-700">{{ $this->pedidoDetalle->cliente->codigo ?: '—' }}</p></div>
                        <div class="col-span-2"><p class="text-xs text-gray-400">Dirección de entrega</p><p class="text-gray-700">{{ $this->pedidoDetalle->direccion_entrega ?: '—' }}</p></div>
                        <div><p class="text-xs text-gray-400">Puntos</p><p class="text-gray-700">{{ $this->pedidoDetalle->cliente->puntos }}</p></div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 text-sm mb-4">
                    <div><p class="text-xs text-gray-400">Fecha pedido</p><p class="text-gray-800 font-medium">{{ $this->pedidoDetalle->fecha_pedido?->format('d/m/Y') ?? '—' }}</p></div>
                    <div><p class="text-xs text-gray-400">Destino</p><p class="text-gray-800 font-medium">{{ $this->pedidoDetalle->destinoLabel() }}</p></div>
                    <div><p class="text-xs text-gray-400">Vendedor</p><p class="text-gray-800 font-medium">{{ $this->pedidoDetalle->vendedor->name }}</p></div>
                    <div><p class="text-xs text-gray-400">Estado</p><x-pedido-status :status="$this->pedidoDetalle->status" /></div>
                    <div><p class="text-xs text-gray-400">Medio de pago</p><p class="text-gray-800 font-medium">{{ $this->pedidoDetalle->medioPagoLabel() }}</p></div>
                    <div><p class="text-xs text-gray-400">Transportadora</p><p class="text-gray-800 font-medium">{{ $this->pedidoDetalle->transportadora?->nombre ?? '—' }}</p></div>
                    <div><p class="text-xs text-gray-400">Número de guía</p><p class="text-gray-800 font-medium">{{ $this->pedidoDetalle->numero_guia ?: '—' }}</p></div>
                    <div><p class="text-xs text-gray-400">Número de factura</p><p class="text-gray-800 font-medium">{{ $this->pedidoDetalle->numero_factura ?: '—' }}</p></div>
                </div>

                <div class="border-t border-gray-100 pt-4 space-y-2">
                    @foreach ($this->pedidoDetalle->items as $item)
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600">
                                {{ $item->cantidad }}× {{ $item->producto_nombre }}
                                <span class="text-gray-400">({{ $item->presentacion }} · {{ \App\Models\PedidoItem::MOLIENDAS[$item->molienda] ?? $item->molienda }})</span>
                                <span class="block text-xs text-gray-400">${{ number_format($item->precio_unitario, 0, ',', '.') }} c/u</span>
                            </span>
                            <span class="font-medium text-gray-800">${{ number_format($item->total, 0, ',', '.') }}</span>
                        </div>
                    @endforeach
                </div>

                <div class="border-t border-gray-100 mt-4 pt-4 space-y-1.5 text-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-gray-500">Subtotal</span>
                        <span class="text-gray-700">${{ number_format($this->pedidoDetalle->subtotal, 0, ',', '.') }}</span>
                    </div>
                    @if ($this->pedidoDetalle->descuento_monto > 0)
                        <div class="flex items-center justify-between">
                            <span class="text-gray-500">Descuento{{ $this->pedidoDetalle->descuento?->nombre ? ' · '.$this->pedidoDetalle->descuento->nombre : '' }}</span>
                            <span class="text-red-500">-${{ number_format($this->pedidoDetalle->descuento_monto, 0, ',', '.') }}</span>
                        </div>
                    @endif
                    <div class="flex items-center justify-between">
                        <span class="text-gray-500">Envío{{ $this->pedidoDetalle->transportadora?->nombre ? ' · '.$this->pedidoDetalle->transportadora->nombre : '' }}</span>
                        <span class="text-gray-700">${{ number_format($this->pedidoDetalle->envio_costo, 0, ',', '.') }}</span>
                    </div>
                </div>

                <div class="border-t border-gray-100 mt-3 pt-3 flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-500">Total</span>
                    <span class="text-lg font-bold text-brand-600">${{ number_format($this->pedidoDetalle->total, 0, ',', '.') }}</span>
                </div>

                @if ($this->pedidoDetalle->notas)
                    <div class="mt-4 rounded-xl bg-amber-50 p-3 text-xs text-amber-800">{{ $this->pedidoDetalle->notas }}</div>
                @endif
            </div>
        </div>
    @endif

    @if ($this->pedidoADespachar)
        <div class="fixed inset-0 z-30 flex items-center justify-center bg-gray-900/40 px-4" wire:click.self="cerrarDespacho">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
                <div class="flex items-start justify-between mb-1">
                    <div class="flex items-center gap-3">
                        <span class="flex items-center justify-center w-10 h-10 rounded-xl bg-brand-50 text-brand-600 shrink-0">
                            <x-heroicon-o-truck class="w-5 h-5" />
                        </span>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Marcar como despachado</h3>
                            <p class="text-xs text-gray-400">Pedido #{{ $this->pedidoADespachar->numero }} · {{ $this->pedidoADespachar->cliente->nombre }}</p>
                        </div>
                    </div>
                    <button type="button" wire:click="cerrarDespacho" class="text-gray-400 hover:text-gray-600">
                        <x-heroicon-o-x-mark class="w-5 h-5" />
                    </button>
                </div>

                <div class="mt-5">
                    <label class="block">
                        <span class="text-xs text-gray-400">Transportadora</span>
                        <p class="mt-1 text-sm text-gray-700">{{ $this->pedidoADespachar->transportadora?->nombre ?? 'Sin transportadora asignada' }}</p>
                    </label>

                    <label class="block mt-4">
                        <span class="text-xs text-gray-400">Número de guía *</span>
                        <input
                            type="text"
                            wire:model="numero_guia"
                            placeholder="Ej. 1234567890"
                            class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-200"
                        />
                        @error('numero_guia') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </label>
                </div>

                <div class="flex items-center justify-end gap-3 mt-6">
                    <button type="button" wire:click="cerrarDespacho" class="px-4 py-2 rounded-xl text-sm text-gray-500 hover:bg-gray-50">Cancelar</button>
                    <button type="button" wire:click="confirmarDespacho" class="flex items-center gap-2 px-4 py-2 rounded-xl bg-brand-600 text-white text-sm font-medium hover:bg-brand-700">
                        <x-heroicon-o-truck class="w-4 h-4" />
                        Marcar como despachado
                    </button>
                </div>
            </div>
        </div>
    @endif

    @if ($this->pedidoAFacturar)
        <div class="fixed inset-0 z-30 flex items-center justify-center bg-gray-900/40 px-4" wire:click.self="cerrarFactura">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
                <div class="flex items-start justify-between mb-1">
                    <div class="flex items-center gap-3">
                        <span class="flex items-center justify-center w-10 h-10 rounded-xl bg-brand-50 text-brand-600 shrink-0">
                            <x-heroicon-o-document-text class="w-5 h-5" />
                        </span>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Facturar pedido</h3>
                            <p class="text-xs text-gray-400">Pedido #{{ $this->pedidoAFacturar->numero }} · {{ $this->pedidoAFacturar->cliente->nombre }}</p>
                        </div>
                    </div>
                    <button type="button" wire:click="cerrarFactura" class="text-gray-400 hover:text-gray-600">
                        <x-heroicon-o-x-mark class="w-5 h-5" />
                    </button>
                </div>

                <div class="mt-5">
                    <label class="block">
                        <span class="text-xs text-gray-400">Número de factura *</span>
                        <input
                            type="text"
                            wire:model="numero_factura"
                            placeholder="Ej. FE-001234"
                            class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-200"
                        />
                        @error('numero_factura') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </label>
                </div>

                <div class="flex items-center justify-end gap-3 mt-6">
                    <button type="button" wire:click="cerrarFactura" class="px-4 py-2 rounded-xl text-sm text-gray-500 hover:bg-gray-50">Cancelar</button>
                    <button type="button" wire:click="confirmarFactura" class="flex items-center gap-2 px-4 py-2 rounded-xl bg-brand-600 text-white text-sm font-medium hover:bg-brand-700">
                        <x-heroicon-o-document-text class="w-4 h-4" />
                        Guardar factura
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
