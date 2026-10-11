@extends('layouts.admin')

@section('title', 'Mensaje de ' . $mensaje->nombre)

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.mensajes.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
        <i class="bi bi-arrow-left me-1"></i> Volver a Mensajes
    </a>
    <h2 class="fw-bold mb-1">Detalle del Mensaje</h2>
</div>

<div class="card border-0 shadow-sm rounded-3 bg-white p-4">
    <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold text-dark mb-1">{{ $mensaje->asunto }}</h4>
            <div class="text-muted small">
                Recibido el {{ $mensaje->created_at->format('d/m/Y \a \l\a\s H:i:s') }}
            </div>
        </div>
        <form action="{{ route('admin.mensajes.update', $mensaje) }}" method="POST">
            @csrf
            @method('PUT')
            <input type="hidden" name="leido" value="{{ $mensaje->leido ? 0 : 1 }}">
            <button type="submit" class="btn btn-sm {{ $mensaje->leido ? 'btn-outline-secondary' : 'btn-outline-success' }}">
                <i class="bi bi-check2-circle me-1"></i> {{ $mensaje->leido ? 'Marcar como no leído' : 'Marcar como leído' }}
            </button>
        </form>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <span class="small text-muted d-block">Remitente:</span>
            <div class="fw-bold text-dark fs-5">{{ $mensaje->nombre }}</div>
        </div>
        <div class="col-md-4">
            <span class="small text-muted d-block">Correo Electrónico:</span>
            <div class="fw-semibold text-primary fs-5">
                <a href="mailto:{{ $mensaje->correo_electronico }}" class="text-decoration-none">
                    {{ $mensaje->correo_electronico }}
                </a>
            </div>
        </div>
        <div class="col-md-4">
            <span class="small text-muted d-block">Teléfono / WhatsApp:</span>
            <div class="fw-bold text-dark fs-5">
                @if ($mensaje->telefono)
                    <a href="tel:{{ $mensaje->telefono }}" class="text-decoration-none text-dark">
                        {{ $mensaje->telefono }}
                    </a>
                @else
                    <span class="text-muted">No indicado</span>
                @endif
            </div>
        </div>
    </div>

    <h5 class="fw-bold text-dark mb-2">Mensaje:</h5>
    <div class="p-4 bg-light rounded-3 text-dark fs-6" style="white-space: pre-line;">
        {{ $mensaje->mensaje }}
    </div>

    <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
        <a href="mailto:{{ $mensaje->correo_electronico }}?subject=Respuesta:%20{{ urlencode($mensaje->asunto) }}" class="btn btn-primary btn-sm fw-bold">
            <i class="bi bi-reply-fill me-1"></i> Responder por Correo
        </a>

        <form action="{{ route('admin.mensajes.destroy', $mensaje) }}" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar este mensaje?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger btn-sm">
                <i class="bi bi-trash me-1"></i> Eliminar Mensaje
            </button>
        </form>
    </div>
</div>
@endsection
