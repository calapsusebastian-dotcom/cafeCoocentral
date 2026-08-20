<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            ProductoSeeder::class,
            ClienteSeeder::class,
            DescuentoSeeder::class,
            TransportadoraSeeder::class,
            BodegaSeeder::class,
            VendorUserSeeder::class,
            PedidoSeeder::class,
            NotificacionSeeder::class,
        ]);
    }
}
