<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTicketRequest;
use App\Http\Requests\Admin\UpdateTicketRequest;
use App\Models\Cliente;
use App\Models\Ticket;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    /**
     * Muestra la lista de tickets de soporte con filtros de estado.
     */
    public function index(Request $request): View
    {
        $query = Ticket::with('cliente.plan');

        if ($request->filled('estado')) {
            $query->where('estado', $request->input('estado'));
        }

        if ($request->filled('categoria')) {
            $query->where('categoria', $request->input('categoria'));
        }

        $tickets = $query->orderBy('id', 'desc')->paginate(10)->withQueryString();

        return view('admin.tickets.index', compact('tickets'));
    }

    /**
     * Muestra el formulario para crear un ticket desde administración.
     */
    public function create(): View
    {
        $clientes = Cliente::activo()->orderBy('nombre_completo', 'asc')->get();

        return view('admin.tickets.create', compact('clientes'));
    }

    /**
     * Almacena un nuevo ticket usando FormRequest.
     */
    public function store(StoreTicketRequest $request): RedirectResponse
    {
        $ticket = Ticket::create($request->validated());

        return redirect()->route('admin.tickets.show', $ticket)
            ->with('success', 'Ticket creado con éxito.');
    }

    /**
     * Muestra el detalle del ticket de soporte.
     */
    public function show(Ticket $ticket): View
    {
        $ticket->load('cliente.plan');

        return view('admin.tickets.show', compact('ticket'));
    }

    /**
     * Muestra el formulario para editar estado y observaciones del ticket.
     */
    public function edit(Ticket $ticket): View
    {
        $ticket->load('cliente');

        return view('admin.tickets.edit', compact('ticket'));
    }

    /**
     * Actualiza el ticket con FormRequest y asigna fecha de resolución si aplica.
     */
    public function update(UpdateTicketRequest $request, Ticket $ticket): RedirectResponse
    {
        $datos = $request->validated();

        if (in_array($datos['estado'], ['resuelto', 'cerrado'], true) && empty($ticket->fecha_resolucion)) {
            $datos['fecha_resolucion'] = now();
        }

        $ticket->update($datos);

        return redirect()->route('admin.tickets.show', $ticket)
            ->with('success', 'Ticket actualizado con éxito.');
    }
}
