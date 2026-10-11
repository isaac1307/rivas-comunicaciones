<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Rivas Comunicaciones') | Tu Conexión de Alta Velocidad</title>
    
    <!-- Bootstrap 5.3 CSS & Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        :root {
            --rc-primary: #0d6efd;
            --rc-primary-dark: #0a58ca;
            --rc-accent: #00d2d3;
            --rc-dark: #0f172a;
        }
        body {
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            color: #334155;
            background-color: #f8fafc;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .main-content {
            flex: 1 0 auto;
        }
        .navbar-brand-logo {
            font-weight: 800;
            letter-spacing: -0.5px;
            color: var(--rc-primary);
        }
        .hero-banner {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0d6efd 100%);
            color: white;
            padding: 5rem 0;
            border-radius: 0 0 2rem 2rem;
        }
        .card-plan {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
        }
        .card-plan:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
        }
        .badge-speed {
            background-color: #e0f2fe;
            color: #0369a1;
            font-weight: 600;
            border-radius: 9999px;
            padding: 0.35rem 0.75rem;
        }
        footer {
            background-color: #0f172a;
            color: #94a3b8;
        }
        footer a {
            color: #cbd5e1;
            text-decoration: none;
            transition: color 0.15s;
        }
        footer a:hover {
            color: #38bdf8;
        }
    </style>
    @stack('styles')
</head>
<body>

    <!-- Navegación Principal -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow-sm py-3">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('home') }}">
                <span class="badge bg-primary fs-5 p-2 rounded-3"><i class="bi bi-broadcast"></i></span>
                <span class="fw-bold tracking-tight text-white">Rivas <span class="text-primary">Comunicaciones</span></span>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" aria-controls="navbarMain" aria-expanded="false" aria-label="Navegación móvil">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarMain">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-3">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('home') ? 'active fw-bold' : '' }}" href="{{ route('home') }}">Inicio</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('planes.index') ? 'active fw-bold' : '' }}" href="{{ route('planes.index') }}">Planes</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('promociones.index') ? 'active fw-bold' : '' }}" href="{{ route('promociones.index') }}">Promociones</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('cobertura') ? 'active fw-bold' : '' }}" href="{{ route('cobertura') }}">Cobertura</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('nosotros') ? 'active fw-bold' : '' }}" href="{{ route('nosotros') }}">Nosotros</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('contacto') ? 'active fw-bold' : '' }}" href="{{ route('contacto') }}">Contacto</a>
                    </li>
                </ul>

                <div class="d-flex align-items-center flex-wrap gap-2">
                    <!-- Formulario Rápido de Consulta de Folio -->
                    <form class="d-flex" role="search" onsubmit="event.preventDefault(); const f = document.getElementById('folioInput').value.trim(); if(f.startsWith('RES-')) { window.location.href='/reservaciones/'+f; } else if(f.startsWith('TKT-')) { window.location.href='/soporte/ticket/'+f; } else if(f) { window.location.href='/reservaciones/'+f; }">
                        <div class="input-group input-group-sm">
                            <input class="form-control" type="search" id="folioInput" placeholder="Consultar Folio (RES/TKT)" aria-label="Folio">
                            <button class="btn btn-outline-light" type="submit" title="Buscar Folio"><i class="bi bi-search"></i></button>
                        </div>
                    </form>

                    <!-- Dropdown Soporte / Tickets -->
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-info dropdown-toggle text-nowrap" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-headset me-1"></i> Soporte
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow">
                            <li><a class="dropdown-item" href="{{ route('tickets.create') }}"><i class="bi bi-plus-circle me-2 text-primary"></i> Levantar Ticket</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><h6 class="dropdown-header">Atención 24/7</h6></li>
                            <li><span class="dropdown-item-text text-muted small"><i class="bi bi-telephone me-2"></i> 777-123-4567</span></li>
                        </ul>
                    </div>

                    <!-- Botón Destacado Solicitar Instalación -->
                    <a href="{{ route('reservaciones.create') }}" class="btn btn-sm btn-primary text-nowrap shadow-sm fw-semibold">
                        <i class="bi bi-calendar-check me-1"></i> Contratar Ahora
                    </a>

                    <!-- Enlace Admin -->
                    <a href="{{ route('admin.login') }}" class="btn btn-sm btn-outline-secondary text-nowrap" title="Acceso Administrativo">
                        <i class="bi bi-lock-fill"></i>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Notificaciones Flash -->
    <div class="container mt-3">
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
                <strong><i class="bi bi-exclamation-circle-fill me-2"></i> Por favor verifique los siguientes errores:</strong>
                <ul class="mb-0 mt-2 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        @endif
    </div>

    <!-- Contenido Principal -->
    <main class="main-content">
        @yield('content')
        {{ $slot ?? '' }}
    </main>

    <!-- Pie de Página -->
    <footer class="py-5 mt-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <span class="badge bg-primary fs-5 p-2 rounded-3"><i class="bi bi-broadcast"></i></span>
                        <h5 class="text-white fw-bold mb-0">Rivas Comunicaciones</h5>
                    </div>
                    <p class="small text-secondary">Proveedor de Servicios de Internet (ISP) líder en la región. Llevamos conectividad de fibra óptica ultra rápida, estable y confiable a hogares y empresas.</p>
                    <div class="d-flex gap-3 text-secondary">
                        <a href="#" class="fs-5"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="fs-5"><i class="bi bi-whatsapp"></i></a>
                        <a href="#" class="fs-5"><i class="bi bi-instagram"></i></a>
                    </div>
                </div>

                <div class="col-lg-2 col-md-6">
                    <h6 class="text-white fw-bold mb-3">Servicios</h6>
                    <ul class="list-unstyled small d-flex flex-column gap-2">
                        <li><a href="{{ route('planes.index') }}">Internet Hogar</a></li>
                        <li><a href="{{ route('planes.index') }}">Internet Negocios</a></li>
                        <li><a href="{{ route('promociones.index') }}">Promociones</a></li>
                        <li><a href="{{ route('reservaciones.create') }}">Agendar Instalación</a></li>
                    </ul>
                </div>

                <div class="col-lg-3 col-md-6">
                    <h6 class="text-white fw-bold mb-3">Atención al Cliente</h6>
                    <ul class="list-unstyled small d-flex flex-column gap-2">
                        <li><a href="{{ route('tickets.create') }}"><i class="bi bi-ticket-detailed me-1"></i> Levantar Ticket</a></li>
                        <li><a href="{{ route('cobertura') }}"><i class="bi bi-geo-alt me-1"></i> Mapa de Cobertura</a></li>
                        <li><a href="{{ route('contacto') }}"><i class="bi bi-envelope me-1"></i> Centro de Contacto</a></li>
                        <li><a href="{{ route('admin.login') }}"><i class="bi bi-shield-lock me-1"></i> Portal Administrativo</a></li>
                    </ul>
                </div>

                <div class="col-lg-3 col-md-6">
                    <h6 class="text-white fw-bold mb-3">Contacto Directo</h6>
                    <ul class="list-unstyled small text-secondary d-flex flex-column gap-2">
                        <li><i class="bi bi-geo-alt-fill text-primary me-2"></i> Av. Telecomunicaciones #100, Morelos, México</li>
                        <li><i class="bi bi-telephone-fill text-primary me-2"></i> (777) 123-4567 / 8900</li>
                        <li><i class="bi bi-envelope-fill text-primary me-2"></i> contacto@rivascomunicaciones.com</li>
                        <li><i class="bi bi-clock-fill text-primary me-2"></i> Lun - Sáb: 8:00 AM - 8:00 PM</li>
                    </ul>
                </div>
            </div>

            <hr class="border-secondary my-4 opacity-25">

            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center small text-secondary">
                <p class="mb-2 mb-md-0">&copy; {{ date('Y') }} Rivas Comunicaciones. Todos los derechos reservados.</p>
                <div class="d-flex gap-3">
                    <span>Monitoreo 24/7</span>
                    <span>•</span>
                    <span>Fibra Óptica Dedicada</span>
                    <span>•</span>
                    <span>ISP Certificado</span>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5.3 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    @stack('scripts')
</body>
</html>
