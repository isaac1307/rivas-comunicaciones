@extends('layouts.app')

@section('title', 'Agendar Instalación o Servicio Técnico')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="text-center mb-4">
                <span class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill fw-bold text-uppercase">Solicitud en Línea</span>
                <h1 class="display-6 fw-bold mt-2">Agendar Instalación o Visita Técnica</h1>
                <p class="text-muted">Completa este formulario para apartar tu fecha de instalación. Te asignaremos un folio único de seguimiento.</p>
            </div>

            <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
                <form action="{{ route('reservaciones.store') }}" method="POST">
                    @csrf

                    <h5 class="fw-bold text-primary mb-3"><i class="bi bi-person-badge-fill me-2"></i> Datos del Solicitante</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label for="nombre_solicitante" class="form-label fw-semibold small">Nombre Completo *</label>
                            <input type="text" class="form-control @error('nombre_solicitante') is-invalid @enderror" id="nombre_solicitante" name="nombre_solicitante" value="{{ old('nombre_solicitante') }}" placeholder="Ej. Carlos Mendoza" required>
                            @error('nombre_solicitante')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="telefono" class="form-label fw-semibold small">Teléfono de Contacto *</label>
                            <input type="tel" class="form-control @error('telefono') is-invalid @enderror" id="telefono" name="telefono" value="{{ old('telefono') }}" placeholder="Ej. 7771234567" required>
                            @error('telefono')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-12">
                            <label for="correo_electronico" class="form-label fw-semibold small">Correo Electrónico (Opcional)</label>
                            <input type="email" class="form-control @error('correo_electronico') is-invalid @enderror" id="correo_electronico" name="correo_electronico" value="{{ old('correo_electronico') }}" placeholder="carlos@ejemplo.com">
                            <div class="form-text">Te enviaremos la confirmación del folio a este correo.</div>
                            @error('correo_electronico')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-12">
                            <label for="direccion" class="form-label fw-semibold small">Dirección Completa de Instalación *</label>
                            <input type="text" class="form-control @error('direccion') is-invalid @enderror" id="direccion" name="direccion" value="{{ old('direccion') }}" placeholder="Calle, Número exterior/interior, Colonia, C.P." required>
                            @error('direccion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <h5 class="fw-bold text-primary mb-3"><i class="bi bi-router-fill me-2"></i> Detalles del Servicio</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label for="tipo_servicio" class="form-label fw-semibold small">Tipo de Servicio *</label>
                            <select class="form-select @error('tipo_servicio') is-invalid @enderror" id="tipo_servicio" name="tipo_servicio" required>
                                <option value="instalacion_nueva" {{ old('tipo_servicio', 'instalacion_nueva') === 'instalacion_nueva' ? 'selected' : '' }}>Instalación Nueva (Contratación)</option>
                                <option value="visita_tecnica" {{ old('tipo_servicio') === 'visita_tecnica' ? 'selected' : '' }}>Visita Técnica / Revisión</option>
                                <option value="atencion_personalizada" {{ old('tipo_servicio') === 'atencion_personalizada' ? 'selected' : '' }}>Atención Personalizada</option>
                            </select>
                            @error('tipo_servicio')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="plan_id" class="form-label fw-semibold small">Plan Deseado</label>
                            <select class="form-select @error('plan_id') is-invalid @enderror" id="plan_id" name="plan_id">
                                <option value="">-- Seleccionar Plan (Opcional) --</option>
                                @foreach ($planes as $plan)
                                    <option value="{{ $plan->id }}" {{ (string) old('plan_id', $planSeleccionadoId) === (string) $plan->id ? 'selected' : '' }}>
                                        {{ $plan->nombre }} ({{ $plan->velocidad_descarga }} Mbps - ${{ number_format($plan->precio_mensual, 2) }}/mes)
                                    </option>
                                @endforeach
                            </select>
                            @error('plan_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-12">
                            <label for="fecha_solicitada" class="form-label fw-semibold small">Fecha Preferida para Visita / Instalación</label>
                            <input type="date" class="form-control @error('fecha_solicitada') is-invalid @enderror" id="fecha_solicitada" name="fecha_solicitada" value="{{ old('fecha_solicitada', date('Y-m-d', strtotime('+1 day'))) }}" min="{{ date('Y-m-d') }}">
                            @error('fecha_solicitada')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-12">
                            <label for="observaciones" class="form-label fw-semibold small">Observaciones o Referencias de Ubicación</label>
                            <textarea class="form-control @error('observaciones') is-invalid @enderror" id="observaciones" name="observaciones" rows="3" placeholder="Entre qué calles, color de fachada o requerimientos especiales...">{{ old('observaciones') }}</textarea>
                            @error('observaciones')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-grid gap-2 mt-4">
                        <button type="submit" class="btn btn-primary btn-lg fw-bold shadow-sm py-3">
                            <i class="bi bi-send-check-fill me-2"></i> Generar Solicitud de Reservación
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
