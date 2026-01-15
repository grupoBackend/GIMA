<?php

namespace Database\Factories\Catalogo;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Activo;
use App\Models\Articulo;
use App\Models\Ubicacion;
use App\Enums\EstadoActivo;

class ActivoFactory extends Factory
{
    protected $model = Activo::class;

    public function definition(): array
    {
        return [
            'articulo_id' => Articulo::factory(),
            'ubicacion_id' => Ubicacion::factory(),
            'estado' => $this->faker->randomElement(EstadoActivo::cases()),
            'valor' => $this->faker->randomFloat(2, 100, 10000),
        ];
    }
}
