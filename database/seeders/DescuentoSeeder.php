<?php

namespace Database\Seeders;

use App\Models\Descuento;
use Illuminate\Database\Seeder;

class DescuentoSeeder extends Seeder
{
    public function run(): void
    {
        Descuento::create(['nombre' => 'Sin descuento', 'tipo' => 'fijo', 'valor' => 0]);
        Descuento::create(['nombre' => 'Descuento autorizado', 'tipo' => 'fijo', 'valor' => 5000]);
        Descuento::create(['nombre' => 'Descuento 10% VIP', 'tipo' => 'porcentaje', 'valor' => 10]);
    }
}
