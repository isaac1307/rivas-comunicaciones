@extends('layouts.admin')

@section('title', 'Gestión de Usuarios del Sistema')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h2 class="fw-bold mb-1">Usuarios del Sistema</h2>
        <p class="text-muted small mb-0">Control de accesos y asignación de roles (Administrador, Operador, Cobrador).</p>
    </div>
    <a href="{{ route('admin.usuarios.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-person-plus me-1"></i> Nuevo Usuario
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
                        <th>Usuario</th>
                        <th>Correo Electrónico</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th>Último Acceso</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody class="small">
                    @forelse ($usuarios as $usr)
                        <tr>
                            <td class="text-muted">#{{ $usr->id }}</td>
                            <td>
                                <div class="fw-bold text-dark">{{ $usr->nombre_completo }}</div>
                                @if ($usr->id === auth()->id())
                                    <span class="badge bg-secondary-subtle text-secondary" style="font-size: 0.7rem;">Tu cuenta actual</span>
                                @endif
                            </td>
                            <td><code>{{ $usr->nombre_usuario }}</code></td>
                            <td>{{ $usr->email ?: '—' }}</td>
                            <td>
                                @if ($usr->isAdmin())
                                    <span class="badge bg-primary">Administrador</span>
                                @elseif ($usr->isCobrador())
                                    <span class="badge bg-success">Cobrador</span>
                                @else
                                    <span class="badge bg-info text-dark">Operador</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $usr->isActivo() ? 'bg-success' : 'bg-danger' }}">
                                    {{ ucfirst($usr->estado) }}
                                </span>
                            </td>
                            <td class="text-muted">
                                {{ $usr->ultimo_acceso ? $usr->ultimo_acceso->format('d/m/Y H:i') : 'Nunca' }}
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.usuarios.edit', $usr) }}" class="btn btn-outline-primary" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @if ($usr->id !== auth()->id())
                                        <form action="{{ route('admin.usuarios.destroy', $usr) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Seguro que deseas eliminar al usuario {{ $usr->nombre_usuario }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="Eliminar">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">No hay usuarios registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if ($usuarios->hasPages())
        <div class="card-footer bg-white border-0 pt-3">
            {{ $usuarios->links() }}
        </div>
    @endif
</div>
@endsection
