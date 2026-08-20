<?php

namespace Database\Seeders;

use App\Models\Bodega;
use Illuminate\Database\Seeder;

class BodegaSeeder extends Seeder
{
    public function run(): void
    {
        $bodegas = [
            ['nombre' => 'Bodega Central Garzón', 'direccion' => 'Zona Industrial, Vía al Aeropuerto', 'telefono' => '318 500 2200', 'contacto' => 'Carlos Muñoz'],
            ['nombre' => 'Bodega Neiva', 'direccion' => 'Cra 5 # 20-14, Neiva, Huila', 'telefono' => '312 445 7788', 'contacto' => 'Lorena Ríos'],
            ['nombre' => 'Finca Beneficiadero El Roble', 'direccion' => 'Vereda El Roble, Pitalito, Huila', 'telefono' => '300 220 9911', 'contacto' => 'Julián Perdomo'],
        ];

        foreach ($bodegas as $bodega) {
            Bodega::create($bodega);
        }
    }
}
