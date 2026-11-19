@extends('layouts.app')

@section('title', 'Contacto y Atención al Cliente')

@section('content')
<div class="container py-5">
    <div class="text-center mb-5">
        <span class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill fw-bold text-uppercase">Estamos para Ayudarte</span>
        <h1 class="display-5 fw-bold mt-2">Centro de Contacto & Atención</h1>
        <p class="lead text-muted mx-auto" style="max-width: 650px;">
            ¿Tienes dudas sobre nuestros servicios, facturación o deseas contratar? Envíanos un mensaje o comunícate por nuestras líneas directas.
        </p>
    </div>

    <div class="row g-5">
        <!-- Formulario -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                <div class="card-body">
                    <h4 class="fw-bold mb-4 text-primary"><i class="bi bi-chat-left-dots-fill me-2"></i> Envíanos un Mensaje</h4>

                    <form action="{{ route('contacto.store') }}" method="POST">
                        @csrf

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="nombre" class="form-label fw-semibold small">Nombre Completo *</label>
                                <input type="text" class="form-control @error('nombre') is-invalid @enderror" id="nombre" name="nombre" value="{{ old('nombre') }}" placeholder="Ej. Juan Pérez" required>
                                @error('nombre')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="correo_electronico" class="form-label fw-semibold small">Correo Electrónico *</label>
                                <input type="email" class="form-control @error('correo_electronico') is-invalid @enderror" id="correo_electronico" name="correo_electronico" value="{{ old('correo_electronico') }}" placeholder="ejemplo@correo.com" required>
                                @error('correo_electronico')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="telefono" class="form-label fw-semibold small">Teléfono / WhatsApp</label>
                                <input type="tel" class="form-control @error('telefono') is-invalid @enderror" id="telefono" name="telefono" value="{{ old('telefono') }}" placeholder="777-123-4567">
                                @error('telefono')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="asunto" class="form-label fw-semibold small">Asunto *</label>
                                <input type="text" class="form-control @error('asunto') is-invalid @enderror" id="asunto" name="asunto" value="{{ old('asunto') }}" placeholder="Ej. Información sobre cobertura" required>
                                @error('asunto')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="mensaje" class="form-label fw-semibold small">Mensaje o Consulta *</label>
                                <textarea class="form-control @error('mensaje') is-invalid @enderror" id="mensaje" name="mensaje" rows="5" placeholder="Escribe aquí los detalles de tu consulta..." required>{{ old('mensaje') }}</textarea>
                                @error('mensaje')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold shadow-sm">
                                    <i class="bi bi-send-fill me-2"></i> Enviar Mensaje
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Información de Sucursal y Teléfonos -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-light mb-4">
                <h5 class="fw-bold mb-3 text-dark"><i class="bi bi-building-fill text-primary me-2"></i> Oficina Central</h5>
                <p class="text-muted small mb-3">
                    Av. Telecomunicaciones #100, Col. Centro, Morelos, México.<br>
                    Estacionamiento para clientes disponible.
                </p>

                <h6 class="fw-bold mb-2 text-dark"><i class="bi bi-clock-fill text-primary me-2"></i> Horario de Atención</h6>
                <ul class="list-unstyled small text-muted mb-4">
                    <li><strong>Lunes a Viernes:</strong> 8:00 AM - 7:00 PM</li>
                    <li><strong>Sábados:</strong> 9:00 AM - 3:00 PM</li>
                    <li><strong>Domingos:</strong> Guardia técnica para emergencias</li>
                </ul>

                <h6 class="fw-bold mb-2 text-dark"><i class="bi bi-telephone-inbound-fill text-primary me-2"></i> Líneas Telefónicas</h6>
                <p class="small text-muted mb-1"><strong>Atención a Clientes:</strong> (777) 123-4567</p>
                <p class="small text-muted mb-0"><strong>Soporte Técnico 24/7:</strong> (777) 987-6543</p>
            </div>

            <div class="card border-0 shadow-sm rounded-4 p-4 bg-dark text-white">
                <h6 class="fw-bold mb-2 text-white"><i class="bi bi-tools text-warning me-2"></i> ¿Ya eres cliente y tienes fallas?</h6>
                <p class="small text-secondary mb-3">No necesitas esperar a que respondan tu correo. Levanta un ticket de soporte digital directamente en nuestro portal para atención inmediata.</p>
                <a href="{{ route('tickets.create') }}" class="btn btn-warning btn-sm fw-bold">
                    <i class="bi bi-ticket-detailed me-1"></i> Generar Ticket de Soporte
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
