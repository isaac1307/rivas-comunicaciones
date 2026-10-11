@extends('layouts.app')

@section('title', 'Levantar Ticket de Soporte Técnico')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="text-center mb-4">
                <span class="badge bg-info-subtle text-info px-3 py-2 rounded-pill fw-bold text-uppercase">Mesa de Ayuda Digital</span>
                <h1 class="display-6 fw-bold mt-2">Generar Ticket de Soporte</h1>
                <p class="text-muted">Reporta cualquier falla, consulta o trámite. Nuestro equipo atenderá tu solicitud con prioridad.</p>
            </div>

            <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
                <form action="{{ route('tickets.store') }}" method="POST">
                    @csrf

                    <h5 class="fw-bold text-primary mb-3"><i class="bi bi-person-check-fill me-2"></i> Identificación del Cliente</h5>
                    <div class="mb-4">
                        <label for="cliente_id" class="form-label fw-semibold small">Selecciona tu Cuenta de Cliente *</label>
                        <select class="form-select form-select-lg @error('cliente_id') is-invalid @enderror" id="cliente_id" name="cliente_id" required>
                            <option value="">-- Selecciona tu Nombre o Contrato --</option>
                            @foreach ($clientes as $cliente)
                                <option value="{{ $cliente->id }}" {{ old('cliente_id') == $cliente->id ? 'selected' : '' }}>
                                    {{ $cliente->nombre_completo }} (Tel: {{ $cliente->telefono }})
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text">Si no localizas tu nombre, comunícate al (777) 123-4567 para vincular tu cuenta.</div>
                        @error('cliente_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <h5 class="fw-bold text-primary mb-3"><i class="bi bi-exclamation-triangle-fill me-2"></i> Detalle del Reporte</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-12">
                            <label for="categoria" class="form-label fw-semibold small">Categoría del Incidente *</label>
                            <select class="form-select @error('categoria') is-invalid @enderror" id="categoria" name="categoria" required>
                                <option value="falla_servicio" {{ old('categoria') === 'falla_servicio' ? 'selected' : '' }}>Falla de Servicio (Sin internet / Intermitencia)</option>
                                <option value="soporte_tecnico" {{ old('categoria') === 'soporte_tecnico' ? 'selected' : '' }}>Soporte Técnico (Configuración de WiFi, Contraseña, etc.)</option>
                                <option value="consulta" {{ old('categoria') === 'consulta' ? 'selected' : '' }}>Consulta General o Facturación</option>
                                <option value="cambio_plan" {{ old('categoria') === 'cambio_plan' ? 'selected' : '' }}>Cambio o Aumento de Plan</option>
                                <option value="cancelacion" {{ old('categoria') === 'cancelacion' ? 'selected' : '' }}>Aclaración o Cancelación</option>
                            </select>
                            @error('categoria')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label for="descripcion" class="form-label fw-semibold small">Descripción Detallada del Problema *</label>
                            <textarea class="form-control @error('descripcion') is-invalid @enderror" id="descripcion" name="descripcion" rows="5" placeholder="Por favor indícanos: ¿Desde cuándo se presenta la falla? ¿Qué luces observas en tu módem (roja, verde, parpadeando)?..." required>{{ old('descripcion') }}</textarea>
                            @error('descripcion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-grid gap-2 mt-4">
                        <button type="submit" class="btn btn-primary btn-lg fw-bold shadow-sm py-3">
                            <i class="bi bi-ticket-fill me-2"></i> Registrar Ticket de Soporte
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
