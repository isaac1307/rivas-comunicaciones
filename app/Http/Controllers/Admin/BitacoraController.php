<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bitacora;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BitacoraController extends Controller
{
    /**
     * Muestra el registro histórico de auditoría con filtros avanzados, búsqueda y métricas.
     */
    public function index(Request $request): View
    {
        $query = $this->filtrarQuery($request);

        $bitacoras = $query->latest('id')->paginate(20)->withQueryString();

        $usuarios = User::orderBy('nombre_completo')->get(['id', 'nombre_usuario', 'nombre_completo', 'rol']);

        // Métricas rápidas de auditoría
        $hoy = Carbon::today();
        $metricas = [
            'total' => Bitacora::count(),
            'hoy' => Bitacora::whereDate('created_at', $hoy)->count(),
            'creaciones' => Bitacora::where('accion', 'creacion')->count(),
            'actualizaciones' => Bitacora::where('accion', 'actualizacion')->count(),
            'eliminaciones' => Bitacora::where('accion', 'eliminacion')->count(),
            'accesos' => Bitacora::whereIn('accion', ['acceso', 'cierre_sesion'])->count(),
        ];

        // Tablas del sistema auditadas
        $tablas = [
            'clientes' => 'Clientes',
            'planes' => 'Planes de Internet',
            'promociones' => 'Promociones',
            'reservaciones' => 'Reservaciones / Solicitudes',
            'tickets' => 'Tickets de Soporte',
            'cargos' => 'Cargos de Cobranza',
            'pagos' => 'Pagos Recibidos',
            'usuarios' => 'Usuarios / Seguridad',
            'mensajes_contacto' => 'Mensajes de Contacto',
        ];

        return view('admin.bitacoras.index', compact('bitacoras', 'usuarios', 'metricas', 'tablas'));
    }

    /**
     * Exporta los eventos filtrados en formato CSV para reportes y cumplimiento.
     */
    public function export(Request $request): StreamedResponse
    {
        $query = $this->filtrarQuery($request);

        $filename = 'auditoria_rivas_'.now()->format('Y-m-d_His').'.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            if ($handle === false) {
                return;
            }

            // UTF-8 BOM para que Excel abra acentos y caracteres especiales correctamente
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            // Encabezados
            fputcsv($handle, [
                'ID',
                'Fecha y Hora',
                'Usuario Responsable',
                'Rol de Usuario',
                'Accion',
                'Descripcion del Evento',
                'Tabla Afectada',
                'Registro ID',
                'Direccion IP',
            ]);

            $query->latest('id')->chunk(500, function (Collection $bitacoras) use ($handle) {
                /** @var Bitacora $bit */
                foreach ($bitacoras as $bit) {
                    fputcsv($handle, [
                        $bit->id,
                        $bit->created_at->format('Y-m-d H:i:s'),
                        $bit->usuario ? ($bit->usuario->nombre_completo ?: $bit->usuario->nombre_usuario) : 'Sistema / Público',
                        $bit->usuario ? ucfirst($bit->usuario->rol) : 'N/A',
                        ucfirst($bit->accion),
                        $bit->descripcion,
                        $bit->tabla_afectada ?: 'N/A',
                        $bit->registro_id ?: 'N/A',
                        $bit->ip_origen ?: 'N/A',
                    ]);
                }
            });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Aplica los filtros recibidos a la consulta Eloquent de Bitacora.
     *
     * @return Builder<Bitacora>
     */
    protected function filtrarQuery(Request $request): Builder
    {
        $query = Bitacora::with('usuario');

        if ($request->filled('accion')) {
            $query->where('accion', $request->input('accion'));
        }

        if ($request->filled('tabla')) {
            $query->where('tabla_afectada', $request->input('tabla'));
        }

        if ($request->filled('usuario_id')) {
            $query->where('usuario_id', $request->input('usuario_id'));
        }

        if ($request->filled('buscar')) {
            $buscar = trim((string) $request->input('buscar'));
            $query->where(function (Builder $q) use ($buscar) {
                $q->where('descripcion', 'like', "%{$buscar}%")
                    ->orWhere('ip_origen', 'like', "%{$buscar}%")
                    ->orWhereHas('usuario', function (Builder $qu) use ($buscar) {
                        $qu->where('nombre_completo', 'like', "%{$buscar}%")
                            ->orWhere('nombre_usuario', 'like', "%{$buscar}%");
                    });
            });
        }

        if ($request->filled('desde')) {
            $query->whereDate('created_at', '>=', $request->input('desde'));
        }

        if ($request->filled('hasta')) {
            $query->whereDate('created_at', '<=', $request->input('hasta'));
        }

        return $query;
    }
}
