@extends('layouts.app')

@section('title', 'Nosotros - Acerca de Rivas Comunicaciones')

@section('content')
<div class="container py-5">
    <!-- Header -->
    <div class="text-center mb-5">
        <span class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill fw-bold text-uppercase">Nuestra Empresa</span>
        <h1 class="display-5 fw-bold mt-2">Conectando Comunidades con Pasión y Excelencia</h1>
        <p class="lead text-muted mx-auto" style="max-width: 750px;">
            Somos Rivas Comunicaciones, un Proveedor de Servicios de Internet (ISP) local comprometido con cerrar la brecha digital mediante tecnología de vanguardia y atención humana.
        </p>
    </div>

    <!-- Historia y Compromiso -->
    <div class="row align-items-center g-5 mb-5 py-4">
        <div class="col-lg-6">
            <h3 class="fw-bold mb-3 text-primary">Nuestra Trayectoria</h3>
            <p class="text-muted">
                Nacimos como una iniciativa orientada a resolver la falta de conectividad confiable en nuestra región. Frente a las limitaciones de las grandes compañías telefónicas tradicionales, decidimos desplegar infraestructura propia de fibra óptica hasta el hogar (FTTH).
            </p>
            <p class="text-muted">
                Hoy contamos con una red moderna, redundante y monitoreada las 24 horas del día por ingenieros locales que conocen las necesidades reales de nuestras familias y negocios.
            </p>
            <div class="row g-3 mt-2">
                <div class="col-6">
                    <div class="border rounded-3 p-3 bg-white text-center">
                        <div class="display-6 fw-bold text-primary mb-1">99.8%</div>
                        <span class="text-muted small">Disponibilidad de Red</span>
                    </div>
                </div>
                <div class="col-6">
                    <div class="border rounded-3 p-3 bg-white text-center">
                        <div class="display-6 fw-bold text-primary mb-1">100%</div>
                        <span class="text-muted small">Fibra Óptica Dedicada</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-primary text-white p-4">
                <div class="card-body">
                    <h4 class="fw-bold mb-4 text-white"><i class="bi bi-award-fill me-2"></i> Nuestros Principios</h4>
                    <div class="d-flex gap-3 mb-3">
                        <div class="fs-4"><i class="bi bi-bullseye"></i></div>
                        <div>
                            <h5 class="fw-bold mb-1 text-white">Misión</h5>
                            <p class="small text-light opacity-90 mb-0">Brindar acceso a internet de alta velocidad con la mejor estabilidad y soporte técnico personalizado, impulsando la productividad y el entretenimiento de nuestra comunidad.</p>
                        </div>
                    </div>
                    <hr class="border-light opacity-25">
                    <div class="d-flex gap-3 mb-3">
                        <div class="fs-4"><i class="bi bi-eye-fill"></i></div>
                        <div>
                            <h5 class="fw-bold mb-1 text-white">Visión</h5>
                            <p class="small text-light opacity-90 mb-0">Consolidarnos como el ISP referente en la región, reconocidos por nuestra innovación tecnológica, transparencia comercial y excelencia en el servicio al cliente.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Valores -->
    <div class="row g-4 text-center my-4">
        <div class="col-12 mb-2">
            <h3 class="fw-bold">Nuestros Valores Fundamentales</h3>
        </div>
        <div class="col-md-4">
            <div class="p-4 bg-white rounded-4 shadow-sm border h-100">
                <div class="badge bg-primary-subtle text-primary p-3 rounded-circle fs-3 mb-3">
                    <i class="bi bi-gem"></i>
                </div>
                <h5 class="fw-bold">Transparencia</h5>
                <p class="text-muted small mb-0">Precios claros sin cobros ocultos, letras pequeñas ni cláusulas abusivas en tus mensualidades.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="p-4 bg-white rounded-4 shadow-sm border h-100">
                <div class="badge bg-success-subtle text-success p-3 rounded-circle fs-3 mb-3">
                    <i class="bi bi-people-fill"></i>
                </div>
                <h5 class="fw-bold">Cercanía</h5>
                <p class="text-muted small mb-0">Atención cálida y directa. Cuando llamas, hablas con un técnico local y no con grabaciones automatizadas.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="p-4 bg-white rounded-4 shadow-sm border h-100">
                <div class="badge bg-warning-subtle text-warning p-3 rounded-circle fs-3 mb-3">
                    <i class="bi bi-gear-wide-connected"></i>
                </div>
                <h5 class="fw-bold">Innovación Continua</h5>
                <p class="text-muted small mb-0">Inversión constante en servidores, enlaces de fibra troncal y equipamiento de última generación.</p>
            </div>
        </div>
    </div>
</div>
@endsection
