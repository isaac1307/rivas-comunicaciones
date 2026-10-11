@extends('layouts.admin')

@section('title', 'Bitácora del Sistema')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h2 class="fw-bold mb-1">Bitácora de Auditoría del Sistema</h2>
        <p class="text-muted small mb-0">Registro histórico inmutable de eventos, cambios y accesos de usuarios.</p>
    </div>
</div>

<!-- Filtros de Auditoría -->
<div class="card border-0 shadow-sm rounded-3 bg-white mb-4 p-3">
    <form method="GET" action="{{ route('admin.bitacoras.index') }}" class="row g-2 align-items-center">
        <div class="col-md-3">
            <select name="accion" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">-- Todas las Acciones --</option>
                <option value="creacion" {{ request('accion') === 'creacion' ? 'selected' : '' }}>Creaciones</option>
                <option value="actualizacion" {{ request('accion') === 'actualizacion' ? 'selected' : '' }}>Actualizaciones</option>
                <option value="eliminacion" {{ request('accion') === 'eliminacion' ? 'selected' : '' }}>Eliminaciones</option>
            </select>
        </div>

        <div class="col-md-3">
            <select name="tabla" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">-- Todas las Tablas --</option>
                <option value="planes" {{ request('tabla') === 'planes' ? 'selected' : '' }}>Planes</option>
                <option value="promociones" {{ request('tabla') === 'promociones' ? 'selected' : '' }}>Promociones</option>
                <option value="clientes" {{ request('tabla') === 'clientes' ? 'selected' : '' }}>Clientes</option>
                <option value="reservaciones" {{ request('tabla') === 'reservaciones' ? 'selected' : '' }}>Reservaciones</option>
                <option value="tickets" {{ request('tabla') === 'tickets' ? 'selected' : '' }}>Tickets</option>
            </select>
        </div>

        <div class="col-md-2">
            @if (request('accion') || request('tabla'))
                <a href="{{ route('admin.bitacoras.index') }}" class="btn btn-outline-secondary btn-sm w-100">
                    <i class="bi bi-x-circle me-1"></i> Limpiar
                </a>
            @endif
        </div>
    </form>
</div>

<div class="card border-0 shadow-sm rounded-3 bg-white">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small text-uppercase text-muted">
                    <tr>
                        <th>ID</th>
                        <th>Fecha y Hora</th>
                        <th>Usuario</th>
                        <th>Acción</th>
                        <th>Descripción del Evento</th>
                        <th>Tabla Afectada</th>
                        <th>Registro ID</th>
                        <th>IP Origen</th>
                    </tr>
                </thead>
                <tbody class="small">
                    @forelse ($bitacoras as $bit)
                        <tr>
                            <td class="text-muted">#{{ $bit->id }}</td>
                            <td class="text-muted">{{ $bit->created_at->format('d/m/Y H:i:s') }}</td>
                            <td>
                                @if ($bit->usuario)
                                    <div class="fw-bold">{{ $bit->usuario->nombre_completo ?: $bit->usuario->nombre_usuario }}</div>
                                    <span class="badge {{ $bit->usuario->isAdmin() ? 'bg-primary' : 'bg-secondary' }}" style="font-size: 0.65rem;">
                                        {{ ucfirst($bit->usuario->rol) }}
                                    </span>
                                @else
                                    <span class="text-muted">Público / Sistema</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $badgeAccion = [
                                        'creacion' => 'bg-success',
                                        'actualizacion' => 'bg-warning text-dark',
                                        'eliminacion' => 'bg-danger',
                                    ];
                                @endphp
                                <span class="badge {{ $badgeAccion[$bit->accion] ?? 'bg-secondary' }}">
                                    {{ ucfirst($bit->accion) }}
                                </span>
                            </td>
                            <td>{{ $bit->descripcion }}</td>
                            <td><code>{{ $bit->tabla_afectada ?: 'N/A' }}</code></td>
                            <td>{{ $bit->registro_id ? '#'.$bit->registro_id : 'N/A' }}</td>
                            <td><span class="badge bg-light text-muted border">{{ $bit->ip_origen ?: 'Localhost' }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">No hay registros de bitácora coincidentes.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if ($bitacoras->hasPages())
        <div class="card-footer bg-white border-0 pt-3">
            {{ $bitacoras->links() }}
        </div>
    @endif
</div>
@endsection
