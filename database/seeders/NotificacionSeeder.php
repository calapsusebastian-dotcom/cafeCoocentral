<?php

namespace Database\Seeders;

use App\Models\Notificacion;
use App\Models\Pedido;
use App\Models\User;
use Illuminate\Database\Seeder;

class NotificacionSeeder extends Seeder
{
    public function run(): void
    {
        $vendedor = User::first();
        $pedidos = Pedido::latest('id')->take(3)->get();

        $notificaciones = [
            ['titulo' => 'Nuevo pedido confirmado', 'mensaje' => "Se confirmó el pedido #{$pedidos->get(0)?->numero} de {$pedidos->get(0)?->cliente?->nombre}.", 'tipo' => 'pedido', 'leida' => false],
            ['titulo' => 'Stock bajo', 'mensaje' => 'Notas de Juventud 250g tiene solo 20 unidades disponibles.', 'tipo' => 'stock', 'leida' => false],
            ['titulo' => 'Pedido enviado', 'mensaje' => "El pedido #{$pedidos->get(1)?->numero} fue despachado con Coordinadora.", 'tipo' => 'pedido', 'leida' => false],
            ['titulo' => 'Pedido entregado', 'mensaje' => "El pedido #{$pedidos->get(2)?->numero} fue entregado al cliente.", 'tipo' => 'pedido', 'leida' => true],
            ['titulo' => 'Actualización del sistema', 'mensaje' => 'Se actualizó la lista de precios de la línea Premium.', 'tipo' => 'sistema', 'leida' => true],
            ['titulo' => 'Nuevo cliente registrado', 'mensaje' => 'Supermercado La Cosecha fue agregado como cliente mayorista.', 'tipo' => 'sistema', 'leida' => true],
        ];

        foreach ($notificaciones as $data) {
            Notificacion::create($data + ['user_id' => $vendedor->id, 'leida_at' => $data['leida'] ? now() : null]);
        }
    }
}
