@extends('layouts.app')

@section('title', 'Zonas de Cobertura - Rivas Comunicaciones')

@section('content')
<div class="container py-5">
    <!-- Header -->
    <div class="text-center mb-5">
        <span class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill fw-bold text-uppercase">Expansión de Red</span>
        <h1 class="display-5 fw-bold mt-2">Mapa y Zonas de Cobertura</h1>
        <p class="lead text-muted mx-auto" style="max-width: 700px;">
            Llegamos a las principales colonias, fraccionamientos y áreas comerciales con fibra óptica de última milla y enlaces inalámbricos de alta capacidad.
        </p>
    </div>

    <!-- Zonas Activas -->
    <div class="row g-4 mb-5">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                    <h4 class="fw-bold mb-0 text-primary"><i class="bi bi-geo-alt-fill me-2"></i> Colonias con Red Activa</h4>
                    <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill">
                        <i class="bi bi-broadcast me-1"></i> Fibra Óptica Disponible
                    </span>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="border rounded-3 p-3 bg-light">
                            <h6 class="fw-bold mb-2 text-dark"><i class="bi bi-check-circle-fill text-success me-2"></i> Zona Centro y Alrededores</h6>
                            <ul class="list-unstyled small text-muted mb-0 ps-3">
                                <li>• Centro Histórico</li>
                                <li>• Col. Las Palmas</li>
                                <li>• Barrio de San Juan</li>
                                <li>• Fracc. Los Arcos</li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded-3 p-3 bg-light">
                            <h6 class="fw-bold mb-2 text-dark"><i class="bi bi-check-circle-fill text-success me-2"></i> Zona Norte & Residencial</h6>
                            <ul class="list-unstyled small text-muted mb-0 ps-3">
                                <li>• Col. Bellavista</li>
                                <li>• Fracc. Real del Bosque</li>
                                <li>• Lomas de Guadalupe</li>
                                <li>• Col. Vista Hermosa</li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded-3 p-3 bg-light">
                            <h6 class="fw-bold mb-2 text-dark"><i class="bi bi-check-circle-fill text-success me-2"></i> Zona Oriente & Industrial</h6>
                            <ul class="list-muted small text-muted mb-0 ps-3">
                                <li>• Parque Industrial San Carlos</li>
                                <li>• Col. Plan de Ayala</li>
                                <li>• Fracc. Las Torres</li>
                                <li>• Boulevard Morelos</li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded-3 p-3 bg-light">
                            <h6 class="fw-bold mb-2 text-dark"><i class="bi bi-clock-history text-warning me-2"></i> Zonas en Despliegue (Próximamente)</h6>
                            <ul class="list-unstyled small text-muted mb-0 ps-3">
                                <li>• Col. Mirador del Sol</li>
                                <li>• San Mateo Tepetate</li>
                                <li>• Fracc. Campo Verde</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-primary text-white">
                <h4 class="fw-bold text-white mb-3"><i class="bi bi-question-circle-fill me-2"></i> ¿Tu colonia no aparece?</h4>
                <p class="small text-light opacity-90 mb-4">
                    Estamos expandiendo nuestra red continuamente. Envíanos tu ubicación para que nuestros ingenieros verifiquen la factibilidad técnica en tu domicilio.
                </p>
                <a href="{{ route('reservaciones.create') }}" class="btn btn-light text-primary fw-bold w-100 py-2 mb-2 shadow-sm">
                    <i class="bi bi-pin-map me-1"></i> Validar Factibilidad
                </a>
                <a href="{{ route('contacto') }}" class="btn btn-outline-light w-100 py-2">
                    <i class="bi bi-envelope me-1"></i> Contactar a Ventas
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
