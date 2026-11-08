<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Promocion;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Muestra la página principal / landing de Rivas Comunicaciones.
     */
    public function index(Request $request): View|RedirectResponse
    {
        if ($request->filled('folio')) {
            $folio = strtoupper(trim((string) $request->query('folio')));

            if (str_starts_with($folio, 'RES-')) {
                return redirect()->route('reservaciones.show', $folio);
            }

            if (str_starts_with($folio, 'TKT-')) {
                return redirect()->route('tickets.show', $folio);
            }

            return redirect()->route('home')
                ->with('error', 'Ingresa un folio válido que inicie con RES- o TKT-.');
        }

        $planesDestacados = Plan::activo()
            ->orderBy('precio_mensual', 'asc')
            ->take(4)
            ->get();

        $promociones = Promocion::vigente()
            ->with('planes')
            ->take(3)
            ->get();

        return view('home', compact('planesDestacados', 'promociones'));
    }

    /**
     * Muestra la sección institucional "Nosotros".
     */
    public function nosotros(): View
    {
        return view('nosotros');
    }

    /**
     * Muestra el mapa y zonas de cobertura del ISP.
     */
    public function cobertura(): View
    {
        return view('cobertura');
    }
}
