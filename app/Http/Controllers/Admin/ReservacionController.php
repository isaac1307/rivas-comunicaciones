<?php

namespace App\Http\Controllers\Admin;

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
        $query = Reservacion::with('plan');

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
        $reservacion = $reservacione->load('plan');

        return view('admin.reservaciones.show', compact('reservacion'));
    }

    /**
     * Muestra el formulario para modificar estado o datos de la reservación.
     */
    public function edit(Reservacion $reservacione): View
    {
        $reservacion = $reservacione->load('plan');
        $planes = Plan::all();

        return view('admin.reservaciones.edit', compact('reservacion', 'planes'));
    }

    /**
     * Actualiza el estado y observaciones de la reservación usando FormRequest.
     */
    public function update(UpdateReservacionRequest $request, Reservacion $reservacione): RedirectResponse
    {
        $reservacione->update($request->validated());

        return redirect()->route('admin.reservaciones.show', $reservacione)
            ->with('success', 'Reservación actualizada con éxito.');
    }
}
