<?php

namespace Database\Factories\General;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Notificacion;
use App\Models\User;

class NotificacionFactory extends Factory
{
    protected $model = Notificacion::class;

    public function definition(): array
    {
        return [
            'usuario_id' => User::factory(),
            'contenido' => $this->faker->sentence(),
            'leido' => $this->faker->boolean(50), // 50% chance of being read
        ];
    }
}
