<div>
    <div class="flex justify-end mb-6">
        <select wire:model.live="periodo" class="rounded-xl border border-gray-200 text-sm px-3 py-2.5">
            <option value="hoy">Hoy</option>
            <option value="semana">Última semana</option>
            <option value="mes">Último mes</option>
            <option value="todo">Todo el histórico</option>
        </select>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-4 mb-6">
        <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 p-5">
            <p class="text-xs text-gray-400 mb-1">Pedidos</p>
            <p class="text-2xl font-bold text-gray-900">{{ $this->totalPedidos }}</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 p-5">
            <p class="text-xs text-gray-400 mb-1">Rutas</p>
            <p class="text-2xl font-bold text-gray-900">{{ $this->totalRutas }}</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 p-5">
            <p class="text-xs text-gray-400 mb-1">Ventas totales</p>
            <p class="text-2xl font-bold text-brand-600">${{ number_format($this->ventasTotales, 0, ',', '.') }}</p>
            <p class="text-xs text-gray-400 mt-1">
                Pedidos ${{ number_format($this->ventasPedidos, 0, ',', '.') }} · Rutas ${{ number_format($this->ventasRutas, 0, ',', '.') }}
            </p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 p-5">
            <p class="text-xs text-gray-400 mb-1">Ticket promedio (pedidos)</p>
            <p class="text-2xl font-bold text-gray-900">${{ number_format($this->ticketPromedio, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 p-5">
            <p class="text-xs text-gray-400 mb-1">Clientes activos</p>
            <p class="text-2xl font-bold text-gray-900">{{ $this->clientesActivos }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 p-6">
            <h2 class="text-sm font-semibold text-gray-900 mb-4">Pedidos por estado</h2>
            <div class="space-y-3">
                @forelse ($this->porEstado as $status => $total)
                    <div class="flex items-center justify-between text-sm">
                        <x-pedido-status :status="$status" />
                        <span class="font-medium text-gray-700">{{ (int) $total }}</span>
                    </div>
                @empty
                    <p class="text-sm text-gray-400">Sin datos para este período.</p>
                @endforelse
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 p-6">
            <h2 class="text-sm font-semibold text-gray-900 mb-4">Rutas por estado</h2>
            <div class="space-y-3">
                @forelse ($this->rutasPorEstado as $status => $total)
                    @php
                        $estilos = match ($status) {
                            'recibida' => 'bg-blue-50 text-blue-700',
                            'despachada' => 'bg-purple-50 text-purple-700',
                            'entregada' => 'bg-emerald-50 text-emerald-700',
                            'cancelada' => 'bg-red-50 text-red-700',
                            default => 'bg-amber-50 text-amber-700',
                        };
                    @endphp
                    <div class="flex items-center justify-between text-sm">
                        <span class="px-2.5 py-1 rounded-lg text-xs font-medium {{ $estilos }}">{{ ucfirst($status) }}</span>
                        <span class="font-medium text-gray-700">{{ (int) $total }}</span>
                    </div>
                @empty
                    <p class="text-sm text-gray-400">Sin datos para este período.</p>
                @endforelse
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 p-6">
            <h2 class="text-sm font-semibold text-gray-900 mb-4">Producto más vendido</h2>
            <div class="space-y-3">
                @forelse ($this->productoMasVendido as $producto)
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-700">{{ $producto->producto_nombre }}</span>
                        <span class="font-medium text-gray-900">{{ $producto->unidades }} uds.</span>
                    </div>
                @empty
                    <p class="text-sm text-gray-400">Sin datos para este período.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
