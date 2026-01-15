<?php

namespace Database\Factories\Mantenimiento;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\SesionesMantenimiento;
use App\Models\Mantenimiento;
use App\Models\User;

class SesionesMantenimientoFactory extends Factory
{
    protected $model = SesionesMantenimiento::class;

    public function definition(): array
    {
        return [
            'mantenimiento_id' => Mantenimiento::factory(),
            'tecnico_id' => User::factory(),
            'fecha' => $this->faker->dateTimeThisMonth(),
            'horas_trabajadas' => $this->faker->randomFloat(2, 1, 8),
            'observaciones' => $this->faker->sentence(),
            'descripcion_trabajo' => $this->faker->paragraph(),
            'costo_hora' => $this->faker->randomFloat(2, 15, 50),
        ];
    }
}
