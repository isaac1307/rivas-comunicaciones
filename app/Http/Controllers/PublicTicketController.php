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
        $clientes = Cliente::activo()->orderBy('nombre_completo', 'asc')->get();

        return view('tickets.create', compact('clientes'));
    }

    /**
     * Procesa y almacena un nuevo ticket usando FormRequest.
     * El folio se genera automáticamente mediante TicketObserver.
     */
    public function store(StoreTicketPublicRequest $request): RedirectResponse
    {
        $ticket = Ticket::create($request->validated());

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
