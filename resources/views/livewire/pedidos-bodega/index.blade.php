<div>
    @if (session('success'))
        <div class="mb-6 rounded-xl bg-emerald-50 text-emerald-700 text-sm px-4 py-3">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="mb-6 rounded-xl bg-red-50 text-red-700 text-sm px-4 py-3">{{ session('error') }}</div>
    @endif

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 items-start">
        <div class="xl:col-span-2 bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 p-6">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-sm font-semibold text-gray-900">Pedidos a bodega</h2>
                <a href="{{ route('pedidos-bodega.nuevo') }}" class="flex items-center gap-2 px-4 py-2 rounded-xl bg-brand-600 text-white text-sm font-medium hover:bg-brand-700 transition-colors">
                    <x-heroicon-o-plus class="w-4 h-4" />
                    Nuevo pedido
                </a>
            </div>

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
                                    @if ($pedido->status === 'pendiente')
                                        <a href="{{ route('pedidos-bodega.editar', $pedido) }}" title="Editar" class="text-gray-400 hover:text-brand-600">
                                            <x-heroicon-o-pencil-square class="w-4 h-4 inline" />
                                        </a>
                                        <button type="button" wire:click="marcarRecibido({{ $pedido->id }})" wire:confirm="¿Marcar este pedido como recibido? Esto sumará las cantidades al inventario." title="Marcar como recibido" class="text-gray-400 hover:text-emerald-600">
                                            <x-heroicon-o-archive-box-arrow-down class="w-4 h-4 inline" />
                                        </button>
                                        <button type="button" wire:click="cancelarPedido({{ $pedido->id }})" wire:confirm="¿Cancelar este pedido a bodega?" title="Cancelar pedido" class="text-gray-400 hover:text-red-500">
                                            <x-heroicon-o-x-mark class="w-4 h-4 inline" />
                                        </button>
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

        {{-- Bodegas --}}
        <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-semibold text-gray-900">Bodegas</h2>
                <button type="button" wire:click="nuevaBodega" class="flex items-center gap-2 px-3 py-2 rounded-xl bg-brand-600 text-white text-xs font-medium hover:bg-brand-700 transition-colors">
                    <x-heroicon-o-plus class="w-4 h-4" />
                    Nueva
                </button>
            </div>

            <div class="space-y-2">
                @forelse ($this->bodegas as $bodega)
                    <div class="flex items-center justify-between rounded-xl border border-gray-100 px-4 py-3">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-gray-800 truncate">{{ $bodega->nombre }}</p>
                            <p class="text-xs text-gray-400 truncate">{{ $bodega->direccion ?: 'Sin dirección' }}</p>
                        </div>
                        <div class="flex items-center gap-3 shrink-0">
                            <button type="button" wire:click="toggleBodega({{ $bodega->id }})" @class([
                                'relative inline-flex h-5 w-9 items-center rounded-full transition-colors shrink-0',
                                'bg-brand-600' => $bodega->activo,
                                'bg-gray-200' => ! $bodega->activo,
                            ])>
                                <span @class(['inline-block h-3.5 w-3.5 transform rounded-full bg-white transition-transform', 'translate-x-4' => $bodega->activo, 'translate-x-1' => ! $bodega->activo])></span>
                            </button>
                            <button type="button" wire:click="editarBodega({{ $bodega->id }})" class="text-gray-400 hover:text-brand-600">
                                <x-heroicon-o-pencil-square class="w-4 h-4" />
                            </button>
                            <button type="button" wire:click="eliminarBodega({{ $bodega->id }})" wire:confirm="¿Eliminar esta bodega?" class="text-gray-400 hover:text-red-500">
                                <x-heroicon-o-trash class="w-4 h-4" />
                            </button>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-400 py-6 text-center">No hay bodegas registradas.</p>
                @endforelse
            </div>
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

    {{-- Modal bodega --}}
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
