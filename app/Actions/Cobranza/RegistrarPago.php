<?php

namespace App\Actions\Cobranza;

use App\Jobs\Red\ReconectarServicioRedJob;
use App\Models\Cargo;
use App\Models\Cliente;
use App\Models\Pago;
use App\Models\Router;
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
        $pago = DB::transaction(function () use ($cliente, $datos, $cobrador) {
            /** @var Cliente $clienteBloqueado */
            $clienteBloqueado = Cliente::query()->lockForUpdate()->findOrFail($cliente->id);

            $cargosPendientes = $clienteBloqueado->cargos()
                ->pendientes()
                ->orderBy('fecha_vencimiento', 'asc')
                ->orderBy('id', 'asc')
                ->lockForUpdate()
                ->get();

            $monto = round((float) $datos['monto'], 2);
            $saldo = round($cargosPendientes->sum(fn (Cargo $cargo) => $cargo->saldo()), 2);
            $permitirSaldoAFavor = (bool) ($datos['permitir_saldo_a_favor'] ?? config('cobranza.permitir_saldo_a_favor', false));

            if ($saldo <= 0 && ! $permitirSaldoAFavor) {
                throw ValidationException::withMessages([
                    'monto' => 'El cliente no tiene saldo pendiente por cobrar.',
                ]);
            }

            if ($monto > $saldo && ! $permitirSaldoAFavor) {
                throw ValidationException::withMessages([
                    'monto' => 'El monto ($'.number_format($monto, 2).') excede el saldo pendiente del cliente ($'.number_format($saldo, 2).').',
                ]);
            }

            $pago = Pago::create([
                'cliente_id' => $clienteBloqueado->id,
                'usuario_id' => $cobrador->id,
                'monto' => $monto,
                'monto_saldo_favor' => 0.00,
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

            if ($restante > 0) {
                $pago->monto_saldo_favor = (string) $restante;
                $pago->save();

                $clienteBloqueado->saldo_a_favor = (string) round((float) $clienteBloqueado->saldo_a_favor + $restante, 2);
                $clienteBloqueado->save();
            }

            return $pago;
        });

        $tieneCargosVencidos = $cliente->cargos()->vencidos()->exists();
        if (! $tieneCargosVencidos && ! empty($cliente->ip_asignada)) {
            $cliente->loadMissing('router');
            $router = $cliente->router ?? Router::query()->where('estado', 'activo')->first();

            if ($router instanceof Router && $router->isActivo()) {
                ReconectarServicioRedJob::dispatch($router, $cliente->ip_asignada);
            }
        }

        return $pago;
    }
}
