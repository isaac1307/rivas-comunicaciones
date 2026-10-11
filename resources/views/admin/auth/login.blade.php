<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso al Panel Administrativo | Rivas Comunicaciones</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: system-ui, -apple-system, sans-serif;
        }
        .login-card {
            border: 0;
            border-radius: 1.25rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.4);
            overflow: hidden;
            max-width: 440px;
            width: 100%;
        }
    </style>
</head>
<body class="p-3">

    <div class="login-card bg-white p-4 p-md-5">
        <div class="text-center mb-4">
            <span class="badge bg-primary fs-3 p-3 rounded-circle mb-3">
                <i class="bi bi-shield-lock-fill"></i>
            </span>
            <h3 class="fw-bold text-dark mb-1">Rivas Comunicaciones</h3>
            <p class="text-muted small">Panel de Gestión Administrativa y Operativa</p>
        </div>

        @if (session('success'))
            <div class="alert alert-success small mb-3">
                <i class="bi bi-check-circle-fill me-1"></i> {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger small mb-3">
                <i class="bi bi-exclamation-circle-fill me-1"></i> {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('admin.login.submit') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label for="nombre_usuario" class="form-label small fw-bold">Usuario *</label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-person-fill"></i></span>
                    <input type="text" class="form-control @error('nombre_usuario') is-invalid @enderror" id="nombre_usuario" name="nombre_usuario" value="{{ old('nombre_usuario') }}" placeholder="admin / operador" required autofocus>
                </div>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label small fw-bold">Contraseña *</label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-key-fill"></i></span>
                    <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" placeholder="••••••••" required>
                </div>
            </div>

            <div class="mb-4 d-flex justify-content-between align-items-center">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="remember" name="remember" value="1">
                    <label class="form-check-label small text-muted" for="remember">
                        Recordar mi sesión
                    </label>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold shadow-sm mb-3">
                <i class="bi bi-box-arrow-in-right me-1"></i> Iniciar Sesión
            </button>
        </form>

        <div class="text-center mt-3 pt-3 border-top">
            <a href="{{ route('home') }}" class="text-muted text-decoration-none small">
                <i class="bi bi-arrow-left me-1"></i> Volver al sitio público
            </a>
        </div>
    </div>

</body>
</html>
