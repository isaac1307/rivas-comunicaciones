@extends('layouts.app')

@section('title', 'Ticket de Soporte ' . $ticket->folio)

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                <div>
                    <span class="badge bg-info-subtle text-info px-3 py-1 rounded-pill small fw-bold">Atención Técnica</span>
                    <h2 class="fw-bold mb-0 mt-1">Ticket: <span class="text-primary">{{ $ticket->folio }}</span></h2>
                </div>
                <a href="{{ route('tickets.create') }}" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-plus-circle me-1"></i> Reportar Otro Problema
                </a>
            </div>

            <!-- Estatus del Ticket -->
            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
                <h5 class="fw-bold mb-3 text-dark">Estatus del Incidente</h5>
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 p-3 rounded-3 bg-light">
                    <div>
                        <span class="small text-muted d-block">Estado:</span>
                        @php
                            $badgeColors = [
                                'abierto' => 'bg-danger text-white',
                                'en_proceso' => 'bg-warning text-dark',
                                'resuelto' => 'bg-success text-white',
                                'cerrado' => 'bg-secondary text-white',
                            ];
                        @endphp
                        <span class="badge {{ $badgeColors[$ticket->estado] ?? 'bg-secondary' }} fs-6 px-3 py-2 text-uppercase fw-bold">
                            {{ str_replace('_', ' ', ucfirst($ticket->estado)) }}
                        </span>
                    </div>
                    <div class="text-end">
                        <span class="small text-muted d-block">Fecha de Reporte:</span>
                        <span class="fw-semibold">{{ $ticket->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                </div>
            </div>

            <!-- Detalle del Ticket -->
            <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
                <h5 class="fw-bold text-primary mb-4 border-bottom pb-2">Información del Reporte</h5>

                <div class="row g-4">
                    <div class="col-md-6">
                        <span class="small text-muted d-block">Cliente Titular:</span>
                        <div class="fw-bold text-dark fs-5">{{ $ticket->cliente->nombre_completo }}</div>
                    </div>

                    <div class="col-md-6">
                        <span class="small text-muted d-block">Plan Asociado:</span>
                        <div class="fw-semibold text-primary fs-5">
                            {{ $ticket->cliente->plan ? $ticket->cliente->plan->nombre : 'Sin plan registrado' }}
                        </div>
                    </div>

                    <div class="col-md-6">
                        <span class="small text-muted d-block">Categoría del Reporte:</span>
                        <div class="fw-bold text-dark text-capitalize">
                            <i class="bi bi-tag-fill me-1 text-primary"></i> {{ str_replace('_', ' ', $ticket->categoria) }}
                        </div>
                    </div>

                    <div class="col-md-6">
                        <span class="small text-muted d-block">Fecha de Resolución:</span>
                        <div class="fw-semibold text-dark">
                            {{ $ticket->fecha_resolucion ? $ticket->fecha_resolucion->format('d/m/Y H:i') : 'Pendiente de resolución' }}
                        </div>
                    </div>

                    <div class="col-12">
                        <span class="small text-muted d-block">Descripción Reportada:</span>
                        <div class="p-3 bg-light rounded-3 text-dark mt-1">
                            {{ $ticket->descripcion }}
                        </div>
                    </div>

                    @if ($ticket->observaciones_admin)
                        <div class="col-12">
                            <span class="small text-muted d-block fw-bold text-success">
                                <i class="bi bi-chat-quote-fill me-1"></i> Respuesta / Observaciones del Equipo Técnico:
                            </span>
                            <div class="p-3 border border-success-subtle bg-success-subtle rounded-3 text-dark mt-1">
                                {{ $ticket->observaciones_admin }}
                            </div>
                        </div>
                    @endif
                </div>

                <hr class="my-4">

                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <span class="text-muted small">Cualquier seguimiento adicional puedes mencionarlo con el folio {{ $ticket->folio }}.</span>
                    <button class="btn btn-outline-secondary btn-sm" onclick="window.print()">
                        <i class="bi bi-printer me-1"></i> Imprimir Comprobante
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
