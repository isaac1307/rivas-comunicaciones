<?php

namespace Database\Factories;

use App\Models\Bitacora;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Bitacora>
 */
class BitacoraFactory extends Factory
{
    protected $model = Bitacora::class;

    public function definition(): array
    {
        return [
            'usuario_id' => User::factory(),
            'accion' => fake()->randomElement(['creacion', 'actualizacion', 'eliminacion']),
            'descripcion' => fake()->sentence(),
            'tabla_afectada' => fake()->randomElement(['planes', 'promociones', 'clientes', 'reservaciones', 'tickets']),
            'registro_id' => fake()->randomNumber(3),
            'ip_origen' => fake()->ipv4(),
        ];
    }
}
