<?php

namespace Database\Factories;

use App\Models\Cliente;
use App\Models\Pago;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pago>
 */
class PagoFactory extends Factory
{
    protected $model = Pago::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cliente_id' => Cliente::factory(),
            'usuario_id' => User::factory(),
            'monto' => 499.00,
            'metodo_pago' => 'efectivo',
            'referencia' => null,
            'fecha_pago' => now(),
            'notas' => null,
            'estado' => 'aplicado',
            'motivo_anulacion' => null,
        ];
    }

    /**
     * Estado anulado
     */
    public function anulado(): static
    {
        return $this->state(fn () => [
            'estado' => 'anulado',
            'motivo_anulacion' => 'Error de captura o cheque rebotado.',
        ]);
    }
}
