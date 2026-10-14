<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cargo;
use App\Models\Cliente;
use App\Models\Pago;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class CobranzaController extends Controller
{
    /**
     * Listado general de cobranza por cliente con métricas globales y filtros.
     */
    public function index(Request $request): View
    {
        $busqueda = trim((string) $request->input('buscar', ''));
        $filtro = (string) $request->input('filtro', 'todos');

        $query = Cliente::query()
            ->with('plan')
            ->conSaldo();

        if ($busqueda !== '') {
            $query->where(function (Builder $q) use ($busqueda) {
                $q->where('nombre_completo', 'like', "%{$busqueda}%")
                    ->orWhere('telefono', 'like', "%{$busqueda}%")
                    ->orWhere('direccion', 'like', "%{$busqueda}%");
            });
        }

        match ($filtro) {
            'con_adeudo' => $query->conAdeudo(),
            'morosos' => $query->morosos(),
            'al_corriente' => $query->whereDoesntHave('cargos', fn (Builder $q) => $q->pendientes()),
            default => null,
        };

        $clientes = $query->orderBy('id', 'desc')->paginate(15)->withQueryString();

        // Métricas de cobranza para el encabezado
        $totalPorCobrar = (float) Cargo::pendientes()->sum('monto') - (float) Cargo::pendientes()->sum('monto_pagado');
        $totalRecaudadoMes = (float) Pago::aplicados()
            ->whereYear('fecha_pago', now()->year)
            ->whereMonth('fecha_pago', now()->month)
            ->sum('monto');

        $metricas = [
            'totalPorCobrar' => max($totalPorCobrar, 0),
            'totalRecaudadoMes' => $totalRecaudadoMes,
            'clientesMorosos' => Cliente::morosos()->count(),
            'clientesConAdeudo' => Cliente::conAdeudo()->count(),
        ];

        return view('admin.cobranza.index', compact('clientes', 'metricas', 'busqueda', 'filtro'));
    }

    /**
     * Estado de cuenta detallado de un cliente (desglose de cargos y pagos).
     */
    public function show(Cliente $cliente): View
    {
        $cliente->load(['plan']);

        $cargos = $cliente->cargos()
            ->with(['plan', 'pagos'])
            ->orderBy('fecha_vencimiento', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        $pagos = $cliente->pagos()
            ->with(['cobrador', 'cargos'])
            ->orderBy('fecha_pago', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        $saldoPendiente = round($cargos->filter(fn (Cargo $c) => $c->estado !== 'cancelado')->sum(fn (Cargo $c) => $c->saldo()), 2);
        $totalFacturado = round($cargos->filter(fn (Cargo $c) => $c->estado !== 'cancelado')->sum(fn (Cargo $c) => (float) $c->monto), 2);
        $totalPagado = round($pagos->where('estado', 'aplicado')->sum(fn (Pago $p) => (float) $p->monto), 2);
        $cargosVencidos = $cargos->filter(fn (Cargo $c) => $c->estaVencido())->count();

        $totales = [
            'saldoPendiente' => $saldoPendiente,
            'totalFacturado' => $totalFacturado,
            'totalPagado' => $totalPagado,
            'cargosVencidos' => $cargosVencidos,
        ];

        $metodosPago = Pago::METODOS;

        return view('admin.cobranza.show', compact('cliente', 'cargos', 'pagos', 'totales', 'metodosPago'));
    }
}
