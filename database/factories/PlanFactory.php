<?php

namespace Database\Factories;

use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Plan>
 */
class PlanFactory extends Factory
{
    protected $model = Plan::class;

    public function definition(): array
    {
        $velocidad = fake()->randomElement([50, 100, 200, 300, 500, 1000]);

        return [
            'nombre' => 'Plan Fibra '.$velocidad.' Megas '.fake()->unique()->randomNumber(3),
            'velocidad_descarga' => $velocidad,
            'velocidad_carga' => (int) ($velocidad / 2),
            'precio_mensual' => fake()->randomFloat(2, 299, 1199),
            'descripcion' => fake()->paragraph(),
            'estado' => 'activo',
        ];
    }
}
