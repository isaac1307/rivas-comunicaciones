@extends('layouts.admin')

@section('title', 'Gestión de Reservaciones de Instalación')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h2 class="fw-bold mb-1">Reservaciones & Solicitudes</h2>
        <p class="text-muted small mb-0">Gestión de prospectos e instalaciones en campo.</p>
    </div>
    <!-- Filtros por Estado -->
    <div class="d-flex gap-2">
        <a href="{{ route('admin.reservaciones.index') }}" class="btn btn-sm {{ !request('estado') ? 'btn-dark' : 'btn-outline-dark' }}">Todos</a>
        <a href="{{ route('admin.reservaciones.index', ['estado' => 'pendiente']) }}" class="btn btn-sm {{ request('estado') === 'pendiente' ? 'btn-warning text-dark' : 'btn-outline-warning text-dark' }}">Pendientes</a>
        <a href="{{ route('admin.reservaciones.index', ['estado' => 'programada']) }}" class="btn btn-sm {{ request('estado') === 'programada' ? 'btn-info text-dark' : 'btn-outline-info text-dark' }}">Programadas</a>
        <a href="{{ route('admin.reservaciones.index', ['estado' => 'completada']) }}" class="btn btn-sm {{ request('estado') === 'completada' ? 'btn-success' : 'btn-outline-success' }}">Completadas</a>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-3 bg-white">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small text-uppercase text-muted">
                    <tr>
                        <th>Folio</th>
                        <th>Solicitante</th>
                        <th>Teléfono</th>
                        <th>Servicio</th>
                        <th>Plan Solicitado</th>
                        <th>Fecha Visita</th>
                        <th>Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody class="small">
                    @forelse ($reservaciones as $res)
                        <tr>
                            <td class="fw-bold text-primary">{{ $res->folio }}</td>
                            <td>{{ $res->nombre_solicitante }}</td>
                            <td>{{ $res->telefono }}</td>
                            <td><span class="badge bg-light text-dark border">{{ str_replace('_', ' ', $res->tipo_servicio) }}</span></td>
                            <td>
                                @if ($res->plan)
                                    <span class="badge bg-primary-subtle text-primary">{{ $res->plan->nombre }}</span>
                                @else
                                    <span class="text-muted small">Por definir</span>
                                @endif
                            </td>
                            <td>
                                {{ $res->fecha_solicitada ? $res->fecha_solicitada->format('d/m/Y') : 'Sin fecha' }}
                            </td>
                            <td>
                                @php
                                    $badgeRes = [
                                        'pendiente' => 'bg-warning text-dark',
                                        'programada' => 'bg-info text-dark',
                                        'completada' => 'bg-success text-white',
                                        'cancelada' => 'bg-danger text-white',
                                    ];
                                @endphp
                                <span class="badge {{ $badgeRes[$res->estado] ?? 'bg-secondary' }}">
                                    {{ ucfirst($res->estado) }}
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.reservaciones.show', $res) }}" class="btn btn-outline-secondary" title="Ver Detalle">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.reservaciones.edit', $res) }}" class="btn btn-outline-primary" title="Editar Estatus">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">No hay reservaciones registradas con los filtros seleccionados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if ($reservaciones->hasPages())
        <div class="card-footer bg-white border-0 pt-3">
            {{ $reservaciones->links() }}
        </div>
    @endif
</div>
@endsection
