<?php

namespace Database\Seeders;

use App\Models\Cliente;
use App\Models\MovimientoInventario;
use App\Models\Pedido;
use App\Models\PedidoItem;
use App\Models\Producto;
use App\Models\Transportadora;
use App\Models\User;
use Illuminate\Database\Seeder;

class PedidoSeeder extends Seeder
{
    public function run(): void
    {
        $vendedor = User::first();
        $clientes = Cliente::all();
        $productos = Producto::all();
        $transportadora = Transportadora::where('nombre', 'Coordinadora')->first();

        $statuses = ['entregado', 'entregado', 'entregado', 'confirmado', 'confirmado', 'enviado', 'pendiente', 'pendiente'];

        foreach ($statuses as $index => $status) {
            $cliente = $clientes->random();
            $lineas = $productos->random(random_int(1, 3));

            $fecha = now()->subDays(($index + 1) * 3);

            $pedido = Pedido::create([
                'fecha_pedido' => $fecha->format('Y-m-d'),
                'cliente_id' => $cliente->id,
                'direccion_entrega' => $cliente->direccion,
                'user_id' => $vendedor->id,
                'subtotal' => 0,
                'transportadora_id' => $transportadora->id,
                'envio_costo' => $transportadora->costo,
                'total' => 0,
                'medio_pago' => collect(['pendiente', 'efectivo', 'transferencia', 'credito_30', 'credito_45', 'credito_60'])->random(),
                'status' => $status,
                'created_at' => $fecha,
                'updated_at' => $fecha,
            ]);

            $subtotal = 0;

            foreach ($lineas as $producto) {
                $cantidad = random_int(1, 5);
                $total = $producto->precio * $cantidad;
                $subtotal += $total;

                PedidoItem::create([
                    'pedido_id' => $pedido->id,
                    'producto_id' => $producto->id,
                    'producto_nombre' => $producto->nombre,
                    'producto_codigo' => $producto->sku,
                    'presentacion' => $producto->presentacion,
                    'precio_unitario' => $producto->precio,
                    'cantidad' => $cantidad,
                    'total' => $total,
                ]);
            }

            $total = $subtotal + (float) $transportadora->costo;

            $pedido->update([
                'numero' => 'ORD-'.str_pad((string) $pedido->id, 4, '0', STR_PAD_LEFT),
                'subtotal' => $subtotal,
                'total' => $total,
                'puntos_generados' => intdiv((int) $total, 1000),
            ]);
        }

        // A handful of inventory movements tied to a couple of seeded orders,
        // without touching current productos.stock (kept matching the screenshot's figures).
        $pedidosRecientes = Pedido::latest('id')->take(3)->get();

        foreach ($pedidosRecientes as $pedido) {
            foreach ($pedido->items as $item) {
                MovimientoInventario::create([
                    'producto_id' => $item->producto_id,
                    'tipo' => 'salida',
                    'cantidad' => $item->cantidad,
                    'motivo' => "Venta pedido #{$pedido->numero}",
                    'pedido_id' => $pedido->id,
                    'user_id' => $vendedor->id,
                ]);
            }
        }

        MovimientoInventario::create([
            'producto_id' => $productos->first()->id,
            'tipo' => 'entrada',
            'cantidad' => 50,
            'motivo' => 'Ingreso de tueste nuevo lote',
            'user_id' => $vendedor->id,
        ]);
    }
}
