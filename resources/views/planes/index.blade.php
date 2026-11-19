@extends('layouts.app')

@section('title', 'Planes de Internet y Tarifas')

@section('content')
<div class="container py-5">
    <div class="text-center mb-5">
        <h1 class="display-5 fw-extrabold text-dark tracking-tight mb-2">Planes de Fibra Óptica Simétrica</h1>
        <p class="lead text-muted mx-auto" style="max-width: 650px; font-size: 1.1rem; line-height: 1.7;">
            Velocidades reales simétricas con el menor ping de la región. Elige el ancho de banda ideal para tu hogar o empresa sin plazos forzosos.
        </p>
    </div>

    <div class="row g-4 justify-content-center reveal-stagger">
        @forelse ($planes as $plan)
            <div class="col-lg-4 col-md-6">
                <div class="card card-plan h-100 p-4">
                    <div class="card-body d-flex flex-column text-center p-0">
                        <div class="mb-3">
                            <span class="badge-speed">
                                <i class="bi bi-speedometer2 me-1"></i> {{ $plan->velocidad_descarga }} Mbps Simétricos
                            </span>
                        </div>

                        <h3 class="fw-extrabold mb-1 text-dark">{{ $plan->nombre }}</h3>
                        <p class="text-muted small mb-4" style="font-size: 0.95rem;">{{ $plan->descripcion ?: 'Conexión de fibra óptica dedicada de alta velocidad.' }}</p>

                        <div class="py-3 px-2 mb-4" style="background-color: #f8fafc; border-radius: 0.75rem;">
                            <div class="d-flex justify-content-center align-items-baseline">
                                <span class="fs-4 fw-bold text-dark">$</span>
                                <span class="display-5 fw-extrabold text-dark">{{ number_format($plan->precio_mensual, 2) }}</span>
                                <span class="text-muted small ms-1">/ mes</span>
                            </div>
                            <span class="badge text-success small mt-1" style="background-color: #f0fdf4; border: 1px solid #bbf7d0;">Sin plazo forzoso</span>
                        </div>

                        <ul class="list-unstyled text-start small text-muted mb-4 d-flex flex-column gap-2 flex-grow-1" style="font-size: 0.95rem;">
                            <li class="d-flex align-items-center gap-2">
                                <i class="bi bi-check-circle-fill text-primary"></i>
                                <span><strong>{{ $plan->velocidad_descarga }} Mbps</strong> de descarga para streaming y juegos</span>
                            </li>
                            <li class="d-flex align-items-center gap-2">
                                <i class="bi bi-check-circle-fill text-primary"></i>
                                <span><strong>{{ $plan->velocidad_carga }} Mbps</strong> de subida para videollamadas y backups</span>
                            </li>
                            <li class="d-flex align-items-center gap-2">
                                <i class="bi bi-check-circle-fill text-primary"></i>
                                <span>Módem WiFi Doble Banda (2.4 & 5.0 GHz) incluido</span>
                            </li>
                            <li class="d-flex align-items-center gap-2">
                                <i class="bi bi-check-circle-fill text-primary"></i>
                                <span>Navegación ilimitada sin tope de datos</span>
                            </li>
                            <li class="d-flex align-items-center gap-2">
                                <i class="bi bi-check-circle-fill text-primary"></i>
                                <span>Soporte técnico preferente en Morelos 24/7</span>
                            </li>
                        </ul>

                        @if ($plan->promociones->isNotEmpty())
                            <div class="p-3 rounded-3 small mb-3 text-start" style="background-color: #fffbeb; border: 1px solid #fde68a; color: #92400e;">
                                <i class="bi bi-tag-fill me-1"></i> <strong>Promoción disponible:</strong>
                                @foreach ($plan->promociones as $promo)
                                    <div>• {{ $promo->titulo }}</div>
                                @endforeach
                            </div>
                        @endif

                        <a href="{{ route('reservaciones.create', ['plan_id' => $plan->id]) }}" class="btn btn-lg w-100 fw-bold mt-auto text-white py-2_5" style="background-color: var(--rc-primary); border-radius: 0.6rem;">
                            <i class="bi bi-calendar-check me-1"></i> Contratar Plan
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5">
                <div class="p-5 bg-white rounded-4 border">
                    <i class="bi bi-router text-muted display-1"></i>
                    <h4 class="mt-3 text-muted">Próximamente más planes</h4>
                    <p class="text-muted small">Estamos actualizando nuestro catálogo de tarifas.</p>
                </div>
            </div>
        @endforelse
    </div>
</div>
@endsection
