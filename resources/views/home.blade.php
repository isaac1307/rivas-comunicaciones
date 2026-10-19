@extends('layouts.app')

@section('title', 'Inicio - Internet de Fibra Óptica en Morelos')

@section('content')
<!-- Hero Section -->
<section class="py-5" style="background-color: var(--rc-dark);">
    <div class="container py-4 py-lg-5">
        <div class="row align-items-center g-5">
            <div class="col-lg-6 text-center text-lg-start">
                <h1 class="display-4 fw-extrabold text-white mb-3 tracking-tight" style="line-height: 1.15; letter-spacing: -0.025em;">
                    Internet de Fibra Óptica Simétrica en Morelos
                </h1>

                <p class="lead mb-4" style="color: #cbd5e1; font-size: 1.15rem; max-width: 520px; line-height: 1.7;">
                    Navegación ultrarrápida con la misma velocidad de subida y bajada. Sin caídas por lluvia, latencia ultra-baja para gaming y streaming 4K sin interrupciones.
                </p>

                <div class="d-flex flex-wrap gap-3 justify-content-center justify-content-lg-start mb-4">
                    <a href="{{ route('reservaciones.create') }}" class="btn btn-lg px-4 text-white fw-bold" style="background-color: var(--rc-primary); border-radius: 0.75rem;">
                        <i class="bi bi-calendar-check me-2"></i> Solicitar Instalación
                    </a>
                    <a href="{{ route('planes.index') }}" class="btn btn-lg px-4 btn-outline-light fw-semibold" style="border-radius: 0.75rem; border-color: rgba(255,255,255,0.25);">
                        <i class="bi bi-grid-fill me-2"></i> Ver Planes y Tarifas
                    </a>
                </div>

                <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-lg-start gap-4 pt-2 text-secondary small">
                    <div class="d-flex align-items-center gap-2 text-light">
                        <i class="bi bi-check-circle-fill text-info"></i>
                        <span>Sin plazos forzosos</span>
                    </div>
                    <div class="d-flex align-items-center gap-2 text-light">
                        <i class="bi bi-check-circle-fill text-info"></i>
                        <span>Velocidad 1:1 garantizada</span>
                    </div>
                    <div class="d-flex align-items-center gap-2 text-light">
                        <i class="bi bi-check-circle-fill text-info"></i>
                        <span>Soporte técnico local 24/7</span>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="p-4 rounded-4" style="background-color: var(--rc-dark-surface);">
                    <!-- Imagen de cabecera fibra óptica -->
                    <div class="position-relative overflow-hidden rounded-3 mb-4" style="height: 230px;">
                        <img src="https://images.unsplash.com/photo-1544197150-b99a580bb7a8?auto=format&fit=crop&w=1200&q=80" 
                             alt="Cable de fibra óptica de alta velocidad" 
                             class="w-100 h-100 object-fit-cover"
                             width="1200"
                             height="800">
                        <div class="position-absolute bottom-0 start-0 end-0 p-3" style="background-color: rgba(11, 19, 41, 0.9);">
                            <span class="badge bg-primary text-white fw-bold me-2"><i class="bi bi-speedometer2 me-1"></i> Red Gigabit</span>
                            <span class="text-white small fw-medium">Monitoreo activo y redundancia en Morelos</span>
                        </div>
                    </div>

                    <!-- Consulta rápida de Folio -->
                    <div>
                        <h5 class="fw-bold text-white mb-1 d-flex align-items-center gap-2">
                            <i class="bi bi-search" style="color: var(--rc-accent);"></i> ¿Ya tienes un Folio?
                        </h5>
                        <p class="small mb-3" style="color: #94a3b8;">
                            Ingresa tu folio de reservación (<span class="text-white font-monospace">RES-</span>) o ticket (<span class="text-white font-monospace">TKT-</span>) para consultar estatus:
                        </p>
                        
                        <form onsubmit="event.preventDefault(); const f = document.getElementById('heroFolio').value.trim(); if(f.startsWith('RES-')) { window.location.href='/reservaciones/'+f; } else if(f.startsWith('TKT-')) { window.location.href='/soporte/ticket/'+f; } else if(f) { window.location.href='/reservaciones/'+f; }">
                            <div class="input-group mb-2">
                                <span class="input-group-text border-0 text-white" style="background-color: rgba(255,255,255,0.08);">
                                    <i class="bi bi-upc-scan"></i>
                                </span>
                                <input type="text" class="form-control text-white border-0" id="heroFolio" placeholder="Ej: RES-20261011-XXXX" required style="background-color: rgba(255,255,255,0.08); font-size: 0.95rem;">
                                <button type="submit" class="btn text-white fw-bold px-3" style="background-color: var(--rc-primary);">
                                    Consultar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Beneficios Clave -->
<section class="container my-5 py-3">
    <div class="text-center mb-5">
        <h2 class="display-6 fw-extrabold text-dark tracking-tight mb-2">¿Por qué elegir Rivas Comunicaciones?</h2>
        <p class="text-dark opacity-75" style="font-size: 1.1rem; max-width: 600px; margin: 0 auto;">Diseñado para la máxima estabilidad, sin caídas y con atención técnica real y cercana.</p>
    </div>

    <div class="row g-4">
        <div class="col-md-3 col-sm-6">
            <div class="p-4 bg-white rounded-4 h-100" style="border: 1px solid var(--rc-border);">
                <div class="d-inline-flex align-items-center justify-content-center p-3 rounded-3 mb-3" style="background-color: #f0fdf4; color: #16a34a; font-size: 1.5rem;">
                    <i class="bi bi-arrow-down-up"></i>
                </div>
                <h5 class="fw-bold text-dark mb-2">Simetría 1:1 Real</h5>
                <p class="text-dark opacity-75 small mb-0" style="font-size: 0.95rem; line-height: 1.6;">Sube videos, realiza videollamadas y comparte archivos a la misma velocidad con la que descargas.</p>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="p-4 bg-white rounded-4 h-100" style="border: 1px solid var(--rc-border);">
                <div class="d-inline-flex align-items-center justify-content-center p-3 rounded-3 mb-3" style="background-color: #eff6ff; color: #2563eb; font-size: 1.5rem;">
                    <i class="bi bi-controller"></i>
                </div>
                <h5 class="fw-bold text-dark mb-2">Baja Latencia</h5>
                <p class="text-dark opacity-75 small mb-0" style="font-size: 0.95rem; line-height: 1.6;">Ping ultra-estable diseñado para gaming competitivo, streaming 4K y trabajo profesional sin cortes.</p>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="p-4 bg-white rounded-4 h-100" style="border: 1px solid var(--rc-border);">
                <div class="d-inline-flex align-items-center justify-content-center p-3 rounded-3 mb-3" style="background-color: #f8fafc; color: #0284c7; font-size: 1.5rem; border: 1px solid #e2e8f0;">
                    <i class="bi bi-headset"></i>
                </div>
                <h5 class="fw-bold text-dark mb-2">Soporte Local 24/7</h5>
                <p class="text-dark opacity-75 small mb-0" style="font-size: 0.95rem; line-height: 1.6;">Técnicos calificados en Morelos listos para atenderte por WhatsApp, teléfono o visita técnica ágil.</p>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="p-4 bg-white rounded-4 h-100" style="border: 1px solid var(--rc-border);">
                <div class="d-inline-flex align-items-center justify-content-center p-3 rounded-3 mb-3" style="background-color: #fffbeb; color: #d97706; font-size: 1.5rem;">
                    <i class="bi bi-wifi"></i>
                </div>
                <h5 class="fw-bold text-dark mb-2">Módem Doble Banda</h5>
                <p class="text-dark opacity-75 small mb-0" style="font-size: 0.95rem; line-height: 1.6;">Equipo WiFi Gigabit (2.4 & 5GHz) de alta potencia y amplio alcance incluido con tu suscripción.</p>
            </div>
        </div>
    </div>
</section>

<!-- Planes Destacados -->
<section class="container my-5 py-4">
    <div class="text-center mb-5">
        <h2 class="display-6 fw-extrabold text-dark tracking-tight mb-2">Tarifas Claras y Simétricas</h2>
        <p class="text-dark opacity-75" style="font-size: 1.1rem; max-width: 600px; margin: 0 auto;">Sin plazos forzosos ni cargos ocultos. Elige el ancho de banda que necesitas.</p>
    </div>

    <div class="row g-4 justify-content-center reveal-stagger">
        @forelse ($planesDestacados as $plan)
            <div class="col-lg-4 col-md-6">
                <div class="card card-plan h-100 p-4">
                    <div class="card-body d-flex flex-column text-center p-0">
                        <div class="mb-3">
                            <span class="badge-speed">
                                <i class="bi bi-lightning-charge-fill me-1"></i> {{ $plan->velocidad_descarga }} Megas Simétricos
                            </span>
                        </div>

                        <h4 class="fw-extrabold text-dark mb-1">{{ $plan->nombre }}</h4>
                        <p class="text-muted small mb-3" style="font-size: 0.95rem;">{{ $plan->descripcion ?: 'Conexión simétrica de fibra óptica dedicada.' }}</p>
                        
                        <div class="my-3 py-3" style="background-color: #f8fafc; border-radius: 0.75rem;">
                            <span class="display-6 fw-extrabold text-dark">${{ number_format($plan->precio_mensual, 2) }}</span>
                            <span class="text-muted small d-block">al mes (IVA incluido)</span>
                        </div>

                        <ul class="list-unstyled text-start small text-dark opacity-90 my-3 d-flex flex-column gap-2 flex-grow-1" style="font-size: 0.95rem;">
                            <li class="d-flex align-items-center gap-2">
                                <i class="bi bi-check-circle-fill text-primary"></i>
                                <span><strong>{{ $plan->velocidad_descarga }} Mbps</strong> de descarga garantizada</span>
                            </li>
                            <li class="d-flex align-items-center gap-2">
                                <i class="bi bi-check-circle-fill text-primary"></i>
                                <span><strong>{{ $plan->velocidad_carga }} Mbps</strong> de subida garantizada</span>
                            </li>
                            <li class="d-flex align-items-center gap-2">
                                <i class="bi bi-check-circle-fill text-primary"></i>
                                <span>WiFi Gigabit Doble Banda (2.4 & 5GHz)</span>
                            </li>
                            <li class="d-flex align-items-center gap-2">
                                <i class="bi bi-check-circle-fill text-primary"></i>
                                <span>Navegación ilimitada sin tope de datos</span>
                            </li>
                            <li class="d-flex align-items-center gap-2">
                                <i class="bi bi-check-circle-fill text-primary"></i>
                                <span>Soporte técnico preferente en Morelos</span>
                            </li>
                        </ul>

                        <a href="{{ route('reservaciones.create', ['plan_id' => $plan->id]) }}" class="btn text-white w-100 fw-bold py-2_5 mt-auto" style="background-color: var(--rc-primary); border-radius: 0.6rem;">
                            Contratar Este Plan
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

    <div class="text-center mt-5">
        <a href="{{ route('planes.index') }}" class="btn btn-outline-primary px-4 py-2 fw-bold" style="border-radius: 0.5rem;">
            Ver catálogo completo de planes <i class="bi bi-arrow-right ms-1"></i>
        </a>
    </div>
</section>

<!-- Infraestructura de Fibra en Morelos -->
<section class="container my-5 py-4">
    <div class="row align-items-center g-5 p-4 p-lg-5 rounded-4 bg-white" style="border: 1px solid var(--rc-border);">
        <div class="col-lg-6">
            <h2 class="display-6 fw-extrabold text-dark tracking-tight mb-3">
                Infraestructura Propia de Fibra Óptica
            </h2>
            <p class="text-dark opacity-75 mb-4" style="font-size: 1.05rem; line-height: 1.7;">
                Desplegamos nuestro propio anillo de fibra óptica directo hasta tu hogar o negocio. A diferencia del cable coaxial tradicional, la fibra óptica es inmune a las interferencias eléctricas y tormentas, manteniendo tu conexión firme cuando más la necesitas.
            </p>
            <div class="row g-3 mb-4">
                <div class="col-6">
                    <div class="p-3 rounded-3" style="background-color: #f8fafc;">
                        <h4 class="fw-extrabold text-primary mb-1">100%</h4>
                        <span class="text-muted small">Fibra dedicada punta a punta</span>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-3 rounded-3" style="background-color: #f8fafc;">
                        <h4 class="fw-extrabold text-primary mb-1">24 - 48h</h4>
                        <span class="text-muted small">Tiempo promedio de instalación</span>
                    </div>
                </div>
            </div>
            <a href="{{ route('cobertura') }}" class="btn text-white fw-bold px-4" style="background-color: var(--rc-primary); border-radius: 0.5rem;">
                <i class="bi bi-geo-alt-fill me-1"></i> Consultar Mapa de Cobertura
            </a>
        </div>
        <div class="col-lg-6">
            <div class="rounded-4 overflow-hidden" style="max-height: 380px;">
                <img src="https://images.unsplash.com/photo-1581092160607-ee22621dd758?auto=format&fit=crop&w=800&q=80" 
                     alt="Técnico de fibra óptica realizando empalme e instalación" 
                     class="w-100 h-100 object-fit-cover"
                     width="800"
                     height="600"
                     loading="lazy">
            </div>
        </div>
    </div>
</section>

<!-- Promociones Vigentes -->
@if ($promociones->isNotEmpty())
<section class="py-5" style="background-color: #f1f5f9;">
    <div class="container py-3">
        <div class="text-center mb-5">
            <h2 class="display-6 fw-extrabold text-dark tracking-tight mb-2">Promociones Exclusivas</h2>
            <p class="text-dark opacity-75" style="font-size: 1.05rem;">Aprovecha descuentos en instalación y mensualidades por tiempo limitado.</p>
        </div>

        <div class="row g-4 justify-content-center">
            @foreach ($promociones as $promo)
                <div class="col-md-4">
                    <div class="card h-100 border-0 rounded-4 overflow-hidden bg-white" style="border: 1px solid var(--rc-border) !important;">
                        <div class="p-3 text-white" style="background-color: var(--rc-primary);">
                            <h5 class="fw-bold mb-0 text-white">{{ $promo->titulo }}</h5>
                        </div>
                        <div class="card-body p-4 d-flex flex-column">
                            <p class="text-dark opacity-75 small mb-3" style="font-size: 0.95rem;">{{ $promo->descripcion }}</p>
                            
                            @if ($promo->planes->isNotEmpty())
                                <div class="mb-3">
                                    <span class="small fw-bold text-dark d-block mb-1">Aplica en planes:</span>
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach ($promo->planes as $p)
                                            <span class="badge bg-light text-dark border">{{ $p->nombre }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <div class="mt-auto pt-3 border-top small text-muted d-flex justify-content-between align-items-center">
                                <span><i class="bi bi-calendar-event me-1"></i> Vigencia:</span>
                                <span class="fw-bold text-danger">{{ $promo->fecha_vencimiento->format('d/m/Y') }}</span>
                            </div>

                            <a href="{{ route('reservaciones.create') }}" class="btn text-white w-100 mt-3 fw-semibold" style="background-color: var(--rc-dark); border-radius: 0.5rem;">
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
    <div class="p-5 text-center text-white rounded-4" style="background-color: var(--rc-dark);">
        <h2 class="display-5 fw-extrabold mb-3 text-white tracking-tight">
            Navega a la Máxima Velocidad
        </h2>
        <p class="lead mb-4 mx-auto" style="color: #cbd5e1; max-width: 620px; font-size: 1.15rem; line-height: 1.7;">
            Agenda la instalación de tu fibra óptica en menos de 2 minutos. Nuestro equipo técnico validará tu cobertura de inmediato.
        </p>
        <div class="d-flex flex-wrap gap-3 justify-content-center">
            <a href="{{ route('reservaciones.create') }}" class="btn btn-lg px-4 text-white fw-bold" style="background-color: var(--rc-primary); border-radius: 0.75rem;">
                <i class="bi bi-calendar-check me-2"></i> Solicitar Instalación Hoy
            </a>
            <a href="{{ route('contacto') }}" class="btn btn-lg px-4 btn-outline-light fw-semibold" style="border-radius: 0.75rem; border-color: rgba(255,255,255,0.25);">
                <i class="bi bi-chat-dots me-2"></i> Hablar con un Asesor
            </a>
        </div>
    </div>
</section>
@endsection
