<?php

namespace App\Actions\Cobranza;

use App\Models\Cargo;
use App\Models\Cliente;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

class GenerarCargosMensuales
{
    /**
     * Genera la mensualidad del periodo para cada cliente activo con plan asignado.
     *
     * Es idempotente: los clientes que ya tienen mensualidad en el periodo se omiten.
     *
     * @return int Número de cargos creados.
     */
    public function handle(CarbonInterface $periodo): int
    {
        $clavePeriodo = $periodo->format('Y-m');
        $diaVencimiento = min(max((int) config('cobranza.dia_vencimiento', 10), 1), 28);
        $fechaVencimiento = $periodo->copy()->startOfMonth()->day($diaVencimiento);
        $creados = 0;

        $query = Cliente::query();
        if (config('cobranza.facturar_suspendidos', false)) {
            $query->whereIn('estado', ['activo', 'suspendido']);
        } else {
            $query->activo();
        }

        $query->whereNotNull('plan_id')
            ->whereDoesntHave('cargos', fn ($q) => $q
                ->where('tipo', 'mensualidad')
                ->where('periodo', $clavePeriodo))
            ->with('plan')
            ->orderBy('id')
            ->chunkById(200, function ($clientes) use ($clavePeriodo, $fechaVencimiento, $periodo, &$creados) {
                DB::transaction(function () use ($clientes, $clavePeriodo, $fechaVencimiento, $periodo, &$creados) {
                    foreach ($clientes as $cliente) {
                        if ($cliente->plan === null) {
                            continue;
                        }

                        $montoCargo = (float) $cliente->plan->precio_mensual;
                        $saldoFavor = (float) $cliente->saldo_a_favor;
                        $montoPagado = 0.00;
                        $estadoCargo = 'pendiente';

                        if ($saldoFavor > 0) {
                            $descuentoSaldoFavor = min($saldoFavor, $montoCargo);
                            $montoPagado = $descuentoSaldoFavor;
                            $cliente->saldo_a_favor = (string) round($saldoFavor - $descuentoSaldoFavor, 2);
                            $cliente->save();

                            if ($montoPagado >= $montoCargo) {
                                $estadoCargo = 'pagado';
                            } elseif ($montoPagado > 0) {
                                $estadoCargo = 'parcial';
                            }
                        }

                        Cargo::create([
                            'cliente_id' => $cliente->id,
                            'plan_id' => $cliente->plan_id,
                            'tipo' => 'mensualidad',
                            'periodo' => $clavePeriodo,
                            'concepto' => 'Mensualidad '.$periodo->locale('es')->translatedFormat('F Y').' - '.$cliente->plan->nombre,
                            'monto' => $montoCargo,
                            'monto_pagado' => $montoPagado,
                            'fecha_vencimiento' => $fechaVencimiento->toDateString(),
                            'estado' => $estadoCargo,
                        ]);

                        $creados++;
                    }
                });
            });

        return $creados;
    }
}
