<?php

namespace Database\Factories;

use App\Models\Cliente;
use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Cliente>
 */
class ClienteFactory extends Factory
{
    protected $model = Cliente::class;

    public function definition(): array
    {
        return [
            'nombre_completo' => fake()->name(),
            'direccion' => fake()->streetAddress().', Col. Centro, C.P. 62000',
            'telefono' => fake()->numerify('777#######'),
            'correo_electronico' => fake()->safeEmail(),
            'plan_id' => Plan::factory(),
            'fecha_alta' => now()->subMonths(rand(1, 12))->toDateString(),
            'estado' => 'activo',
            'notas' => fake()->optional()->sentence(),
        ];
    }
}
