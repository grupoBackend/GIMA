<?php

namespace Database\Factories\Mantenimiento;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\CalendarioMantenimiento;
use App\Models\Activo;
use App\Models\User;
use App\Enums\EstadoMantenimiento;
use App\Enums\TipoMantenimiento;

class CalendarioMantenimientoFactory extends Factory
{
    protected $model = CalendarioMantenimiento::class;

    public function definition(): array
    {
        return [
            'activo_id' => Activo::factory(),
            'tecnico_asignado_id' => User::factory(),
            'tipo' => $this->faker->randomElement(TipoMantenimiento::cases()),
            'fecha_programada' => $this->faker->dateTimeBetween('now', '+1 year'),
            'descripcion' => $this->faker->sentence(),
            'estado' => $this->faker->randomElement(EstadoMantenimiento::cases()),
        ];
    }
}
