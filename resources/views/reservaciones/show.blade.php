@extends('layouts.app')

@section('title', 'Detalle de Reservación ' . $reservacion->folio)

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                <div>
                    <span class="badge bg-primary-subtle text-primary px-3 py-1 rounded-pill small fw-bold">Consulta de Solicitud</span>
                    <h2 class="fw-bold mb-0 mt-1">Folio: <span class="text-primary">{{ $reservacion->folio }}</span></h2>
                </div>
                <a href="{{ route('reservaciones.create') }}" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-plus-circle me-1"></i> Nueva Solicitud
                </a>
            </div>

            <!-- Estatus Tracker -->
            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
                <h5 class="fw-bold mb-3 text-dark">Estatus del Trámite</h5>
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 p-3 rounded-3 bg-light">
                    <div>
                        <span class="small text-muted d-block">Estado Actual:</span>
                        @php
                            $badgeColors = [
                                'pendiente' => 'bg-warning text-dark',
                                'programada' => 'bg-info text-dark',
                                'completada' => 'bg-success text-white',
                                'cancelada' => 'bg-danger text-white',
                            ];
                        @endphp
                        <span class="badge {{ $badgeColors[$reservacion->estado] ?? 'bg-secondary' }} fs-6 px-3 py-2 text-uppercase fw-bold">
                            {{ ucfirst($reservacion->estado) }}
                        </span>
                    </div>
                    <div class="text-end">
                        <span class="small text-muted d-block">Fecha de Registro:</span>
                        <span class="fw-semibold">{{ $reservacion->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                </div>
            </div>

            <!-- Datos de la Reservación -->
            <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
                <h5 class="fw-bold text-primary mb-4 border-bottom pb-2">Información del Servicio</h5>
                
                <div class="row g-4">
                    <div class="col-md-6">
                        <span class="small text-muted d-block">Solicitante:</span>
                        <div class="fw-bold text-dark fs-5">{{ $reservacion->nombre_solicitante }}</div>
                    </div>

                    <div class="col-md-6">
                        <span class="small text-muted d-block">Teléfono:</span>
                        <div class="fw-bold text-dark fs-5"><i class="bi bi-telephone me-1 text-primary"></i> {{ $reservacion->telefono }}</div>
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
                        <span class="small text-muted d-block">Dirección de Instalación:</span>
                        <div class="fw-semibold text-dark"><i class="bi bi-geo-alt me-1 text-primary"></i> {{ $reservacion->direccion }}</div>
                    </div>

                    <div class="col-md-6">
                        <span class="small text-muted d-block">Plan de Internet Solicitado:</span>
                        @if ($reservacion->plan)
                            <div class="fw-bold text-primary fs-5">{{ $reservacion->plan->nombre }}</div>
                            <span class="badge bg-light text-dark border">{{ $reservacion->plan->velocidad_descarga }} Mbps Bajada • ${{ number_format($reservacion->plan->precio_mensual, 2) }}/mes</span>
                        @else
                            <div class="text-muted">Por definir con el asesor técnico</div>
                        @endif
                    </div>

                    <div class="col-md-6">
                        <span class="small text-muted d-block">Fecha Solicitada para Visita:</span>
                        <div class="fw-bold text-dark fs-5">
                            <i class="bi bi-calendar-event me-1 text-primary"></i>
                            {{ $reservacion->fecha_solicitada ? $reservacion->fecha_solicitada->format('d/m/Y') : 'Por agendar' }}
                        </div>
                    </div>

                    @if ($reservacion->observaciones)
                        <div class="col-md-12">
                            <span class="small text-muted d-block">Observaciones:</span>
                            <div class="p-3 bg-light rounded-3 text-muted small mt-1">
                                {{ $reservacion->observaciones }}
                            </div>
                        </div>
                    @endif
                </div>

                <hr class="my-4">

                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <span class="text-muted small">Conserva este folio para cualquier aclaración telefónica o presencial.</span>
                    <button class="btn btn-outline-secondary btn-sm" onclick="window.print()">
                        <i class="bi bi-printer me-1"></i> Imprimir Folio
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
