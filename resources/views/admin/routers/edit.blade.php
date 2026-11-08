@extends('layouts.admin')

@section('title', 'Editar Nodo MikroTik - ' . $router->nombre)

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.routers.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
        <i class="bi bi-arrow-left me-1"></i> Volver a Nodos
    </a>
    <h2 class="fw-bold mb-1">Editar Nodo MikroTik</h2>
    <p class="text-muted small mb-0">Modifica los parámetros de conexión o estado del router {{ $router->nombre }}.</p>
</div>

@if ($router->clientes()->count() > 0)
    <div class="alert alert-info border-0 shadow-sm rounded-3 d-flex align-items-center mb-4">
        <i class="bi bi-info-circle-fill fs-4 text-info me-3"></i>
        <div>
            <strong>Nodo en producción:</strong> Este enrutador tiene actualmente <strong>{{ $router->clientes()->count() }} clientes asociados</strong>. Cualquier cambio de IP afectará las tareas automáticas de corte y reconexión.
        </div>
    </div>
@endif

<div class="card border-0 shadow-sm rounded-3 bg-white p-4">
    <form action="{{ route('admin.routers.update', $router) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row g-3">
            <div class="col-md-6">
                <label for="nombre" class="form-label fw-bold small">Nombre Descriptivo del Nodo *</label>
                <input type="text" class="form-control @error('nombre') is-invalid @enderror" id="nombre" name="nombre" value="{{ old('nombre', $router->nombre) }}" required>
                <div class="form-text small">Identificador visible para los operadores al asociar clientes.</div>
                @error('nombre')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="ip_address" class="form-label fw-bold small">Dirección IP / Host del Router *</label>
                <input type="text" class="form-control font-monospace @error('ip_address') is-invalid @enderror" id="ip_address" name="ip_address" value="{{ old('ip_address', $router->ip_address) }}" required>
                <div class="form-text small">Dirección IP local, VPN o túnel accesible desde este servidor web.</div>
                @error('ip_address')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="api_user" class="form-label fw-bold small">Usuario de la API REST *</label>
                <input type="text" class="form-control font-monospace @error('api_user') is-invalid @enderror" id="api_user" name="api_user" value="{{ old('api_user', $router->api_user) }}" required>
                <div class="form-text small">Usuario de RouterOS con permisos para /rest/ip/firewall y /rest/queue.</div>
                @error('api_user')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="api_password" class="form-label fw-bold small">Nueva Contraseña de la API (Opcional)</label>
                <div class="input-group">
                    <input type="password" class="form-control @error('api_password') is-invalid @enderror" id="api_password" name="api_password" placeholder="Dejar en blanco para mantener la contraseña actual">
                    <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('api_password', this)">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
                <div class="form-text small text-muted">
                    Solo llena este campo si deseas actualizar la clave de acceso del router.
                </div>
                @error('api_password')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="estado" class="form-label fw-bold small">Estado del Nodo *</label>
                <select class="form-select @error('estado') is-invalid @enderror" id="estado" name="estado" required>
                    <option value="activo" {{ old('estado', $router->estado) === 'activo' ? 'selected' : '' }}>Activo (Permite asignación y despacho de comandos)</option>
                    <option value="inactivo" {{ old('estado', $router->estado) === 'inactivo' ? 'selected' : '' }}>Inactivo (Mantenimiento o fuera de servicio)</option>
                </select>
                @error('estado')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6 d-flex align-items-center">
                <div class="form-check form-switch mt-3">
                    <input type="hidden" name="usar_tls" value="0">
                    <input class="form-check-input" type="checkbox" role="switch" id="usar_tls" name="usar_tls" value="1" {{ old('usar_tls', $router->usar_tls) ? 'checked' : '' }}>
                    <label class="form-check-label fw-bold small text-dark" for="usar_tls">
                        <i class="bi bi-lock-fill text-success me-1"></i> Usar HTTPS / TLS (`www-ssl`) para la API REST
                    </label>
                    <div class="text-muted small">Actívalo si el router tiene certificado SSL configurado en IP -> Services -> www-ssl.</div>
                </div>
            </div>

            <div class="col-12 mt-4 d-flex justify-content-between align-items-center">
                <a href="{{ route('admin.routers.index') }}" class="btn btn-secondary">Cancelar</a>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary fw-bold">
                        <i class="bi bi-save me-1"></i> Actualizar Nodo
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    function togglePasswordVisibility(inputId, btn) {
        const input = document.getElementById(inputId);
        const icon = btn.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
        }
    }
</script>
@endpush
