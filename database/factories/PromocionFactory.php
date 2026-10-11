<?php

namespace Database\Factories;

use App\Models\Promocion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Promocion>
 */
class PromocionFactory extends Factory
{
    protected $model = Promocion::class;

    public function definition(): array
    {
        return [
            'titulo' => fake()->sentence(4),
            'descripcion' => fake()->paragraph(),
            'fecha_inicio' => now()->subDays(5)->toDateString(),
            'fecha_vencimiento' => now()->addDays(30)->toDateString(),
            'estado' => 'activa',
        ];
    }
}
