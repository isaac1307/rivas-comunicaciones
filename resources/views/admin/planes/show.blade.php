@extends('layouts.admin')

@section('title', 'Detalle del Plan ' . $plan->nombre)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <a href="{{ route('admin.planes.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
            <i class="bi bi-arrow-left me-1"></i> Volver a Planes
        </a>
        <h2 class="fw-bold mb-1">{{ $plan->nombre }}</h2>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.planes.edit', $plan) }}" class="btn btn-primary btn-sm">
            <i class="bi bi-pencil me-1"></i> Editar Plan
        </a>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-3 bg-white p-3">
            <span class="text-muted small">Precio Mensual</span>
            <div class="fs-2 fw-bold text-primary mt-1">${{ number_format($plan->precio_mensual, 2) }}</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-3 bg-white p-3">
            <span class="text-muted small">Velocidades (Descarga / Carga)</span>
            <div class="fs-3 fw-bold text-dark mt-1">{{ $plan->velocidad_descarga }} / {{ $plan->velocidad_carga }} Mbps</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-3 bg-white p-3">
            <span class="text-muted small">Estado Actual</span>
            <div class="mt-2">
                <span class="badge {{ $plan->estado === 'activo' ? 'bg-success' : 'bg-secondary' }} fs-6">
                    {{ ucfirst($plan->estado) }}
                </span>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-3 bg-white p-4 mb-4">
    <h5 class="fw-bold text-dark mb-2">Descripción</h5>
    <p class="text-muted mb-0">{{ $plan->descripcion ?: 'Sin descripción registrada.' }}</p>
</div>

<!-- Clientes Inscritos -->
<div class="card border-0 shadow-sm rounded-3 bg-white">
    <div class="card-header bg-transparent border-0 pt-3 px-3">
        <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-people-fill text-primary me-2"></i> Clientes Activos con este Plan ({{ $plan->clientes->count() }})</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small text-uppercase text-muted">
                    <tr>
                        <th>ID</th>
                        <th>Nombre del Cliente</th>
                        <th>Teléfono</th>
                        <th>Fecha de Alta</th>
                        <th>Estado</th>
                        <th class="text-end">Acción</th>
                    </tr>
                </thead>
                <tbody class="small">
                    @forelse ($plan->clientes as $cli)
                        <tr>
                            <td>#{{ $cli->id }}</td>
                            <td class="fw-bold">{{ $cli->nombre_completo }}</td>
                            <td>{{ $cli->telefono }}</td>
                            <td>{{ $cli->fecha_alta->format('d/m/Y') }}</td>
                            <td><span class="badge bg-success">{{ ucfirst($cli->estado) }}</span></td>
                            <td class="text-end">
                                <a href="{{ route('admin.clientes.show', $cli) }}" class="btn btn-sm btn-outline-primary py-0">Expediente</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-3">No hay clientes inscritos en este plan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
