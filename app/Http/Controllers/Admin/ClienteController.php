<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Red\GestionarEstadoServicioCliente;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreClienteRequest;
use App\Http\Requests\Admin\UpdateClienteRequest;
use App\Jobs\Red\AsignarPlanRedJob;
use App\Models\Cliente;
use App\Models\Plan;
use App\Models\Router;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    /**
     * Muestra la lista paginada de clientes con filtros por estado (activos por defecto) y búsqueda.
     */
    public function index(Request $request): View
    {
        $estado = (string) $request->input('estado', 'activo');
        $buscar = trim((string) $request->input('buscar', ''));

        $query = Cliente::with(['plan', 'router'])
            ->withCount('tickets');

        if ($estado !== 'todos') {
            $query->where('estado', $estado);
        }

        if ($buscar !== '') {
            $query->buscar($buscar);
        }

        $clientes = $query->orderBy('id', 'desc')->paginate(10)->withQueryString();

        $totales = [
            'total' => Cliente::count(),
            'activos' => Cliente::where('estado', 'activo')->count(),
            'suspendidos' => Cliente::where('estado', 'suspendido')->count(),
            'inactivos' => Cliente::where('estado', 'inactivo')->count(),
        ];

        return view('admin.clientes.index', compact('clientes', 'estado', 'buscar', 'totales'));
    }

    /**
     * Muestra el formulario para dar de alta a un cliente.
     */
    public function create(): View
    {
        $planes = Plan::activo()->get();
        $routers = Router::where('estado', 'activo')->orderBy('nombre')->get();

        return view('admin.clientes.create', compact('planes', 'routers'));
    }

    /**
     * Almacena un cliente en la base de datos usando FormRequest.
     */
    public function store(StoreClienteRequest $request): RedirectResponse
    {
        $cliente = Cliente::create($request->validated());

        if ($cliente->plan_id && ! empty($cliente->ip_asignada)) {
            $cliente->loadMissing(['router', 'plan']);
            $router = $cliente->router;

            if ($router instanceof Router && $router->isActivo() && $cliente->plan) {
                AsignarPlanRedJob::dispatch(
                    $cliente->ip_asignada,
                    $cliente->nombre_completo,
                    $router,
                    $cliente->plan
                );
            }
        }

        return redirect()->route('admin.clientes.index')
            ->with('success', 'Cliente dado de alta con éxito.');
    }

    /**
     * Muestra el expediente del cliente, sus tickets de soporte y reservaciones de instalación.
     */
    public function show(Cliente $cliente): View
    {
        $cliente->load(['plan', 'router', 'tickets' => function ($query) {
            $query->latest();
        }, 'reservaciones' => function ($query) {
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
        $routers = Router::orderBy('nombre')->get();

        return view('admin.clientes.edit', compact('cliente', 'planes', 'routers'));
    }

    /**
     * Actualiza el cliente en la base de datos y sincroniza cortes o planes en MikroTik según corresponda.
     */
    public function update(UpdateClienteRequest $request, Cliente $cliente, GestionarEstadoServicioCliente $gestorRed): RedirectResponse
    {
        $estadoAnterior = $cliente->estado;
        $cliente->update($request->validated());
        $estadoNuevo = $cliente->estado;

        $mensajeExito = 'Información del cliente actualizada con éxito.';

        if ($estadoNuevo === 'suspendido' && $estadoAnterior !== 'suspendido') {
            $resultado = $gestorRed->suspender($cliente);
            if (! $resultado['success']) {
                return redirect()->route('admin.clientes.index')
                    ->with('warning', "Cliente actualizado a suspendido en el sistema, pero ocurrió un aviso en MikroTik: {$resultado['mensaje']}");
            }
            $mensajeExito = "Cliente suspendido exitosamente. {$resultado['mensaje']}";
        } elseif ($estadoNuevo === 'activo' && $estadoAnterior === 'suspendido') {
            $resultado = $gestorRed->reconectar($cliente);
            if (! $resultado['success']) {
                return redirect()->route('admin.clientes.index')
                    ->with('warning', "Cliente activado en el sistema, pero ocurrió un aviso en MikroTik: {$resultado['mensaje']}");
            }
            $mensajeExito = "Cliente reactivado exitosamente. {$resultado['mensaje']}";
        } elseif ($cliente->estado === 'activo' && $cliente->plan_id && ! empty($cliente->ip_asignada)) {
            $cliente->loadMissing(['router', 'plan']);
            $router = $cliente->router;

            if ($router instanceof Router && $router->isActivo() && $cliente->plan) {
                AsignarPlanRedJob::dispatch(
                    $cliente->ip_asignada,
                    $cliente->nombre_completo,
                    $router,
                    $cliente->plan
                );
            }
        }

        return redirect()->route('admin.clientes.index')
            ->with('success', $mensajeExito);
    }

    /**
     * Suspende de inmediato el servicio de internet del cliente en MikroTik y actualiza su estado.
     */
    public function suspenderServicio(Cliente $cliente, GestionarEstadoServicioCliente $gestorRed): RedirectResponse
    {
        $resultado = $gestorRed->suspender($cliente);

        return back()->with($resultado['success'] ? 'success' : 'error', $resultado['mensaje']);
    }

    /**
     * Reconecta de inmediato el servicio de internet del cliente en MikroTik y actualiza su estado a activo.
     */
    public function reconectarServicio(Cliente $cliente, GestionarEstadoServicioCliente $gestorRed): RedirectResponse
    {
        $resultado = $gestorRed->reconectar($cliente);

        return back()->with($resultado['success'] ? 'success' : 'error', $resultado['mensaje']);
    }

    /**
     * Elimina el cliente del sistema (solo administradores y sin historial financiero).
     */
    public function destroy(Request $request, Cliente $cliente): RedirectResponse
    {
        if (! $request->user()?->isAdmin()) {
            abort(403, 'Solo un administrador puede eliminar clientes del sistema.');
        }

        if ($cliente->tieneHistorialDeCobranza()) {
            return back()->with('error', 'No es posible eliminar al cliente porque cuenta con historial de cobranza (cargos o pagos). Si ya no cuenta con el servicio, cambia su estado a "Inactivo".');
        }

        $cliente->delete();

        return redirect()->route('admin.clientes.index')
            ->with('success', 'Cliente eliminado con éxito.');
    }
}
