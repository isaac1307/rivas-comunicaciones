@extends('layouts.app')

@section('title', 'Nosotros - Acerca de Rivas Comunicaciones')

@section('content')
<div class="container py-5">
    <!-- Header -->
    <div class="text-center mb-5">
        <h1 class="display-5 fw-extrabold text-dark tracking-tight mb-3">Conectando Morelos con Tecnología y Calidez</h1>
        <p class="lead text-muted mx-auto" style="max-width: 720px; font-size: 1.15rem; line-height: 1.7;">
            Somos Rivas Comunicaciones, un Proveedor de Servicios de Internet (ISP) regional comprometido con llevar fibra óptica de alta velocidad y soporte técnico humano directo a tu puerta.
        </p>
    </div>

    <!-- Historia y Compromiso con imagen -->
    <div class="row align-items-center g-5 mb-5 py-3">
        <div class="col-lg-6">
            <h2 class="display-6 fw-extrabold text-dark tracking-tight mb-3">Nuestra Trayectoria</h2>
            <p class="text-muted" style="font-size: 1.05rem; line-height: 1.7;">
                Nacimos como una iniciativa local para solucionar la falta de conectividad confiable en nuestra región. Frente a las limitaciones del cable coaxial de las compañías tradicionales, decidimos desplegar infraestructura propia de fibra óptica dedicada hasta el hogar (FTTH).
            </p>
            <p class="text-muted" style="font-size: 1.05rem; line-height: 1.7;">
                Hoy contamos con una red moderna, redundante y monitoreada 24/7 por ingenieros y técnicos locales que conocen y entienden las necesidades de nuestras familias y negocios.
            </p>

            <div class="row g-3 mt-4">
                <div class="col-4">
                    <div class="p-3 bg-white rounded-3 text-center" style="border: 1px solid var(--rc-border);">
                        <div class="display-6 fw-extrabold text-primary mb-1">99.8%</div>
                        <span class="text-muted small fw-medium">Uptime Red</span>
                    </div>
                </div>
                <div class="col-4">
                    <div class="p-3 bg-white rounded-3 text-center" style="border: 1px solid var(--rc-border);">
                        <div class="display-6 fw-extrabold text-primary mb-1">100%</div>
                        <span class="text-muted small fw-medium">Fibra Óptica</span>
                    </div>
                </div>
                <div class="col-4">
                    <div class="p-3 bg-white rounded-3 text-center" style="border: 1px solid var(--rc-border);">
                        <div class="display-6 fw-extrabold text-primary mb-1">24/7</div>
                        <span class="text-muted small fw-medium">Soporte Local</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="rounded-4 overflow-hidden mb-4" style="height: 280px; border: 1px solid var(--rc-border);">
                <img src="https://images.unsplash.com/photo-1558494949-ef010cbdcc31?auto=format&fit=crop&w=800&q=80" 
                     alt="Infraestructura de red y servidores de Rivas Comunicaciones" 
                     class="w-100 h-100 object-fit-cover"
                     width="800"
                     height="500">
            </div>

            <div class="p-4 rounded-4 text-white" style="background-color: var(--rc-dark); border: 1px solid rgba(255,255,255,0.08);">
                <div class="d-flex gap-3 mb-3">
                    <div class="fs-4 text-info"><i class="bi bi-bullseye"></i></div>
                    <div>
                        <h5 class="fw-bold mb-1 text-white">Nuestra Misión</h5>
                        <p class="small text-light opacity-90 mb-0" style="line-height: 1.6;">Brindar acceso a internet simétrico de alta velocidad con la mejor estabilidad y soporte técnico personalizado, impulsando el desarrollo y bienestar de Morelos.</p>
                    </div>
                </div>
                <div class="d-flex gap-3 pt-3" style="border-top: 1px solid rgba(255,255,255,0.1);">
                    <div class="fs-4 text-info"><i class="bi bi-eye-fill"></i></div>
                    <div>
                        <h5 class="fw-bold mb-1 text-white">Nuestra Visión</h5>
                        <p class="small text-light opacity-90 mb-0" style="line-height: 1.6;">Consolidarnos como el ISP referente en la región, reconocidos por nuestra innovación tecnológica, transparencia comercial y trato humano cercano.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Valores Fundamentales -->
    <div class="my-5 py-4">
        <div class="text-center mb-5">
            <h2 class="display-6 fw-extrabold text-dark tracking-tight mb-2">Nuestros Valores Fundamentales</h2>
            <p class="text-muted" style="font-size: 1.05rem;">Principios que guían nuestro trabajo técnico y atención diaria.</p>
        </div>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="p-4 bg-white rounded-4 h-100" style="border: 1px solid var(--rc-border);">
                    <div class="d-inline-flex align-items-center justify-content-center p-3 rounded-3 mb-3" style="background-color: #f0fdf4; color: #16a34a; font-size: 1.5rem;">
                        <i class="bi bi-gem"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-2">Transparencia Total</h5>
                    <p class="text-muted small mb-0" style="font-size: 0.95rem; line-height: 1.6;">Precios claros con IVA incluido, sin cobros sorpresa, letras pequeñas ni contratos con cláusulas forzosas.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-4 bg-white rounded-4 h-100" style="border: 1px solid var(--rc-border);">
                    <div class="d-inline-flex align-items-center justify-content-center p-3 rounded-3 mb-3" style="background-color: #eff6ff; color: #2563eb; font-size: 1.5rem;">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-2">Cercanía y Empatía</h5>
                    <p class="text-muted small mb-0" style="font-size: 0.95rem; line-height: 1.6;">Atención técnica directa. Cuando te comunicas, te atiende un técnico de la región sin conmutadores automatizados interminables.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-4 bg-white rounded-4 h-100" style="border: 1px solid var(--rc-border);">
                    <div class="d-inline-flex align-items-center justify-content-center p-3 rounded-3 mb-3" style="background-color: #f8fafc; color: #0284c7; font-size: 1.5rem; border: 1px solid #e2e8f0;">
                        <i class="bi bi-gear-wide-connected"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-2">Innovación Continua</h5>
                    <p class="text-muted small mb-0" style="font-size: 0.95rem; line-height: 1.6;">Inversión constante en servidores locales, anillos troncales de fibra y enrutadores Gigabit de última generación.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
