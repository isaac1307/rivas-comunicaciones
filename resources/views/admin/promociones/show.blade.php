@extends('layouts.admin')

@section('title', 'Detalle de Promoción ' . $promocion->titulo)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <a href="{{ route('admin.promociones.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
            <i class="bi bi-arrow-left me-1"></i> Volver a Promociones
        </a>
        <h2 class="fw-bold mb-1">{{ $promocion->titulo }}</h2>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.promociones.edit', $promocion) }}" class="btn btn-primary btn-sm">
            <i class="bi bi-pencil me-1"></i> Editar Promoción
        </a>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-3 bg-white p-3">
            <span class="text-muted small">Periodo de Vigencia</span>
            <div class="fs-4 fw-bold text-dark mt-1">
                {{ $promocion->fecha_inicio->format('d/m/Y') }} al <span class="text-danger">{{ $promocion->fecha_vencimiento->format('d/m/Y') }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-3 bg-white p-3">
            <span class="text-muted small">Estado</span>
            <div class="mt-2">
                <span class="badge {{ $promocion->estado === 'activa' ? 'bg-success' : 'bg-secondary' }} fs-6">
                    {{ ucfirst($promocion->estado) }}
                </span>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-3 bg-white p-4 mb-4">
    <h5 class="fw-bold text-dark mb-2">Descripción de la Promoción</h5>
    <p class="text-muted mb-0">{{ $promocion->descripcion }}</p>
</div>

<!-- Planes Vinculados (Pivot) -->
<div class="card border-0 shadow-sm rounded-3 bg-white p-4">
    <h5 class="fw-bold text-dark mb-3"><i class="bi bi-router text-primary me-2"></i> Planes donde Aplica esta Promoción ({{ $promocion->planes->count() }})</h5>
    <div class="row g-3">
        @forelse ($promocion->planes as $plan)
            <div class="col-md-4">
                <div class="border rounded-3 p-3 bg-light">
                    <h6 class="fw-bold mb-1 text-primary">{{ $plan->nombre }}</h6>
                    <div class="text-muted small">
                        Descarga: {{ $plan->velocidad_descarga }} Mbps • Precio: ${{ number_format($plan->precio_mensual, 2) }}/mes
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-muted">Aplica a todos los planes comerciales del catálogo.</div>
        @endforelse
    </div>
</div>
@endsection
