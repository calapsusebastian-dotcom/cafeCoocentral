<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Producción #{{ $produccion->numero }} · Café Coocentral</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        @page { size: auto; margin: 1.2cm; }

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
        <a href="{{ route('produccion.index') }}" class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1.5">
            <x-heroicon-o-arrow-left class="w-4 h-4" />
            Volver a Producción
        </a>
        <button type="button" onclick="window.print()" class="flex items-center gap-2 px-4 py-2 rounded-xl bg-brand-600 text-white text-sm font-medium hover:bg-brand-700">
            <x-heroicon-o-printer class="w-4 h-4" />
            Imprimir
        </button>
    </div>

    <div class="print-container max-w-3xl mx-auto py-8 px-4">
        <div class="print-sheet bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 p-8">
            <div class="flex items-start justify-between pb-6 border-b border-gray-100">
                <div class="flex items-center gap-3">
                    <span class="flex items-center justify-center w-11 h-11 rounded-2xl bg-brand-50 text-brand-600">
                        <x-icon.coffee-bean class="w-6 h-6" />
                    </span>
                    <div>
                        <p class="font-semibold text-gray-900 leading-tight">Café Coocentral</p>
                        <p class="text-xs text-gray-400 leading-tight">Pasión que sabe a origen</p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-xs text-gray-400">Producción</p>
                    <p class="text-xl font-bold text-brand-600">#{{ $produccion->numero }}</p>
                    @php
                        $estilos = $produccion->status === 'trasladado' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700';
                    @endphp
                    <span class="inline-flex items-center mt-1 px-2.5 py-1 rounded-full text-xs font-medium {{ $estilos }}">{{ ucfirst($produccion->status) }}</span>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-6 py-6 border-b border-gray-100 text-sm">
                <div class="grid grid-cols-[auto_1fr] gap-x-3 gap-y-1.5">
                    <span class="text-gray-400">Fecha</span>
                    <span class="text-gray-800 font-medium">{{ $produccion->fecha_produccion?->format('d/m/Y') ?? '—' }}</span>
                    <span class="text-gray-400">Registrado por</span>
                    <span class="text-gray-800 font-medium">{{ $produccion->usuario->name }}</span>
                </div>
                <div class="grid grid-cols-2 gap-y-1.5 gap-x-3 max-w-xs ml-auto">
                    <span class="text-gray-400">N.° IMOV</span>
                    <span class="text-gray-800 font-medium text-right">{{ $produccion->numero_imov ?: '—' }}</span>
                    <span class="text-gray-400">Trasladado</span>
                    <span class="text-gray-800 font-medium text-right">{{ $produccion->trasladado_at?->format('d/m/Y') ?? '—' }}</span>
                </div>
            </div>

            <div class="py-6">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs text-gray-400 border-b border-gray-100">
                            <th class="py-2 font-medium">Producto</th>
                            <th class="py-2 font-medium">Molienda</th>
                            <th class="py-2 font-medium text-right">Cant.</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($produccion->items as $item)
                            <tr class="border-b border-gray-50">
                                <td class="py-2.5">
                                    <p class="font-medium text-gray-800">{{ $item->producto_nombre }}</p>
                                    <p class="text-xs text-gray-400">{{ $item->producto_codigo }} · {{ $item->presentacion }}</p>
                                </td>
                                <td class="py-2.5 text-gray-500">{{ \App\Models\PedidoItem::MOLIENDAS[$item->molienda] ?? $item->molienda }}</td>
                                <td class="py-2.5 text-right text-gray-700">{{ $item->cantidad }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="2" class="pt-3 text-right text-xs text-gray-400 uppercase tracking-wide">Total unidades</td>
                            <td class="pt-3 text-right font-bold text-brand-600">{{ $produccion->items->sum('cantidad') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            @if ($produccion->notas)
                <div class="mt-6 rounded-xl bg-amber-50 p-4 text-sm text-amber-800">
                    <p class="text-xs font-medium text-amber-700 uppercase tracking-wide mb-1">Notas</p>
                    {{ $produccion->notas }}
                </div>
            @endif

            <p class="mt-8 pt-4 border-t border-gray-100 text-center text-xs text-gray-400">
                Café Coocentral · Documento generado el {{ now()->locale('es')->isoFormat('D [de] MMMM [de] YYYY, h:mm a') }}
            </p>
        </div>
    </div>
</body>
</html>
