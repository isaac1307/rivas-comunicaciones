@extends('layouts.admin')

@section('title', 'Gestión de Planes de Internet')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h2 class="fw-bold mb-1">Planes de Internet</h2>
        <p class="text-muted small mb-0">Catálogo de tarifas y anchos de banda del ISP.</p>
    </div>
    <a href="{{ route('admin.planes.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-circle me-1"></i> Nuevo Plan
    </a>
</div>

<div class="card border-0 shadow-sm rounded-3 bg-white">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small text-uppercase text-muted">
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Velocidad (Bajada / Subida)</th>
                        <th>Precio Mensual</th>
                        <th>Clientes Activos</th>
                        <th>Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody class="small">
                    @forelse ($planes as $plan)
                        <tr>
                            <td class="text-muted">#{{ $plan->id }}</td>
                            <td class="fw-bold text-dark">{{ $plan->nombre }}</td>
                            <td>
                                <span class="badge bg-primary-subtle text-primary">
                                    <i class="bi bi-arrow-down"></i> {{ $plan->velocidad_descarga }} Mbps
                                </span>
                                <span class="badge bg-info-subtle text-info ms-1">
                                    <i class="bi bi-arrow-up"></i> {{ $plan->velocidad_carga }} Mbps
                                </span>
                            </td>
                            <td class="fw-bold text-success fs-6">${{ number_format($plan->precio_mensual, 2) }}</td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    <i class="bi bi-people me-1"></i> {{ $plan->clientes_count }}
                                </span>
                            </td>
                            <td>
                                <span class="badge {{ $plan->estado === 'activo' ? 'bg-success' : 'bg-secondary' }}">
                                    {{ ucfirst($plan->estado) }}
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.planes.show', $plan) }}" class="btn btn-outline-secondary" title="Ver Detalle">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.planes.edit', $plan) }}" class="btn btn-outline-primary" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('admin.planes.destroy', $plan) }}" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar este plan?')" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Eliminar">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No se han registrado planes de internet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if ($planes->hasPages())
        <div class="card-footer bg-white border-0 pt-3">
            {{ $planes->links() }}
        </div>
    @endif
</div>
@endsection
