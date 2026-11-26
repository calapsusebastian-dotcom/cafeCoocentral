<?php

namespace App\Http\Controllers;

use App\Models\Ruta;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Descarga todas las rutas en un CSV para Excel: una fila por cada producto
 * de cada cliente, repitiendo los datos de la ruta y del cliente.
 */
class ExportarRutasController extends Controller
{
    private const ENCABEZADOS = [
        'Ruta', 'Nombre de ruta', 'Fecha', 'Estado', 'Responsable', 'Notas',
        'Recibida', 'Despachada', 'Entregada', 'Conductor', 'Cédula conductor', 'Costo de ruta',
        'Orden de parada', 'Cliente', 'Documento', 'Código cliente', 'Teléfono', 'Email', 'Dirección', 'Ciudad',
        'Número de orden', 'Medio de pago', 'Observaciones', 'Factura', 'Facturado',
        'Producto', 'Código producto', 'Presentación', 'Molienda', 'Cantidad', 'Precio unitario', 'Total línea',
    ];

    public function __invoke(): StreamedResponse
    {
        return response()->streamDownload(function () {
            $salida = fopen('php://output', 'w');

            // BOM para que Excel respete las tildes.
            fwrite($salida, "\xEF\xBB\xBF");
            $this->escribir($salida, self::ENCABEZADOS);

            // Por bloques, para no cargar todo el histórico en memoria.
            Ruta::with(['usuario', 'clientes.cliente', 'clientes.productos'])
                ->lazyByIdDesc(100)
                ->each(function (Ruta $ruta) use ($salida) {
                    foreach ($this->filas($ruta) as $fila) {
                        $this->escribir($salida, $fila);
                    }
                });

            fclose($salida);
        }, 'rutas-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * Las rutas sin clientes y los clientes sin productos también salen,
     * con las columnas restantes vacías.
     */
    private function filas(Ruta $ruta): iterable
    {
        $datosRuta = [
            $ruta->numero,
            $ruta->nombre,
            $ruta->fecha?->format('d/m/Y'),
            ucfirst((string) $ruta->status),
            $ruta->usuario?->name,
            $ruta->notas,
            $ruta->recibida_at?->format('d/m/Y H:i'),
            $ruta->despachada_at?->format('d/m/Y H:i'),
            $ruta->entregada_at?->format('d/m/Y H:i'),
            $ruta->conductor_nombre,
            $ruta->conductor_cc,
            $this->numero($ruta->costo_ruta),
        ];

        if ($ruta->clientes->isEmpty()) {
            yield array_pad($datosRuta, count(self::ENCABEZADOS), null);

            return;
        }

        foreach ($ruta->clientes as $parada) {
            $cliente = $parada->cliente;

            $datosParada = [
                ...$datosRuta,
                $parada->orden,
                $cliente?->nombre,
                $cliente?->documento,
                $cliente?->codigo,
                $cliente?->telefono,
                $cliente?->email,
                $cliente?->direccion,
                $cliente?->ciudad,
                $parada->numero_orden,
                $parada->medioPagoLabel(),
                $parada->observaciones,
                $parada->numero_factura,
                $parada->facturado_at?->format('d/m/Y H:i'),
            ];

            if ($parada->productos->isEmpty()) {
                yield array_pad($datosParada, count(self::ENCABEZADOS), null);

                continue;
            }

            foreach ($parada->productos as $producto) {
                yield [
                    ...$datosParada,
                    $producto->producto_nombre,
                    $producto->producto_codigo,
                    $producto->presentacion,
                    $producto->molienda,
                    $producto->cantidad,
                    $this->numero($producto->precio_unitario),
                    $this->numero($producto->precio_unitario * $producto->cantidad),
                ];
            }
        }
    }

    /** Coma decimal y sin separador de miles, como lo lee Excel en español. */
    private function numero(mixed $valor): ?string
    {
        return $valor === null ? null : str_replace(',00', '', number_format((float) $valor, 2, ',', ''));
    }

    private function escribir($salida, array $fila): void
    {
        // Un texto que empieza por = + - @ lo ejecutaría Excel como fórmula.
        $fila = array_map(
            fn ($valor) => is_string($valor) && preg_match('/^[=+\-@\t\r]/', $valor) ? "'".$valor : $valor,
            $fila
        );

        fputcsv($salida, $fila, ';', '"', '');
    }
}
