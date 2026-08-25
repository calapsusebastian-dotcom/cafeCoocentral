<x-slot:headerActions>
    <a href="{{ route('rutas.nueva') }}" class="flex items-center gap-2 px-4 py-2.5 rounded-xl bg-brand-600 text-white text-sm font-medium hover:bg-brand-700 transition-colors">
        <x-heroicon-o-plus class="w-4 h-4" />
        Nueva ruta
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
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar por número o nombre de ruta..." class="w-full pl-9 pr-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-200" />
            </div>
            <select wire:model.live="statusFiltro" class="rounded-xl border border-gray-200 text-sm px-3 py-2.5">
                <option value="">Todos los estados</option>
                <option value="pendiente">Pendiente</option>
                <option value="recibida">Recibida</option>
                <option value="despachada">Despachada</option>
                <option value="entregada">Entregada</option>
                <option value="cancelada">Cancelada</option>
            </select>
        </div>

        <div class="overflow-x-auto -mx-2">
            <table class="w-full text-sm min-w-[700px]">
                <thead>
                    <tr class="text-left text-xs text-gray-400 border-b border-gray-100">
                        <th class="px-2 py-2 font-medium">Ruta</th>
                        <th class="px-2 py-2 font-medium">Nombre</th>
                        <th class="px-2 py-2 font-medium">Clientes</th>
                        <th class="px-2 py-2 font-medium">Facturación</th>
                        <th class="px-2 py-2 font-medium">Fecha</th>
                        <th class="px-2 py-2 font-medium">Responsable</th>
                        <th class="px-2 py-2 font-medium">Estado</th>
                        <th class="px-2 py-2"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($this->rutas as $ruta)
                        @php
                            $estilos = match ($ruta->status) {
                                'recibida' => 'bg-blue-50 text-blue-700',
                                'despachada' => 'bg-purple-50 text-purple-700',
                                'entregada' => 'bg-emerald-50 text-emerald-700',
                                'cancelada' => 'bg-red-50 text-red-700',
                                default => 'bg-amber-50 text-amber-700',
                            };
                            $etiqueta = ucfirst($ruta->status);
                        @endphp
                        <tr class="border-b border-gray-50 last:border-0">
                            <td class="px-2 py-3 font-medium text-brand-600">#{{ $ruta->numero }}</td>
                            <td class="px-2 py-3 text-gray-700">{{ $ruta->nombre }}</td>
                            <td class="px-2 py-3 text-gray-500">{{ $ruta->clientes_count }}</td>
                            <td class="px-2 py-3">
                                <span @class([
                                    'inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium',
                                    'bg-emerald-50 text-emerald-700' => $ruta->clientes_facturados_count === $ruta->clientes_count && $ruta->clientes_count > 0,
                                    'bg-gray-100 text-gray-500' => ! ($ruta->clientes_facturados_count === $ruta->clientes_count && $ruta->clientes_count > 0),
                                ])>
                                    {{ $ruta->clientes_facturados_count }}/{{ $ruta->clientes_count }} facturados
                                </span>
                            </td>
                            <td class="px-2 py-3 text-gray-500">{{ $ruta->fecha->format('d/m/Y') }}</td>
                            <td class="px-2 py-3 text-gray-500">{{ $ruta->usuario->name }}</td>
                            <td class="px-2 py-3">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $estilos }}">{{ $etiqueta }}</span>
                            </td>
                            <td class="px-2 py-3 text-right space-x-2">
                                <button type="button" wire:click="verRuta({{ $ruta->id }})" class="text-gray-400 hover:text-brand-600">
                                    <x-heroicon-o-eye class="w-4 h-4 inline" />
                                </button>
                                <a href="{{ route('rutas.imprimir', $ruta) }}" target="_blank" title="Imprimir ruta" class="text-gray-400 hover:text-brand-600">
                                    <x-heroicon-o-printer class="w-4 h-4 inline" />
                                </a>
                                @if ($ruta->status === 'pendiente' || $ruta->status === 'recibida')
                                    <a href="{{ route('rutas.editar', $ruta) }}" title="Editar ruta" class="text-gray-400 hover:text-brand-600">
                                        <x-heroicon-o-pencil-square class="w-4 h-4 inline" />
                                    </a>
                                @endif
                                @if ($ruta->status === 'pendiente')
                                    <button type="button" wire:click="marcarRecibida({{ $ruta->id }})" title="Marcar como recibida" class="text-gray-400 hover:text-brand-600">
                                        <x-heroicon-o-inbox-arrow-down class="w-4 h-4 inline" />
                                    </button>
                                    @if (auth()->user()->is_admin)
                                        <button type="button" wire:click="cancelarRuta({{ $ruta->id }})" title="Cancelar ruta" class="text-gray-400 hover:text-red-500">
                                            <x-heroicon-o-x-circle class="w-4 h-4 inline" />
                                        </button>
                                    @endif
                                @elseif ($ruta->status === 'recibida')
                                    <button type="button" wire:click="abrirDespacho({{ $ruta->id }})" title="Despachar ruta" class="text-gray-400 hover:text-brand-600">
                                        <x-heroicon-o-truck class="w-4 h-4 inline" />
                                    </button>
                                    @if (auth()->user()->is_admin)
                                        <button type="button" wire:click="cancelarRuta({{ $ruta->id }})" title="Cancelar ruta" class="text-gray-400 hover:text-red-500">
                                            <x-heroicon-o-x-circle class="w-4 h-4 inline" />
                                        </button>
                                    @endif
                                @elseif ($ruta->status === 'despachada')
                                    <button type="button" wire:click="marcarEntregada({{ $ruta->id }})" title="Marcar como entregada" class="text-gray-400 hover:text-brand-600">
                                        <x-heroicon-o-check-circle class="w-4 h-4 inline" />
                                    </button>
                                    @if (auth()->user()->is_admin)
                                        <button type="button" wire:click="cancelarRuta({{ $ruta->id }})" title="Cancelar ruta" class="text-gray-400 hover:text-red-500">
                                            <x-heroicon-o-x-circle class="w-4 h-4 inline" />
                                        </button>
                                    @endif
                                @endif
                                @if (auth()->user()->is_admin)
                                    <button
                                        type="button"
                                        wire:click="eliminarRuta({{ $ruta->id }})"
                                        wire:confirm="¿Eliminar la ruta #{{ $ruta->numero }}? Esta acción no se puede deshacer."
                                        title="Eliminar ruta"
                                        class="text-gray-400 hover:text-red-500"
                                    >
                                        <x-heroicon-o-trash class="w-4 h-4 inline" />
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-2 py-10 text-center text-gray-400">No hay rutas que coincidan con la búsqueda.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $this->rutas->links() }}
        </div>
    </div>

    @if ($this->rutaDetalle)
        <div class="fixed inset-0 z-30 flex items-center justify-center bg-gray-900/40 px-4" wire:click.self="cerrarModal">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-3xl p-6 max-h-[85vh] overflow-y-auto">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <p class="text-xs text-gray-400">Ruta</p>
                        <h3 class="text-lg font-semibold text-gray-900">#{{ $this->rutaDetalle->numero }} · {{ $this->rutaDetalle->nombre }}</h3>
                    </div>
                    <div class="flex items-center gap-3">
                        <a href="{{ route('rutas.imprimir', $this->rutaDetalle) }}" target="_blank" class="flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700 font-medium">
                            <x-heroicon-o-printer class="w-4 h-4" />
                            Imprimir
                        </a>
                        <button type="button" wire:click="cerrarModal" class="text-gray-400 hover:text-gray-600">
                            <x-heroicon-o-x-mark class="w-5 h-5" />
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-sm mb-4">
                    <div><p class="text-xs text-gray-400">Fecha</p><p class="text-gray-800 font-medium">{{ $this->rutaDetalle->fecha->format('d/m/Y') }}</p></div>
                    <div><p class="text-xs text-gray-400">Responsable</p><p class="text-gray-800 font-medium">{{ $this->rutaDetalle->usuario->name }}</p></div>
                    <div><p class="text-xs text-gray-400">Estado</p><p class="text-gray-800 font-medium">{{ ucfirst($this->rutaDetalle->status) }}</p></div>
                    <div><p class="text-xs text-gray-400">Clientes</p><p class="text-gray-800 font-medium">{{ $this->rutaDetalle->clientes->count() }}</p></div>
                    @if ($this->rutaDetalle->conductor_nombre)
                        <div><p class="text-xs text-gray-400">Conductor</p><p class="text-gray-800 font-medium">{{ $this->rutaDetalle->conductor_nombre }}</p></div>
                        <div><p class="text-xs text-gray-400">Cédula conductor</p><p class="text-gray-800 font-medium">{{ $this->rutaDetalle->conductor_cc }}</p></div>
                        <div><p class="text-xs text-gray-400">Costo de la ruta</p><p class="text-gray-800 font-medium">${{ number_format($this->rutaDetalle->costo_ruta, 0, ',', '.') }}</p></div>
                        <div><p class="text-xs text-gray-400">Centro de costos</p><p class="text-gray-800 font-medium">{{ $this->rutaDetalle->centro_costo ?: '—' }}</p></div>
                    @endif
                </div>

                <div class="space-y-4">
                    @foreach ($this->rutaDetalle->clientes as $rutaCliente)
                        <div class="rounded-xl bg-gray-50 p-4">
                            <div class="flex items-start justify-between gap-2 mb-3">
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">{{ $rutaCliente->cliente->nombre }}</p>
                                    <div class="flex items-center gap-4 mt-0.5">
                                        <p class="text-xs text-gray-400">Documento <span class="text-gray-600 font-medium">{{ $rutaCliente->cliente->documento }}</span></p>
                                        <p class="text-xs text-gray-400">Zona <span class="text-gray-600 font-medium">{{ $rutaCliente->cliente->ciudad }}</span></p>
                                        <p class="text-xs text-gray-400">Medio de pago <span class="text-gray-600 font-medium">{{ $rutaCliente->medioPagoLabel() }}</span></p>
                                        @if ($rutaCliente->numero_orden)
                                            <p class="text-xs text-gray-400">N.° de orden <span class="text-gray-600 font-medium">{{ $rutaCliente->numero_orden }}</span></p>
                                        @endif
                                    </div>
                                </div>
                                <button type="button" wire:click="abrirFactura({{ $rutaCliente->id }})" class="shrink-0">
                                    @if ($rutaCliente->numero_factura)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 hover:bg-emerald-100">
                                            Factura #{{ $rutaCliente->numero_factura }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-amber-50 text-amber-700 hover:bg-amber-100">
                                            <x-heroicon-o-document-text class="w-3.5 h-3.5" />
                                            Facturar
                                        </span>
                                    @endif
                                </button>
                            </div>
                            <div class="overflow-x-auto -mx-1">
                                <table class="w-full text-sm">
                                    <thead>
                                        <tr class="text-left text-xs text-gray-400 border-b border-gray-200">
                                            <th class="px-1 py-1.5 font-medium">Producto</th>
                                            <th class="px-1 py-1.5 font-medium">Código</th>
                                            <th class="px-1 py-1.5 font-medium text-right">Cant.</th>
                                            <th class="px-1 py-1.5 font-medium text-right">Precio unitario</th>
                                            <th class="px-1 py-1.5 font-medium text-right">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($rutaCliente->productos as $producto)
                                            <tr class="border-b border-gray-100 last:border-0">
                                                <td class="px-1 py-2">
                                                    <p class="text-gray-800">{{ $producto->producto_nombre }}</p>
                                                    <p class="text-xs text-gray-400">{{ $producto->presentacion }} · {{ \App\Models\PedidoItem::MOLIENDAS[$producto->molienda] ?? $producto->molienda }}</p>
                                                </td>
                                                <td class="px-1 py-2 text-gray-500">{{ $producto->producto_codigo }}</td>
                                                <td class="px-1 py-2 text-right text-gray-700 font-medium">{{ $producto->cantidad }}</td>
                                                <td class="px-1 py-2 text-right text-gray-600">${{ number_format($producto->precio_unitario, 0, ',', '.') }}</td>
                                                <td class="px-1 py-2 text-right font-medium text-gray-800">${{ number_format($producto->precio_unitario * $producto->cantidad, 0, ',', '.') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="border-t border-gray-200 mt-2 pt-2 flex items-center justify-between">
                                <span class="text-xs font-medium text-gray-500">Total cliente</span>
                                <span class="text-sm font-bold text-brand-600">${{ number_format($rutaCliente->productos->sum(fn ($p) => $p->precio_unitario * $p->cantidad), 0, ',', '.') }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="border-t border-gray-100 mt-4 pt-4 flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-500">Valor total</span>
                    <span class="text-lg font-bold text-brand-600">
                        ${{ number_format($this->rutaDetalle->clientes->flatMap->productos->sum(fn ($p) => $p->precio_unitario * $p->cantidad), 0, ',', '.') }}
                    </span>
                </div>

                @if ($this->rutaDetalle->notas)
                    <div class="mt-4 rounded-xl bg-amber-50 p-3 text-xs text-amber-800">{{ $this->rutaDetalle->notas }}</div>
                @endif
            </div>
        </div>
    @endif

    @if ($this->rutaClienteAFacturar)
        <div class="fixed inset-0 z-40 flex items-center justify-center bg-gray-900/40 px-4" wire:click.self="cerrarFactura">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
                <div class="flex items-start justify-between mb-1">
                    <div class="flex items-center gap-3">
                        <span class="flex items-center justify-center w-10 h-10 rounded-xl bg-brand-50 text-brand-600 shrink-0">
                            <x-heroicon-o-document-text class="w-5 h-5" />
                        </span>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Facturar cliente</h3>
                            <p class="text-xs text-gray-400">{{ $this->rutaClienteAFacturar->cliente->nombre }}</p>
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

    @if ($this->rutaADespachar)
        <div class="fixed inset-0 z-40 flex items-center justify-center bg-gray-900/40 px-4" wire:click.self="cerrarDespacho">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
                <div class="flex items-start justify-between mb-1">
                    <div class="flex items-center gap-3">
                        <span class="flex items-center justify-center w-10 h-10 rounded-xl bg-brand-50 text-brand-600 shrink-0">
                            <x-heroicon-o-truck class="w-5 h-5" />
                        </span>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Despachar ruta</h3>
                            <p class="text-xs text-gray-400">Ruta #{{ $this->rutaADespachar->numero }} · {{ $this->rutaADespachar->nombre }}</p>
                        </div>
                    </div>
                    <button type="button" wire:click="cerrarDespacho" class="text-gray-400 hover:text-gray-600">
                        <x-heroicon-o-x-mark class="w-5 h-5" />
                    </button>
                </div>

                <div class="mt-5 space-y-4">
                    <label class="block">
                        <span class="text-xs text-gray-400">Nombre del conductor *</span>
                        <input
                            type="text"
                            wire:model="conductor_nombre"
                            placeholder="Ej. Juan Pérez"
                            class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-200"
                        />
                        @error('conductor_nombre') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </label>

                    <label class="block">
                        <span class="text-xs text-gray-400">Cédula del conductor *</span>
                        <input
                            type="text"
                            wire:model="conductor_cc"
                            placeholder="Ej. 1077871726"
                            class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-200"
                        />
                        @error('conductor_cc') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </label>

                    <label class="block">
                        <span class="text-xs text-gray-400">Costo de la ruta *</span>
                        <div class="relative mt-1">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-gray-400">$</span>
                            <input
                                type="number"
                                step="0.01"
                                min="0"
                                wire:model="costo_ruta"
                                placeholder="0"
                                class="w-full rounded-lg border border-gray-200 pl-7 pr-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-200 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
                            />
                        </div>
                        @error('costo_ruta') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </label>

                    <label class="block">
                        <span class="text-xs text-gray-400">Centro de costos *</span>
                        <select wire:model="centro_costo" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-200">
                            <option value="">Selecciona un centro de costos</option>
                            <option value="Garzón">Garzón</option>
                            <option value="Bogotá">Bogotá</option>
                            <option value="Neiva">Neiva</option>
                            <option value="Producción">Producción</option>
                        </select>
                        @error('centro_costo') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </label>
                </div>

                <div class="flex items-center justify-end gap-3 mt-6">
                    <button type="button" wire:click="cerrarDespacho" class="px-4 py-2 rounded-xl text-sm text-gray-500 hover:bg-gray-50">Cancelar</button>
                    <button type="button" wire:click="confirmarDespacho" class="flex items-center gap-2 px-4 py-2 rounded-xl bg-brand-600 text-white text-sm font-medium hover:bg-brand-700">
                        <x-heroicon-o-truck class="w-4 h-4" />
                        Confirmar despacho
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
