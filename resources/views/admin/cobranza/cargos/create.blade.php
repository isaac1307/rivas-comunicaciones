@extends('layouts.admin')

@section('title', 'Nuevo Cargo Manual - ' . $cliente->nombre_completo)

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <a href="{{ route('admin.cobranza.show', $cliente) }}" class="btn btn-outline-secondary btn-sm mb-2">
                    <i class="bi bi-arrow-left me-1"></i> Volver a Estado de Cuenta
                </a>
                <h3 class="fw-bold mb-0">Registrar Cargo Extraordinario</h3>
                <p class="text-muted small">Cliente: <strong>{{ $cliente->nombre_completo }}</strong> (Tel: {{ $cliente->telefono }})</p>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-3 bg-white p-4">
            <form method="POST" action="{{ route('admin.cargos.store', $cliente) }}">
                @csrf

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="tipo" class="form-label fw-semibold small">Tipo de Cargo</label>
                        <select class="form-select @error('tipo') is-invalid @enderror" id="tipo" name="tipo" required>
                            <option value="instalacion" {{ old('tipo') === 'instalacion' ? 'selected' : '' }}>Instalación de servicio</option>
                            <option value="reconexion" {{ old('tipo') === 'reconexion' ? 'selected' : '' }}>Reconexión por corte</option>
                            <option value="mensualidad" {{ old('tipo') === 'mensualidad' ? 'selected' : '' }}>Mensualidad extraordinaria</option>
                            <option value="otro" {{ old('tipo', 'otro') === 'otro' ? 'selected' : '' }}>Otro concepto / Cargo especial</option>
                        </select>
                        @error('tipo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="periodo" class="form-label fw-semibold small">Periodo Aplicable (Año-Mes)</label>
                        <input type="month" class="form-control @error('periodo') is-invalid @enderror" id="periodo" name="periodo" value="{{ old('periodo', now()->format('Y-m')) }}" required>
                        @error('periodo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label for="concepto" class="form-label fw-semibold small">Concepto / Descripción del Cargo</label>
                        <input type="text" class="form-control @error('concepto') is-invalid @enderror" id="concepto" name="concepto" value="{{ old('concepto') }}" placeholder="Ej: Instalación de router adicional y cableado exterior" required>
                        @error('concepto')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="monto" class="form-label fw-semibold small">Monto a Cobrar ($)</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" step="0.01" min="0.01" class="form-control @error('monto') is-invalid @enderror" id="monto" name="monto" value="{{ old('monto') }}" placeholder="0.00" required>
                        </div>
                        @error('monto')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="fecha_vencimiento" class="form-label fw-semibold small">Fecha de Vencimiento</label>
                        <input type="date" class="form-control @error('fecha_vencimiento') is-invalid @enderror" id="fecha_vencimiento" name="fecha_vencimiento" value="{{ old('fecha_vencimiento', now()->addDays(7)->format('Y-m-d')) }}" required>
                        @error('fecha_vencimiento')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <hr class="my-4">

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.cobranza.show', $cliente) }}" class="btn btn-secondary btn-sm">Cancelar</a>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bi bi-save me-1"></i> Guardar Cargo
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
