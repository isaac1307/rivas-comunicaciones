@extends('layouts.admin')

@section('title', 'Editar Cliente ' . $cliente->nombre_completo)

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.clientes.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
        <i class="bi bi-arrow-left me-1"></i> Volver a Clientes
    </a>
    <h2 class="fw-bold mb-1">Editar Cliente: {{ $cliente->nombre_completo }}</h2>
</div>

<div class="card border-0 shadow-sm rounded-3 bg-white p-4">
    <form action="{{ route('admin.clientes.update', $cliente) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row g-3">
            <div class="col-md-6">
                <label for="nombre_completo" class="form-label fw-bold small">Nombre Completo *</label>
                <input type="text" class="form-control @error('nombre_completo') is-invalid @enderror" id="nombre_completo" name="nombre_completo" value="{{ old('nombre_completo', $cliente->nombre_completo) }}" required>
                @error('nombre_completo')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="telefono" class="form-label fw-bold small">Teléfono *</label>
                <input type="tel" class="form-control @error('telefono') is-invalid @enderror" id="telefono" name="telefono" value="{{ old('telefono', $cliente->telefono) }}" required>
                @error('telefono')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="correo_electronico" class="form-label fw-bold small">Correo Electrónico</label>
                <input type="email" class="form-control @error('correo_electronico') is-invalid @enderror" id="correo_electronico" name="correo_electronico" value="{{ old('correo_electronico', $cliente->correo_electronico) }}">
                @error('correo_electronico')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="plan_id" class="form-label fw-bold small">Plan Contratado</label>
                <select class="form-select @error('plan_id') is-invalid @enderror" id="plan_id" name="plan_id">
                    <option value="">-- Sin Plan Asignado --</option>
                    @foreach ($planes as $plan)
                        <option value="{{ $plan->id }}" {{ old('plan_id', $cliente->plan_id) == $plan->id ? 'selected' : '' }}>
                            {{ $plan->nombre }} (${{ number_format($plan->precio_mensual, 2) }}/mes)
                        </option>
                    @endforeach
                </select>
                @error('plan_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-12">
                <label for="direccion" class="form-label fw-bold small">Dirección del Servicio *</label>
                <input type="text" class="form-control @error('direccion') is-invalid @enderror" id="direccion" name="direccion" value="{{ old('direccion', $cliente->direccion) }}" required>
                @error('direccion')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="fecha_alta" class="form-label fw-bold small">Fecha de Alta *</label>
                <input type="date" class="form-control @error('fecha_alta') is-invalid @enderror" id="fecha_alta" name="fecha_alta" value="{{ old('fecha_alta', $cliente->fecha_alta->format('Y-m-d')) }}" required>
                @error('fecha_alta')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="estado" class="form-label fw-bold small">Estado *</label>
                <select class="form-select @error('estado') is-invalid @enderror" id="estado" name="estado" required>
                    <option value="activo" {{ old('estado', $cliente->estado) === 'activo' ? 'selected' : '' }}>Activo</option>
                    <option value="inactivo" {{ old('estado', $cliente->estado) === 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                </select>
                @error('estado')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12">
                <label for="notas" class="form-label fw-bold small">Notas Internas</label>
                <textarea class="form-control @error('notas') is-invalid @enderror" id="notas" name="notas" rows="3">{{ old('notas', $cliente->notas) }}</textarea>
                @error('notas')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12 mt-4 d-flex justify-content-end gap-2">
                <a href="{{ route('admin.clientes.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary fw-bold">
                    <i class="bi bi-save me-1"></i> Actualizar Cliente
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
