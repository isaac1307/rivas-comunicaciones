<?php

namespace Database\Factories;

use App\Models\Cliente;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ticket>
 */
class TicketFactory extends Factory
{
    protected $model = Ticket::class;

    public function definition(): array
    {
        return [
            // El folio se generará automáticamente por el observer si viene vacío
            'folio' => null,
            'cliente_id' => Cliente::factory(),
            'categoria' => fake()->randomElement(['falla_servicio', 'soporte_tecnico', 'consulta', 'cambio_plan', 'cancelacion']),
            'descripcion' => fake()->paragraph(),
            'estado' => 'abierto',
            'fecha_resolucion' => null,
            'observaciones_admin' => null,
        ];
    }
}
