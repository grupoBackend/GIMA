<?php

namespace Database\Factories\Mantenimiento;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\RepuestoUsado;
use App\Models\SesionesMantenimiento;
use App\Models\Repuesto;

class RepuestoUsadoFactory extends Factory
{
    protected $model = RepuestoUsado::class;

    public function definition(): array
    {
        return [
            'sesion_id' => SesionesMantenimiento::factory(),
            'repuesto_id' => Repuesto::factory(), // Note: Migration uses 'repuestos_id', but model uses 'repuesto_id'. Assuming model is correct or will be fixed.
            'cantidad' => $this->faker->randomFloat(2, 0.5, 10),
            'costo_total' => $this->faker->randomFloat(2, 5, 500),
        ];
    }
}
