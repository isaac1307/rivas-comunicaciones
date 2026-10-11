<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bitacora;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class BitacoraController extends Controller
{
    /**
     * Muestra el registro histórico de auditoría (solo lectura para administradores).
     */
    public function index(Request $request): View
    {
        $query = Bitacora::with('usuario');

        if ($request->filled('accion')) {
            $query->where('accion', $request->input('accion'));
        }

        if ($request->filled('tabla')) {
            $query->where('tabla_afectada', $request->input('tabla'));
        }

        $bitacoras = $query->latest()->paginate(15)->withQueryString();

        return view('admin.bitacoras.index', compact('bitacoras'));
    }
}
