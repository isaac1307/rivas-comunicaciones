@extends('layouts.admin')

@section('title', 'Bitácora de Auditoría & Seguridad')

@section('content')
<!-- Encabezado y Acciones Principales -->
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div>
        <div class="d-flex align-items-center gap-2">
            <h2 class="fw-bold mb-0">Bitácora de Auditoría & Seguridad</h2>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1">
                Inmutable
            </span>
        </div>
        <p class="text-muted small mb-0 mt-1">
            Historial cronológico de cambios, operaciones de clientes, cobros y accesos de usuarios.
        </p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('admin.bitacoras.export', request()->query()) }}" class="btn btn-outline-success btn-sm fw-semibold shadow-sm">
            <i class="bi bi-file-earmark-spreadsheet me-1"></i> Exportar a CSV (Excel)
        </a>
        @if (request()->hasAny(['buscar', 'accion', 'tabla', 'usuario_id', 'desde', 'hasta']))
            <a href="{{ route('admin.bitacoras.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-counterclockwise me-1"></i> Restablecer Filtros
            </a>
        @endif
    </div>
</div>

<!-- Tarjetas KPI / Métricas Rápidas -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3 bg-white h-100 p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small text-uppercase fw-semibold d-block">Total de Eventos</span>
                    <h3 class="fw-bold mb-0 mt-1 text-dark">{{ number_format($metricas['total']) }}</h3>
                    <span class="text-muted small" style="font-size: 0.75rem;">Historial completo</span>
                </div>
                <div class="rounded-circle bg-primary-subtle text-primary p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                    <i class="bi bi-database-check fs-4"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3 bg-white h-100 p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small text-uppercase fw-semibold d-block">Actividad Hoy</span>
                    <h3 class="fw-bold mb-0 mt-1 text-success">{{ number_format($metricas['hoy']) }}</h3>
                    <span class="text-muted small" style="font-size: 0.75rem;">Registros generados hoy</span>
                </div>
                <div class="rounded-circle bg-success-subtle text-success p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                    <i class="bi bi-calendar-event fs-4"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3 bg-white h-100 p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small text-uppercase fw-semibold d-block">Altas & Cambios</span>
                    <h3 class="fw-bold mb-0 mt-1 text-warning-emphasis">{{ number_format($metricas['creaciones'] + $metricas['actualizaciones']) }}</h3>
                    <span class="text-muted small" style="font-size: 0.75rem;">{{ $metricas['creaciones'] }} altas / {{ $metricas['actualizaciones'] }} ediciones</span>
                </div>
                <div class="rounded-circle bg-warning-subtle text-warning-emphasis p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                    <i class="bi bi-pencil-square fs-4"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3 bg-white h-100 p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small text-uppercase fw-semibold d-block">Accesos / Sesiones</span>
                    <h3 class="fw-bold mb-0 mt-1 text-info">{{ number_format($metricas['accesos']) }}</h3>
                    <span class="text-muted small" style="font-size: 0.75rem;">Inicios y cierres de sesión</span>
                </div>
                <div class="rounded-circle bg-info-subtle text-info p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                    <i class="bi bi-shield-lock fs-4"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filtros Avanzados -->
<div class="card border-0 shadow-sm rounded-3 bg-white mb-4">
    <div class="card-header bg-transparent border-0 pt-3 px-3 pb-0 d-flex justify-content-between align-items-center">
        <h6 class="fw-bold mb-0 text-dark">
            <i class="bi bi-funnel text-primary me-1"></i> Filtros de Auditoría
        </h6>
        @if (request()->hasAny(['buscar', 'accion', 'tabla', 'usuario_id', 'desde', 'hasta']))
            <span class="badge bg-primary text-white small">Filtros aplicados</span>
        @endif
    </div>
    <div class="card-body p-3">
        <form method="GET" action="{{ route('admin.bitacoras.index') }}" class="row g-2">
            <!-- Búsqueda General -->
            <div class="col-md-4">
                <label class="form-label small text-muted mb-1">Búsqueda libre</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="buscar" value="{{ request('buscar') }}" class="form-control border-start-0" placeholder="Buscar por descripción, IP o usuario...">
                </div>
            </div>

            <!-- Filtro por Tipo de Acción -->
            <div class="col-sm-6 col-md-2">
                <label class="form-label small text-muted mb-1">Acción</label>
                <select name="accion" class="form-select form-select-sm">
                    <option value="">Todas las Acciones</option>
                    <option value="creacion" {{ request('accion') === 'creacion' ? 'selected' : '' }}>Creación / Alta</option>
                    <option value="actualizacion" {{ request('accion') === 'actualizacion' ? 'selected' : '' }}>Actualización</option>
                    <option value="eliminacion" {{ request('accion') === 'eliminacion' ? 'selected' : '' }}>Eliminación / Baja</option>
                    <option value="acceso" {{ request('accion') === 'acceso' ? 'selected' : '' }}>Inicio de Sesión</option>
                    <option value="cierre_sesion" {{ request('accion') === 'cierre_sesion' ? 'selected' : '' }}>Cierre de Sesión</option>
                </select>
            </div>

            <!-- Filtro por Tabla / Módulo -->
            <div class="col-sm-6 col-md-2">
                <label class="form-label small text-muted mb-1">Módulo / Tabla</label>
                <select name="tabla" class="form-select form-select-sm">
                    <option value="">Todos los Módulos</option>
                    @foreach ($tablas as $key => $label)
                        <option value="{{ $key }}" {{ request('tabla') === $key ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Filtro por Usuario -->
            <div class="col-sm-6 col-md-2">
                <label class="form-label small text-muted mb-1">Usuario Responsable</label>
                <select name="usuario_id" class="form-select form-select-sm">
                    <option value="">Todos los Usuarios</option>
                    @foreach ($usuarios as $usr)
                        <option value="{{ $usr->id }}" {{ request('usuario_id') == $usr->id ? 'selected' : '' }}>
                            {{ $usr->nombre_completo ?: $usr->nombre_usuario }} ({{ ucfirst($usr->rol) }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Fecha Desde -->
            <div class="col-sm-3 col-md-1">
                <label class="form-label small text-muted mb-1">Desde</label>
                <input type="date" name="desde" value="{{ request('desde') }}" class="form-control form-control-sm">
            </div>

            <!-- Fecha Hasta -->
            <div class="col-sm-3 col-md-1">
                <label class="form-label small text-muted mb-1">Hasta</label>
                <input type="date" name="hasta" value="{{ request('hasta') }}" class="form-control form-control-sm">
            </div>

            <!-- Botones de Acción -->
            <div class="col-12 text-end pt-2 d-flex justify-content-end gap-2">
                <button type="submit" class="btn btn-primary btn-sm px-3">
                    <i class="bi bi-funnel-fill me-1"></i> Filtrar Eventos
                </button>
                @if (request()->hasAny(['buscar', 'accion', 'tabla', 'usuario_id', 'desde', 'hasta']))
                    <a href="{{ route('admin.bitacoras.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-x-circle me-1"></i> Limpiar
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Tabla de Registros de Bitácora -->
<div class="card border-0 shadow-sm rounded-3 bg-white">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small text-uppercase text-muted">
                    <tr>
                        <th style="width: 70px;">ID</th>
                        <th style="width: 170px;">Fecha y Hora</th>
                        <th style="width: 200px;">Usuario Responsable</th>
                        <th style="width: 140px;">Acción</th>
                        <th>Descripción del Evento</th>
                        <th style="width: 150px;">Módulo / Registro</th>
                        <th style="width: 130px;">IP Origen</th>
                        <th style="width: 80px;" class="text-end">Detalle</th>
                    </tr>
                </thead>
                <tbody class="small">
                    @forelse ($bitacoras as $bit)
                        <tr>
                            <td class="text-muted fw-semibold">#{{ $bit->id }}</td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $bit->created_at->format('d/m/Y H:i:s') }}</div>
                                <div class="text-muted" style="font-size: 0.75rem;">
                                    <i class="bi bi-clock me-1"></i>{{ $bit->created_at->diffForHumans() }}
                                </div>
                            </td>
                            <td>
                                @if ($bit->usuario)
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle bg-light border d-flex align-items-center justify-content-center fw-bold text-primary" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                            {{ strtoupper(substr($bit->usuario->nombre_completo ?: $bit->usuario->nombre_usuario, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark lh-sm">{{ $bit->usuario->nombre_completo ?: $bit->usuario->nombre_usuario }}</div>
                                            @php
                                                $badgeRol = [
                                                    'administrador' => 'bg-primary-subtle text-primary border border-primary-subtle',
                                                    'operador' => 'bg-info-subtle text-info border border-info-subtle',
                                                    'cobrador' => 'bg-success-subtle text-success border border-success-subtle',
                                                ];
                                            @endphp
                                            <span class="badge {{ $badgeRol[$bit->usuario->rol] ?? 'bg-secondary' }}" style="font-size: 0.65rem;">
                                                {{ ucfirst($bit->usuario->rol) }}
                                            </span>
                                        </div>
                                    </div>
                                @else
                                    <div class="d-flex align-items-center gap-2 text-muted">
                                        <i class="bi bi-hdd-network fs-5"></i>
                                        <div>
                                            <div class="fw-semibold">Sistema / Público</div>
                                            <span class="badge bg-light text-muted border" style="font-size: 0.65rem;">Automático</span>
                                        </div>
                                    </div>
                                @endif
                            </td>
                            <td>
                                @php
                                    $badgeAccion = [
                                        'creacion' => 'bg-success-subtle text-success border border-success-subtle',
                                        'actualizacion' => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
                                        'eliminacion' => 'bg-danger-subtle text-danger border border-danger-subtle',
                                        'acceso' => 'bg-info-subtle text-info border border-info-subtle',
                                        'cierre_sesion' => 'bg-secondary-subtle text-secondary border border-secondary-subtle',
                                    ];
                                    $iconoAccion = [
                                        'creacion' => 'bi-plus-circle-fill',
                                        'actualizacion' => 'bi-pencil-fill',
                                        'eliminacion' => 'bi-trash3-fill',
                                        'acceso' => 'bi-box-arrow-in-right',
                                        'cierre_sesion' => 'bi-box-arrow-right',
                                    ];
                                @endphp
                                <span class="badge {{ $badgeAccion[$bit->accion] ?? 'bg-secondary' }} px-2 py-1">
                                    <i class="bi {{ $iconoAccion[$bit->accion] ?? 'bi-dot' }} me-1"></i>
                                    {{ ucfirst(str_replace('_', ' ', $bit->accion)) }}
                                </span>
                            </td>
                            <td>
                                <span class="text-dark fw-medium">{{ $bit->descripcion }}</span>
                            </td>
                            <td>
                                @if ($bit->tabla_afectada)
                                    <div class="mb-1">
                                        <span class="badge bg-light text-dark border font-monospace" style="font-size: 0.72rem;">
                                            {{ $bit->tabla_afectada }}
                                        </span>
                                    </div>
                                @endif

                                @if ($bit->accion !== 'eliminacion' && $bit->registro_id)
                                    @switch($bit->tabla_afectada)
                                        @case('clientes')
                                            <a href="{{ route('admin.clientes.show', $bit->registro_id) }}" class="badge bg-primary-subtle text-primary border border-primary-subtle text-decoration-none" title="Ver cliente">
                                                <i class="bi bi-person me-1"></i> #{{ $bit->registro_id }} <i class="bi bi-box-arrow-up-right ms-1"></i>
                                            </a>
                                            @break
                                        @case('tickets')
                                            <a href="{{ route('admin.tickets.show', $bit->registro_id) }}" class="badge bg-primary-subtle text-primary border border-primary-subtle text-decoration-none" title="Ver ticket">
                                                <i class="bi bi-ticket-detailed me-1"></i> #{{ $bit->registro_id }} <i class="bi bi-box-arrow-up-right ms-1"></i>
                                            </a>
                                            @break
                                        @case('reservaciones')
                                            <a href="{{ route('admin.reservaciones.show', $bit->registro_id) }}" class="badge bg-primary-subtle text-primary border border-primary-subtle text-decoration-none" title="Ver reservación">
                                                <i class="bi bi-calendar-check me-1"></i> #{{ $bit->registro_id }} <i class="bi bi-box-arrow-up-right ms-1"></i>
                                            </a>
                                            @break
                                        @case('planes')
                                            <a href="{{ route('admin.planes.edit', $bit->registro_id) }}" class="badge bg-primary-subtle text-primary border border-primary-subtle text-decoration-none" title="Ver plan">
                                                <i class="bi bi-wifi me-1"></i> #{{ $bit->registro_id }} <i class="bi bi-box-arrow-up-right ms-1"></i>
                                            </a>
                                            @break
                                        @case('promociones')
                                            <a href="{{ route('admin.promociones.edit', $bit->registro_id) }}" class="badge bg-primary-subtle text-primary border border-primary-subtle text-decoration-none" title="Ver promoción">
                                                <i class="bi bi-tag me-1"></i> #{{ $bit->registro_id }} <i class="bi bi-box-arrow-up-right ms-1"></i>
                                            </a>
                                            @break
                                        @case('usuarios')
                                            <a href="{{ route('admin.usuarios.edit', $bit->registro_id) }}" class="badge bg-primary-subtle text-primary border border-primary-subtle text-decoration-none" title="Ver usuario">
                                                <i class="bi bi-person-gear me-1"></i> #{{ $bit->registro_id }} <i class="bi bi-box-arrow-up-right ms-1"></i>
                                            </a>
                                            @break
                                        @default
                                            <span class="badge bg-light text-muted border">#{{ $bit->registro_id }}</span>
                                    @endswitch
                                @else
                                    <span class="text-muted small">{{ $bit->registro_id ? '#'.$bit->registro_id : 'N/A' }}</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-light text-muted border font-monospace">
                                    <i class="bi bi-geo-alt me-1"></i>{{ $bit->ip_origen ?: '127.0.0.1' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-2" data-bs-toggle="modal" data-bs-target="#modalBitacora{{ $bit->id }}" title="Ver detalles técnicos">
                                    <i class="bi bi-info-circle"></i>
                                </button>

                                <!-- Modal Detalle de Evento -->
                                <div class="modal fade" id="modalBitacora{{ $bit->id }}" tabindex="-1" aria-labelledby="modalLabel{{ $bit->id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 shadow">
                                            <div class="modal-header bg-light">
                                                <h6 class="modal-title fw-bold" id="modalLabel{{ $bit->id }}">
                                                    <i class="bi bi-shield-check text-primary me-2"></i> Detalle de Auditoría #{{ $bit->id }}
                                                </h6>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                            </div>
                                            <div class="modal-body text-start">
                                                <div class="mb-3">
                                                    <span class="small text-muted d-block">Descripción completa del evento:</span>
                                                    <div class="p-3 bg-light rounded-3 text-dark fw-medium mt-1 border">
                                                        {{ $bit->descripcion }}
                                                    </div>
                                                </div>

                                                <div class="row g-2 small">
                                                    <div class="col-6">
                                                        <span class="text-muted d-block">Tipo de Acción:</span>
                                                        <span class="badge {{ $badgeAccion[$bit->accion] ?? 'bg-secondary' }} mt-1">
                                                            {{ ucfirst(str_replace('_', ' ', $bit->accion)) }}
                                                        </span>
                                                    </div>
                                                    <div class="col-6">
                                                        <span class="text-muted d-block">Módulo Afectado:</span>
                                                        <code class="fw-bold">{{ $bit->tabla_afectada ?: 'N/A' }}</code> (ID: {{ $bit->registro_id ?: 'N/A' }})
                                                    </div>
                                                    <div class="col-6 mt-3">
                                                        <span class="text-muted d-block">Fecha y Hora:</span>
                                                        <div class="fw-bold text-dark">{{ $bit->created_at->format('d/m/Y H:i:s') }}</div>
                                                        <div class="text-muted" style="font-size: 0.75rem;">({{ $bit->created_at->diffForHumans() }})</div>
                                                    </div>
                                                    <div class="col-6 mt-3">
                                                        <span class="text-muted d-block">Dirección IP de Origen:</span>
                                                        <div class="fw-bold text-dark"><i class="bi bi-broadcast me-1"></i>{{ $bit->ip_origen ?: 'No registrada' }}</div>
                                                    </div>
                                                    <div class="col-12 mt-3 pt-2 border-top">
                                                        <span class="text-muted d-block">Usuario Responsable:</span>
                                                        @if ($bit->usuario)
                                                            <div class="fw-bold text-dark">{{ $bit->usuario->nombre_completo ?: $bit->usuario->nombre_usuario }}</div>
                                                            <div class="text-muted small">Rol: {{ ucfirst($bit->usuario->rol) }} | Usuario: {{ $bit->usuario->nombre_usuario }}</div>
                                                        @else
                                                            <div class="text-muted">Proceso automático del sistema o formulario público</div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer bg-light py-2">
                                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="text-muted mb-2">
                                    <i class="bi bi-journal-x fs-1 text-secondary"></i>
                                </div>
                                <h6 class="fw-bold text-dark">No se encontraron eventos coincidentes</h6>
                                <p class="text-muted small mb-3">Intenta cambiar los parámetros de búsqueda o restablecer los filtros de auditoría.</p>
                                @if (request()->hasAny(['buscar', 'accion', 'tabla', 'usuario_id', 'desde', 'hasta']))
                                    <a href="{{ route('admin.bitacoras.index') }}" class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-arrow-counterclockwise me-1"></i> Limpiar Filtros
                                    </a>
                                @endif
                            </td>
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
