@extends('layouts.admin')

@section('title', 'Dashboard de Operaciones')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h2 class="fw-bold mb-1">Panel de Control & Estadísticas</h2>
        <p class="text-muted small mb-0">Resumen operativo de Rivas Comunicaciones en tiempo real.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.reservaciones.index') }}" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-calendar-check me-1"></i> Ver Reservaciones
        </a>
        <a href="{{ route('admin.tickets.index') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-ticket-detailed me-1"></i> Ver Tickets
        </a>
    </div>
</div>

<!-- Tarjetas de Métricas -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card bg-white p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small text-uppercase fw-bold">Clientes Activos</span>
                    <h3 class="fw-extrabold text-primary mb-0 mt-1">{{ $metricas['clientesActivos'] }}</h3>
                    <span class="text-muted small">Total registrados: {{ $metricas['totalClientes'] }}</span>
                </div>
                <div class="badge bg-primary-subtle text-primary p-3 rounded-circle fs-4">
                    <i class="bi bi-people-fill"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card stat-card bg-white p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small text-uppercase fw-bold">Tickets Abiertos</span>
                    <h3 class="fw-extrabold text-danger mb-0 mt-1">{{ $metricas['ticketsAbiertos'] }}</h3>
                    <span class="text-success small"><i class="bi bi-check-circle"></i> {{ $metricas['ticketsResueltos'] }} resueltos</span>
                </div>
                <div class="badge bg-danger-subtle text-danger p-3 rounded-circle fs-4">
                    <i class="bi bi-exclamation-octagon-fill"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card stat-card bg-white p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small text-uppercase fw-bold">Reservaciones Pendientes</span>
                    <h3 class="fw-extrabold text-warning mb-0 mt-1">{{ $metricas['reservacionesPendientes'] }}</h3>
                    <span class="text-muted small">{{ $metricas['reservacionesProgramadas'] }} programadas</span>
                </div>
                <div class="badge bg-warning-subtle text-warning p-3 rounded-circle fs-4">
                    <i class="bi bi-calendar-event-fill"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card stat-card bg-white p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small text-uppercase fw-bold">Mensajes Nuevos</span>
                    <h3 class="fw-extrabold text-info mb-0 mt-1">{{ $metricas['mensajesNoLeidos'] }}</h3>
                    <span class="text-muted small">Planes activos: {{ $metricas['totalPlanes'] }}</span>
                </div>
                <div class="badge bg-info-subtle text-info p-3 rounded-circle fs-4">
                    <i class="bi bi-chat-dots-fill"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Últimas Reservaciones -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
            <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center pt-3 px-3">
                <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-calendar-check me-2 text-primary"></i> Últimas Reservaciones</h5>
                <a href="{{ route('admin.reservaciones.index') }}" class="btn btn-link btn-sm text-primary text-decoration-none">Ver todas</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small text-uppercase text-muted">
                            <tr>
                                <th>Folio</th>
                                <th>Solicitante</th>
                                <th>Servicio</th>
                                <th>Estado</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody class="small">
                            @forelse ($ultimasReservaciones as $res)
                                <tr>
                                    <td class="fw-bold">{{ $res->folio }}</td>
                                    <td>{{ $res->nombre_solicitante }}</td>
                                    <td><span class="badge bg-light text-dark border">{{ str_replace('_', ' ', $res->tipo_servicio) }}</span></td>
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
                                    <td>
                                        <a href="{{ route('admin.reservaciones.show', $res) }}" class="btn btn-sm btn-outline-primary py-0">
                                            Detalle
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-3">No hay reservaciones recientes.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Últimos Tickets de Soporte -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
            <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center pt-3 px-3">
                <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-ticket-detailed me-2 text-primary"></i> Tickets de Soporte Recientes</h5>
                <a href="{{ route('admin.tickets.index') }}" class="btn btn-link btn-sm text-primary text-decoration-none">Ver todos</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small text-uppercase text-muted">
                            <tr>
                                <th>Folio</th>
                                <th>Cliente</th>
                                <th>Categoría</th>
                                <th>Estado</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody class="small">
                            @forelse ($ultimosTickets as $tkt)
                                <tr>
                                    <td class="fw-bold">{{ $tkt->folio }}</td>
                                    <td>{{ $tkt->cliente->nombre_completo }}</td>
                                    <td><span class="badge bg-light text-dark border">{{ str_replace('_', ' ', $tkt->categoria) }}</span></td>
                                    <td>
                                        @php
                                            $badgeTkt = [
                                                'abierto' => 'bg-danger text-white',
                                                'en_proceso' => 'bg-warning text-dark',
                                                'resuelto' => 'bg-success text-white',
                                                'cerrado' => 'bg-secondary text-white',
                                            ];
                                        @endphp
                                        <span class="badge {{ $badgeTkt[$tkt->estado] ?? 'bg-secondary' }}">
                                            {{ str_replace('_', ' ', ucfirst($tkt->estado)) }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.tickets.show', $tkt) }}" class="btn btn-sm btn-outline-primary py-0">
                                            Gestionar
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-3">No hay tickets de soporte recientes.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Actividad Reciente de Auditoría (Bitácora) -->
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-3 bg-white">
            <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center pt-3 px-3">
                <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-clock-history me-2 text-primary"></i> Actividad Reciente del Sistema (Bitácora)</h5>
                @if (auth()->user()->isAdmin())
                    <a href="{{ route('admin.bitacoras.index') }}" class="btn btn-link btn-sm text-primary text-decoration-none">Ver historial completo</a>
                @endif
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small text-uppercase text-muted">
                            <tr>
                                <th>Fecha</th>
                                <th>Usuario</th>
                                <th>Acción</th>
                                <th>Descripción</th>
                                <th>Tabla</th>
                                <th>IP</th>
                            </tr>
                        </thead>
                        <tbody class="small">
                            @forelse ($ultimasBitacoras as $bit)
                                <tr>
                                    <td class="text-muted">{{ $bit->created_at->format('d/m/Y H:i:s') }}</td>
                                    <td class="fw-bold">{{ $bit->usuario ? ($bit->usuario->nombre_completo ?: $bit->usuario->nombre_usuario) : 'Sistema / Público' }}</td>
                                    <td>
                                        <span class="badge {{ $bit->accion === 'creacion' ? 'bg-success' : ($bit->accion === 'actualizacion' ? 'bg-warning text-dark' : 'bg-danger') }}">
                                            {{ ucfirst($bit->accion) }}
                                        </span>
                                    </td>
                                    <td>{{ $bit->descripcion }}</td>
                                    <td><code>{{ $bit->tabla_afectada }}</code></td>
                                    <td class="text-muted">{{ $bit->ip_origen ?: 'N/A' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-3">No hay registros de bitácora todavía.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
