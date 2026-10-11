@extends('layouts.admin')

@section('title', 'Editar Promoción ' . $promocion->titulo)

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.promociones.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
        <i class="bi bi-arrow-left me-1"></i> Volver a Promociones
    </a>
    <h2 class="fw-bold mb-1">Editar Promoción: {{ $promocion->titulo }}</h2>
</div>

<div class="card border-0 shadow-sm rounded-3 bg-white p-4">
    <form action="{{ route('admin.promociones.update', $promocion) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row g-3">
            <div class="col-md-8">
                <label for="titulo" class="form-label fw-bold small">Título de la Promoción *</label>
                <input type="text" class="form-control @error('titulo') is-invalid @enderror" id="titulo" name="titulo" value="{{ old('titulo', $promocion->titulo) }}" required>
                @error('titulo')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4">
                <label for="estado" class="form-label fw-bold small">Estado *</label>
                <select class="form-select @error('estado') is-invalid @enderror" id="estado" name="estado" required>
                    <option value="activa" {{ old('estado', $promocion->estado) === 'activa' ? 'selected' : '' }}>Activa</option>
                    <option value="inactiva" {{ old('estado', $promocion->estado) === 'inactiva' ? 'selected' : '' }}>Inactiva</option>
                </select>
                @error('estado')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="fecha_inicio" class="form-label fw-bold small">Fecha de Inicio *</label>
                <input type="date" class="form-control @error('fecha_inicio') is-invalid @enderror" id="fecha_inicio" name="fecha_inicio" value="{{ old('fecha_inicio', $promocion->fecha_inicio->format('Y-m-d')) }}" required>
                @error('fecha_inicio')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="fecha_vencimiento" class="form-label fw-bold small">Fecha de Vencimiento *</label>
                <input type="date" class="form-control @error('fecha_vencimiento') is-invalid @enderror" id="fecha_vencimiento" name="fecha_vencimiento" value="{{ old('fecha_vencimiento', $promocion->fecha_vencimiento->format('Y-m-d')) }}" required>
                @error('fecha_vencimiento')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12">
                <label class="form-label fw-bold small d-block mb-2">Planes Aplicables (Pivot)</label>
                @php
                    $planesAsignados = $promocion->planes->pluck('id')->toArray();
                @endphp
                <div class="row g-2 p-3 bg-light rounded-3">
                    @foreach ($planes as $plan)
                        <div class="col-md-4 col-sm-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="planes[]" value="{{ $plan->id }}" id="plan_{{ $plan->id }}" {{ in_array($plan->id, old('planes', $planesAsignados)) ? 'checked' : '' }}>
                                <label class="form-check-label small" for="plan_{{ $plan->id }}">
                                    {{ $plan->nombre }} (${{ number_format($plan->precio_mensual, 2) }})
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="col-12">
                <label for="descripcion" class="form-label fw-bold small">Descripción y Condiciones *</label>
                <textarea class="form-control @error('descripcion') is-invalid @enderror" id="descripcion" name="descripcion" rows="4" required>{{ old('descripcion', $promocion->descripcion) }}</textarea>
                @error('descripcion')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12 mt-4 d-flex justify-content-end gap-2">
                <a href="{{ route('admin.promociones.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary fw-bold">
                    <i class="bi bi-save me-1"></i> Actualizar Promoción
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
