@extends('layouts.admin')

@section('title', 'Gestión de Tickets de Soporte')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h2 class="fw-bold mb-1">Tickets de Soporte Técnico</h2>
        <p class="text-muted small mb-0">Mesa de ayuda digital e incidencias reportadas.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.tickets.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-circle me-1"></i> Levantar Ticket
        </a>
    </div>
</div>

<!-- Filtros Rápidos -->
<div class="card border-0 shadow-sm rounded-3 bg-white mb-4 p-3">
    <div class="d-flex flex-wrap gap-2 align-items-center">
        <span class="small fw-bold text-muted me-2">Filtrar Estado:</span>
        <a href="{{ route('admin.tickets.index') }}" class="btn btn-sm {{ !request('estado') ? 'btn-dark' : 'btn-outline-dark' }}">Todos</a>
        <a href="{{ route('admin.tickets.index', ['estado' => 'abierto']) }}" class="btn btn-sm {{ request('estado') === 'abierto' ? 'btn-danger' : 'btn-outline-danger' }}">Abiertos</a>
        <a href="{{ route('admin.tickets.index', ['estado' => 'en_proceso']) }}" class="btn btn-sm {{ request('estado') === 'en_proceso' ? 'btn-warning text-dark' : 'btn-outline-warning text-dark' }}">En Proceso</a>
        <a href="{{ route('admin.tickets.index', ['estado' => 'resuelto']) }}" class="btn btn-sm {{ request('estado') === 'resuelto' ? 'btn-success' : 'btn-outline-success' }}">Resueltos</a>
        <a href="{{ route('admin.tickets.index', ['estado' => 'cerrado']) }}" class="btn btn-sm {{ request('estado') === 'cerrado' ? 'btn-secondary' : 'btn-outline-secondary' }}">Cerrados</a>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-3 bg-white">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small text-uppercase text-muted">
                    <tr>
                        <th>Folio</th>
                        <th>Cliente</th>
                        <th>Categoría</th>
                        <th>Descripción</th>
                        <th>Fecha Reporte</th>
                        <th>Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody class="small">
                    @forelse ($tickets as $tkt)
                        <tr>
                            <td class="fw-bold text-primary">{{ $tkt->folio }}</td>
                            <td>
                                <div class="fw-bold">{{ $tkt->cliente->nombre_completo }}</div>
                                <div class="text-muted small">Tel: {{ $tkt->cliente->telefono }}</div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    {{ str_replace('_', ' ', $tkt->categoria) }}
                                </span>
                            </td>
                            <td>{{ Str::limit($tkt->descripcion, 55) }}</td>
                            <td>{{ $tkt->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                @php
                                    $badgeTkt = [
                                        'abierto' => 'bg-danger text-white',
                                        'en_proceso' => 'bg-warning text-dark',
                                        'resuelto' => 'bg-success text-white',
                                        'cerrado' => 'bg-secondary text-white',
                                    ];
                                @endphp
                                <span class="badge {{ $badgeTkt[$tkt->estado] ?? 'bg-secondary' }}">
                                    {{ str_replace('_', ' ', ucfirst($tkt->estado)) }}
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.tickets.show', $tkt) }}" class="btn btn-outline-secondary" title="Ver Detalle">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.tickets.edit', $tkt) }}" class="btn btn-outline-primary" title="Atender Ticket">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No hay tickets registrados con los filtros seleccionados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if ($tickets->hasPages())
        <div class="card-footer bg-white border-0 pt-3">
            {{ $tickets->links() }}
        </div>
    @endif
</div>
@endsection
