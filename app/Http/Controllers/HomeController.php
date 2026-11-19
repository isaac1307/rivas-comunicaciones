<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Promocion;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    /**
     * Muestra la página principal / landing de Rivas Comunicaciones.
     */
    public function index(): View
    {
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
