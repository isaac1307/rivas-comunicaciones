<?php

namespace App\Actions\Cobranza;

use App\Enums\EstadoCliente;
use App\Enums\EstadoPago;
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

            $montoCentavos = (int) round((float) $datos['monto'] * 100);
            $saldoCentavos = (int) $cargosPendientes->sum(fn (Cargo $cargo) => $cargo->saldoEnCentavos());
            $monto = round($montoCentavos / 100, 2);
            $saldo = round($saldoCentavos / 100, 2);
            $permitirSaldoAFavor = (bool) ($datos['permitir_saldo_a_favor'] ?? config('cobranza.permitir_saldo_a_favor', false));

            if ($saldoCentavos <= 0 && ! $permitirSaldoAFavor) {
                throw ValidationException::withMessages([
                    'monto' => 'El cliente no tiene saldo pendiente por cobrar.',
                ]);
            }

            if ($montoCentavos > $saldoCentavos && ! $permitirSaldoAFavor) {
                throw ValidationException::withMessages([
                    'monto' => 'El monto ($'.number_format($monto, 2).') excede el saldo pendiente del cliente ($'.number_format($saldo, 2).').',
                ]);
            }

            $pago = Pago::create([
                'cliente_id' => $clienteBloqueado->id,
                'usuario_id' => $cobrador->id,
                'monto' => number_format($montoCentavos / 100, 2, '.', ''),
                'monto_saldo_favor' => '0.00',
                'metodo_pago' => $datos['metodo_pago'],
                'referencia' => $datos['referencia'] ?? null,
                'fecha_pago' => $datos['fecha_pago'] ?? now(),
                'notas' => $datos['notas'] ?? null,
                'estado' => EstadoPago::Aplicado->value,
            ]);

            $restanteCentavos = $montoCentavos;

            foreach ($cargosPendientes as $cargo) {
                if ($restanteCentavos <= 0) {
                    break;
                }

                $aplicadoCentavos = min($restanteCentavos, $cargo->saldoEnCentavos());
                $pagadoCargoCentavos = (int) round((float) $cargo->monto_pagado * 100) + $aplicadoCentavos;

                $cargo->monto_pagado = number_format($pagadoCargoCentavos / 100, 2, '.', '');
                $cargo->recalcularEstado();
                $cargo->save();

                $pago->cargos()->attach($cargo->id, [
                    'monto_aplicado' => number_format($aplicadoCentavos / 100, 2, '.', ''),
                ]);

                $restanteCentavos -= $aplicadoCentavos;
            }

            if ($restanteCentavos > 0) {
                $pago->monto_saldo_favor = number_format($restanteCentavos / 100, 2, '.', '');
                $pago->save();

                $saldoFavorActualCentavos = (int) round((float) $clienteBloqueado->saldo_a_favor * 100);
                $clienteBloqueado->saldo_a_favor = number_format(($saldoFavorActualCentavos + $restanteCentavos) / 100, 2, '.', '');
                $clienteBloqueado->save();
            }

            // Si el cliente estaba suspendido y liquidó todos sus cargos vencidos, reactivarlo en BD
            if ($clienteBloqueado->estado === EstadoCliente::Suspendido->value
                && ! $clienteBloqueado->cargos()->vencidos()->exists()) {
                $clienteBloqueado->estado = EstadoCliente::Activo->value;
                $clienteBloqueado->save();
            }

            return $pago;
        });

        $tieneCargosVencidos = $cliente->cargos()->vencidos()->exists();
        if (! $tieneCargosVencidos && ! empty($cliente->ip_asignada)) {
            $cliente->loadMissing('router');
            $router = $cliente->router;

            if ($router instanceof Router && $router->isActivo()) {
                ReconectarServicioRedJob::dispatch($router, $cliente->ip_asignada);
            }
        }

        return $pago;
    }
}
