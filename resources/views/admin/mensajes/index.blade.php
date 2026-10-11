@extends('layouts.admin')

@section('title', 'Mensajes de Contacto')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h2 class="fw-bold mb-1">Mensajes de Contacto</h2>
        <p class="text-muted small mb-0">Bandeja de entrada del formulario público de contacto.</p>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-3 bg-white">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small text-uppercase text-muted">
                    <tr>
                        <th>Estado</th>
                        <th>Nombre</th>
                        <th>Correo Electrónico</th>
                        <th>Teléfono</th>
                        <th>Asunto</th>
                        <th>Fecha</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody class="small">
                    @forelse ($mensajes as $msg)
                        <tr class="{{ ! $msg->leido ? 'table-warning fw-semibold' : '' }}">
                            <td>
                                @if ($msg->leido)
                                    <span class="badge bg-secondary">Leído</span>
                                @else
                                    <span class="badge bg-primary">Nuevo</span>
                                @endif
                            </td>
                            <td>{{ $msg->nombre }}</td>
                            <td>{{ $msg->correo_electronico }}</td>
                            <td>{{ $msg->telefono ?: 'N/A' }}</td>
                            <td>{{ Str::limit($msg->asunto, 35) }}</td>
                            <td>{{ $msg->created_at->format('d/m/Y H:i') }}</td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.mensajes.show', $msg) }}" class="btn btn-outline-secondary" title="Leer Mensaje">
                                        <i class="bi bi-envelope-open"></i>
                                    </a>
                                    <form action="{{ route('admin.mensajes.destroy', $msg) }}" method="POST" onsubmit="return confirm('¿Deseas eliminar este mensaje?')" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Eliminar">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No hay mensajes de contacto recibidos.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if ($mensajes->hasPages())
        <div class="card-footer bg-white border-0 pt-3">
            {{ $mensajes->links() }}
        </div>
    @endif
</div>
@endsection
