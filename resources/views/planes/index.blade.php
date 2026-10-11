@extends('layouts.app')

@section('title', 'Planes de Internet y Tarifas')

@section('content')
<div class="container py-5">
    <div class="text-center mb-5">
        <span class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill fw-bold text-uppercase">Catálogo de Servicios</span>
        <h1 class="display-5 fw-bold mt-2">Planes de Fibra Óptica para Cada Necesidad</h1>
        <p class="lead text-muted mx-auto" style="max-width: 650px;">
            Velocidades reales, simétricas y con soporte de primer nivel. Elige el plan que mejor se adapte a tu hogar o empresa.
        </p>
    </div>

    <div class="row g-4 justify-content-center">
        @forelse ($planes as $plan)
            <div class="col-lg-4 col-md-6">
                <div class="card card-plan h-100 shadow-sm border-0 rounded-4 overflow-hidden bg-white p-4">
                    <div class="card-body d-flex flex-column text-center">
                        <span class="badge-speed align-self-center mb-3">
                            <i class="bi bi-speedometer2"></i> {{ $plan->velocidad_descarga }} Mbps
                        </span>

                        <h3 class="fw-bold mb-1 text-dark">{{ $plan->nombre }}</h3>
                        <p class="text-muted small mb-4">{{ $plan->descripcion ?: 'Conexión de fibra óptica dedicada de alta velocidad.' }}</p>

                        <div class="py-3 border-top border-bottom mb-4">
                            <div class="d-flex justify-content-center align-items-baseline">
                                <span class="fs-4 fw-bold text-primary">$</span>
                                <span class="display-5 fw-extrabold text-primary">{{ number_format($plan->precio_mensual, 2) }}</span>
                                <span class="text-muted small ms-1">/ mes</span>
                            </div>
                            <span class="badge bg-success-subtle text-success small mt-1">Sin plazo forzoso</span>
                        </div>

                        <ul class="list-unstyled text-start small text-muted mb-4 space-y-3 flex-grow-1">
                            <li class="mb-2 d-flex align-items-center">
                                <i class="bi bi-arrow-down-circle-fill text-success fs-5 me-2"></i>
                                <span><strong>{{ $plan->velocidad_descarga }} Mbps</strong> de descarga para streaming y juegos</span>
                            </li>
                            <li class="mb-2 d-flex align-items-center">
                                <i class="bi bi-arrow-up-circle-fill text-primary fs-5 me-2"></i>
                                <span><strong>{{ $plan->velocidad_carga }} Mbps</strong> de subida para videollamadas y backups</span>
                            </li>
                            <li class="mb-2 d-flex align-items-center">
                                <i class="bi bi-router-fill text-dark fs-5 me-2"></i>
                                <span>Módem WiFi Doble Banda (2.4 & 5.0 GHz) incluido</span>
                            </li>
                            <li class="mb-2 d-flex align-items-center">
                                <i class="bi bi-infinity text-info fs-5 me-2"></i>
                                <span>Navegación ilimitada sin tope de datos</span>
                            </li>
                            <li class="d-flex align-items-center">
                                <i class="bi bi-headset text-warning fs-5 me-2"></i>
                                <span>Soporte técnico especializado 24/7</span>
                            </li>
                        </ul>

                        @if ($plan->promociones->isNotEmpty())
                            <div class="bg-warning-subtle text-warning-emphasis p-2 rounded-3 small mb-3 text-start">
                                <i class="bi bi-tag-fill me-1"></i> <strong>Promoción disponible:</strong>
                                @foreach ($plan->promociones as $promo)
                                    <div>• {{ $promo->titulo }}</div>
                                @endforeach
                            </div>
                        @endif

                        <a href="{{ route('reservaciones.create', ['plan_id' => $plan->id]) }}" class="btn btn-primary btn-lg w-100 fw-bold mt-auto shadow-sm">
                            <i class="bi bi-check2-circle me-1"></i> Contratar Plan
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5">
                <div class="p-5 bg-white rounded-4 shadow-sm">
                    <i class="bi bi-router text-muted display-1"></i>
                    <h4 class="mt-3 text-muted">Próximamente más planes</h4>
                    <p class="text-muted small">Estamos actualizando nuestro catálogo de tarifas.</p>
                </div>
            </div>
        @endforelse
    </div>
</div>
@endsection
