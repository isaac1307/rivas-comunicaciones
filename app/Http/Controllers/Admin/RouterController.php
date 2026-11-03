<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Red\GenerarScriptMikrotik;
use App\Actions\Red\SincronizarPlanesMikrotik;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRouterRequest;
use App\Http\Requests\Admin\UpdateRouterRequest;
use App\Jobs\Red\SincronizarPlanesRouterJob;
use App\Models\Plan;
use App\Models\Router;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class RouterController extends Controller
{
    /**
     * Muestra el catálogo de enrutadores / nodos MikroTik.
     */
    public function index(): View
    {
        $routers = Router::withCount('clientes')
            ->orderBy('id', 'desc')
            ->paginate(10);

        $metricas = [
            'total' => Router::count(),
            'activos' => Router::where('estado', 'activo')->count(),
            'inactivos' => Router::where('estado', 'inactivo')->count(),
            'clientesEnNodos' => Router::withCount('clientes')->get()->sum('clientes_count'),
        ];

        return view('admin.routers.index', compact('routers', 'metricas'));
    }

    /**
     * Muestra el formulario para dar de alta un nuevo nodo MikroTik.
     */
    public function create(): View
    {
        return view('admin.routers.create');
    }

    /**
     * Almacena un nuevo nodo MikroTik en la base de datos y opcionalmente aprovisiona planes de velocidad.
     */
    public function store(StoreRouterRequest $request): RedirectResponse
    {
        $datos = $request->validated();
        $autoAprovisionar = $request->boolean('auto_aprovisionar_planes', true);
        unset($datos['auto_aprovisionar_planes']);

        $router = Router::create($datos);

        if ($autoAprovisionar && $router->isActivo()) {
            SincronizarPlanesRouterJob::dispatch($router);

            return redirect()->route('admin.routers.index')
                ->with('success', 'Nodo MikroTik registrado exitosamente. Se programó el aprovisionamiento automático de planes de velocidad en segundo plano.');
        }

        return redirect()->route('admin.routers.index')
            ->with('success', 'Nodo MikroTik registrado exitosamente.');
    }

    /**
     * Muestra el formulario de edición de un nodo MikroTik.
     */
    public function edit(Router $router): View
    {
        return view('admin.routers.edit', compact('router'));
    }

    /**
     * Actualiza la información y credenciales del nodo MikroTik.
     */
    public function update(UpdateRouterRequest $request, Router $router): RedirectResponse
    {
        $datos = $request->validated();

        if (empty($datos['api_password'])) {
            unset($datos['api_password']);
        }

        $router->update($datos);

        return redirect()->route('admin.routers.index')
            ->with('success', "Información del nodo '{$router->nombre}' actualizada con éxito.");
    }

    /**
     * Elimina el enrutador del catálogo si no tiene clientes asignados.
     */
    public function destroy(Router $router): RedirectResponse
    {
        $clientesCount = $router->clientes()->count();

        if ($clientesCount > 0) {
            return back()->with('error', "No se puede eliminar el nodo '{$router->nombre}' porque tiene {$clientesCount} cliente(s) asignado(s). Reasigna los clientes a otro nodo antes de continuar.");
        }

        $router->delete();

        return redirect()->route('admin.routers.index')
            ->with('success', 'Nodo MikroTik eliminado correctamente.');
    }

    /**
     * Realiza una prueba de conectividad y autenticación hacia la REST API de RouterOS.
     */
    public function testConexion(Router $router): RedirectResponse
    {
        $timeout = (int) config('mikrotik.timeout', config('services.mikrotik.timeout', 4));

        try {
            $response = Http::withBasicAuth($router->api_user, $router->api_password)
                ->timeout($timeout)
                ->get("http://{$router->ip_address}/rest/system/resource");

            if ($response->successful()) {
                /** @var array<string, mixed> $data */
                $data = $response->json() ?? [];
                $version = $data['version'] ?? 'RouterOS v7';
                $board = $data['board-name'] ?? 'MikroTik';
                $uptime = $data['uptime'] ?? 'Activo';

                return back()->with('success', "¡Conexión exitosa con el nodo '{$router->nombre}'! Equipo: {$board}, Versión: {$version}, Uptime: {$uptime}.");
            }

            $mensajeError = match ($response->status()) {
                401 => "Error 401 (No autorizado): El usuario '{$router->api_user}' o la contraseña son incorrectos en MikroTik. Verifique las credenciales en WinBox (System -> Users).",
                403 => "Error 403 (Acceso denegado): El usuario '{$router->api_user}' no cuenta con permisos suficientes (política 'api', 'rest-api' o grupo 'full' en System -> Users).",
                404 => "Error 404 (No encontrado): El endpoint REST no fue encontrado en {$router->ip_address}. Asegúrese de que el router cuente con RouterOS v7.1 o superior.",
                default => "El router respondió con código HTTP {$response->status()}: Verifique que el servicio REST esté activo en IP -> Services -> www o api.",
            };

            return back()->with('error', $mensajeError);
        } catch (\Throwable $e) {
            return back()->with('error', "No se pudo conectar al router '{$router->nombre}' en {$router->ip_address}: {$e->getMessage()}");
        }
    }

    /**
     * Sincroniza y aprovisiona los planes de velocidad y reglas de corte en el router mediante la REST API.
     */
    public function sincronizarPlanes(Router $router, SincronizarPlanesMikrotik $action): RedirectResponse
    {
        $resultado = $action->handle($router);

        if ($resultado['success']) {
            return back()->with('success', $resultado['mensaje']);
        }

        return back()->with('error', $resultado['mensaje']);
    }

    /**
     * Muestra la vista con el script CLI de RouterOS para copiar o exportar a MikroTik.
     */
    public function verScript(Router $router, GenerarScriptMikrotik $action): View
    {
        $planes = Plan::activo()->orderBy('velocidad_descarga', 'asc')->get();
        $script = $action->handle($router, $planes);

        return view('admin.routers.script', compact('router', 'planes', 'script'));
    }

    /**
     * Descarga el archivo de configuración .rsc para importar en MikroTik vía Files o Terminal.
     */
    public function descargarScript(Router $router, GenerarScriptMikrotik $action): Response
    {
        $script = $action->handle($router);
        $slug = Str::slug($router->nombre);
        $filename = "rivas_planes_{$slug}.rsc";

        return response($script, 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
