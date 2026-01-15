<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Activo;
use App\Enums\EstadoActivo;

class ActivoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Activo para la Laptop Dell (Articulo ID 1)
        Activo::create([
            'articulo_id'  => 1, // Corresponde a la Laptop Latitude 5420
            'ubicacion_id' => 1, // Asumiendo que existe una ubicación (Ej: Oficina Principal)
            'estado'       => EstadoActivo::BAJA, // Estado del activo
            'valor'        => 1200.50, // Valor monetario del activo
        ]);

        // 2. Activo para el Video Beam Epson (Articulo ID 2)
        Activo::create([
            'articulo_id'  => 2, // Corresponde al Proyector PowerLite
            'ubicacion_id' => 2, // Misma ubicación (Sala de Conferencias)
            'estado'       => EstadoActivo::MANTENIMIENTO, // Para variar el estado
            'valor'        => 450.00,
        ]);

        // 3. Activo para la Silla Herman Miller (Articulo ID 3)
        Activo::create([
            'articulo_id'  => 3, // Corresponde a la Silla Aeron
            'ubicacion_id' => 3, // Otra ubicación (Ej: Oficina de Gerencia)
            'estado'       => EstadoActivo::OPERATIVO,
            'valor'        => 850.00,
        ]);
    }
}
