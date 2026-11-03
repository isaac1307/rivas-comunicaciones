@extends('layouts.admin')

@section('title', 'Nodos y Routers MikroTik')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h2 class="fw-bold mb-1">Nodos y Routers MikroTik</h2>
        <p class="text-muted small mb-0">Gestión de enrutadores multi-nodo para control de ancho de banda y cortes automáticos.</p>
    </div>
    <a href="{{ route('admin.routers.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-circle me-1"></i> Registrar Nuevo Nodo
    </a>
</div>

<!-- Métricas de Infraestructura -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3 bg-white p-3 h-100 border-start border-primary border-4">
            <span class="text-muted small text-uppercase fw-semibold">Total de Nodos</span>
            <div class="fs-4 fw-bold text-dark mt-1">{{ $metricas['total'] }}</div>
            <span class="text-muted small">Routers registrados</span>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3 bg-white p-3 h-100 border-start border-success border-4">
            <span class="text-muted small text-uppercase fw-semibold">Nodos Activos</span>
            <div class="fs-4 fw-bold text-success mt-1">{{ $metricas['activos'] }}</div>
            <span class="text-muted small">Operativos para provisionamiento</span>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3 bg-white p-3 h-100 border-start border-secondary border-4">
            <span class="text-muted small text-uppercase fw-semibold">Nodos Inactivos</span>
            <div class="fs-4 fw-bold text-muted mt-1">{{ $metricas['inactivos'] }}</div>
            <span class="text-muted small">Deshabilitados temporalmente</span>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3 bg-white p-3 h-100 border-start border-info border-4">
            <span class="text-muted small text-uppercase fw-semibold">Clientes en Nodos</span>
            <div class="fs-4 fw-bold text-info mt-1">{{ $metricas['clientesEnNodos'] }}</div>
            <span class="text-muted small">Suscriptores con nodo asignado</span>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-3 bg-white">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small text-uppercase text-muted">
                    <tr>
                        <th>ID</th>
                        <th>Nombre del Nodo</th>
                        <th>Dirección IP / Host</th>
                        <th>Usuario API</th>
                        <th>Contraseña API</th>
                        <th>Clientes</th>
                        <th>Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody class="small">
                    @forelse ($routers as $router)
                        <tr>
                            <td class="text-muted">#{{ $router->id }}</td>
                            <td>
                                <div class="fw-bold text-dark d-flex align-items-center gap-2">
                                    <span class="p-2 bg-primary-subtle text-primary rounded-2">
                                        <i class="bi bi-router"></i>
                                    </span>
                                    <div>
                                        <div>{{ $router->nombre }}</div>
                                        <div class="text-muted small" style="font-size: 0.75rem;">RouterOS v7 REST API</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="font-monospace fw-bold text-primary">{{ $router->ip_address }}</span>
                            </td>
                            <td>
                                <span class="font-monospace text-muted">{{ $router->api_user }}</span>
                            </td>
                            <td>
                                <span class="badge bg-light text-muted border" title="Encriptada en base de datos con AES-256">
                                    <i class="bi bi-shield-lock me-1"></i> Encriptada
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-info-subtle text-info border border-info-subtle">
                                    <i class="bi bi-people me-1"></i> {{ $router->clientes_count }} clientes
                                </span>
                            </td>
                            <td>
                                @if ($router->isActivo())
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle me-1"></i> Activo
                                    </span>
                                @else
                                    <span class="badge bg-secondary">
                                        <i class="bi bi-dash-circle me-1"></i> Inactivo
                                    </span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <form action="{{ route('admin.routers.test-conexion', $router) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-info" title="Probar Conexión REST API">
                                            <i class="bi bi-broadcast-pin me-1"></i> Probar
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.routers.sincronizar-planes', $router) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-success" title="Sincronizar Planes y Colas de Velocidad por API">
                                            <i class="bi bi-arrow-repeat me-1"></i> Sincronizar
                                        </button>
                                    </form>
                                    <a href="{{ route('admin.routers.script', $router) }}" class="btn btn-outline-dark" title="Ver Script RouterOS para Terminal / Consola WinBox">
                                        <i class="bi bi-terminal me-1"></i> Script CLI
                                    </a>
                                    <a href="{{ route('admin.routers.edit', $router) }}" class="btn btn-outline-primary" title="Editar Nodo">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('admin.routers.destroy', $router) }}" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar este nodo?')" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Eliminar" {{ $router->clientes_count > 0 ? 'disabled' : '' }}>
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                <i class="bi bi-hdd-network text-muted fs-1 d-block mb-2"></i>
                                No hay enrutadores MikroTik registrados todavía.
                                <div class="mt-2">
                                    <a href="{{ route('admin.routers.create') }}" class="btn btn-primary btn-sm">
                                        Registrar Primer Router
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if ($routers->hasPages())
        <div class="card-footer bg-white border-0 pt-3">
            {{ $routers->links() }}
        </div>
    @endif
</div>
@endsection
