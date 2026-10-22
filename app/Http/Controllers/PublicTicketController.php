<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTicketPublicRequest;
use App\Models\Cliente;
use App\Models\Ticket;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class PublicTicketController extends Controller
{
    /**
     * Muestra el formulario para levantar un nuevo ticket de soporte.
     */
    public function create(): View
    {
        return view('tickets.create');
    }

    /**
     * Procesa y almacena un nuevo ticket usando FormRequest.
     * El folio se genera automáticamente mediante TicketObserver.
     */
    public function store(StoreTicketPublicRequest $request): RedirectResponse
    {
        $datos = $request->validated();

        if (empty($datos['cliente_id'])) {
            $cliente = Cliente::where('telefono', $request->input('telefono'))
                ->whereIn('estado', ['activo', 'suspendido'])
                ->firstOrFail();
            $datos['cliente_id'] = $cliente->id;
        }

        unset($datos['telefono']);

        $ticket = Ticket::create($datos);

        return redirect()->route('tickets.show', $ticket->folio)
            ->with('success', '¡Tu ticket de soporte ha sido generado exitosamente! Conserva tu folio para dar seguimiento.');
    }

    /**
     * Consulta y muestra el estado del ticket por folio.
     */
    public function show(string $folio): View
    {
        $ticket = Ticket::with('cliente.plan')
            ->where('folio', $folio)
            ->firstOrFail();

        return view('tickets.show', compact('ticket'));
    }
}
