<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReservacionRequest;
use App\Models\Plan;
use App\Models\Reservacion;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PublicReservacionController extends Controller
{
    /**
     * Muestra el formulario para agendar o solicitar una reservación.
     */
    public function create(Request $request): View
    {
        $planes = Plan::activo()->orderBy('precio_mensual', 'asc')->get();
        $planSeleccionadoId = $request->query('plan_id');

        return view('reservaciones.create', compact('planes', 'planSeleccionadoId'));
    }

    /**
     * Almacena una nueva reservación usando FormRequest.
     * El folio se genera automáticamente mediante ReservacionObserver.
     */
    public function store(StoreReservacionRequest $request): RedirectResponse
    {
        $reservacion = Reservacion::create($request->validated());

        return redirect()->route('reservaciones.show', $reservacion->folio)
            ->with('success', '¡Tu reservación ha sido generada exitosamente! Guarda tu folio para dar seguimiento.');
    }

    /**
     * Consulta y muestra el detalle de la reservación por su folio.
     */
    public function show(string $folio): View
    {
        $reservacion = Reservacion::with('plan')
            ->where('folio', $folio)
            ->firstOrFail();

        return view('reservaciones.show', compact('reservacion'));
    }
}
