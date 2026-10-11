@extends('layouts.admin')

@section('title', 'Gestionar Ticket ' . $ticket->folio)

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.tickets.show', $ticket) }}" class="btn btn-outline-secondary btn-sm mb-2">
        <i class="bi bi-arrow-left me-1"></i> Volver a Detalle
    </a>
    <h2 class="fw-bold mb-1">Atender Ticket: {{ $ticket->folio }}</h2>
    <span class="text-muted">Cliente: {{ $ticket->cliente->nombre_completo }}</span>
</div>

<div class="card border-0 shadow-sm rounded-3 bg-white p-4">
    <form action="{{ route('admin.tickets.update', $ticket) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row g-3">
            <div class="col-md-6">
                <label for="categoria" class="form-label fw-bold small">Categoría *</label>
                <select class="form-select @error('categoria') is-invalid @enderror" id="categoria" name="categoria" required>
                    <option value="falla_servicio" {{ old('categoria', $ticket->categoria) === 'falla_servicio' ? 'selected' : '' }}>Falla de Servicio</option>
                    <option value="soporte_tecnico" {{ old('categoria', $ticket->categoria) === 'soporte_tecnico' ? 'selected' : '' }}>Soporte Técnico</option>
                    <option value="consulta" {{ old('categoria', $ticket->categoria) === 'consulta' ? 'selected' : '' }}>Consulta</option>
                    <option value="cambio_plan" {{ old('categoria', $ticket->categoria) === 'cambio_plan' ? 'selected' : '' }}>Cambio de Plan</option>
                    <option value="cancelacion" {{ old('categoria', $ticket->categoria) === 'cancelacion' ? 'selected' : '' }}>Cancelación</option>
                </select>
                @error('categoria')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="estado" class="form-label fw-bold small">Estado del Ticket *</label>
                <select class="form-select form-select-lg @error('estado') is-invalid @enderror" id="estado" name="estado" required>
                    <option value="abierto" {{ old('estado', $ticket->estado) === 'abierto' ? 'selected' : '' }}>Abierto (Pendiente de Asignación)</option>
                    <option value="en_proceso" {{ old('estado', $ticket->estado) === 'en_proceso' ? 'selected' : '' }}>En Proceso (Técnico Atendiendo)</option>
                    <option value="resuelto" {{ old('estado', $ticket->estado) === 'resuelto' ? 'selected' : '' }}>Resuelto (Solución Aplicada)</option>
                    <option value="cerrado" {{ old('estado', $ticket->estado) === 'cerrado' ? 'selected' : '' }}>Cerrado Definitivo</option>
                </select>
                @error('estado')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="fecha_resolucion" class="form-label fw-bold small">Fecha y Hora de Resolución</label>
                <input type="datetime-local" class="form-control @error('fecha_resolucion') is-invalid @enderror" id="fecha_resolucion" name="fecha_resolucion" value="{{ old('fecha_resolucion', $ticket->fecha_resolucion?->format('Y-m-d\TH:i')) }}">
                <div class="form-text">Si seleccionas "Resuelto" y dejas vacío, se guardará la fecha actual.</div>
                @error('fecha_resolucion')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12">
                <div class="p-3 bg-light rounded-3">
                    <span class="small fw-bold text-muted d-block">Descripción Original del Cliente:</span>
                    <p class="mb-0 text-dark small mt-1">{{ $ticket->descripcion }}</p>
                </div>
            </div>

            <div class="col-12">
                <label for="observaciones_admin" class="form-label fw-bold small">Observaciones Técnicas / Acciones Realizadas *</label>
                <textarea class="form-control @error('observaciones_admin') is-invalid @enderror" id="observaciones_admin" name="observaciones_admin" rows="4" placeholder="Indica el diagnóstico técnico, acciones tomadas (reemplazo de fibra, reinicio de puerto OLT, etc.)...">{{ old('observaciones_admin', $ticket->observaciones_admin) }}</textarea>
                @error('observaciones_admin')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12 mt-4 d-flex justify-content-end gap-2">
                <a href="{{ route('admin.tickets.show', $ticket) }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary fw-bold">
                    <i class="bi bi-save me-1"></i> Actualizar Ticket
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
