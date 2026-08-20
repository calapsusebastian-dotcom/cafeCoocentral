<?php

namespace Database\Seeders;

use App\Models\Cliente;
use Illuminate\Database\Seeder;

class ClienteSeeder extends Seeder
{
    public function run(): void
    {
        $clientes = [
            ['nombre' => 'Distribuciones del Huila S.A.S', 'tipo_persona' => 'juridica', 'documento' => '900.123.456-7', 'codigo' => 'CLI-001', 'telefono' => '321 456 7890', 'email' => 'contacto@distrihuila.com', 'direccion' => 'Cra 8 # 16-45 Barrio Altico', 'ciudad' => 'Garzón, Huila', 'tipo_precio' => 'mayorista'],
            ['nombre' => 'Tostados La Esquina', 'tipo_persona' => 'natural', 'documento' => '901.234.567-1', 'codigo' => 'CLI-002', 'telefono' => '311 222 3344', 'email' => 'laesquina@example.com', 'direccion' => 'Calle 5 # 10-20', 'ciudad' => 'Neiva, Huila', 'tipo_precio' => 'minorista'],
            ['nombre' => 'Café Express del Sur', 'tipo_persona' => 'juridica', 'documento' => '830.987.654-2', 'codigo' => 'CLI-003', 'telefono' => '300 555 1122', 'email' => 'ventas@expresodelsur.com', 'direccion' => 'Av. Panamericana # 3-50', 'ciudad' => 'Pitalito, Huila', 'tipo_precio' => 'mayorista'],
            ['nombre' => 'Panadería y Café San José', 'tipo_persona' => 'natural', 'documento' => '1075.334.221', 'codigo' => 'CLI-004', 'telefono' => '315 987 6543', 'email' => null, 'direccion' => 'Cra 12 # 8-33', 'ciudad' => 'Garzón, Huila', 'tipo_precio' => 'minorista'],
            ['nombre' => 'Distribuidora Andina Ltda', 'tipo_persona' => 'juridica', 'documento' => '812.445.998-9', 'codigo' => 'CLI-005', 'telefono' => '318 776 4400', 'email' => 'compras@andina.com.co', 'direccion' => 'Zona Industrial Bod. 12', 'ciudad' => 'Bogotá D.C.', 'tipo_precio' => 'distribuidor'],
            ['nombre' => 'Supermercado La Cosecha', 'tipo_persona' => 'juridica', 'documento' => '900.556.213-4', 'codigo' => 'CLI-006', 'telefono' => '312 890 1234', 'email' => 'compras@lacosecha.com', 'direccion' => 'Calle 20 # 15-08', 'ciudad' => 'Garzón, Huila', 'tipo_precio' => 'mayorista'],
        ];

        foreach ($clientes as $cliente) {
            Cliente::create($cliente + ['puntos' => random_int(0, 300)]);
        }
    }
}
