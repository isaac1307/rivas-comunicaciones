<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateMensajeContactoRequest;
use App\Models\MensajeContacto;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class MensajeContactoController extends Controller
{
    /**
     * Muestra la lista de mensajes de contacto recibidos.
     */
    public function index(): View
    {
        $mensajes = MensajeContacto::latest()->paginate(10);

        return view('admin.mensajes.index', compact('mensajes'));
    }

    /**
     * Muestra el detalle del mensaje y lo marca como leído.
     */
    public function show(MensajeContacto $mensaje): View
    {
        if (! $mensaje->leido) {
            $mensaje->update(['leido' => true]);
        }

        return view('admin.mensajes.show', compact('mensaje'));
    }

    /**
     * Actualiza el estado de leído del mensaje.
     */
    public function update(UpdateMensajeContactoRequest $request, MensajeContacto $mensaje): RedirectResponse
    {
        $mensaje->update($request->validated());

        return back()->with('success', 'Estado del mensaje actualizado.');
    }

    /**
     * Elimina el mensaje de contacto.
     */
    public function destroy(MensajeContacto $mensaje): RedirectResponse
    {
        $mensaje->delete();

        return redirect()->route('admin.mensajes.index')
            ->with('success', 'Mensaje eliminado con éxito.');
    }
}
