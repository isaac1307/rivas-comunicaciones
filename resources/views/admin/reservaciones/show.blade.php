@extends('layouts.admin')

@section('title', 'Detalle de Reservación ' . $reservacion->folio)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <a href="{{ route('admin.reservaciones.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
            <i class="bi bi-arrow-left me-1"></i> Volver a Reservaciones
        </a>
        <h2 class="fw-bold mb-1">Folio: <span class="text-primary">{{ $reservacion->folio }}</span></h2>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.reservaciones.edit', $reservacion) }}" class="btn btn-primary btn-sm">
            <i class="bi bi-pencil me-1"></i> Actualizar Estado
        </a>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-3 bg-white p-3">
            <span class="text-muted small">Estado Actual</span>
            <div class="mt-2">
                @php
                    $badgeRes = [
                        'pendiente' => 'bg-warning text-dark',
                        'programada' => 'bg-info text-dark',
                        'completada' => 'bg-success text-white',
                        'cancelada' => 'bg-danger text-white',
                    ];
                @endphp
                <span class="badge {{ $badgeRes[$reservacion->estado] ?? 'bg-secondary' }} fs-6 text-uppercase">
                    {{ ucfirst($reservacion->estado) }}
                </span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-3 bg-white p-3">
            <span class="text-muted small">Fecha Solicitada</span>
            <div class="fs-4 fw-bold text-dark mt-1">
                {{ $reservacion->fecha_solicitada ? $reservacion->fecha_solicitada->format('d/m/Y') : 'Por definir' }}
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-3 bg-white p-3">
            <span class="text-muted small">Fecha de Registro</span>
            <div class="fs-6 fw-bold text-muted mt-1">{{ $reservacion->created_at->format('d/m/Y H:i') }}</div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-3 bg-white p-4">
    <h5 class="fw-bold text-primary mb-3">Datos del Prospecto y Servicio</h5>
    <div class="row g-3">
        <div class="col-md-6">
            <span class="small text-muted d-block">Nombre del Solicitante:</span>
            <div class="fw-bold text-dark fs-5">{{ $reservacion->nombre_solicitante }}</div>
        </div>
        <div class="col-md-6">
            <span class="small text-muted d-block">Teléfono:</span>
            <div class="fw-bold text-dark fs-5"><i class="bi bi-telephone text-primary me-1"></i> {{ $reservacion->telefono }}</div>
        </div>
        <div class="col-md-6">
            <span class="small text-muted d-block">Correo Electrónico:</span>
            <div class="fw-semibold text-dark">{{ $reservacion->correo_electronico ?: 'No especificado' }}</div>
        </div>
        <div class="col-md-6">
            <span class="small text-muted d-block">Tipo de Servicio:</span>
            <div class="fw-semibold text-dark text-capitalize">{{ str_replace('_', ' ', $reservacion->tipo_servicio) }}</div>
        </div>
        <div class="col-md-12">
            <span class="small text-muted d-block">Dirección:</span>
            <div class="fw-semibold text-dark"><i class="bi bi-geo-alt text-primary me-1"></i> {{ $reservacion->direccion }}</div>
        </div>
        <div class="col-md-6">
            <span class="small text-muted d-block">Plan de Internet:</span>
            <div class="fw-bold text-primary fs-5">{{ $reservacion->plan ? $reservacion->plan->nombre : 'Sin plan definido' }}</div>
        </div>
        @if ($reservacion->observaciones)
            <div class="col-12">
                <span class="small text-muted d-block">Observaciones:</span>
                <div class="p-3 bg-light rounded-3 text-muted small mt-1">
                    {{ $reservacion->observaciones }}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
