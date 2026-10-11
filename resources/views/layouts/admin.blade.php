<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Panel Administrativo') | Rivas Comunicaciones</title>
    
    <!-- Bootstrap 5.3 CSS & Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        :root {
            --sidebar-width: 260px;
            --admin-primary: #0284c7;
            --admin-dark: #0f172a;
        }
        body {
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: #f1f5f9;
            color: #1e293b;
        }
        #sidebar {
            width: var(--sidebar-width);
            min-height: 100vh;
            background-color: var(--admin-dark);
            color: #e2e8f0;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1040;
            transition: all 0.3s;
        }
        #sidebar .nav-link {
            color: #94a3b8;
            padding: 0.75rem 1.25rem;
            border-radius: 0.5rem;
            margin: 0.15rem 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 500;
            transition: all 0.15s ease-in-out;
        }
        #sidebar .nav-link:hover {
            color: #ffffff;
            background-color: rgba(255, 255, 255, 0.08);
        }
        #sidebar .nav-link.active {
            color: #ffffff;
            background-color: var(--admin-primary);
            box-shadow: 0 4px 6px -1px rgba(2, 132, 199, 0.3);
        }
        #main-wrapper {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .admin-header {
            background-color: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 0.75rem 1.5rem;
            position: sticky;
            top: 0;
            z-index: 1020;
        }
        .stat-card {
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
            transition: transform 0.15s ease;
        }
        .stat-card:hover {
            transform: translateY(-2px);
        }
        @media (max-width: 991.98px) {
            #sidebar {
                margin-left: calc(-1 * var(--sidebar-width));
            }
            #sidebar.active {
                margin-left: 0;
            }
            #main-wrapper {
                margin-left: 0;
            }
        }
    </style>
    @stack('styles')
</head>
<body>

    <!-- Sidebar de Navegación Admin -->
    <nav id="sidebar" class="d-flex flex-column py-3">
        <div class="px-4 mb-4">
            <a href="{{ route('admin.dashboard') }}" class="text-white text-decoration-none d-flex align-items-center gap-2">
                <span class="badge bg-primary fs-5 p-2 rounded-3"><i class="bi bi-hdd-network"></i></span>
                <div>
                    <div class="fw-bold fs-6 tracking-tight">Rivas Admin</div>
                    <div class="text-secondary small">Panel de Control</div>
                </div>
            </a>
        </div>

        <ul class="nav flex-column mb-auto">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>

            <li class="px-4 mt-3 mb-1 text-uppercase text-secondary small fw-bold" style="font-size: 0.7rem; letter-spacing: 0.05em;">Operación Comercial</li>
            
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.planes.*') ? 'active' : '' }}" href="{{ route('admin.planes.index') }}">
                    <i class="bi bi-router"></i> Planes de Internet
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.promociones.*') ? 'active' : '' }}" href="{{ route('admin.promociones.index') }}">
                    <i class="bi bi-tags"></i> Promociones
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.clientes.*') ? 'active' : '' }}" href="{{ route('admin.clientes.index') }}">
                    <i class="bi bi-people"></i> Clientes
                </a>
            </li>

            <li class="px-4 mt-3 mb-1 text-uppercase text-secondary small fw-bold" style="font-size: 0.7rem; letter-spacing: 0.05em;">Atención & Soporte</li>

            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.reservaciones.*') ? 'active' : '' }}" href="{{ route('admin.reservaciones.index') }}">
                    <i class="bi bi-calendar-check"></i> Reservaciones
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.tickets.*') ? 'active' : '' }}" href="{{ route('admin.tickets.index') }}">
                    <i class="bi bi-ticket-detailed"></i> Tickets de Soporte
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.mensajes.*') ? 'active' : '' }}" href="{{ route('admin.mensajes.index') }}">
                    <i class="bi bi-envelope"></i> Mensajes Contacto
                </a>
            </li>

            @if (auth()->check() && auth()->user()->isAdmin())
                <li class="px-4 mt-3 mb-1 text-uppercase text-secondary small fw-bold" style="font-size: 0.7rem; letter-spacing: 0.05em;">Seguridad & Auditoría</li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.bitacoras.*') ? 'active' : '' }}" href="{{ route('admin.bitacoras.index') }}">
                        <i class="bi bi-journal-text"></i> Bitácora del Sistema
                    </a>
                </li>
            @endif
        </ul>

        <hr class="border-secondary mx-3 opacity-25">

        <div class="px-3">
            <a href="{{ route('home') }}" target="_blank" class="btn btn-sm btn-outline-secondary w-100 text-start">
                <i class="bi bi-box-arrow-up-right me-2"></i> Ver Sitio Público
            </a>
        </div>
    </nav>

    <!-- Contenedor Principal -->
    <div id="main-wrapper">
        <!-- Barra Superior Header -->
        <header class="admin-header d-flex justify-content-between align-items-center">
            <button class="btn btn-outline-secondary btn-sm d-lg-none" type="button" onclick="document.getElementById('sidebar').classList.toggle('active')">
                <i class="bi bi-list"></i>
            </button>

            <div class="d-none d-sm-block">
                <span class="text-secondary small">Panel de Administración • ISP Rivas Comunicaciones</span>
            </div>

            <div class="d-flex align-items-center gap-3">
                @if (auth()->check())
                    <div class="text-end d-none d-sm-block">
                        <div class="fw-semibold small">{{ auth()->user()->nombre_completo ?: auth()->user()->nombre_usuario }}</div>
                        <span class="badge {{ auth()->user()->isAdmin() ? 'bg-primary' : 'bg-secondary' }}" style="font-size: 0.7rem;">
                            {{ ucfirst(auth()->user()->rol) }}
                        </span>
                    </div>

                    <form action="{{ route('admin.logout') }}" method="POST" class="m-0">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger btn-sm" title="Cerrar Sesión">
                            <i class="bi bi-box-arrow-right me-1"></i> Salir
                        </button>
                    </form>
                @endif
            </div>
        </header>

        <!-- Mensajes Flash -->
        <div class="container-fluid px-4 mt-3">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                    <strong><i class="bi bi-exclamation-circle-fill me-2"></i> Corrige los siguientes errores:</strong>
                    <ul class="mb-0 mt-2 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                </div>
            @endif
        </div>

        <!-- Contenido Específico de cada Vista -->
        <main class="flex-grow-1 container-fluid px-4 py-3">
            @yield('content')
        </main>

        <footer class="bg-white border-top py-3 text-center text-muted small mt-auto">
            &copy; {{ date('Y') }} Rivas Comunicaciones. Sistema Monolítico Laravel MVC.
        </footer>
    </div>

    <!-- Bootstrap 5.3 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    @stack('scripts')
</body>
</html>
