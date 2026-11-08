@extends('layouts.app')

@section('title', 'Planes y Tarifas de Fibra Óptica')
@section('meta_description', 'Tabulador de planes de internet por fibra óptica FTTH y radioenlace en Morelos. Mensualidad fija sin plazo forzoso y router doble banda incluido.')

@section('content')
<section class="rc-page-head">
    <div class="rc-wrap rc-page-head__grid">
        <div class="rc-page-head__title">
            <h1 class="rc-lines">
                <span class="rc-line"><span data-line style="--i:0">Planes y</span></span>
                <span class="rc-line"><span data-line style="--i:1">tarifas</span></span>
            </h1>
        </div>
        <div class="rc-page-head__desc">
            <p class="rc-lead">
                Mensualidad fija en pesos mexicanos con equipo Wi-Fi doble banda en comodato. Sin cláusulas de permanencia ni cargos ocultos al corte.
            </p>
        </div>
    </div>

    <div class="rc-wrap" style="margin-top: 2.5rem;">
        <figure class="rc-shutter">
            <div class="rc-media-frame" style="aspect-ratio: 1920 / 496;">
                <img
                    src="{{ asset('assets/editorial/ribboncutout1.jpg') }}"
                    width="1920"
                    height="496"
                    fetchpriority="high"
                    decoding="async"
                    alt="Corte transversal de cable troncal de fibra óptica mostrando sus tubos protectores y cintas de vidrio"
                >
            </div>
        </figure>
    </div>
</section>

<section class="rc-section rc-section--bg" style="padding-top: 1.5rem;">
    <div class="rc-wrap">
        <div class="rc-pricing__intro">
            <div class="rc-pricing__head" data-reveal>
                <h2>Escala mensual</h2>
            </div>
            <div class="rc-pricing__sub" data-reveal style="--s:1">
                <p class="rc-muted">
                    Cada nivel entrega caudal dedicado hasta tu router principal y permite subir o bajar de velocidad al corte mensual sin penalización.
                </p>
            </div>
        </div>

        @if ($planes->isNotEmpty())
            <div class="rc-steps-row">
                @foreach ($planes as $index => $plan)
                    @php
                        $stepNum = min(4, ($index % 4) + 1);
                    @endphp
                    <article
                        class="rc-plan-tile rc-plan-tile--step-{{ $stepNum }}"
                        data-reveal
                        style="--s:{{ $index }}"
                    >
                        <div class="rc-plan-tile__body">
                            <h3>{{ $plan->nombre }}</h3>
                            <div class="rc-plan-tile__price">
                                ${{ number_format((float) $plan->precio_mensual, 0) }}<span class="rc-plan-tile__unit">/ mes</span>
                            </div>

                            <ul class="rc-plan-tile__specs rc-tabular">
                                <li><strong>{{ $plan->velocidad_descarga }}&nbsp;Mbps</strong> de descarga directa</li>
                                <li><strong>{{ $plan->velocidad_carga }}&nbsp;Mbps</strong> de subida sostenida</li>
                                <li>{{ $plan->descripcion ?: 'Tendido óptico dedicado hasta tu domicilio.' }}</li>
                                <li>Router Wi-Fi doble banda (2.4 y 5&nbsp;GHz) en comodato</li>
                                <li>Navegación continua sin tope mensual de transferencia</li>
                                @if ($plan->promociones->isNotEmpty())
                                    @foreach ($plan->promociones as $promo)
                                        <li><strong>Beneficio activo:</strong> {{ $promo->titulo }}</li>
                                    @endforeach
                                @endif
                            </ul>
                        </div>

                        <a href="{{ route('reservaciones.create', ['plan_id' => $plan->id]) }}" class="rc-plan-tile__action">
                            <span>Contratar plan</span>
                            <span aria-hidden="true">→</span>
                        </a>
                    </article>
                @endforeach
            </div>
        @else
            <div class="rc-split">
                <div class="rc-split__media">
                    <h3>Tabulador en actualización</h3>
                </div>
                <div class="rc-split__content">
                    <p class="rc-muted">
                        Estamos renovando las capacidades de acceso para esta temporada en Morelos. Solicita cotización directa para hogar, taller o comercio.
                    </p>
                    <p style="margin-top: 1.5rem;">
                        <a href="{{ route('contacto') }}" class="rc-btn rc-btn--gold">Solicitar cotización</a>
                    </p>
                </div>
            </div>
        @endif
    </div>
</section>

<section class="rc-section rc-section--surface">
    <div class="rc-wrap">
        <div class="rc-proof__header">
            <h2 class="rc-proof__title" data-reveal>Condiciones técnicas</h2>
            <div class="rc-proof__aside" data-reveal style="--s:1">
                <p class="rc-muted">
                    Todos los paquetes comparten la misma infraestructura óptica y el mismo respaldo regional de nuestras cuadrillas en Morelos.
                </p>
            </div>
        </div>

        <table class="rc-ledger rc-tabular" data-reveal style="--s:1">
            <thead>
                <tr>
                    <th scope="col">Concepto</th>
                    <th scope="col">Especificación entregada</th>
                    <th scope="col">Modalidad</th>
                    <th scope="col">Costo adicional</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="rc-ledger__zone">Equipo interior</td>
                    <td>Terminal óptica ONT o CPE exterior con Wi-Fi doble banda</td>
                    <td>Comodato durante contrato activo</td>
                    <td>$0 MXN</td>
                </tr>
                <tr>
                    <td class="rc-ledger__zone">Tendido exterior</td>
                    <td>Acometida desde caja de distribución en poste hasta roseta</td>
                    <td>Instalación por brigada propia</td>
                    <td>Según promoción</td>
                </tr>
                <tr>
                    <td class="rc-ledger__zone">Política de tráfico</td>
                    <td>Caudal ilimitado sin reducción por consumo mensual</td>
                    <td>Residencial y comercial</td>
                    <td>$0 MXN</td>
                </tr>
                <tr>
                    <td class="rc-ledger__zone">Permanencia</td>
                    <td>Contrato abierto con ajuste de velocidad en cualquier corte</td>
                    <td>Sin plazo forzoso</td>
                    <td>$0 MXN</td>
                </tr>
            </tbody>
        </table>

        <div class="rc-territory-pair" style="margin-bottom: 0;">
            <figure class="rc-territory-pair__map" data-reveal style="--s:0">
                <div class="rc-media-frame">
                    <img
                        class="rc-drift"
                        src="{{ asset('assets/editorial/fiber-optic-illuminated.jpg') }}"
                        width="1920"
                        height="1203"
                        loading="lazy"
                        decoding="async"
                        alt="Conductor de fibra óptica enrollado sobre fondo oscuro transmitiendo luz láser en ambos extremos"
                    >
                </div>
                <figcaption class="rc-caption">Transmisión óptica directa hacia cada suscriptor.</figcaption>
            </figure>

            <figure class="rc-territory-pair__valley" data-reveal style="--s:1">
                <div class="rc-media-frame">
                    <img
                        class="rc-drift"
                        src="{{ asset('assets/editorial/zona-metropolitana-de-cuernavaca-vista-civac-2019.jpg') }}"
                        width="1341"
                        height="893"
                        loading="lazy"
                        decoding="async"
                        alt="Vista aérea nocturna del valle de Cuernavaca y el corredor industrial CIVAC en Jiutepec"
                    >
                </div>
                <figcaption class="rc-caption">Red metropolitana activa en Cuernavaca, Jiutepec y Temixco.</figcaption>
            </figure>
        </div>
    </div>
</section>

<section class="rc-showcase" aria-label="Enlace rural complementario">
    <div class="rc-showcase__frame">
        <video
            class="rc-showcase__bg"
            data-loop-video
            autoplay
            muted
            loop
            playsinline
            preload="metadata"
            poster="{{ asset('assets/editorial/lenticular-cloud-over-longs-peak-colorado-time-poster.jpg') }}"
            width="854"
            height="480"
            aria-hidden="true"
        >
            <source src="{{ asset('assets/editorial/lenticular-cloud-over-longs-peak-colorado-time.webm') }}" type="video/webm">
        </video>
        <div class="rc-wrap rc-showcase__grid">
            <article class="rc-showcase__card" data-reveal>
                <div class="rc-showcase__card-body">
                    <h2>Enlace rural</h2>
                    <p>Donde aún no llega el poste óptico, habilitamos enlaces dedicados en línea de vista con la misma tarifa clara.</p>
                    <figure class="rc-showcase__thumb">
                        <img
                            src="{{ asset('assets/editorial/telecommunications-mast-at-sunset.jpg') }}"
                            width="1920"
                            height="1440"
                            loading="lazy"
                            decoding="async"
                            alt="Torre reticulada de telecomunicaciones recortada contra el cielo crepuscular"
                        >
                    </figure>
                </div>
                <a href="{{ route('cobertura') }}" class="rc-showcase__card-foot">
                    <span>Ver cobertura completa</span>
                    <span aria-hidden="true">→</span>
                </a>
            </article>
        </div>
    </div>
</section>

<section aria-label="Contratación directa">
    <a href="{{ route('reservaciones.create') }}" class="rc-cta-band">
        <div class="rc-cta-band__top">
            <h2 class="rc-cta-band__heading">Agenda hoy</h2>
            <p class="rc-cta-band__lead">
                Aparta tu fecha de instalación en línea y recibe tu folio de seguimiento al instante.
            </p>
            <span class="rc-cta-band__arrow-wrap" aria-hidden="true">
                <svg class="rc-cta-band__arrow" viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="3.5">
                    <path d="M10 38L38 10M38 10H14M38 10V34" />
                </svg>
            </span>
        </div>
        <div class="rc-cta-band__word-wrap">
            <span class="rc-cta-band__word">Contratar</span>
        </div>
    </a>
</section>
@endsection
