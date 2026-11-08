<?php

namespace App\Actions\Cobranza;

use App\Enums\EstadoCargo;
use App\Enums\EstadoCliente;
use App\Enums\TipoCargo;
use App\Models\Cargo;
use App\Models\Cliente;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

class GenerarCargosMensuales
{
    /**
     * Genera la mensualidad del periodo para cada cliente activo con plan asignado.
     *
     * Es idempotente y segura frente a concurrencia: bloquea individualmente cada cliente,
     * valida la ausencia de mensualidad en el periodo bajo bloqueo y aplica saldo a favor en centavos.
     *
     * @return int Número de cargos creados.
     */
    public function handle(CarbonInterface $periodo): int
    {
        $clavePeriodo = $periodo->format('Y-m');
        $diaVencimiento = min(max((int) config('cobranza.dia_vencimiento', 10), 1), 28);
        $fechaVencimiento = $periodo->copy()->startOfMonth()->day($diaVencimiento);
        $creados = 0;

        $estadosPermitidos = config('cobranza.facturar_suspendidos', false)
            ? [EstadoCliente::Activo->value, EstadoCliente::Suspendido->value]
            : [EstadoCliente::Activo->value];

        Cliente::query()
            ->whereIn('estado', $estadosPermitidos)
            ->whereNotNull('plan_id')
            ->whereDoesntHave('cargos', fn ($q) => $q
                ->where('tipo', TipoCargo::Mensualidad->value)
                ->where('periodo', $clavePeriodo))
            ->orderBy('id')
            ->chunkById(200, function ($clientes) use ($clavePeriodo, $fechaVencimiento, $periodo, $estadosPermitidos, &$creados) {
                foreach ($clientes as $clienteItem) {
                    $creado = DB::transaction(function () use ($clienteItem, $clavePeriodo, $fechaVencimiento, $periodo, $estadosPermitidos) {
                        /** @var Cliente|null $cliente */
                        $cliente = Cliente::query()
                            ->with('plan')
                            ->lockForUpdate()
                            ->find($clienteItem->id);

                        if (! $cliente || $cliente->plan === null || ! in_array($cliente->estado, $estadosPermitidos, true)) {
                            return false;
                        }

                        $yaExiste = $cliente->cargos()
                            ->where('tipo', TipoCargo::Mensualidad->value)
                            ->where('periodo', $clavePeriodo)
                            ->lockForUpdate()
                            ->exists();

                        if ($yaExiste) {
                            return false;
                        }

                        $montoCargoCentavos = (int) round((float) $cliente->plan->precio_mensual * 100);
                        $saldoFavorCentavos = (int) round((float) $cliente->saldo_a_favor * 100);
                        $montoPagadoCentavos = 0;
                        $estadoCargo = EstadoCargo::Pendiente->value;

                        if ($saldoFavorCentavos > 0) {
                            $descuentoCentavos = min($saldoFavorCentavos, $montoCargoCentavos);
                            $montoPagadoCentavos = $descuentoCentavos;
                            $cliente->saldo_a_favor = number_format(($saldoFavorCentavos - $descuentoCentavos) / 100, 2, '.', '');
                            $cliente->save();

                            if ($montoPagadoCentavos >= $montoCargoCentavos) {
                                $estadoCargo = EstadoCargo::Pagado->value;
                            } elseif ($montoPagadoCentavos > 0) {
                                $estadoCargo = EstadoCargo::Parcial->value;
                            }
                        }

                        Cargo::create([
                            'cliente_id' => $cliente->id,
                            'plan_id' => $cliente->plan_id,
                            'tipo' => TipoCargo::Mensualidad->value,
                            'periodo' => $clavePeriodo,
                            'concepto' => 'Mensualidad '.$periodo->locale('es')->translatedFormat('F Y').' - '.$cliente->plan->nombre,
                            'monto' => number_format($montoCargoCentavos / 100, 2, '.', ''),
                            'monto_pagado' => number_format($montoPagadoCentavos / 100, 2, '.', ''),
                            'fecha_vencimiento' => $fechaVencimiento->toDateString(),
                            'estado' => $estadoCargo,
                        ]);

                        return true;
                    });

                    if ($creado) {
                        $creados++;
                    }
                }
            });

        return $creados;
    }
}
