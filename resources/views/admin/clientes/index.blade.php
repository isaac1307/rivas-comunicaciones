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
                            <td class="fw-bold text-dark">{{ $cli->nombre_completo }}</td>
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
                            <td colspan="8" class="text-center text-muted py-4">No hay clientes dados de alta en el sistema.</td>
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
