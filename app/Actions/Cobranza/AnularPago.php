<?php

namespace App\Actions\Cobranza;

use App\Enums\EstadoCliente;
use App\Enums\EstadoPago;
use App\Jobs\Red\SuspenderServicioRedJob;
use App\Models\Cargo;
use App\Models\Cliente;
use App\Models\Pago;
use App\Models\Router;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AnularPago
{
    /**
     * Anula un pago y revierte los abonos que hizo a cada cargo.
     *
     * @throws ValidationException Si el pago ya estaba anulado.
     */
    public function handle(Pago $pago, string $motivo): void
    {
        /** @var Cliente $clienteAfectado */
        $clienteAfectado = DB::transaction(function () use ($pago, $motivo) {
            /** @var Pago $pagoBloqueado */
            $pagoBloqueado = Pago::whereKey($pago->id)->lockForUpdate()->firstOrFail();

            if ($pagoBloqueado->estaAnulado()) {
                throw ValidationException::withMessages([
                    'motivo_anulacion' => 'Este pago ya fue anulado anteriormente.',
                ]);
            }

            /** @var Cliente $cliente */
            $cliente = Cliente::whereKey($pagoBloqueado->cliente_id)->lockForUpdate()->firstOrFail();

            $montoSaldoFavorPagoCentavos = (int) round((float) $pagoBloqueado->monto_saldo_favor * 100);
            if ($montoSaldoFavorPagoCentavos > 0) {
                $saldoFavorActualCentavos = (int) round((float) $cliente->saldo_a_favor * 100);

                if ($saldoFavorActualCentavos < $montoSaldoFavorPagoCentavos) {
                    throw ValidationException::withMessages([
                        'motivo_anulacion' => 'No es posible anular este pago porque el cliente ya ha utilizado parte del saldo a favor generado ($'.number_format($montoSaldoFavorPagoCentavos / 100, 2).'). Saldo a favor disponible: $'.number_format($saldoFavorActualCentavos / 100, 2).'.',
                    ]);
                }

                $cliente->saldo_a_favor = number_format(($saldoFavorActualCentavos - $montoSaldoFavorPagoCentavos) / 100, 2, '.', '');
                $cliente->save();
            }

            // Marcar el pago como anulado antes de recalcular los cargos
            $pagoBloqueado->estado = EstadoPago::Anulado->value;
            $pagoBloqueado->motivo_anulacion = $motivo;
            $pagoBloqueado->save();

            $cargoIds = $pagoBloqueado->cargos()->pluck('cargos.id')->all();
            if (! empty($cargoIds)) {
                $cargosAfectados = Cargo::whereIn('id', $cargoIds)->lockForUpdate()->get();

                foreach ($cargosAfectados as $cargo) {
                    $montoPagadoValido = (float) DB::table('aplicaciones_pago')
                        ->join('pagos', 'aplicaciones_pago.pago_id', '=', 'pagos.id')
                        ->where('aplicaciones_pago.cargo_id', $cargo->id)
                        ->where('pagos.estado', EstadoPago::Aplicado->value)
                        ->sum('aplicaciones_pago.monto_aplicado');

                    $pagadoCentavos = (int) round($montoPagadoValido * 100);
                    $cargo->monto_pagado = number_format($pagadoCentavos / 100, 2, '.', '');
                    $cargo->recalcularEstado();
                    $cargo->save();
                }
            }

            if ($cliente->cargos()->vencidos()->exists() && $cliente->estado === EstadoCliente::Activo->value) {
                $cliente->estado = EstadoCliente::Suspendido->value;
                $cliente->save();
            }

            return $cliente;
        });

        $tieneCargosVencidos = $clienteAfectado->cargos()->vencidos()->exists();
        if ($tieneCargosVencidos && ! empty($clienteAfectado->ip_asignada)) {
            $clienteAfectado->loadMissing('router');
            $router = $clienteAfectado->router;

            if ($router instanceof Router && $router->isActivo()) {
                SuspenderServicioRedJob::dispatch($router, $clienteAfectado->ip_asignada, (string) $clienteAfectado->id);
            }
        }
    }
}
