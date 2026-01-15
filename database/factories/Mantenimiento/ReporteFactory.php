<?php

namespace Database\Factories\Mantenimiento;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Reporte;
use App\Models\User;
use App\Models\Activo;
use App\Enums\NivelPrioridad;
use App\Enums\EstadoReporte;

class ReporteFactory extends Factory
{
    protected $model = Reporte::class;

    public function definition(): array
    {
        return [
            'usuario_id' => User::factory(),
            'activo_id' => Activo::factory(),
            'descripcion' => $this->faker->paragraph(),
            'prioridad' => $this->faker->randomElement(NivelPrioridad::cases()),
            'estado' => $this->faker->randomElement(EstadoReporte::cases()),
        ];
    }
}
