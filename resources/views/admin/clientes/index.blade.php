@extends('layouts.admin')

@section('title', 'Gestión de Clientes')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h2 class="fw-bold mb-1">Clientes Registrados</h2>
        <p class="text-muted small mb-0">Base de datos de contratos y suscriptores de internet.</p>
    </div>
    <a href="{{ route('admin.clientes.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-person-plus me-1"></i> Alta de Cliente
    </a>
</div>

<!-- Filtros y Búsqueda -->
<div class="card border-0 shadow-sm rounded-3 bg-white mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('admin.clientes.index') }}" class="row g-2 align-items-center">
            <div class="col-md-5">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="buscar" value="{{ $buscar }}" class="form-control border-start-0" placeholder="Buscar por nombre, teléfono o dirección...">
                </div>
            </div>
            <div class="col-md-5">
                <div class="btn-group btn-group-sm w-100" role="group">
                    <a href="{{ route('admin.clientes.index', ['buscar' => $buscar, 'estado' => 'activo']) }}" class="btn {{ $estado === 'activo' ? 'btn-success' : 'btn-outline-secondary' }}">
                        <i class="bi bi-person-check me-1"></i> Clientes Activos ({{ $totales['activos'] }})
                    </a>
                    <a href="{{ route('admin.clientes.index', ['buscar' => $buscar, 'estado' => 'todos']) }}" class="btn {{ $estado === 'todos' ? 'btn-primary' : 'btn-outline-secondary' }}">
                        Todos ({{ $totales['total'] }})
                    </a>
                    <a href="{{ route('admin.clientes.index', ['buscar' => $buscar, 'estado' => 'inactivo']) }}" class="btn {{ $estado === 'inactivo' ? 'btn-danger' : 'btn-outline-secondary' }}">
                        Inactivos ({{ $totales['inactivos'] }})
                    </a>
                </div>
            </div>
            <div class="col-md-2 text-md-end">
                @if ($buscar !== '' || $estado !== 'activo')
                    <a href="{{ route('admin.clientes.index') }}" class="btn btn-outline-secondary btn-sm w-100">Restablecer</a>
                @endif
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-3 bg-white">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small text-uppercase text-muted">
                    <tr>
                        <th>ID</th>
                        <th>Nombre Completo</th>
                        <th>Teléfono</th>
                        <th>Plan Contratado</th>
                        <th>Fecha Alta</th>
                        <th>Tickets</th>
                        <th>Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody class="small">
                    @forelse ($clientes as $cli)
                        <tr>
                            <td class="text-muted">#{{ $cli->id }}</td>
                            <td>
                                <div class="fw-bold text-dark">{{ $cli->nombre_completo }}</div>
                                <div class="text-muted" style="font-size: 0.8rem;">
                                    <i class="bi bi-geo-alt me-1"></i>{{ Str::limit($cli->direccion, 40) }}
                                </div>
                            </td>
                            <td>{{ $cli->telefono }}</td>
                            <td>
                                @if ($cli->plan)
                                    <span class="badge bg-primary-subtle text-primary">{{ $cli->plan->nombre }}</span>
                                @else
                                    <span class="text-muted small">Sin plan</span>
                                @endif
                            </td>
                            <td>{{ $cli->fecha_alta->format('d/m/Y') }}</td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    <i class="bi bi-ticket-detailed me-1"></i> {{ $cli->tickets_count }}
                                </span>
                            </td>
                            <td>
                                <span class="badge {{ $cli->estado === 'activo' ? 'bg-success' : 'bg-secondary' }}">
                                    <i class="bi {{ $cli->estado === 'activo' ? 'bi-check-circle' : 'bi-dash-circle' }} me-1"></i>
                                    {{ ucfirst($cli->estado) }}
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.clientes.show', $cli) }}" class="btn btn-outline-secondary" title="Ver Expediente">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.clientes.edit', $cli) }}" class="btn btn-outline-primary" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('admin.clientes.destroy', $cli) }}" method="POST" onsubmit="return confirm('¿Seguro que deseas dar de baja a este cliente?')" class="d-inline">
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
                            <td colspan="8" class="text-center text-muted py-4">
                                No se encontraron clientes con los filtros aplicados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if ($clientes->hasPages())
        <div class="card-footer bg-white border-0 pt-3">
            {{ $clientes->links() }}
        </div>
    @endif
</div>
@endsection
