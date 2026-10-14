<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePlanRequest;
use App\Http\Requests\Admin\UpdatePlanRequest;
use App\Models\Plan;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    /**
     * Muestra la lista paginada de planes de internet.
     */
    public function index(): View
    {
        $planes = Plan::withCount('clientes')
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('admin.planes.index', compact('planes'));
    }

    /**
     * Muestra el formulario para crear un nuevo plan.
     */
    public function create(): View
    {
        return view('admin.planes.create');
    }

    /**
     * Almacena un nuevo plan en la base de datos usando FormRequest.
     */
    public function store(StorePlanRequest $request): RedirectResponse
    {
        Plan::create($request->validated());

        return redirect()->route('admin.planes.index')
            ->with('success', 'Plan registrado con éxito.');
    }

    /**
     * Muestra el detalle del plan y sus relaciones.
     */
    public function show(Plan $plane): View
    {
        $plan = $plane->load(['clientes', 'promociones']);

        return view('admin.planes.show', compact('plan'));
    }

    /**
     * Muestra el formulario para editar el plan.
     */
    public function edit(Plan $plane): View
    {
        $plan = $plane;

        return view('admin.planes.edit', compact('plan'));
    }

    /**
     * Actualiza el plan en la base de datos usando FormRequest.
     */
    public function update(UpdatePlanRequest $request, Plan $plane): RedirectResponse
    {
        $plane->update($request->validated());

        return redirect()->route('admin.planes.index')
            ->with('success', 'Plan actualizado con éxito.');
    }

    /**
     * Elimina el plan si no tiene clientes asignados (solo administradores).
     */
    public function destroy(Request $request, Plan $plane): RedirectResponse
    {
        if (! $request->user()?->isAdmin()) {
            abort(403, 'Solo un administrador puede eliminar planes del sistema.');
        }

        if ($plane->clientes()->exists()) {
            return back()->with('error', 'No se puede eliminar el plan porque tiene clientes asignados.');
        }

        $plane->delete();

        return redirect()->route('admin.planes.index')
            ->with('success', 'Plan eliminado con éxito.');
    }
}
