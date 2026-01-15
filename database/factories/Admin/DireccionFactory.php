<?php

namespace Database\Factories\Admin;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Direccion;

class DireccionFactory extends Factory
{
    protected $model = Direccion::class;

    public function definition(): array
    {
        return [
            'estado' => $this->faker->state(),
            'ciudad' => $this->faker->city(),
            'sector' => $this->faker->streetName(),
            'calle' => $this->faker->streetAddress(),
            'sede' => $this->faker->company(), // company name usually isn't combined with suffix in Spanish
        ];
    }
}
