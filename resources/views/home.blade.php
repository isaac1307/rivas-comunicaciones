@extends('layouts.app')

@section('title', 'Inicio - Internet de Fibra Óptica')

@section('content')
<!-- Hero Section -->
<section class="hero-banner text-center text-lg-start py-5 mb-5">
    <div class="container py-4">
        <div class="row align-items-center g-5">
            <div class="col-lg-7">
                <span class="badge bg-primary px-3 py-2 rounded-pill text-uppercase mb-3 fw-bold tracking-wider">
                    <i class="bi bi-lightning-charge-fill me-1"></i> Red 100% Fibra Óptica Dedicada
                </span>
                <h1 class="display-4 fw-extrabold text-white mb-3">Conéctate al Futuro con Rivas Comunicaciones</h1>
                <p class="lead text-light mb-4 opacity-90">
                    Disfruta de navegación ultrarrápida, baja latencia para gaming y streaming en 4K sin interrupciones. La mejor velocidad simétrica y atención personalizada en tu localidad.
                </p>
                <div class="d-flex flex-wrap gap-3 justify-content-center justify-content-lg-start">
                    <a href="{{ route('reservaciones.create') }}" class="btn btn-primary btn-lg px-4 shadow fw-bold">
                        <i class="bi bi-calendar-check me-2"></i> Solicitar Instalación
                    </a>
                    <a href="{{ route('planes.index') }}" class="btn btn-outline-light btn-lg px-4 fw-semibold">
                        <i class="bi bi-grid-fill me-2"></i> Ver Planes y Tarifas
                    </a>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="card bg-white text-dark shadow-lg border-0 rounded-4 p-4">
                    <div class="card-body">
                        <h4 class="fw-bold mb-2 text-primary d-flex align-items-center gap-2">
                            <i class="bi bi-search"></i> ¿Ya tienes un Folio?
                        </h4>
                        <p class="text-muted small mb-4">Ingresa tu número de reservación de instalación (RES-...) o ticket de soporte (TKT-...) para conocer su estatus en tiempo real.</p>
                        
                        <form onsubmit="event.preventDefault(); const f = document.getElementById('heroFolio').value.trim(); if(f.startsWith('RES-')) { window.location.href='/reservaciones/'+f; } else if(f.startsWith('TKT-')) { window.location.href='/soporte/ticket/'+f; } else if(f) { window.location.href='/reservaciones/'+f; }">
                            <div class="mb-3">
                                <label for="heroFolio" class="form-label small fw-bold">Número de Folio</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-upc-scan"></i></span>
                                    <input type="text" class="form-control form-control-lg" id="heroFolio" placeholder="Ej: RES-20261011-XXXX" required>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-dark btn-lg w-100 fw-semibold">
                                <i class="bi bi-arrow-right-circle me-1"></i> Consultar Estatus
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Beneficios Clave -->
<section class="container mb-5">
    <div class="row g-4 text-center">
        <div class="col-md-3 col-sm-6">
            <div class="p-4 bg-white rounded-4 shadow-sm border h-100">
                <div class="badge bg-primary-subtle text-primary p-3 rounded-circle fs-3 mb-3">
                    <i class="bi bi-arrow-down-up"></i>
                </div>
                <h5 class="fw-bold">Velocidad Simétrica</h5>
                <p class="text-muted small mb-0">Sube y descarga archivos a la misma velocidad sin pérdidas de rendimiento.</p>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="p-4 bg-white rounded-4 shadow-sm border h-100">
                <div class="badge bg-success-subtle text-success p-3 rounded-circle fs-3 mb-3">
                    <i class="bi bi-shield-check"></i>
                </div>
                <h5 class="fw-bold">Conexión Estable</h5>
                <p class="text-muted small mb-0">Red inmune a interferencias climáticas y electromagnéticas.</p>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="p-4 bg-white rounded-4 shadow-sm border h-100">
                <div class="badge bg-info-subtle text-info p-3 rounded-circle fs-3 mb-3">
                    <i class="bi bi-headset"></i>
                </div>
                <h5 class="fw-bold">Soporte Local 24/7</h5>
                <p class="text-muted small mb-0">Técnicos especializados listos para resolver cualquier inquietud.</p>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="p-4 bg-white rounded-4 shadow-sm border h-100">
                <div class="badge bg-warning-subtle text-warning p-3 rounded-circle fs-3 mb-3">
                    <i class="bi bi-wifi"></i>
                </div>
                <h5 class="fw-bold">Módem Doble Banda</h5>
                <p class="text-muted small mb-0">Equipo WiFi Gigabit de última generación incluido en tu instalación.</p>
            </div>
        </div>
    </div>
</section>

<!-- Planes Destacados -->
<section class="container my-5 py-4">
    <div class="text-center mb-5">
        <span class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill fw-bold text-uppercase">Nuestras Tarifas</span>
        <h2 class="display-6 fw-bold mt-2">Planes de Internet Diseñados para Ti</h2>
        <p class="text-muted">Sin plazos forzosos ni letras chiquitas. Escoge el ancho de banda ideal.</p>
    </div>

    <div class="row g-4 justify-content-center">
        @forelse ($planesDestacados as $plan)
            <div class="col-lg-3 col-md-6">
                <div class="card card-plan h-100 shadow-sm text-center p-3">
                    <div class="card-body d-flex flex-column">
                        <span class="badge-speed align-self-center mb-3">
                            <i class="bi bi-speedometer"></i> {{ $plan->velocidad_descarga }} Megas
                        </span>
                        <h4 class="fw-bold mb-1">{{ $plan->nombre }}</h4>
                        <p class="text-muted small mb-3">{{ $plan->descripcion ?: 'Conexión simétrica de fibra óptica.' }}</p>
                        
                        <div class="my-3 py-2 border-top border-bottom">
                            <span class="fs-1 fw-extrabold text-primary">${{ number_format($plan->precio_mensual, 2) }}</span>
                            <span class="text-muted small">/ mes</span>
                        </div>

                        <ul class="list-unstyled text-start small text-muted my-3 space-y-2 flex-grow-1">
                            <li class="mb-2"><i class="bi bi-check2 text-success fw-bold me-2"></i> {{ $plan->velocidad_descarga }} Mbps Bajada</li>
                            <li class="mb-2"><i class="bi bi-check2 text-success fw-bold me-2"></i> {{ $plan->velocidad_carga }} Mbps Subida</li>
                            <li class="mb-2"><i class="bi bi-check2 text-success fw-bold me-2"></i> WiFi Doble Banda (2.4 & 5GHz)</li>
                            <li class="mb-2"><i class="bi bi-check2 text-success fw-bold me-2"></i> Conexión Ilimitada</li>
                        </ul>

                        <a href="{{ route('reservaciones.create', ['plan_id' => $plan->id]) }}" class="btn btn-outline-primary w-100 fw-bold mt-auto">
                            Contratar Plan
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5">
                <p class="text-muted">No hay planes disponibles en este momento.</p>
            </div>
        @endforelse
    </div>

    <div class="text-center mt-4">
        <a href="{{ route('planes.index') }}" class="btn btn-link text-primary fw-bold text-decoration-none">
            Ver todos los planes de internet <i class="bi bi-arrow-right"></i>
        </a>
    </div>
</section>

<!-- Promociones Vigentes -->
@if ($promociones->isNotEmpty())
<section class="bg-light py-5">
    <div class="container py-3">
        <div class="text-center mb-5">
            <span class="badge bg-danger px-3 py-2 rounded-pill fw-bold text-uppercase">Aprovecha Hoy</span>
            <h2 class="display-6 fw-bold mt-2">Promociones y Descuentos Exclusivos</h2>
            <p class="text-muted">Descuentos en mensualidades y promociones por contratación anticipada.</p>
        </div>

        <div class="row g-4 justify-content-center">
            @foreach ($promociones as $promo)
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden">
                        <div class="bg-gradient bg-primary text-white p-3 text-center">
                            <h5 class="fw-bold mb-0 text-white">{{ $promo->titulo }}</h5>
                        </div>
                        <div class="card-body p-4 d-flex flex-column">
                            <p class="text-muted small mb-3">{{ $promo->descripcion }}</p>
                            
                            @if ($promo->planes->isNotEmpty())
                                <div class="mb-3">
                                    <span class="small fw-bold text-dark d-block mb-1">Aplica en planes:</span>
                                    @foreach ($promo->planes as $p)
                                        <span class="badge bg-secondary-subtle text-dark me-1 mb-1">{{ $p->nombre }}</span>
                                    @endforeach
                                </div>
                            @endif

                            <div class="mt-auto pt-3 border-top small text-muted d-flex justify-content-between align-items-center">
                                <span><i class="bi bi-calendar-event me-1"></i> Válido hasta:</span>
                                <span class="fw-bold text-danger">{{ $promo->fecha_vencimiento->format('d/m/Y') }}</span>
                            </div>

                            <a href="{{ route('reservaciones.create') }}" class="btn btn-sm btn-primary w-100 mt-3 fw-semibold">
                                Aprovechar Oferta
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- Llamado a la Acción -->
<section class="container my-5 py-4">
    <div class="bg-primary text-white rounded-4 p-5 text-center shadow-lg position-relative overflow-hidden">
        <div class="position-relative z-1">
            <h2 class="display-5 fw-extrabold mb-3 text-white">¿Listo para navegar a la máxima velocidad?</h2>
            <p class="lead mb-4 opacity-90 mx-auto" style="max-width: 600px;">
                Agenda la instalación de tu servicio en menos de 2 minutos. Nuestro equipo técnico validará tu cobertura de inmediato.
            </p>
            <div class="d-flex flex-wrap gap-3 justify-content-center">
                <a href="{{ route('reservaciones.create') }}" class="btn btn-light btn-lg text-primary fw-bold px-4 shadow">
                    <i class="bi bi-calendar-check me-2"></i> Solicitar Instalación Hoy
                </a>
                <a href="{{ route('contacto') }}" class="btn btn-outline-light btn-lg fw-semibold px-4">
                    <i class="bi bi-chat-dots me-2"></i> Chatear con Asesor
                </a>
            </div>
        </div>
    </div>
</section>
@endsection
