<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Cobranza\GenerarCargosMensuales;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\GenerarCargosRequest;
use App\Http\Requests\Admin\StoreCargoRequest;
use App\Models\Cargo;
use App\Models\Cliente;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CargoController extends Controller
{
    /**
     * Muestra el formulario para registrar un cargo manual a un cliente.
     */
    public function create(Cliente $cliente): View
    {
        return view('admin.cobranza.cargos.create', compact('cliente'));
    }

    /**
     * Registra un cargo manual (instalación, reconexión, otro).
     */
    public function store(StoreCargoRequest $request, Cliente $cliente): RedirectResponse
    {
        $datos = $request->validated();

        $cargo = Cargo::create([
            'cliente_id' => $cliente->id,
            'plan_id' => $cliente->plan_id,
            'tipo' => $datos['tipo'],
            'periodo' => $datos['periodo'],
            'concepto' => $datos['concepto'],
            'monto' => $datos['monto'],
            'monto_pagado' => 0,
            'fecha_vencimiento' => $datos['fecha_vencimiento'],
            'estado' => 'pendiente',
        ]);

        return redirect()->route('admin.cobranza.show', $cliente)
            ->with('success', "Cargo manual registrado con éxito (#{$cargo->id}).");
    }

    /**
     * Dispara la generación masiva de cargos de mensualidad del periodo para todos los clientes activos.
     */
    public function generarMasivo(GenerarCargosRequest $request, GenerarCargosMensuales $generarCargos): RedirectResponse
    {
        $periodoInput = $request->validated('periodo');

        $periodo = $periodoInput
            ? CarbonImmutable::createFromFormat('!Y-m', $periodoInput)
            : CarbonImmutable::now()->startOfMonth();

        if (! $periodo instanceof CarbonImmutable) {
            return back()->with('error', 'El formato del periodo es incorrecto.');
        }

        $creados = $generarCargos->handle($periodo);

        return redirect()->route('admin.cobranza.index')
            ->with('success', "Se generaron {$creados} cargo(s) de mensualidad correspondientes al periodo {$periodo->format('Y-m')}.");
    }

    /**
     * Cancela un cargo manual pendiente que no tiene pagos aplicados (solo administradores).
     */
    public function cancelar(Request $request, Cargo $cargo): RedirectResponse
    {
        if (! $request->user()?->isAdmin()) {
            abort(403, 'Solo un administrador puede cancelar cargos del sistema.');
        }

        if ((float) $cargo->monto_pagado > 0) {
            return back()->with('error', 'No se puede cancelar un cargo que ya cuenta con pagos o abonos aplicados.');
        }

        $cargo->update(['estado' => 'cancelado']);

        return back()->with('success', "El cargo #{$cargo->id} ha sido cancelado.");
    }
}
