@extends('layouts.admin')

@section('title', 'Registrar Nuevo Plan')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.planes.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
        <i class="bi bi-arrow-left me-1"></i> Volver a Planes
    </a>
    <h2 class="fw-bold mb-1">Nuevo Plan de Internet</h2>
</div>

<div class="card border-0 shadow-sm rounded-3 bg-white p-4">
    <form action="{{ route('admin.planes.store') }}" method="POST">
        @csrf

        <div class="row g-3">
            <div class="col-md-6">
                <label for="nombre" class="form-label fw-bold small">Nombre del Plan *</label>
                <input type="text" class="form-control @error('nombre') is-invalid @enderror" id="nombre" name="nombre" value="{{ old('nombre') }}" placeholder="Ej: Plan Fibra 100 Megas" required>
                @error('nombre')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-3">
                <label for="velocidad_descarga" class="form-label fw-bold small">Velocidad de Descarga (Mbps) *</label>
                <input type="number" class="form-control @error('velocidad_descarga') is-invalid @enderror" id="velocidad_descarga" name="velocidad_descarga" value="{{ old('velocidad_descarga') }}" min="1" placeholder="100" required>
                @error('velocidad_descarga')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-3">
                <label for="velocidad_carga" class="form-label fw-bold small">Velocidad de Carga (Mbps) *</label>
                <input type="number" class="form-control @error('velocidad_carga') is-invalid @enderror" id="velocidad_carga" name="velocidad_carga" value="{{ old('velocidad_carga') }}" min="1" placeholder="50" required>
                @error('velocidad_carga')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="precio_mensual" class="form-label fw-bold small">Precio Mensual ($ MXN) *</label>
                <input type="number" step="0.01" class="form-control @error('precio_mensual') is-invalid @enderror" id="precio_mensual" name="precio_mensual" value="{{ old('precio_mensual') }}" min="0" placeholder="499.00" required>
                @error('precio_mensual')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="estado" class="form-label fw-bold small">Estado *</label>
                <select class="form-select @error('estado') is-invalid @enderror" id="estado" name="estado" required>
                    <option value="activo" {{ old('estado', 'activo') === 'activo' ? 'selected' : '' }}>Activo (Visible al público)</option>
                    <option value="inactivo" {{ old('estado') === 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                </select>
                @error('estado')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12">
                <label for="descripcion" class="form-label fw-bold small">Descripción del Plan</label>
                <textarea class="form-control @error('descripcion') is-invalid @enderror" id="descripcion" name="descripcion" rows="4" placeholder="Detalles de tecnología, módem incluido, etc.">{{ old('descripcion') }}</textarea>
                @error('descripcion')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12 mt-4 d-flex justify-content-end gap-2">
                <a href="{{ route('admin.planes.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary fw-bold">
                    <i class="bi bi-save me-1"></i> Guardar Plan
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
