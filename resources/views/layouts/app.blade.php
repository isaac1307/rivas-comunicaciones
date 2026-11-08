<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover, interactive-widget=resizes-content">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" media="(prefers-color-scheme: light)" content="#0c0a08">
    <meta name="theme-color" media="(prefers-color-scheme: dark)" content="#0c0a08">
    <title>@yield('title', 'Fibra Óptica en Morelos') | Rivas Comunicaciones</title>
    <meta name="description" content="@yield('meta_description', 'Proveedor regional de internet por fibra óptica FTTH y radioenlace en Morelos. Instalación en 24 a 48 horas sin plazo forzoso.')">

    <script>document.documentElement.classList.add('js');</script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wdth,wght@62..125,100..900&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('css/editorial.css') }}">
    @stack('styles')
</head>
<body class="rc-public">

    <!-- Mobile Navigation Drawer (popover="manual" + horizontal scroll-snap) -->
    <div class="rc-drawer" id="rc-drawer" popover="manual">
        <div class="rc-drawer__scroller">
            <nav class="rc-drawer__sheet" tabindex="-1" aria-label="Navegación móvil">
                <div>
                    <div class="rc-drawer__top">
                        <span class="rc-wordmark">Rivas Comunicaciones</span>
                        <button type="button" class="rc-menu-btn" id="rc-drawer-close">Cerrar</button>
                    </div>
                    <ul class="rc-drawer__list">
                        <li><a class="rc-drawer__link" href="{{ route('home') }}" @if(request()->routeIs('home')) aria-current="page" @endif>Inicio</a></li>
                        <li><a class="rc-drawer__link" href="{{ route('planes.index') }}" @if(request()->routeIs('planes.*')) aria-current="page" @endif>Planes</a></li>
                        <li><a class="rc-drawer__link" href="{{ route('cobertura') }}" @if(request()->routeIs('cobertura')) aria-current="page" @endif>Cobertura</a></li>
                        <li><a class="rc-drawer__link" href="{{ route('promociones.index') }}" @if(request()->routeIs('promociones.*')) aria-current="page" @endif>Promociones</a></li>
                        <li><a class="rc-drawer__link" href="{{ route('nosotros') }}" @if(request()->routeIs('nosotros')) aria-current="page" @endif>Nosotros</a></li>
                        <li><a class="rc-drawer__link" href="{{ route('tickets.create') }}" @if(request()->routeIs('tickets.*')) aria-current="page" @endif>Soporte</a></li>
                        <li><a class="rc-drawer__link" href="{{ route('contacto') }}" @if(request()->routeIs('contacto')) aria-current="page" @endif>Contacto</a></li>
                    </ul>
                </div>
                <div>
                    <a href="{{ route('reservaciones.create') }}" class="rc-btn rc-btn--gold rc-btn--full">
                        <span>Contratar servicio</span>
                        <span aria-hidden="true">→</span>
                    </a>
                </div>
            </nav>
        </div>
    </div>

    <header class="rc-header">
        <div class="rc-wrap rc-header__inner">
            <a class="rc-wordmark" href="{{ route('home') }}">Rivas Comunicaciones</a>

            <ul class="rc-nav" aria-label="Navegación principal">
                <li><a class="rc-nav-link" href="{{ route('planes.index') }}" @if(request()->routeIs('planes.*')) aria-current="page" @endif>Planes</a></li>
                <li><a class="rc-nav-link" href="{{ route('cobertura') }}" @if(request()->routeIs('cobertura')) aria-current="page" @endif>Cobertura</a></li>
                <li><a class="rc-nav-link" href="{{ route('promociones.index') }}" @if(request()->routeIs('promociones.*')) aria-current="page" @endif>Promociones</a></li>
                <li><a class="rc-nav-link" href="{{ route('nosotros') }}" @if(request()->routeIs('nosotros')) aria-current="page" @endif>Nosotros</a></li>
                <li><a class="rc-nav-link" href="{{ route('tickets.create') }}" @if(request()->routeIs('tickets.*')) aria-current="page" @endif>Soporte</a></li>
                <li><a class="rc-nav-link" href="{{ route('contacto') }}" @if(request()->routeIs('contacto')) aria-current="page" @endif>Contacto</a></li>
            </ul>

            <div class="rc-header__actions">
                <a href="{{ route('reservaciones.create') }}" class="rc-btn rc-btn--gold rc-header__cta-desktop">Contratar</a>
                <button
                    type="button"
                    class="rc-menu-btn"
                    id="rc-drawer-open"
                    aria-label="Menú"
                    aria-expanded="false"
                    aria-controls="rc-drawer"
                >Menú</button>
            </div>
        </div>
    </header>

    @if (session('success') || session('error') || $errors->any())
        <div class="rc-wrap" role="region" aria-live="polite">
            @if (session('success'))
                <div class="rc-flash rc-flash--success">{{ session('success') }}</div>
            @endif

            @if (session('error'))
                <div class="rc-flash rc-flash--error">{{ session('error') }}</div>
            @endif

            @if ($errors->any())
                <div class="rc-flash rc-flash--error">
                    <strong>Revisa los campos marcados antes de continuar:</strong>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    @endif

    <main class="rc-main">
        @yield('content')
        {{ $slot ?? '' }}
    </main>

    <footer class="rc-footer">
        <div class="rc-wrap">
            <div class="rc-footer__monument" data-reveal>Rivas Comunicaciones</div>

            <div class="rc-footer__meta">
                <div class="rc-footer__facts" data-reveal style="--s:1">
                    <p>Av. Telecomunicaciones 100, Cuernavaca, Morelos.</p>
                    <p class="rc-muted">Lunes a sábado 8:00–20:00 · Teléfono (777)&nbsp;123-4567 · contacto@rivascomunicaciones.com</p>
                </div>

                <ul class="rc-footer__links" data-reveal style="--s:2">
                    <li><a class="rc-link" href="{{ route('planes.index') }}">Planes</a></li>
                    <li><a class="rc-link" href="{{ route('cobertura') }}">Cobertura</a></li>
                    <li><a class="rc-link" href="{{ route('promociones.index') }}">Promociones</a></li>
                    <li><a class="rc-link" href="{{ route('nosotros') }}">Nosotros</a></li>
                    <li><a class="rc-link" href="{{ route('reservaciones.create') }}">Agendar instalación</a></li>
                    <li><a class="rc-link" href="{{ route('tickets.create') }}">Levantar ticket</a></li>
                    <li><a class="rc-link" href="{{ route('contacto') }}">Contacto</a></li>
                    @auth
                        <li><a class="rc-link" href="{{ route(auth()->user()->rutaPanel()) }}">Panel operativo</a></li>
                    @else
                        <li><a class="rc-link" href="{{ route('admin.login') }}">Acceso personal</a></li>
                    @endauth
                </ul>
            </div>

            <p class="rc-credits">
                Fotografía y metraje documental:
                <a href="https://commons.wikimedia.org/wiki/File:Zona_metropolitana_de_Cuernavaca,_vista_CIVAC_(2019).jpg">“Zona metropolitana de Cuernavaca, vista CIVAC (2019)”</a> por DocsMX, <a href="https://creativecommons.org/licenses/by-sa/4.0">CC BY-SA 4.0</a>;
                <a href="https://commons.wikimedia.org/wiki/File:Cuernavaca_1968.webp">“Cuernavaca 1968”</a> por USGS, <a href="https://creativecommons.org/publicdomain/mark/1.0/">dominio público</a>;
                <a href="https://commons.wikimedia.org/wiki/File:Vista_en_la_cima_de_las_monta%C3%B1as_de_Loma_Bonita.JPG">“Vista en la cima de las montañas de Loma Bonita”</a> por Capucine Byron, <a href="https://creativecommons.org/licenses/by-sa/4.0">CC BY-SA 4.0</a>;
                <a href="https://commons.wikimedia.org/wiki/File:Telecommunications_mast_at_sunset.jpg">“Telecommunications mast at sunset”</a> por W.carter, <a href="https://creativecommons.org/licenses/by-sa/4.0">CC BY-SA 4.0</a>;
                <a href="https://commons.wikimedia.org/wiki/File:RibbonCutout1.jpg">“RibbonCutout1”</a> por Infestor, <a href="https://creativecommons.org/licenses/by-sa/4.0">CC BY-SA 4.0</a>;
                <a href="https://commons.wikimedia.org/w/index.php?curid=4199186">“Fiber optic illuminated”</a> por Hustvedt, <a href="https://creativecommons.org/licenses/by-sa/3.0/">CC BY-SA 3.0</a>;
                <a href="https://commons.wikimedia.org/wiki/File:Lenticular_cloud_over_Longs_Peak,_Colorado_(time_lapse).ogv">“Lenticular cloud over Longs Peak, Colorado (time lapse)”</a> por Stepleton, <a href="https://creativecommons.org/publicdomain/zero/1.0/deed.en">CC0</a>, vía Wikimedia Commons.
                © {{ date('Y') }} Rivas Comunicaciones.
            </p>
        </div>
    </footer>

    <script src="{{ asset('js/editorial.js') }}" defer></script>
    @stack('scripts')
</body>
</html>
