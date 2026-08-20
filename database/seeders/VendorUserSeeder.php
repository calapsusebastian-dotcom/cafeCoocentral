<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class VendorUserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'María López',
            'email' => 'maria.lopez@cafecoocentral.com',
            'password' => Hash::make('password'),
            'role' => 'Administradora',
            'is_admin' => true,
        ]);

        User::create([
            'name' => 'Carlos Ramírez',
            'email' => 'carlos.ramirez@cafecoocentral.com',
            'password' => Hash::make('password'),
            'role' => 'Vendedor',
            'is_admin' => false,
            'modulos' => ['pedidos.nuevo', 'pedidos.index', 'pedidos-web.index', 'clientes.index', 'notificaciones.index'],
        ]);

        User::create([
            'name' => 'Ana Facturación',
            'email' => 'facturacion@cafecoocentral.com',
            'password' => Hash::make('password'),
            'role' => 'Facturadora',
            'is_admin' => false,
            'modulos' => ['facturacion.index'],
        ]);
    }
}
