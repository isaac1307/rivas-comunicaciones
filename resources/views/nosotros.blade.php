@extends('layouts.app')

@section('title', 'Nosotros · Ingeniería Regional')
@section('meta_description', 'Conoce a Rivas Comunicaciones: operador regional de fibra óptica FTTH y radioenlaces en Morelos con brigadas propias y atención directa.')

@section('content')
<section class="rc-page-head">
    <div class="rc-wrap rc-page-head__grid">
        <div class="rc-page-head__title">
            <h1 class="rc-lines">
                <span class="rc-line"><span data-line style="--i:0">Ingeniería</span></span>
                <span class="rc-line"><span data-line style="--i:1">local</span></span>
            </h1>
        </div>
        <div class="rc-page-head__desc">
            <p class="rc-lead">
                Rivas Comunicaciones nació en Morelos para sustituir el viejo cableado compartido por tendidos propios de fibra óptica y radioenlaces bien calculados.
            </p>
        </div>
    </div>
</section>

<section class="rc-section rc-section--surface">
    <div class="rc-wrap rc-split">
        <figure class="rc-split__media" data-reveal>
            <div class="rc-media-frame">
                <img
                    class="rc-drift"
                    src="{{ asset('assets/editorial/telecommunications-mast-at-sunset.jpg') }}"
                    width="1920"
                    height="1440"
                    fetchpriority="high"
                    decoding="async"
                    alt="Torre reticulada de telecomunicaciones recortada contra el cielo crepuscular sobre una cresta arbolada"
                >
            </div>
            <figcaption class="rc-caption">Infraestructura troncal y enlaces de cresta operados por personal de planta.</figcaption>
        </figure>

        <div class="rc-split__content" data-reveal style="--s:1">
            <h2>Red propia en campo</h2>
            <p style="margin-top: 1rem;">
                Operar en la topografía de Cuernavaca y sus barrancas exige conocer cada poste, crucero y cerro. En lugar de revender capacidad saturada de terceros, en Rivas Comunicaciones tendemos fibra hasta el domicilio (FTTH) y administramos nuestros propios enrutadores MikroTik en cada sector.
            </p>
            <p class="rc-muted">
                Cuando levantas un reporte o agendas una instalación, no hablas con un centro de llamadas fuera del estado: te responde el mismo equipo técnico que fusiona la fibra, alinea las antenas y monitorea el tráfico diario de la red.
            </p>

            <dl class="rc-dl" style="margin-top: 2.25rem;">
                <div>
                    <dt>Arquitectura urbana</dt>
                    <dd>Fibra óptica pasiva directa hasta la ONT dentro de tu casa</dd>
                </div>
                <div>
                    <dt>Arquitectura rural</dt>
                    <dd>Enlaces punto a punto en frecuencia limpia con línea de vista</dd>
                </div>
                <div>
                    <dt>Gestión de caudal</dt>
                    <dd>Control por nodo MikroTik con aislamiento entre suscriptores</dd>
                </div>
                <div>
                    <dt>Trato comercial</dt>
                    <dd>Mensualidad clara en pesos, sin plazos forzosos</dd>
                </div>
            </dl>
        </div>
    </div>
</section>

<section class="rc-section rc-section--bg">
    <div class="rc-wrap">
        <div class="rc-proof__header">
            <h2 class="rc-proof__title" data-reveal>Criterio de operación</h2>
            <div class="rc-proof__aside" data-reveal style="--s:1">
                <p class="rc-muted">
                    Tres reglas prácticas rigen cada alta nueva y cada mantenimiento preventivo en nuestra red.
                </p>
            </div>
        </div>

        <figure style="margin-bottom: 3rem;" data-reveal style="--s:1">
            <div class="rc-media-frame" style="aspect-ratio: 1920 / 496;">
                <img
                    src="{{ asset('assets/editorial/ribboncutout1.jpg') }}"
                    width="1920"
                    height="496"
                    loading="lazy"
                    decoding="async"
                    alt="Corte transversal de cable troncal de fibra óptica mostrando sus tubos protectores y cintas de vidrio"
                >
            </div>
            <figcaption class="rc-caption">Sección física de cable troncal multifibra empleado en distribución primaria.</figcaption>
        </figure>

        <ul class="rc-caudal__stages" style="margin-bottom: 4rem;">
            <li data-reveal style="--s:0">
                <h3>Tarifa sin letra chica</h3>
                <p class="rc-muted" style="margin-top: 0.4rem;">Publicamos el precio exacto de cada velocidad y entregamos el equipo en comodato mientras mantengas activa tu cuenta.</p>
            </li>
            <li data-reveal style="--s:1">
                <h3>Trazabilidad por folio</h3>
                <p class="rc-muted" style="margin-top: 0.4rem;">Toda instalación (RES-) e incidencia técnica (TKT-) genera un folio auditable para consultar avances en cualquier momento.</p>
            </li>
            <li data-reveal style="--s:2">
                <h3>Brigadas de la región</h3>
                <p class="rc-muted" style="margin-top: 0.4rem;">Nuestros instaladores salen diariamente desde Cuernavaca hacia Jiutepec, Temixco, Zapata y Tepoztlán.</p>
            </li>
        </ul>

        <div class="rc-territory-pair" style="margin-bottom: 0;">
            <figure class="rc-territory-pair__map" data-reveal style="--s:0">
                <div class="rc-media-frame">
                    <img
                        class="rc-drift"
                        src="{{ asset('assets/editorial/cuernavaca-1968.webp') }}"
                        width="1156"
                        height="1812"
                        loading="lazy"
                        decoding="async"
                        alt="Plano cartográfico histórico de las avenidas y barrancas de Cuernavaca trazado en 1968"
                    >
                </div>
                <figcaption class="rc-caption">Presencia de origen en la traza metropolitana de Cuernavaca.</figcaption>
            </figure>

            <figure class="rc-territory-pair__valley" data-reveal style="--s:1">
                <div class="rc-media-frame">
                    <img
                        class="rc-drift"
                        src="{{ asset('assets/editorial/vista-en-la-cima-de-las-montanas.jpg') }}"
                        width="1920"
                        height="2560"
                        loading="lazy"
                        decoding="async"
                        alt="Laderas y formaciones rocosas de la sierra de Loma Bonita en Tepoztlán, Morelos"
                    >
                </div>
                <figcaption class="rc-caption">Ingeniería adaptada al relieve serrano del estado de Morelos.</figcaption>
            </figure>
        </div>
    </div>
</section>

<section aria-label="Contratación directa">
    <a href="{{ route('planes.index') }}" class="rc-cta-band">
        <div class="rc-cta-band__top">
            <h2 class="rc-cta-band__heading">Conoce las tarifas</h2>
            <p class="rc-cta-band__lead">
                Revisa las velocidades disponibles para domicilio, estudio o negocio en Morelos.
            </p>
            <span class="rc-cta-band__arrow-wrap" aria-hidden="true">
                <svg class="rc-cta-band__arrow" viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="3.5">
                    <path d="M10 38L38 10M38 10H14M38 10V34" />
                </svg>
            </span>
        </div>
        <div class="rc-cta-band__word-wrap">
            <span class="rc-cta-band__word">Tarifas</span>
        </div>
    </a>
</section>
@endsection
