<?php

namespace Database\Factories;

use App\Models\Cargo;
use App\Models\Cliente;
use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Cargo>
 */
class CargoFactory extends Factory
{
    protected $model = Cargo::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $monto = fake()->randomElement([349.00, 499.00, 699.00, 999.00]);

        return [
            'cliente_id' => Cliente::factory(),
            'plan_id' => Plan::factory(),
            'tipo' => 'mensualidad',
            'periodo' => now()->format('Y-m'),
            'concepto' => 'Mensualidad Internet Fibra Óptica',
            'monto' => $monto,
            'monto_pagado' => 0,
            'fecha_vencimiento' => now()->startOfMonth()->addDays(9)->toDateString(),
            'estado' => 'pendiente',
        ];
    }

    /**
     * Estado vencido
     */
    public function vencido(): static
    {
        return $this->state(fn () => [
            'fecha_vencimiento' => now()->subDays(5)->toDateString(),
            'estado' => 'pendiente',
        ]);
    }

    /**
     * Estado pagado
     */
    public function pagado(): static
    {
        return $this->state(function (array $attributes) {
            $monto = $attributes['monto'] ?? 499.00;

            return [
                'monto' => $monto,
                'monto_pagado' => $monto,
                'estado' => 'pagado',
            ];
        });
    }
}
