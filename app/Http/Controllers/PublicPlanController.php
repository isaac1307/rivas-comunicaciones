<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Contracts\View\View;

class PublicPlanController extends Controller
{
    /**
     * Muestra el catálogo de planes de internet disponibles.
     */
    public function index(): View
    {
        $planes = Plan::activo()
            ->with(['promociones' => function ($query) {
                $query->vigente();
            }])
            ->orderBy('precio_mensual', 'asc')
            ->get();

        return view('planes.index', compact('planes'));
    }
}
