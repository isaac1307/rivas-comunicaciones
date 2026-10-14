<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Cobranza\AnularPago;
use App\Actions\Cobranza\RegistrarPago;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AnularPagoRequest;
use App\Http\Requests\Admin\StorePagoRequest;
use App\Models\Cliente;
use App\Models\Pago;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class PagoController extends Controller
{
    /**
     * Registra un pago recibido para un cliente específico.
     */
    public function store(StorePagoRequest $request, Cliente $cliente, RegistrarPago $registrarPago): RedirectResponse
    {
        /** @var User $cobrador */
        $cobrador = $request->user();

        $pago = $registrarPago->handle($cliente, $request->validated(), $cobrador);

        return redirect()->route('admin.cobranza.show', $cliente)
            ->with('success', "Pago registrado con éxito (Folio: {$pago->folio}).");
    }

    /**
     * Muestra el recibo imprimible de un pago.
     */
    public function recibo(Pago $pago): View
    {
        $pago->load(['cliente.plan', 'cobrador', 'cargos']);

        return view('admin.cobranza.pagos.recibo', compact('pago'));
    }

    /**
     * Anula un pago y revierte sus aplicaciones sobre los cargos (solo administradores).
     */
    public function anular(AnularPagoRequest $request, Pago $pago, AnularPago $anularPago): RedirectResponse
    {
        if (! $request->user()?->isAdmin()) {
            abort(403, 'Solo un administrador puede anular pagos del sistema.');
        }

        $anularPago->handle($pago, $request->validated('motivo_anulacion'));

        return back()->with('success', "El pago {$pago->folio} ha sido anulado y sus adeudos han sido restaurados.");
    }
}
