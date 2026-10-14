@extends('layouts.admin')

@section('title', 'Editar Usuario - ' . $usuario->nombre_usuario)

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <a href="{{ route('admin.usuarios.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
                    <i class="bi bi-arrow-left me-1"></i> Volver a Usuarios
                </a>
                <h3 class="fw-bold mb-0">Editar Usuario</h3>
                <p class="text-muted small">Modifica los datos, rol o estado del usuario <strong>{{ $usuario->nombre_usuario }}</strong>.</p>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-3 bg-white p-4">
            <form method="POST" action="{{ route('admin.usuarios.update', $usuario) }}">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="nombre_completo" class="form-label fw-semibold small">Nombre Completo</label>
                        <input type="text" class="form-control @error('nombre_completo') is-invalid @enderror" id="nombre_completo" name="nombre_completo" value="{{ old('nombre_completo', $usuario->nombre_completo) }}" required>
                        @error('nombre_completo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="nombre_usuario" class="form-label fw-semibold small">Nombre de Usuario (Login)</label>
                        <input type="text" class="form-control @error('nombre_usuario') is-invalid @enderror" id="nombre_usuario" name="nombre_usuario" value="{{ old('nombre_usuario', $usuario->nombre_usuario) }}" required>
                        @error('nombre_usuario')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="email" class="form-label fw-semibold small">Correo Electrónico (opcional)</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $usuario->email) }}">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <label for="rol" class="form-label fw-semibold small">Rol en el Sistema</label>
                        <select class="form-select @error('rol') is-invalid @enderror" id="rol" name="rol" required>
                            @foreach ($roles as $clave => $etiqueta)
                                <option value="{{ $clave }}" {{ old('rol', $usuario->rol) === $clave ? 'selected' : '' }}>
                                    {{ $etiqueta }}
                                </option>
                            @endforeach
                        </select>
                        @error('rol')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <label for="estado" class="form-label fw-semibold small">Estado</label>
                        <select class="form-select @error('estado') is-invalid @enderror" id="estado" name="estado" required>
                            <option value="activo" {{ old('estado', $usuario->estado) === 'activo' ? 'selected' : '' }}>Activo</option>
                            <option value="inactivo" {{ old('estado', $usuario->estado) === 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                        </select>
                        @error('estado')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 mt-4">
                        <div class="alert alert-light border small text-muted mb-0">
                            <i class="bi bi-info-circle me-1"></i> Si no deseas modificar la contraseña del usuario, deja los siguientes campos en blanco.
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label for="password" class="form-label fw-semibold small">Nueva Contraseña (opcional)</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" minlength="8">
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="password_confirmation" class="form-label fw-semibold small">Confirmar Nueva Contraseña</label>
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" minlength="8">
                    </div>
                </div>

                <hr class="my-4">

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.usuarios.index') }}" class="btn btn-secondary btn-sm">Cancelar</a>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bi bi-save me-1"></i> Actualizar Usuario
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
