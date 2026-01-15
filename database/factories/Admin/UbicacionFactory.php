<?php

namespace Database\Factories\Admin;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Ubicacion;
use App\Models\Direccion;

class UbicacionFactory extends Factory
{
    protected $model = Ubicacion::class;

    public function definition(): array
    {
        return [
            'direccion_id' => Direccion::factory(),
            'edificio' => $this->faker->randomElement(['Principal', 'Anexo A', 'Anexo B', null]),
            'piso' => $this->faker->randomElement(['1', '2', '3', '4', '5', null]),
            'salon' => $this->faker->randomElement(['101', '102', '201', '202', '301', null]),
        ];
    }
}
