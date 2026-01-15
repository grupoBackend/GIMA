<?php

namespace Database\Factories\Mantenimiento;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Mantenimiento;
use App\Models\Activo;
use App\Models\User;
use App\Models\Reporte;
use App\Enums\EstadoMantenimiento;
use App\Enums\TipoMantenimiento;

class MantenimientoFactory extends Factory
{
    protected $model = Mantenimiento::class;

    public function definition(): array
    {
        return [
            'activo_id' => Activo::factory(),
            'supervisor_id' => User::factory(),
            'tecnico_principal_id' => User::factory(),
            'tipo' => $this->faker->randomElement(TipoMantenimiento::cases()),
            'reporte_id' => null, // Default to null for preventive
            'fecha_apertura' => $this->faker->dateTimeThisYear(),
            'fecha_cierre' => $this->faker->dateTimeThisYear('+1 month'),
            'estado' => $this->faker->randomElement(EstadoMantenimiento::cases()),
            'descripcion' => $this->faker->paragraph(),
            'validado' => $this->faker->boolean(),
            'costo_total' => $this->faker->randomFloat(2, 50, 2000),
        ];
    }

    /**
     * Indicate that the maintenance is corrective and linked to a report.
     */
    public function correctivo(): Factory
    {
        return $this->state(fn (array $attributes) => [
            'tipo' => TipoMantenimiento::CORRECTIVO,
            'reporte_id' => Reporte::factory(),
        ]);
    }
}
