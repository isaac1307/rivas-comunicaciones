<?php

namespace App\Actions\Cobranza;

use App\Models\Cargo;
use App\Models\Cliente;
use App\Models\Pago;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RegistrarPago
{
    /**
     * Registra un pago del cliente y lo aplica a sus cargos pendientes, del más antiguo al más reciente.
     *
     * @param  array<string, mixed>  $datos
     *
     * @throws ValidationException Si el monto excede el saldo pendiente del cliente.
     */
    public function handle(Cliente $cliente, array $datos, User $cobrador): Pago
    {
        return DB::transaction(function () use ($cliente, $datos, $cobrador) {
            $cargosPendientes = $cliente->cargos()
                ->pendientes()
                ->orderBy('fecha_vencimiento')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            $monto = round((float) $datos['monto'], 2);
            $saldo = round($cargosPendientes->sum(fn (Cargo $cargo) => $cargo->saldo()), 2);

            if ($saldo <= 0) {
                throw ValidationException::withMessages([
                    'monto' => 'El cliente no tiene saldo pendiente por cobrar.',
                ]);
            }

            if ($monto > $saldo) {
                throw ValidationException::withMessages([
                    'monto' => 'El monto ($'.number_format($monto, 2).') excede el saldo pendiente del cliente ($'.number_format($saldo, 2).').',
                ]);
            }

            $pago = Pago::create([
                'cliente_id' => $cliente->id,
                'usuario_id' => $cobrador->id,
                'monto' => $monto,
                'metodo_pago' => $datos['metodo_pago'],
                'referencia' => $datos['referencia'] ?? null,
                'fecha_pago' => $datos['fecha_pago'] ?? now(),
                'notas' => $datos['notas'] ?? null,
                'estado' => 'aplicado',
            ]);

            $restante = $monto;

            foreach ($cargosPendientes as $cargo) {
                if ($restante <= 0) {
                    break;
                }

                $aplicado = min($restante, $cargo->saldo());

                $cargo->monto_pagado = (string) round((float) $cargo->monto_pagado + $aplicado, 2);
                $cargo->recalcularEstado();
                $cargo->save();

                $pago->cargos()->attach($cargo->id, ['monto_aplicado' => $aplicado]);

                $restante = round($restante - $aplicado, 2);
            }

            return $pago;
        });
    }
}
