<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MaterialArticulo;
use App\Models\Articulo;
use App\Enums\TipoMaterial; // <--- Importamos tu Enum

class MaterialArticuloSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Buscamos un artículo para asignarle los materiales
        $articulo = Articulo::first();

        if (!$articulo) {
            $this->command->error('❌ No hay artículos creados. Corre el CatalogoSeeder primero.');
            return;
        }

        $this->command->info('✅ Creando materiales para el artículo: ' . $articulo->nombre);

        // 2. Creamos un MANUAL (PDF)
        MaterialArticulo::create([
            'articulo_id' => $articulo->id,
            'tipo' => TipoMaterial::MANUAL, // Usamos el Enum directo
            'titulo' => 'Manual de Usuario 2026',
            'descripcion' => 'Guía completa de instalación y mantenimiento.',
            'url' => 'https://www.orion.com/descargas/manual-usuario.pdf',
            'fecha_subida' => now(),
        ]);

        // 3. Creamos un DATASHEET (Ficha Técnica)
        MaterialArticulo::create([
            'articulo_id' => $articulo->id,
            'tipo' => TipoMaterial::DATASHEET,
            'titulo' => 'Ficha Técnica v1.0',
            'descripcion' => 'Especificaciones eléctricas y dimensiones.',
            'url' => 'https://www.orion.com/descargas/ficha-tecnica.pdf',
            'fecha_subida' => now()->subDays(2), // Subido hace 2 días
        ]);

        // 4. Creamos un ENLACE (Video de YouTube)
        MaterialArticulo::create([
            'articulo_id' => $articulo->id,
            'tipo' => TipoMaterial::ENLACE,
            'titulo' => 'Video Tutorial de Montaje',
            'descripcion' => 'Video explicativo en YouTube.',
            'url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'fecha_subida' => now(),
        ]);
    }
}