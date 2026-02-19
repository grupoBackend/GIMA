<?php

namespace Database\Factories\Inventario;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Repuesto;
use App\Models\Proveedor;
use App\Models\Direccion;

class RepuestoFactory extends Factory
{
    protected $model = Repuesto::class;

    public function definition(): array
    {
        return [
            'proveedor_id' => Proveedor::factory(),
            'direccion_id' => Direccion::factory(),
            'descripcion' => $this->faker->sentence(),
            'codigo' => $this->faker->unique()->ean8(),
            'stock' => $this->faker->randomNumber(2, false),
            'stock_minimo' => $this->faker->randomNumber(2, false),
            'costo' => $this->faker->randomFloat(2, 5, 500),
        ];
    }
}
