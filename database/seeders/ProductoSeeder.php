<?php

namespace Database\Seeders;

use App\Models\Producto;
use Illuminate\Database\Seeder;

class ProductoSeeder extends Seeder
{
    public function run(): void
    {
        $productos = [
            ['nombre' => 'Café Clásico', 'categoria' => 'Tostado', 'presentacion' => '500g', 'sku' => 'CD500', 'precio' => 29900, 'stock' => 120],
            ['nombre' => 'Café Especial', 'categoria' => 'Tostado', 'presentacion' => '250g', 'sku' => 'CE250', 'precio' => 17500, 'stock' => 85],
            ['nombre' => 'Café Premium', 'categoria' => 'Tostado', 'presentacion' => '250g', 'sku' => 'CP250', 'precio' => 19900, 'stock' => 60],
            ['nombre' => 'Café Mujeres', 'categoria' => 'Origen', 'presentacion' => '250g', 'sku' => 'CM250', 'precio' => 18900, 'stock' => 40],
            ['nombre' => 'Notas de Juventud', 'categoria' => 'Origen', 'presentacion' => '250g', 'sku' => 'NJ250', 'precio' => 17500, 'stock' => 20],
            ['nombre' => 'Café Orgánico', 'categoria' => 'Origen', 'presentacion' => '500g', 'sku' => 'CO500', 'precio' => 32900, 'stock' => 55],
            ['nombre' => 'Café Descafeinado', 'categoria' => 'Tostado', 'presentacion' => '340g', 'sku' => 'CD340', 'precio' => 21900, 'stock' => 30],
            ['nombre' => 'Café Geisha', 'categoria' => 'Origen', 'presentacion' => '250g', 'sku' => 'CG250', 'precio' => 45900, 'stock' => 15],
        ];

        foreach ($productos as $producto) {
            Producto::create($producto + ['activo' => true]);
        }
    }
}
