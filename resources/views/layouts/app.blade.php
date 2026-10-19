<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Rivas Comunicaciones') | Tu Conexión de Alta Velocidad</title>
    
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 CSS & Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        :root {
            --rc-primary: #0284c7;
            --rc-primary-dark: #0369a1;
            --rc-primary-light: #e0f2fe;
            --rc-accent: #38bdf8;
            --rc-dark: #0b1329;
            --rc-dark-surface: #111e3b;
            --rc-ink: #0f172a;
            --rc-body: #334155;
            --rc-muted: #64748b;
            --rc-border: #e2e8f0;
            --rc-surface: #ffffff;
            --rc-bg: #f8fafc;
        }
        body {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            color: var(--rc-body);
            background-color: var(--rc-bg);
            font-size: 1rem;
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        .main-content {
            flex: 1 0 auto;
        }
        .navbar-brand-logo {
            font-weight: 800;
            letter-spacing: -0.025em;
            color: var(--rc-primary);
        }
        .card-plan {
            transition: transform 200ms var(--ease-out), box-shadow 200ms var(--ease-out);
            border: 1px solid var(--rc-border);
            border-radius: 1.25rem;
            background-color: var(--rc-surface);
        }
        @media (hover: hover) and (pointer: fine) {
            .card-plan:hover {
                transform: translateY(-4px);
                box-shadow: 0 16px 32px -8px rgba(15, 23, 42, 0.08), 0 4px 8px -4px rgba(15, 23, 42, 0.04);
            }
        }
        .badge-speed {
            background-color: #f0f9ff;
            color: #0369a1;
            font-weight: 700;
            border-radius: 9999px;
            padding: 0.45rem 1rem;
            font-size: 0.875rem;
            letter-spacing: -0.01em;
        }
        .site-navbar {
            background-color: var(--rc-dark);
        }
        .site-navbar .nav-link {
            color: #e2e8f0;
            font-weight: 500;
            padding: 0.5rem 0.85rem;
            border-radius: 0.5rem;
            transition: color 150ms ease, background-color 150ms ease;
        }
        .site-navbar .nav-link:hover {
            color: #ffffff;
            background-color: rgba(255, 255, 255, 0.08);
        }
        .site-navbar .nav-link.active {
            color: #38bdf8;
            font-weight: 700;
            background-color: rgba(2, 132, 199, 0.16);
        }
        footer {
            background-color: var(--rc-dark);
            color: #94a3b8;
        }
        footer a {
            color: #cbd5e1;
            text-decoration: none;
            transition: color 150ms ease;
        }
        footer a:hover {
            color: #38bdf8;
        }
    </style>
    @include('partials.motion')
    @stack('styles')
</head>
<body>

    <!-- Navegación Principal -->
    <nav class="navbar navbar-expand-lg navbar-dark site-navbar sticky-top py-3">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('home') }}">
                <span class="d-inline-flex align-items-center justify-content-center p-2 rounded-3 text-white" style="background-color: var(--rc-primary); width: 36px; height: 36px;">
                    <i class="bi bi-broadcast"></i>
                </span>
                <span class="fw-extrabold text-white tracking-tight fs-5">Rivas <span style="color: var(--rc-accent);">Comunicaciones</span></span>
            </a>
            
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" aria-controls="navbarMain" aria-expanded="false" aria-label="Navegación móvil">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarMain">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-3 gap-1">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">Inicio</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('planes.index') ? 'active' : '' }}" href="{{ route('planes.index') }}">Planes</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('promociones.index') ? 'active' : '' }}" href="{{ route('promociones.index') }}">Promociones</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('cobertura') ? 'active' : '' }}" href="{{ route('cobertura') }}">Cobertura</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('nosotros') ? 'active' : '' }}" href="{{ route('nosotros') }}">Nosotros</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('contacto') ? 'active' : '' }}" href="{{ route('contacto') }}">Contacto</a>
                    </li>
                </ul>

                <div class="d-flex align-items-center flex-wrap gap-2">
                    <!-- Formulario Rápido de Consulta de Folio -->
                    <form class="d-flex" role="search" onsubmit="event.preventDefault(); const f = document.getElementById('folioInput').value.trim(); if(f.startsWith('RES-')) { window.location.href='/reservaciones/'+f; } else if(f.startsWith('TKT-')) { window.location.href='/soporte/ticket/'+f; } else if(f) { window.location.href='/reservaciones/'+f; }">
                        <div class="input-group input-group-sm">
                            <input class="form-control text-white bg-transparent border-secondary" style="border-color: rgba(255,255,255,0.2) !important; font-size: 0.85rem;" type="search" id="folioInput" placeholder="Folio (RES/TKT)" aria-label="Folio">
                            <button class="btn btn-outline-light border-secondary" style="border-color: rgba(255,255,255,0.2) !important;" type="submit" title="Buscar Folio"><i class="bi bi-search"></i></button>
                        </div>
                    </form>

                    <!-- Dropdown Soporte / Tickets -->
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-light text-nowrap" style="border-color: rgba(255,255,255,0.2);" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-headset me-1" style="color: var(--rc-accent);"></i> Soporte
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg" style="background: var(--rc-dark-surface); border: 1px solid rgba(255,255,255,0.1) !important;">
                            <li><a class="dropdown-item text-white" href="{{ route('tickets.create') }}"><i class="bi bi-plus-circle me-2" style="color: var(--rc-accent);"></i> Levantar Ticket</a></li>
                            <li><hr class="dropdown-divider border-secondary opacity-25"></li>
                            <li><h6 class="dropdown-header text-uppercase small" style="color: #94a3b8; font-size: 0.7rem; letter-spacing: 0.05em;">Atención Telefónica 24/7</h6></li>
                            <li><span class="dropdown-item-text text-light small fw-semibold"><i class="bi bi-telephone me-2 text-primary"></i> (777) 123-4567</span></li>
                        </ul>
                    </div>

                    <!-- Botón Destacado Solicitar Instalación -->
                    <a href="{{ route('reservaciones.create') }}" class="btn btn-sm text-white text-nowrap fw-bold px-3" style="background-color: var(--rc-primary);">
                        <i class="bi bi-calendar-check me-1"></i> Contratar Ahora
                    </a>

                    <!-- Enlace Admin -->
                    <a href="{{ route('admin.login') }}" class="btn btn-sm btn-outline-light border-0 text-secondary" title="Acceso Administrativo">
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

            <div class="pt-4 mt-5 d-flex flex-column flex-md-row justify-content-between align-items-center small text-secondary" style="border-top: 1px solid rgba(255, 255, 255, 0.08);">
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
