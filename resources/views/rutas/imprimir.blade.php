<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ruta #{{ $ruta->numero }} · Café Coocentral</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        @page { size: auto; margin: 1.2cm; }

        /* Plain, framework-independent rules for the print layout — kept outside
           Tailwind's utility classes so the printable width is never at the mercy
           of how a given browser's print engine applies layered/utility CSS. */
        @media print {
            html, body {
                width: 100%;
                background: #fff !important;
            }
            .no-print {
                display: none !important;
            }
            .print-container {
                max-width: 720px !important;
                width: 100% !important;
                margin: 0 auto !important;
                padding: 0 !important;
            }
            .print-sheet {
                box-shadow: none !important;
                border: none !important;
                border-radius: 0 !important;
            }
        }
    </style>
</head>
<body class="antialiased bg-gray-100 text-gray-900">
    <div class="no-print sticky top-0 z-10 bg-white border-b border-gray-100 px-6 py-3 flex items-center justify-between">
        <a href="{{ route('rutas.index') }}" class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1.5">
            <x-heroicon-o-arrow-left class="w-4 h-4" />
            Volver a Rutas
        </a>
        <button type="button" onclick="window.print()" class="flex items-center gap-2 px-4 py-2 rounded-xl bg-brand-600 text-white text-sm font-medium hover:bg-brand-700">
            <x-heroicon-o-printer class="w-4 h-4" />
            Imprimir
        </button>
    </div>

    <div class="print-container max-w-3xl mx-auto py-6 px-4">
        <div class="print-sheet bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 p-6">
            <div class="flex items-start justify-between pb-4 border-b border-gray-100">
                <div class="flex items-center gap-3">
                    <span class="flex items-center justify-center w-9 h-9 rounded-xl bg-brand-50 text-brand-600">
                        <x-icon.coffee-bean class="w-5 h-5" />
                    </span>
                    <div>
                        <p class="font-semibold text-gray-900 leading-tight text-sm">Café Coocentral</p>
                        <p class="text-xs text-gray-400 leading-tight">Pasión que sabe a origen</p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-xs text-gray-400">Ruta</p>
                    <p class="text-lg font-bold text-brand-600 leading-tight">#{{ $ruta->numero }}</p>
                    <p class="text-sm text-gray-600 font-medium">{{ $ruta->nombre }}</p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 py-3 border-b border-gray-100 text-sm">
                <div class="grid grid-cols-[auto_1fr] gap-x-3 gap-y-1">
                    <span class="text-gray-400">Fecha</span>
                    <span class="text-gray-800 font-medium">{{ $ruta->fecha->format('d/m/Y') }}</span>
                    <span class="text-gray-400">Responsable</span>
                    <span class="text-gray-800 font-medium">{{ $ruta->usuario->name }}</span>
                    <span class="text-gray-400">Estado</span>
                    <span class="text-gray-800 font-medium">{{ ucfirst($ruta->status) }}</span>
                </div>
                <div class="grid grid-cols-[auto_1fr] gap-x-3 gap-y-1">
                    <span class="text-gray-400">Clientes</span>
                    <span class="text-gray-800 font-medium">{{ $ruta->clientes->count() }}</span>
                    <span class="text-gray-400">Total unidades</span>
                    <span class="text-gray-800 font-medium">{{ $porProducto->sum('total') }}</span>
                    @if ($ruta->conductor_nombre)
                        <span class="text-gray-400">Conductor</span>
                        <span class="text-gray-800 font-medium">{{ $ruta->conductor_nombre }} · CC {{ $ruta->conductor_cc }}</span>
                        <span class="text-gray-400">Costo de la ruta</span>
                        <span class="text-gray-800 font-medium">${{ number_format($ruta->costo_ruta, 0, ',', '.') }}</span>
                    @endif
                </div>
            </div>

            <div class="py-3">
                <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-2">Resumen por productos</p>
                <div class="space-y-2">
                    @foreach ($porProducto as $producto)
                        <div class="flex items-center justify-between gap-3 rounded-lg bg-gray-50 px-3 py-2">
                            <p class="text-sm text-gray-900 shrink-0">
                                <span class="font-medium">{{ $producto['nombre'] }}</span>
                                <span class="text-xs text-gray-400">{{ $producto['presentacion'] }}</span>
                            </p>
                            <div class="flex flex-wrap justify-end gap-x-3 gap-y-0.5">
                                @foreach ($producto['moliendas'] as $molienda => $grupo)
                                    <span class="text-xs text-gray-600 whitespace-nowrap">
                                        {{ \App\Models\PedidoItem::MOLIENDAS[$molienda] ?? $molienda }}: <span class="font-semibold text-gray-800">{{ $grupo['total'] }}</span>
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="py-3 border-t border-gray-100">
                <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-2">Detalle por cliente</p>
                <div class="space-y-2">
                    @foreach ($ruta->clientes as $rutaCliente)
                        <div class="rounded-lg border border-gray-100 p-3">
                            <div class="flex items-start justify-between mb-1">
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">{{ $rutaCliente->cliente->nombre }}</p>
                                    <p class="text-xs text-gray-400">{{ $rutaCliente->cliente->documento }} · {{ $rutaCliente->cliente->ciudad }}</p>
                                </div>
                                @if ($rutaCliente->numero_factura)
                                    <span class="text-xs font-medium text-emerald-700">Factura #{{ $rutaCliente->numero_factura }}</span>
                                @endif
                            </div>
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="text-left text-xs text-gray-400 border-b border-gray-100">
                                        <th class="py-1 font-medium">Producto</th>
                                        <th class="py-1 font-medium">Molienda</th>
                                        <th class="py-1 font-medium text-right">Cant.</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($rutaCliente->productos as $producto)
                                        <tr class="border-b border-gray-50 last:border-0">
                                            <td class="py-1">
                                                <span class="text-gray-800">{{ $producto->producto_nombre }}</span>
                                                <span class="text-xs text-gray-400">{{ $producto->presentacion }}</span>
                                            </td>
                                            <td class="py-1 text-gray-500">{{ \App\Models\PedidoItem::MOLIENDAS[$producto->molienda] ?? $producto->molienda }}</td>
                                            <td class="py-1 text-right text-gray-700 font-medium">{{ $producto->cantidad }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endforeach
                </div>
            </div>

            @if ($ruta->notas)
                <div class="mt-2 rounded-lg bg-amber-50 p-3 text-sm text-amber-800">
                    <p class="text-xs font-medium text-amber-700 uppercase tracking-wide mb-1">Notas</p>
                    {{ $ruta->notas }}
                </div>
            @endif

            <p class="mt-4 pt-3 border-t border-gray-100 text-center text-xs text-gray-400">
                Café Coocentral · Documento generado el {{ now()->locale('es')->isoFormat('D [de] MMMM [de] YYYY, h:mm a') }}
            </p>
        </div>
    </div>
</body>
</html>
