<?php

namespace Database\Factories\Admin;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\HistorialLogs;
use App\Models\User;

class HistorialLogsFactory extends Factory
{
    protected $model = HistorialLogs::class;

    public function definition(): array
    {
        $entities = ['User', 'Activo', 'Mantenimiento', 'Reporte'];
        $entity = $this->faker->randomElement($entities);

        return [
            'usuario_id' => User::factory(),
            'entidad' => $entity,
            'entidad_id' => $this->faker->randomNumber(2), // Generic ID
            'accion' => $this->faker->randomElement(['creado', 'actualizado', 'eliminado', 'aprobado']),
            'descripcion' => $this->faker->sentence(),
            'fecha' => $this->faker->dateTimeThisYear(),
        ];
    }
}
