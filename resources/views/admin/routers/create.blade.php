@extends('layouts.admin')

@section('title', 'Registrar Nodo MikroTik')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.routers.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
        <i class="bi bi-arrow-left me-1"></i> Volver a Nodos
    </a>
    <h2 class="fw-bold mb-1">Registrar Nuevo Nodo MikroTik</h2>
    <p class="text-muted small mb-0">Configura un nuevo router para automatización de colas de ancho de banda y cortes de servicio.</p>
</div>

<div class="card border-0 shadow-sm rounded-3 bg-white p-4">
    <form action="{{ route('admin.routers.store') }}" method="POST">
        @csrf

        <div class="row g-3">
            <div class="col-md-6">
                <label for="nombre" class="form-label fw-bold small">Nombre Descriptivo del Nodo *</label>
                <input type="text" class="form-control @error('nombre') is-invalid @enderror" id="nombre" name="nombre" value="{{ old('nombre') }}" placeholder="Ej: Nodo Pinotepa - Starlink 1" required>
                <div class="form-text small">Identificador visible para los operadores al asociar clientes.</div>
                @error('nombre')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="ip_address" class="form-label fw-bold small">Dirección IP / Host del Router *</label>
                <input type="text" class="form-control font-monospace @error('ip_address') is-invalid @enderror" id="ip_address" name="ip_address" value="{{ old('ip_address', '192.168.1.116') }}" placeholder="192.168.1.116 ó router.dominio.com" required>
                <div class="form-text small">Dirección IP local, VPN o túnel accesible desde este servidor web.</div>
                @error('ip_address')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="api_user" class="form-label fw-bold small">Usuario de la API REST *</label>
                <input type="text" class="form-control font-monospace @error('api_user') is-invalid @enderror" id="api_user" name="api_user" value="{{ old('api_user', 'admin') }}" placeholder="admin" required>
                <div class="form-text small">Usuario de RouterOS con permisos para /rest/ip/firewall y /rest/queue.</div>
                @error('api_user')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="api_password" class="form-label fw-bold small">Contraseña de la API *</label>
                <div class="input-group">
                    <input type="password" class="form-control @error('api_password') is-invalid @enderror" id="api_password" name="api_password" placeholder="Contraseña de acceso" required>
                    <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('api_password', this)">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
                <div class="form-text small text-success">
                    <i class="bi bi-shield-check me-1"></i> Se almacenará con encriptación fuerte (AES-256) en la base de datos.
                </div>
                @error('api_password')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="estado" class="form-label fw-bold small">Estado del Nodo *</label>
                <select class="form-select @error('estado') is-invalid @enderror" id="estado" name="estado" required>
                    <option value="activo" {{ old('estado', 'activo') === 'activo' ? 'selected' : '' }}>Activo (Permite asignación y despacho de comandos)</option>
                    <option value="inactivo" {{ old('estado') === 'inactivo' ? 'selected' : '' }}>Inactivo (Mantenimiento o fuera de servicio)</option>
                </select>
                @error('estado')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6 d-flex align-items-center">
                <div class="form-check form-switch mt-3">
                    <input type="hidden" name="usar_tls" value="0">
                    <input class="form-check-input" type="checkbox" role="switch" id="usar_tls" name="usar_tls" value="1" {{ old('usar_tls') ? 'checked' : '' }}>
                    <label class="form-check-label fw-bold small text-dark" for="usar_tls">
                        <i class="bi bi-lock-fill text-success me-1"></i> Usar HTTPS / TLS (`www-ssl`) para la API REST
                    </label>
                    <div class="text-muted small">Actívalo si el router tiene certificado SSL configurado en IP -> Services -> www-ssl.</div>
                </div>
            </div>

            <div class="col-12 mt-3">
                <div class="form-check form-switch p-3 bg-light rounded-3 border">
                    <input class="form-check-input ms-0 me-3" type="checkbox" role="switch" id="auto_aprovisionar_planes" name="auto_aprovisionar_planes" value="1" {{ old('auto_aprovisionar_planes', '1') === '1' ? 'checked' : '' }}>
                    <label class="form-check-label fw-bold text-dark" for="auto_aprovisionar_planes">
                        <i class="bi bi-speedometer2 text-primary me-1"></i> Aprovisionar planes de velocidad automáticamente al guardar
                    </label>
                    <div class="text-muted small ps-4 mt-1">
                        Si está marcado y el router es accesible, se crearán o actualizarán de inmediato en MikroTik los perfiles PPP, colas y reglas de corte para todos los planes activos en segundo plano.
                    </div>
                </div>
            </div>

            <div class="col-12 mt-4 d-flex justify-content-end gap-2">
                <a href="{{ route('admin.routers.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary fw-bold">
                    <i class="bi bi-save me-1"></i> Guardar Nodo MikroTik
                </button>
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
