@extends('layouts.admin')

@section('title', 'Gestión de Promociones')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h2 class="fw-bold mb-1">Promociones Comerciales</h2>
        <p class="text-muted small mb-0">Campañas con descuento y beneficios temporales.</p>
    </div>
    <a href="{{ route('admin.promociones.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-circle me-1"></i> Nueva Promoción
    </a>
</div>

<div class="card border-0 shadow-sm rounded-3 bg-white">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small text-uppercase text-muted">
                    <tr>
                        <th>ID</th>
                        <th>Título</th>
                        <th>Planes Vinculados</th>
                        <th>Periodo de Vigencia</th>
                        <th>Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody class="small">
                    @forelse ($promociones as $promo)
                        <tr>
                            <td class="text-muted">#{{ $promo->id }}</td>
                            <td class="fw-bold text-dark">{{ $promo->titulo }}</td>
                            <td>
                                @forelse ($promo->planes as $p)
                                    <span class="badge bg-light text-dark border">{{ $p->nombre }}</span>
                                @empty
                                    <span class="text-muted small">Todos los planes</span>
                                @endforelse
                            </td>
                            <td>
                                {{ $promo->fecha_inicio->format('d/m/Y') }} al <span class="fw-bold text-danger">{{ $promo->fecha_vencimiento->format('d/m/Y') }}</span>
                            </td>
                            <td>
                                <span class="badge {{ $promo->estado === 'activa' ? 'bg-success' : 'bg-secondary' }}">
                                    {{ ucfirst($promo->estado) }}
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.promociones.show', $promo) }}" class="btn btn-outline-secondary" title="Ver Detalle">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.promociones.edit', $promo) }}" class="btn btn-outline-primary" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('admin.promociones.destroy', $promo) }}" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar esta promoción?')" class="d-inline">
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
                            <td colspan="6" class="text-center text-muted py-4">No se han registrado promociones.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if ($promociones->hasPages())
        <div class="card-footer bg-white border-0 pt-3">
            {{ $promociones->links() }}
        </div>
    @endif
</div>
@endsection
