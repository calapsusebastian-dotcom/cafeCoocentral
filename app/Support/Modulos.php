<?php

namespace App\Support;

class Modulos
{
    /**
     * Canonical list of assignable modules, keyed by the route name that
     * represents each one. Shared by the sidebar, the Usuarios permission
     * editor, and the route-level access middleware so they never drift.
     * Notificaciones is intentionally not listed here — every logged-in
     * user can see it regardless of role.
     *
     * @return array<string, array{label: string, icon: string}>
     */
    public static function todos(): array
    {
        return [
            'pedidos.nuevo' => ['label' => 'Nuevo Pedido', 'icon' => 'shopping-cart'],
            'pedidos.index' => ['label' => 'Pedidos', 'icon' => 'clipboard-document-list'],
            'pedidos-web.index' => ['label' => 'Pedidos Web', 'icon' => 'globe-alt'],
            'facturacion.index' => ['label' => 'Facturación', 'icon' => 'document-text'],
            'clientes.index' => ['label' => 'Clientes', 'icon' => 'users'],
            'productos.index' => ['label' => 'Productos', 'icon' => 'cube'],
            'inventario.index' => ['label' => 'Inventario', 'icon' => 'archive-box'],
            'pedidos-bodega.index' => ['label' => 'Pedidos a Bodega', 'icon' => 'building-storefront'],
            'rutas.index' => ['label' => 'Rutas', 'icon' => 'map'],
            'reportes.index' => ['label' => 'Reportes', 'icon' => 'chart-bar'],
            'descuentos.index' => ['label' => 'Descuentos', 'icon' => 'tag'],
        ];
    }

    public static function claves(): array
    {
        return array_keys(self::todos());
    }
}
