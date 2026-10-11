<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreClienteRequest;
use App\Http\Requests\Admin\UpdateClienteRequest;
use App\Models\Cliente;
use App\Models\Plan;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ClienteController extends Controller
{
    /**
     * Muestra la lista paginada de clientes con su plan asignado.
     */
    public function index(): View
    {
        $clientes = Cliente::with('plan')
            ->withCount('tickets')
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('admin.clientes.index', compact('clientes'));
    }

    /**
     * Muestra el formulario para dar de alta a un cliente.
     */
    public function create(): View
    {
        $planes = Plan::activo()->get();

        return view('admin.clientes.create', compact('planes'));
    }

    /**
     * Almacena un cliente en la base de datos usando FormRequest.
     */
    public function store(StoreClienteRequest $request): RedirectResponse
    {
        Cliente::create($request->validated());

        return redirect()->route('admin.clientes.index')
            ->with('success', 'Cliente dado de alta con éxito.');
    }

    /**
     * Muestra el expediente del cliente y sus tickets de soporte.
     */
    public function show(Cliente $cliente): View
    {
        $cliente->load(['plan', 'tickets' => function ($query) {
            $query->latest();
        }]);

        return view('admin.clientes.show', compact('cliente'));
    }

    /**
     * Muestra el formulario para editar datos del cliente.
     */
    public function edit(Cliente $cliente): View
    {
        $planes = Plan::all();

        return view('admin.clientes.edit', compact('cliente', 'planes'));
    }

    /**
     * Actualiza el cliente en la base de datos usando FormRequest.
     */
    public function update(UpdateClienteRequest $request, Cliente $cliente): RedirectResponse
    {
        $cliente->update($request->validated());

        return redirect()->route('admin.clientes.index')
            ->with('success', 'Información del cliente actualizada con éxito.');
    }

    /**
     * Elimina el cliente del sistema.
     */
    public function destroy(Cliente $cliente): RedirectResponse
    {
        $cliente->delete();

        return redirect()->route('admin.clientes.index')
            ->with('success', 'Cliente eliminado con éxito.');
    }
}
