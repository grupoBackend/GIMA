<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Articulo;
use App\Enums\TipoArticulo;

class ArticuloSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Laptop (Es un EQUIPO)
        Articulo::create([
            'tipo'        => TipoArticulo::EQUIPO, // Laravel traduce esto a 'equipo' solo
            'marca'       => 'Dell',
            'modelo'      => 'Latitude 5420',
            'descripcion' => 'Laptop i7 con 16GB RAM para Desarrollo',
        ]);

        // 2. Video Beam (Es un EQUIPO)
        Articulo::create([
            'tipo'        => TipoArticulo::EQUIPO,
            'marca'       => 'Epson',
            'modelo'      => 'PowerLite X41',
            'descripcion' => 'Proyector para sala de conferencias',
        ]);

        // 3. Silla (Es MOBILIARIO)
        Articulo::create([
            'tipo'        => TipoArticulo::MOBILIARIO,
            'marca'       => 'Herman Miller',
            'modelo'      => 'Aeron',
            'descripcion' => 'Silla ergonómica operativa',
        ]);
    }
}