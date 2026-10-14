<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePromocionRequest;
use App\Http\Requests\Admin\UpdatePromocionRequest;
use App\Models\Plan;
use App\Models\Promocion;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PromocionController extends Controller
{
    /**
     * Muestra la lista paginada de promociones con sus planes vinculados.
     */
    public function index(): View
    {
        $promociones = Promocion::with('planes')
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('admin.promociones.index', compact('promociones'));
    }

    /**
     * Muestra el formulario para crear una nueva promoción.
     */
    public function create(): View
    {
        $planes = Plan::all();

        return view('admin.promociones.create', compact('planes'));
    }

    /**
     * Almacena una nueva promoción y sincroniza los planes en la tabla pivot.
     */
    public function store(StorePromocionRequest $request): RedirectResponse
    {
        $datos = $request->validated();
        $promocion = Promocion::create($datos);

        if ($request->has('planes')) {
            $promocion->planes()->sync($request->input('planes', []));
        }

        return redirect()->route('admin.promociones.index')
            ->with('success', 'Promoción registrada con éxito.');
    }

    /**
     * Muestra el detalle de la promoción.
     */
    public function show(Promocion $promocione): View
    {
        $promocion = $promocione->load('planes');

        return view('admin.promociones.show', compact('promocion'));
    }

    /**
     * Muestra el formulario para editar la promoción.
     */
    public function edit(Promocion $promocione): View
    {
        $promocion = $promocione->load('planes');
        $planes = Plan::all();

        return view('admin.promociones.edit', compact('promocion', 'planes'));
    }

    /**
     * Actualiza la promoción y sincroniza los planes mediante sync().
     */
    public function update(UpdatePromocionRequest $request, Promocion $promocione): RedirectResponse
    {
        $datos = $request->validated();
        $promocione->update($datos);

        $promocione->planes()->sync($request->input('planes', []));

        return redirect()->route('admin.promociones.index')
            ->with('success', 'Promoción actualizada con éxito.');
    }

    /**
     * Elimina la promoción y desvincula sus relaciones pivot (solo administradores).
     */
    public function destroy(Request $request, Promocion $promocione): RedirectResponse
    {
        if (! $request->user()?->isAdmin()) {
            abort(403, 'Solo un administrador puede eliminar promociones del sistema.');
        }

        $promocione->planes()->detach();
        $promocione->delete();

        return redirect()->route('admin.promociones.index')
            ->with('success', 'Promoción eliminada con éxito.');
    }
}
