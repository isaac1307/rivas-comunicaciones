@extends('layouts.admin')

@section('title', 'Actualizar Reservación ' . $reservacion->folio)

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.reservaciones.show', $reservacion) }}" class="btn btn-outline-secondary btn-sm mb-2">
        <i class="bi bi-arrow-left me-1"></i> Volver a Detalle
    </a>
    <h2 class="fw-bold mb-1">Actualizar Estatus: Folio {{ $reservacion->folio }}</h2>
</div>

<div class="card border-0 shadow-sm rounded-3 bg-white p-4">
    <form action="{{ route('admin.reservaciones.update', $reservacion) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row g-3">
            <div class="col-md-6">
                <label for="estado" class="form-label fw-bold small">Estado de la Reservación *</label>
                <select class="form-select form-select-lg @error('estado') is-invalid @enderror" id="estado" name="estado" required>
                    <option value="pendiente" {{ old('estado', $reservacion->estado) === 'pendiente' ? 'selected' : '' }}>Pendiente de Validación</option>
                    <option value="programada" {{ old('estado', $reservacion->estado) === 'programada' ? 'selected' : '' }}>Programada (Cita Asignada)</option>
                    <option value="completada" {{ old('estado', $reservacion->estado) === 'completada' ? 'selected' : '' }}>Completada (Instalado con Éxito)</option>
                    <option value="cancelada" {{ old('estado', $reservacion->estado) === 'cancelada' ? 'selected' : '' }}>Cancelada (Sin cobertura / Rechazado)</option>
                </select>
                <div class="form-text text-muted small mt-1">
                    <i class="bi bi-info-circle text-primary me-1"></i>
                    Al marcar como <strong>Completada (Instalado con Éxito)</strong>, el solicitante se registrará o activará automáticamente en <strong>Clientes Activos</strong>.
                </div>
                @error('estado')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="fecha_solicitada" class="form-label fw-bold small">Fecha Programada de Visita</label>
                <input type="date" class="form-control @error('fecha_solicitada') is-invalid @enderror" id="fecha_solicitada" name="fecha_solicitada" value="{{ old('fecha_solicitada', $reservacion->fecha_solicitada?->format('Y-m-d')) }}">
                @error('fecha_solicitada')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="plan_id" class="form-label fw-bold small">Plan Asignado</label>
                <select class="form-select @error('plan_id') is-invalid @enderror" id="plan_id" name="plan_id">
                    <option value="">-- Sin Plan Asignado --</option>
                    @foreach ($planes as $plan)
                        <option value="{{ $plan->id }}" {{ old('plan_id', $reservacion->plan_id) == $plan->id ? 'selected' : '' }}>
                            {{ $plan->nombre }} (${{ number_format($plan->precio_mensual, 2) }})
                        </option>
                    @endforeach
                </select>
                @error('plan_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12">
                <label for="observaciones" class="form-label fw-bold small">Observaciones del Administrador / Técnico</label>
                <textarea class="form-control @error('observaciones') is-invalid @enderror" id="observaciones" name="observaciones" rows="4" placeholder="Notas sobre la visita técnica, asignación de cuadrilla, etc.">{{ old('observaciones', $reservacion->observaciones) }}</textarea>
                @error('observaciones')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12 mt-4 d-flex justify-content-end gap-2">
                <a href="{{ route('admin.reservaciones.show', $reservacion) }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary fw-bold">
                    <i class="bi bi-save me-1"></i> Guardar Cambios
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
