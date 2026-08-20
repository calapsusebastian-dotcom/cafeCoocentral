<?php

namespace Database\Seeders;

use App\Models\Transportadora;
use Illuminate\Database\Seeder;

class TransportadoraSeeder extends Seeder
{
    public function run(): void
    {
        Transportadora::create(['nombre' => 'Coordinadora', 'costo' => 8000]);
        Transportadora::create(['nombre' => 'Servientrega', 'costo' => 9500]);
        Transportadora::create(['nombre' => 'Envío propio', 'costo' => 0]);
    }
}
