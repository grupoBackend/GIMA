<?php

namespace Database\Factories\Catalogo;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Articulo;
use App\Enums\TipoArticulo;

class ArticuloFactory extends Factory
{
    protected $model = Articulo::class;

    public function definition(): array
    {
        return [
            'tipo' => $this->faker->randomElement(TipoArticulo::cases()),
            'marca' => $this->faker->company(),
            'modelo' => $this->faker->word() . ' ' . $this->faker->randomNumber(3),
            'descripcion' => $this->faker->sentence(),
        ];
    }
}
