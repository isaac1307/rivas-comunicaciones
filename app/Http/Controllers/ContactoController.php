<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactoRequest;
use App\Models\MensajeContacto;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ContactoController extends Controller
{
    /**
     * Muestra la vista de contacto con datos de atención y formulario.
     */
    public function index(): View
    {
        return view('contacto');
    }

    /**
     * Procesa y guarda el mensaje de contacto usando FormRequest.
     */
    public function store(StoreContactoRequest $request): RedirectResponse
    {
        MensajeContacto::create($request->validated());

        return redirect()->route('contacto')
            ->with('success', '¡Gracias por contactarnos! Tu mensaje ha sido recibido y nos comunicaremos contigo a la brevedad.');
    }
}
