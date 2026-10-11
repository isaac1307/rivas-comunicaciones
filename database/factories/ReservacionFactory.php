<?php

namespace Database\Factories;

use App\Models\Plan;
use App\Models\Reservacion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Reservacion>
 */
class ReservacionFactory extends Factory
{
    protected $model = Reservacion::class;

    public function definition(): array
    {
        return [
            // El folio se generará automáticamente por el observer si viene vacío
            'folio' => null,
            'nombre_solicitante' => fake()->name(),
            'telefono' => fake()->numerify('777#######'),
            'direccion' => fake()->address(),
            'correo_electronico' => fake()->safeEmail(),
            'tipo_servicio' => fake()->randomElement(['instalacion_nueva', 'visita_tecnica', 'atencion_personalizada']),
            'plan_id' => Plan::factory(),
            'fecha_solicitada' => now()->addDays(rand(1, 10))->toDateString(),
            'observaciones' => fake()->optional()->sentence(),
            'estado' => 'pendiente',
        ];
    }
}
