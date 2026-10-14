@extends('layouts.admin')

@section('title', 'Expediente de ' . $cliente->nombre_completo)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <a href="{{ route('admin.clientes.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
            <i class="bi bi-arrow-left me-1"></i> Volver a Clientes
        </a>
        <h2 class="fw-bold mb-1">{{ $cliente->nombre_completo }}</h2>
        <span class="badge bg-light text-dark border">Cliente #{{ $cliente->id }}</span>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.cobranza.show', $cliente) }}" class="btn btn-outline-success btn-sm">
            <i class="bi bi-cash-stack me-1"></i> Cobranza / Pagos
        </a>
        <a href="{{ route('admin.clientes.edit', $cliente) }}" class="btn btn-primary btn-sm">
            <i class="bi bi-pencil me-1"></i> Editar Cliente
        </a>
        <a href="{{ route('admin.tickets.create') }}" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-plus-circle me-1"></i> Nuevo Ticket para Cliente
        </a>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-3 bg-white p-3">
            <span class="text-muted small">Plan de Internet</span>
            @if ($cliente->plan)
                <div class="fs-4 fw-bold text-primary mt-1">{{ $cliente->plan->nombre }}</div>
                <span class="text-muted small">${{ number_format($cliente->plan->precio_mensual, 2) }}/mes ({{ $cliente->plan->velocidad_descarga }} Mbps)</span>
            @else
                <div class="fs-5 text-muted mt-1">Sin plan contratado</div>
            @endif
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-3 bg-white p-3">
            <span class="text-muted small">Contacto</span>
            <div class="fs-5 fw-bold text-dark mt-1"><i class="bi bi-telephone text-primary me-1"></i> {{ $cliente->telefono }}</div>
            <div class="text-muted small"><i class="bi bi-envelope me-1"></i> {{ $cliente->correo_electronico ?: 'Sin correo' }}</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-3 bg-white p-3">
            <span class="text-muted small">Estatus y Fecha de Alta</span>
            <div class="mt-1 d-flex align-items-center gap-2">
                <span class="badge {{ $cliente->estado === 'activo' ? 'bg-success' : 'bg-secondary' }} fs-6">
                    {{ ucfirst($cliente->estado) }}
                </span>
                <span class="text-muted small">Desde {{ $cliente->fecha_alta->format('d/m/Y') }}</span>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-3 bg-white p-4 mb-4">
    <h5 class="fw-bold text-dark mb-2">Dirección de Instalación</h5>
    <p class="text-dark mb-3"><i class="bi bi-geo-alt text-primary me-1"></i> {{ $cliente->direccion }}</p>

    @if ($cliente->notas)
        <h6 class="fw-bold text-dark mb-1">Notas del Expediente</h6>
        <p class="text-muted small mb-0">{{ $cliente->notas }}</p>
    @endif
</div>

@if ($cliente->reservaciones && $cliente->reservaciones->isNotEmpty())
    <!-- Historial de Solicitud de Instalación Vinculada -->
    <div class="card border-0 shadow-sm rounded-3 bg-white mb-4">
        <div class="card-header bg-transparent border-0 pt-3 px-3">
            <h5 class="fw-bold mb-0 text-dark">
                <i class="bi bi-calendar-check text-success me-2"></i> Solicitud de Instalación de Origen ({{ $cliente->reservaciones->count() }})
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small">
                    <thead class="table-light text-uppercase text-muted">
                        <tr>
                            <th>Folio</th>
                            <th>Servicio Solicitado</th>
                            <th>Plan</th>
                            <th>Fecha Programada</th>
                            <th>Estado</th>
                            <th class="text-end">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($cliente->reservaciones as $res)
                            <tr>
                                <td class="fw-bold text-primary">{{ $res->folio }}</td>
                                <td><span class="badge bg-light text-dark border">{{ str_replace('_', ' ', $res->tipo_servicio) }}</span></td>
                                <td>{{ $res->plan ? $res->plan->nombre : 'Sin plan' }}</td>
                                <td>{{ $res->fecha_solicitada ? $res->fecha_solicitada->format('d/m/Y') : 'Sin fecha' }}</td>
                                <td>
                                    <span class="badge bg-success text-white">
                                        <i class="bi bi-check-circle me-1"></i> {{ ucfirst($res->estado) }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('admin.reservaciones.show', $res) }}" class="btn btn-sm btn-outline-primary py-0">
                                        Ver Reservación
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif

<!-- Historial de Tickets del Cliente -->
<div class="card border-0 shadow-sm rounded-3 bg-white">
    <div class="card-header bg-transparent border-0 pt-3 px-3">
        <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-ticket-detailed-fill text-primary me-2"></i> Historial de Tickets del Cliente ({{ $cliente->tickets->count() }})</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small text-uppercase text-muted">
                    <tr>
                        <th>Folio</th>
                        <th>Categoría</th>
                        <th>Descripción</th>
                        <th>Fecha Reporte</th>
                        <th>Estado</th>
                        <th class="text-end">Acción</th>
                    </tr>
                </thead>
                <tbody class="small">
                    @forelse ($cliente->tickets as $tkt)
                        <tr>
                            <td class="fw-bold">{{ $tkt->folio }}</td>
                            <td><span class="badge bg-light text-dark border">{{ str_replace('_', ' ', $tkt->categoria) }}</span></td>
                            <td>{{ Str::limit($tkt->descripcion, 50) }}</td>
                            <td>{{ $tkt->created_at->format('d/m/Y H:i') }}</td>
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
                            <td class="text-end">
                                <a href="{{ route('admin.tickets.show', $tkt) }}" class="btn btn-sm btn-outline-primary py-0">Gestionar</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-3">El cliente no tiene tickets registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
