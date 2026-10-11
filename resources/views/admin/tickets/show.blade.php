@extends('layouts.admin')

@section('title', 'Ticket ' . $ticket->folio)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <a href="{{ route('admin.tickets.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
            <i class="bi bi-arrow-left me-1"></i> Volver a Tickets
        </a>
        <h2 class="fw-bold mb-1">Ticket: <span class="text-primary">{{ $ticket->folio }}</span></h2>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.tickets.edit', $ticket) }}" class="btn btn-primary btn-sm">
            <i class="bi bi-pencil me-1"></i> Actualizar / Resolver
        </a>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-3 bg-white p-3">
            <span class="text-muted small">Estado</span>
            <div class="mt-2">
                @php
                    $badgeTkt = [
                        'abierto' => 'bg-danger text-white',
                        'en_proceso' => 'bg-warning text-dark',
                        'resuelto' => 'bg-success text-white',
                        'cerrado' => 'bg-secondary text-white',
                    ];
                @endphp
                <span class="badge {{ $badgeTkt[$ticket->estado] ?? 'bg-secondary' }} fs-6 text-uppercase">
                    {{ str_replace('_', ' ', ucfirst($ticket->estado)) }}
                </span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-3 bg-white p-3">
            <span class="text-muted small">Categoría</span>
            <div class="fs-5 fw-bold text-dark mt-1 text-capitalize">
                {{ str_replace('_', ' ', $ticket->categoria) }}
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-3 bg-white p-3">
            <span class="text-muted small">Fecha de Resolución</span>
            <div class="fs-6 fw-bold text-muted mt-1">
                {{ $ticket->fecha_resolucion ? $ticket->fecha_resolucion->format('d/m/Y H:i') : 'No resuelto todavía' }}
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-3 bg-white p-4 mb-4">
    <h5 class="fw-bold text-primary mb-3">Información del Cliente Afectado</h5>
    <div class="row g-3">
        <div class="col-md-6">
            <span class="small text-muted d-block">Cliente:</span>
            <div class="fw-bold fs-5">{{ $ticket->cliente->nombre_completo }}</div>
        </div>
        <div class="col-md-6">
            <span class="small text-muted d-block">Teléfono:</span>
            <div class="fw-bold fs-5"><i class="bi bi-telephone text-primary me-1"></i> {{ $ticket->cliente->telefono }}</div>
        </div>
        <div class="col-md-6">
            <span class="small text-muted d-block">Dirección:</span>
            <div><i class="bi bi-geo-alt text-primary me-1"></i> {{ $ticket->cliente->direccion }}</div>
        </div>
        <div class="col-md-6">
            <span class="small text-muted d-block">Plan de Internet:</span>
            <div class="fw-bold text-primary">{{ $ticket->cliente->plan ? $ticket->cliente->plan->nombre : 'Sin plan' }}</div>
        </div>
    </div>

    <hr class="my-4">

    <h5 class="fw-bold text-dark mb-2">Falla Reportada</h5>
    <div class="p-3 bg-light rounded-3 text-dark mb-4">
        {{ $ticket->descripcion }}
    </div>

    @if ($ticket->observaciones_admin)
        <h5 class="fw-bold text-success mb-2"><i class="bi bi-check2-circle me-1"></i> Observaciones Técnicas / Resolución</h5>
        <div class="p-3 border border-success-subtle bg-success-subtle rounded-3 text-dark">
            {{ $ticket->observaciones_admin }}
        </div>
    @endif
</div>
@endsection
