<?php

namespace Database\Factories\Inventario;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Proveedor;

class ProveedorFactory extends Factory
{
    protected $model = Proveedor::class;

    public function definition(): array
    {
        return [
            'nombre' => $this->faker->company(),
            'contacto' => $this->faker->name(),
            'telefono' => $this->faker->phoneNumber(),
            'email' => $this->faker->unique()->safeEmail(),
        ];
    }
}
