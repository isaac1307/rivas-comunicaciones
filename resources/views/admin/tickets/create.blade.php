@extends('layouts.admin')

@section('title', 'Levantar Ticket de Soporte')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.tickets.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
        <i class="bi bi-arrow-left me-1"></i> Volver a Tickets
    </a>
    <h2 class="fw-bold mb-1">Levantar Ticket Administrativo</h2>
</div>

<div class="card border-0 shadow-sm rounded-3 bg-white p-4">
    <form action="{{ route('admin.tickets.store') }}" method="POST">
        @csrf

        <div class="row g-3">
            <div class="col-md-6">
                <label for="cliente_id" class="form-label fw-bold small">Cliente Titular *</label>
                <select class="form-select @error('cliente_id') is-invalid @enderror" id="cliente_id" name="cliente_id" required>
                    <option value="">-- Seleccionar Cliente --</option>
                    @foreach ($clientes as $cli)
                        <option value="{{ $cli->id }}" {{ old('cliente_id') == $cli->id ? 'selected' : '' }}>
                            {{ $cli->nombre_completo }} (Tel: {{ $cli->telefono }})
                        </option>
                    @endforeach
                </select>
                @error('cliente_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="categoria" class="form-label fw-bold small">Categoría del Incidente *</label>
                <select class="form-select @error('categoria') is-invalid @enderror" id="categoria" name="categoria" required>
                    <option value="falla_servicio" {{ old('categoria') === 'falla_servicio' ? 'selected' : '' }}>Falla de Servicio</option>
                    <option value="soporte_tecnico" {{ old('categoria') === 'soporte_tecnico' ? 'selected' : '' }}>Soporte Técnico</option>
                    <option value="consulta" {{ old('categoria') === 'consulta' ? 'selected' : '' }}>Consulta</option>
                    <option value="cambio_plan" {{ old('categoria') === 'cambio_plan' ? 'selected' : '' }}>Cambio de Plan</option>
                    <option value="cancelacion" {{ old('categoria') === 'cancelacion' ? 'selected' : '' }}>Cancelación</option>
                </select>
                @error('categoria')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="estado" class="form-label fw-bold small">Estado Inicial *</label>
                <select class="form-select @error('estado') is-invalid @enderror" id="estado" name="estado" required>
                    <option value="abierto" {{ old('estado', 'abierto') === 'abierto' ? 'selected' : '' }}>Abierto</option>
                    <option value="en_proceso" {{ old('estado') === 'en_proceso' ? 'selected' : '' }}>En Proceso</option>
                    <option value="resuelto" {{ old('estado') === 'resuelto' ? 'selected' : '' }}>Resuelto</option>
                    <option value="cerrado" {{ old('estado') === 'cerrado' ? 'selected' : '' }}>Cerrado</option>
                </select>
                @error('estado')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12">
                <label for="descripcion" class="form-label fw-bold small">Descripción del Reporte *</label>
                <textarea class="form-control @error('descripcion') is-invalid @enderror" id="descripcion" name="descripcion" rows="4" placeholder="Detalles de la falla o solicitud del cliente..." required>{{ old('descripcion') }}</textarea>
                @error('descripcion')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12">
                <label for="observaciones_admin" class="form-label fw-bold small">Observaciones Internas / Diagnóstico</label>
                <textarea class="form-control @error('observaciones_admin') is-invalid @enderror" id="observaciones_admin" name="observaciones_admin" rows="3" placeholder="Comentarios del operador o diagnóstico inicial...">{{ old('observaciones_admin') }}</textarea>
                @error('observaciones_admin')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12 mt-4 d-flex justify-content-end gap-2">
                <a href="{{ route('admin.tickets.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary fw-bold">
                    <i class="bi bi-save me-1"></i> Generar Ticket
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
