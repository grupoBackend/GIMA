<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Ubicacion;
use App\Models\Direccion; // Importante para poder crear la dirección

// Seeder para Ubicaciones con una Dirección generica
// y tres Ubicaciones vinculadas a esa Dirección para pruebas


class UbicacionSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Crear una Dirección usando los campos exactos de tu modelo Direccion.php
        $direccion = Direccion::create([
            'estado' => 'Bolívar',
            'ciudad' => 'Ciudad Guayana',
            'sector' => 'Unare',          // Campo requerido según tu modelo
            'calle'  => 'Av. Atlántico',  // Campo requerido según tu modelo
            'sede'   => 'Sede Principal', // Campo requerido según tu modelo
        ]);

        // 2. Crear las Ubicaciones vinculadas a esa dirección (usando $direccion->id)
        
        // Ubicación 1
        Ubicacion::create([
            'direccion_id' => $direccion->id, 
            'edificio'     => 'Edificio Central',
            'piso'         => 'Planta Baja',
            'salon'        => 'Recepción',
        ]);

        // Ubicación 2
        Ubicacion::create([
            'direccion_id' => $direccion->id,
            'edificio'     => 'Torre Administrativa',
            'piso'         => 'Piso 3',
            'salon'        => 'Sala de Conferencias',
        ]);

        // Ubicación 3
        Ubicacion::create([
            'direccion_id' => $direccion->id,
            'edificio'     => 'Almacén General',
            'piso'         => 'Sótano',
            'salon'        => 'Depósito 01',
        ]);
    }
}