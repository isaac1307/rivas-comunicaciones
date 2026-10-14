<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Reservaciones\CompletarReservacion;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateReservacionRequest;
use App\Models\Plan;
use App\Models\Reservacion;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReservacionController extends Controller
{
    /**
     * Muestra la lista de reservaciones de instalación y servicio.
     */
    public function index(Request $request): View
    {
        $query = Reservacion::with(['plan', 'cliente']);

        if ($request->filled('estado')) {
            $query->where('estado', $request->input('estado'));
        }

        $reservaciones = $query->orderBy('id', 'desc')->paginate(10)->withQueryString();

        return view('admin.reservaciones.index', compact('reservaciones'));
    }

    /**
     * Muestra el detalle de una reservación específica.
     */
    public function show(Reservacion $reservacione): View
    {
        $reservacion = $reservacione->load(['plan', 'cliente']);

        return view('admin.reservaciones.show', compact('reservacion'));
    }

    /**
     * Muestra el formulario para modificar estado o datos de la reservación.
     */
    public function edit(Reservacion $reservacione): View
    {
        $reservacion = $reservacione->load(['plan', 'cliente']);
        $planes = Plan::all();

        return view('admin.reservaciones.edit', compact('reservacion', 'planes'));
    }

    /**
     * Actualiza el estado y observaciones de la reservación.
     * Si el estado es completada, da de alta o activa al cliente automáticamente en Clientes Activos.
     */
    public function update(UpdateReservacionRequest $request, Reservacion $reservacione, CompletarReservacion $completarReservacion): RedirectResponse
    {
        $cliente = $completarReservacion->handle($reservacione, $request->validated());

        $mensaje = 'Reservación actualizada con éxito.';

        if ($cliente) {
            $mensaje = "¡Instalación completada con éxito! El cliente '{$cliente->nombre_completo}' ahora se muestra en Clientes Activos.";
        }

        return redirect()->route('admin.reservaciones.show', $reservacione)
            ->with('success', $mensaje);
    }
}
