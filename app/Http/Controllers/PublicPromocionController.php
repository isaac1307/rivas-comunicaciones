<?php

namespace App\Http\Controllers;

use App\Models\Promocion;
use Illuminate\Contracts\View\View;

class PublicPromocionController extends Controller
{
    /**
     * Muestra el catálogo de promociones vigentes con sus planes asociados.
     */
    public function index(): View
    {
        $promociones = Promocion::vigente()
            ->with(['planes' => function ($query) {
                $query->where('estado', 'activo');
            }])
            ->orderBy('fecha_vencimiento', 'asc')
            ->get();

        return view('promociones.index', compact('promociones'));
    }
}
