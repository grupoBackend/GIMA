<?php

namespace Database\Factories\Catalogo;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\MaterialArticulo;
use App\Models\Articulo;
use App\Enums\TipoMaterial;

class MaterialArticuloFactory extends Factory
{
    protected $model = MaterialArticulo::class;

    public function definition(): array
    {
        return [
            'articulo_id' => Articulo::factory(),
            'tipo' => $this->faker->randomElement(TipoMaterial::cases()),
            'titulo' => $this->faker->sentence(3),
            'descripcion' => $this->faker->paragraph(),
            'url' => $this->faker->url(),
            'fecha_subida' => $this->faker->dateTimeThisYear(),
        ];
    }
}
