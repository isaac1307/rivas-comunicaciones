@extends('layouts.app')

@section('title', 'Promociones Vigentes')

@section('content')
<div class="container py-5">
    <div class="text-center mb-5">
        <span class="badge bg-danger px-3 py-2 rounded-pill fw-bold text-uppercase">Ofertas Especiales</span>
        <h1 class="display-5 fw-bold mt-2">Promociones y Descuentos Activos</h1>
        <p class="lead text-muted mx-auto" style="max-width: 650px;">
            Aprovecha nuestros paquetes con beneficios exclusivos en instalación y mensualidades reducidas.
        </p>
    </div>

    <div class="row g-4 justify-content-center">
        @forelse ($promociones as $promo)
            <div class="col-lg-4 col-md-6">
                <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden bg-white">
                    <div class="bg-primary text-white p-4 text-center">
                        <span class="badge bg-white text-primary text-uppercase px-3 py-1 rounded-pill small fw-bold mb-2">
                            <i class="bi bi-clock-history me-1"></i> Vigente
                        </span>
                        <h4 class="fw-bold mb-0 text-white">{{ $promo->titulo }}</h4>
                    </div>
                    
                    <div class="card-body p-4 d-flex flex-column">
                        <p class="text-muted small mb-4">{{ $promo->descripcion }}</p>

                        <div class="mb-4">
                            <span class="small fw-bold text-dark d-block mb-2">Planes participantes:</span>
                            @if ($promo->planes->isNotEmpty())
                                <div class="d-flex flex-wrap gap-1">
                                    @foreach ($promo->planes as $p)
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle py-2 px-3 rounded-3">
                                            <i class="bi bi-speedometer2 me-1"></i> {{ $p->nombre }} (${{ number_format($p->precio_mensual, 2) }}/mes)
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <span class="badge bg-secondary-subtle text-muted">Aplica en todos los planes</span>
                            @endif
                        </div>

                        <div class="mt-auto pt-3 border-top d-flex justify-content-between align-items-center small text-muted">
                            <span>Vigencia:</span>
                            <span class="fw-bold text-danger">
                                <i class="bi bi-calendar-check me-1"></i> {{ $promo->fecha_vencimiento->format('d/m/Y') }}
                            </span>
                        </div>

                        <a href="{{ route('reservaciones.create') }}" class="btn btn-primary w-100 mt-3 fw-bold">
                            Contratar con esta Promoción
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5">
                <div class="p-5 bg-white rounded-4 shadow-sm">
                    <i class="bi bi-tags text-muted display-1"></i>
                    <h4 class="mt-3 text-muted">No hay promociones activas por el momento</h4>
                    <p class="text-muted small">Vuelve pronto para consultar nuestras nuevas ofertas de temporada.</p>
                    <a href="{{ route('planes.index') }}" class="btn btn-outline-primary mt-2">Ver Planes Estándar</a>
                </div>
            </div>
        @endforelse
    </div>
</div>
@endsection
