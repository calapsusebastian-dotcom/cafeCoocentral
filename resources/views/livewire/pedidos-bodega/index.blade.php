<x-slot:headerActions>
    <a href="{{ route('bodegas.index') }}" class="hidden md:inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-gray-200 text-sm text-gray-500 hover:text-gray-700 hover:bg-gray-50 transition-colors">
        <x-heroicon-o-building-office-2 class="w-4 h-4" />
        Gestionar bodegas
    </a>
    <a href="{{ route('pedidos-bodega.nuevo') }}" class="flex items-center gap-2 px-4 py-2.5 rounded-xl bg-brand-600 text-white text-sm font-medium hover:bg-brand-700 transition-colors">
        <x-heroicon-o-plus class="w-4 h-4" />
        Nuevo pedido
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
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar por número o bodega..." class="w-full pl-9 pr-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-200" />
            </div>
            <select wire:model.live="statusFiltro" class="rounded-xl border border-gray-200 text-sm px-3 py-2.5">
                <option value="">Todos los estados</option>
                <option value="pendiente">Pendiente</option>
                <option value="recibido">Recibido</option>
                <option value="cancelado">Cancelado</option>
            </select>
        </div>

        <div class="overflow-x-auto -mx-2">
            <table class="w-full text-sm min-w-[600px]">
                <thead>
                    <tr class="text-left text-xs text-gray-400 border-b border-gray-100">
                        <th class="px-2 py-2 font-medium">Pedido</th>
                        <th class="px-2 py-2 font-medium">Bodega</th>
                        <th class="px-2 py-2 font-medium">Fecha</th>
                        <th class="px-2 py-2 font-medium">Unidades</th>
                        <th class="px-2 py-2 font-medium">Estado</th>
                        <th class="px-2 py-2"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($this->pedidos as $pedido)
                        <tr class="border-b border-gray-50 last:border-0">
                            <td class="px-2 py-3 font-medium text-brand-600">#{{ $pedido->numero }}</td>
                            <td class="px-2 py-3 text-gray-700">{{ $pedido->bodega->nombre }}</td>
                            <td class="px-2 py-3 text-gray-500">{{ $pedido->fecha_pedido?->format('d/m/Y') }}</td>
                            <td class="px-2 py-3 text-gray-700">{{ $pedido->items->sum('cantidad') }}</td>
                            <td class="px-2 py-3">
                                <x-pedido-status :status="$pedido->status" />
                            </td>
                            <td class="px-2 py-3 text-right space-x-2">
                                <button type="button" wire:click="verPedido({{ $pedido->id }})" class="text-gray-400 hover:text-brand-600">
                                    <x-heroicon-o-eye class="w-4 h-4 inline" />
                                </button>
                                <a href="{{ route('pedidos-bodega.imprimir', $pedido) }}" target="_blank" title="Imprimir" class="text-gray-400 hover:text-brand-600">
                                    <x-heroicon-o-printer class="w-4 h-4 inline" />
                                </a>
                                @if ($pedido->status === 'pendiente')
                                    <a href="{{ route('pedidos-bodega.editar', $pedido) }}" title="Editar" class="text-gray-400 hover:text-brand-600">
                                        <x-heroicon-o-pencil-square class="w-4 h-4 inline" />
                                    </a>
                                    <button type="button" wire:click="marcarRecibido({{ $pedido->id }})" wire:confirm="¿Marcar este pedido como recibido? Esto sumará las cantidades al inventario." title="Marcar como recibido" class="text-gray-400 hover:text-emerald-600">
                                        <x-heroicon-o-archive-box-arrow-down class="w-4 h-4 inline" />
                                    </button>
                                    @if (auth()->user()->is_admin)
                                        <button type="button" wire:click="cancelarPedido({{ $pedido->id }})" wire:confirm="¿Cancelar este pedido a bodega?" title="Cancelar pedido" class="text-gray-400 hover:text-red-500">
                                            <x-heroicon-o-x-mark class="w-4 h-4 inline" />
                                        </button>
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-2 py-10 text-center text-gray-400">No hay pedidos a bodega que coincidan con la búsqueda.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $this->pedidos->links() }}
        </div>
    </div>

    {{-- Detalle del pedido --}}
    @if ($this->pedidoDetalle)
        <div class="fixed inset-0 z-30 flex items-center justify-center bg-gray-900/40 px-4" wire:click.self="cerrarModal">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg p-6 max-h-[85vh] overflow-y-auto">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <p class="text-xs text-gray-400">Pedido a bodega</p>
                        <h3 class="text-lg font-semibold text-gray-900">#{{ $this->pedidoDetalle->numero }}</h3>
                    </div>
                    <div class="flex items-center gap-3">
                        <a href="{{ route('pedidos-bodega.imprimir', $this->pedidoDetalle) }}" target="_blank" class="flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700 font-medium">
                            <x-heroicon-o-printer class="w-4 h-4" />
                            Imprimir
                        </a>
                        @if ($this->pedidoDetalle->status === 'pendiente')
                            <a href="{{ route('pedidos-bodega.editar', $this->pedidoDetalle) }}" class="flex items-center gap-1.5 text-sm text-brand-600 hover:text-brand-700 font-medium">
                                <x-heroicon-o-pencil-square class="w-4 h-4" />
                                Editar
                            </a>
                        @endif
                        <button type="button" wire:click="cerrarModal" class="text-gray-400 hover:text-gray-600">
                            <x-heroicon-o-x-mark class="w-5 h-5" />
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 text-sm mb-4">
                    <div><p class="text-xs text-gray-400">Bodega</p><p class="text-gray-800 font-medium">{{ $this->pedidoDetalle->bodega->nombre }}</p></div>
                    <div><p class="text-xs text-gray-400">Estado</p><x-pedido-status :status="$this->pedidoDetalle->status" /></div>
                    <div><p class="text-xs text-gray-400">Fecha</p><p class="text-gray-800 font-medium">{{ $this->pedidoDetalle->fecha_pedido?->format('d/m/Y') }}</p></div>
                    <div><p class="text-xs text-gray-400">Solicitado por</p><p class="text-gray-800 font-medium">{{ $this->pedidoDetalle->usuario->name }}</p></div>
                </div>

                <div class="border-t border-gray-100 pt-4 space-y-2">
                    @foreach ($this->pedidoDetalle->items as $item)
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600">{{ $item->producto_nombre }} <span class="text-gray-400">({{ $item->presentacion }} · {{ \App\Models\PedidoItem::MOLIENDAS[$item->molienda] ?? $item->molienda }})</span></span>
                            <span class="font-medium text-gray-800">{{ $item->cantidad }} uds.</span>
                        </div>
                    @endforeach
                </div>

                <div class="border-t border-gray-100 mt-4 pt-4 flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-500">Total unidades</span>
                    <span class="text-lg font-bold text-brand-600">{{ $this->pedidoDetalle->items->sum('cantidad') }}</span>
                </div>

                @if ($this->pedidoDetalle->notas)
                    <div class="mt-4 rounded-xl bg-amber-50 p-3 text-xs text-amber-800">{{ $this->pedidoDetalle->notas }}</div>
                @endif
            </div>
        </div>
    @endif
</div>
