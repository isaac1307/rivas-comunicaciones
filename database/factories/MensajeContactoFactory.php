<?php

namespace Database\Factories;

use App\Models\MensajeContacto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MensajeContacto>
 */
class MensajeContactoFactory extends Factory
{
    protected $model = MensajeContacto::class;

    public function definition(): array
    {
        return [
            'nombre' => fake()->name(),
            'correo_electronico' => fake()->safeEmail(),
            'telefono' => fake()->numerify('777#######'),
            'asunto' => fake()->sentence(3),
            'mensaje' => fake()->paragraph(),
            'leido' => false,
        ];
    }
}
