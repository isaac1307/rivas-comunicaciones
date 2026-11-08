@extends('layouts.app')

@section('title', 'Promociones Vigentes')
@section('meta_description', 'Beneficios vigentes en instalación de fibra óptica y mensualidades para nuevos contratos en Cuernavaca y Morelos.')

@section('content')
<section class="rc-page-head">
    <div class="rc-wrap rc-page-head__grid">
        <div class="rc-page-head__title">
            <h1 class="rc-lines">
                <span class="rc-line"><span data-line style="--i:0">Promociones</span></span>
                <span class="rc-line"><span data-line style="--i:1">vigentes</span></span>
            </h1>
        </div>
        <div class="rc-page-head__desc">
            <p class="rc-lead">
                Condiciones preferentes de alta y bonificaciones de temporada aplicables al contratar en nuestras zonas activas de Morelos.
            </p>
        </div>
    </div>
</section>

<section class="rc-section rc-section--surface">
    <div class="rc-wrap">
        <div class="rc-proof__header">
            <h2 class="rc-proof__title" data-reveal>Beneficios activos</h2>
            <div class="rc-proof__aside" data-reveal style="--s:1">
                <p class="rc-muted">
                    Cada beneficio queda asentado en tu orden de reservación desde el momento en que agendas en línea.
                </p>
            </div>
        </div>

        @if ($promociones->isNotEmpty())
            <div class="rc-promo-list">
                @foreach ($promociones as $index => $promo)
                    <article class="rc-promo-row" data-reveal style="--s:{{ $index }}">
                        <div class="rc-promo-row__head">
                            <h3>{{ $promo->titulo }}</h3>
                            <p class="rc-muted rc-tabular" style="margin-top: 0.5rem;">
                                Vigente hasta el {{ $promo->fecha_vencimiento->format('d/m/Y') }}
                            </p>
                        </div>

                        <div class="rc-promo-row__body">
                            <p>{{ $promo->descripcion }}</p>

                            @if ($promo->planes->isNotEmpty())
                                <ul class="rc-tag-list rc-tabular">
                                    @foreach ($promo->planes as $p)
                                        <li>
                                            <a class="rc-tag" href="{{ route('reservaciones.create', ['plan_id' => $p->id]) }}">
                                                {{ $p->nombre }} · ${{ number_format((float) $p->precio_mensual, 0) }}/mes
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="rc-muted">Aplicable en todo el catálogo residencial y comercial activo.</p>
                            @endif

                            <div>
                                <a href="{{ route('reservaciones.create') }}" class="rc-btn rc-btn--gold">
                                    Contratar con esta promoción
                                </a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @else
            <div class="rc-split">
                <div class="rc-split__media">
                    <h3>Temporada regular</h3>
                </div>
                <div class="rc-split__content">
                    <p class="rc-muted">
                        En este momento operamos con nuestro tabulador estándar sin cargos forzosos. Consulta los planes vigentes o agenda tu instalación directa.
                    </p>
                    <p style="margin-top: 1.5rem;">
                        <a href="{{ route('planes.index') }}" class="rc-btn rc-btn--gold">Revisar planes vigentes</a>
                    </p>
                </div>
            </div>
        @endif
    </div>
</section>

<section class="rc-section rc-section--bg">
    <div class="rc-wrap">
        <div class="rc-territory-pair">
            <figure class="rc-territory-pair__map" data-reveal style="--s:0">
                <div class="rc-media-frame">
                    <img
                        class="rc-drift"
                        src="{{ asset('assets/editorial/cuernavaca-1968.webp') }}"
                        width="1156"
                        height="1812"
                        loading="lazy"
                        decoding="async"
                        alt="Plano cartográfico histórico de las avenidas y barrancas de Cuernavaca trazado por el servicio geológico en 1968"
                    >
                </div>
                <figcaption class="rc-caption">Cobertura urbana continua en Cuernavaca y área conurbada.</figcaption>
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
                        alt="Vista aérea al anochecer del valle metropolitano de Cuernavaca y Jiutepec iluminado bajo la sierra de Morelos"
                    >
                </div>
                <figcaption class="rc-caption">Atención directa en el valle metropolitano y corredor CIVAC.</figcaption>
            </figure>
        </div>

        <div class="rc-territory-pair" style="margin-bottom: 0;">
            <figure class="rc-territory-pair__map" data-reveal style="--s:0">
                <div class="rc-media-frame">
                    <img
                        src="{{ asset('assets/editorial/fiber-optic-illuminated.jpg') }}"
                        width="1920"
                        height="1203"
                        loading="lazy"
                        decoding="async"
                        alt="Conductor de fibra óptica enrollado sobre fondo oscuro transmitiendo luz láser"
                    >
                </div>
                <figcaption class="rc-caption">Equipo ONT doble banda incluido sin costo de renta adicional.</figcaption>
            </figure>

            <figure class="rc-territory-pair__valley" data-reveal style="--s:1">
                <div class="rc-media-frame">
                    <img
                        src="{{ asset('assets/editorial/vista-en-la-cima-de-las-montanas.jpg') }}"
                        width="1920"
                        height="2560"
                        loading="lazy"
                        decoding="async"
                        alt="Laderas y formaciones rocosas de la sierra de Loma Bonita en Tepoztlán, Morelos"
                    >
                </div>
                <figcaption class="rc-caption">Promociones válidas tanto en red óptica como en enlace rural.</figcaption>
            </figure>
        </div>
    </div>
</section>

<section aria-label="Contratación directa">
    <a href="{{ route('reservaciones.create') }}" class="rc-cta-band">
        <div class="rc-cta-band__top">
            <h2 class="rc-cta-band__heading">Agenda hoy</h2>
            <p class="rc-cta-band__lead">
                Al terminar el periodo promocional pasas a la tarifa mensual estándar sin incrementos ocultos.
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
