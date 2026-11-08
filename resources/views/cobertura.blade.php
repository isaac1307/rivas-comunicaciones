@extends('layouts.app')

@section('title', 'Zonas de Cobertura')
@section('meta_description', 'Mapa operativo y Zonas de Cobertura de fibra óptica FTTH y radioenlace de Rivas Comunicaciones en Cuernavaca, Jiutepec, Temixco, Tepoztlán y Morelos.')

@section('content')
<section class="rc-page-head">
    <div class="rc-wrap rc-page-head__grid">
        <div class="rc-page-head__title">
            <h1 class="rc-lines">
                <span class="rc-line"><span data-line style="--i:0">Zonas de</span></span>
                <span class="rc-line"><span data-line style="--i:1">Cobertura</span></span>
            </h1>
        </div>
        <div class="rc-page-head__desc">
            <p class="rc-lead">
                Zonas de Cobertura activas con fibra óptica hasta el hogar (FTTH) y enlaces inalámbricos de línea de vista en seis municipios de Morelos.
            </p>
        </div>
    </div>
</section>

<section class="rc-section rc-section--surface">
    <div class="rc-wrap">
        <div class="rc-territory-pair" style="margin-bottom: 4rem;">
            <figure class="rc-territory-pair__map" data-reveal style="--s:0">
                <div class="rc-media-frame">
                    <img
                        class="rc-drift"
                        src="{{ asset('assets/editorial/cuernavaca-1968.webp') }}"
                        width="1156"
                        height="1812"
                        fetchpriority="high"
                        decoding="async"
                        alt="Plano cartográfico histórico de las avenidas y barrancas de Cuernavaca trazado por el servicio geológico en 1968"
                    >
                </div>
                <figcaption class="rc-caption">Traza histórica de Cuernavaca (USGS, 1968), nodo central de nuestros anillos ópticos urbanos.</figcaption>
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
                <figcaption class="rc-caption">Corredor metropolitano Cuernavaca–CIVAC atendido por brigadas propias.</figcaption>
            </figure>
        </div>

        <div class="rc-proof__header">
            <h2 class="rc-proof__title" data-reveal>Padrón territorial</h2>
            <div class="rc-proof__aside" data-reveal style="--s:1">
                <p class="rc-muted">
                    Tiempos reales de tendido según disponibilidad de caja óptica en poste o requerimiento de mástil para línea de vista.
                </p>
            </div>
        </div>

        <table class="rc-ledger rc-tabular" data-reveal style="--s:1">
            <thead>
                <tr>
                    <th scope="col">Corredor</th>
                    <th scope="col">Colonias y sectores atendidos</th>
                    <th scope="col">Tecnología disponible</th>
                    <th scope="col">Alta estimada</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="rc-ledger__zone">Cuernavaca</td>
                    <td>Centro Histórico, Las Palmas, San Juan, Los Arcos, Reforma</td>
                    <td>Fibra óptica FTTH directa</td>
                    <td>24 a 36 horas</td>
                </tr>
                <tr>
                    <td class="rc-ledger__zone"></td>
                    <td>Vista Hermosa, Teopanzolco, Bellavista, Lomas de Guadalupe, Plan de Ayala</td>
                    <td>Fibra óptica FTTH residencial y pyme</td>
                    <td>24 a 36 horas</td>
                </tr>
                <tr class="rc-ledger__spacer" aria-hidden="true"><td colspan="4"></td></tr>
                <tr>
                    <td class="rc-ledger__zone">Oriente e Industrial</td>
                    <td>Jiutepec cabecera, CIVAC, Parque Industrial San Carlos, Las Torres</td>
                    <td>Fibra óptica FTTH simétrica y enlace dedicado</td>
                    <td>24 a 36 horas</td>
                </tr>
                <tr>
                    <td class="rc-ledger__zone"></td>
                    <td>Tepoztlán cabecera, lomas y corredor Yautepec</td>
                    <td>Radioenlace punto a punto en altura</td>
                    <td>24 a 48 horas</td>
                </tr>
                <tr class="rc-ledger__spacer" aria-hidden="true"><td colspan="4"></td></tr>
                <tr>
                    <td class="rc-ledger__zone">Conurbación Sur</td>
                    <td>Temixco Centro, Burgos, Emiliano Zapata y Xochitepec</td>
                    <td>Fibra óptica FTTH y nodo híbrido</td>
                    <td>24 a 48 horas</td>
                </tr>
                <tr>
                    <td class="rc-ledger__zone">En ampliación</td>
                    <td>Mirador del Sol, San Mateo Tepetate y Campo Verde</td>
                    <td>Factibilidad bajo visita técnica previa</td>
                    <td>Programada</td>
                </tr>
            </tbody>
        </table>
    </div>
</section>

<section class="rc-section rc-section--bg">
    <div class="rc-wrap">
        <div class="rc-territory-pair">
            <figure class="rc-territory-pair__map" data-reveal style="--s:0">
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
                <figcaption class="rc-caption">Cobertura serrana mediante radioenlaces alineados a nuestras torres.</figcaption>
            </figure>

            <figure class="rc-territory-pair__valley" data-reveal style="--s:1">
                <div class="rc-media-frame">
                    <img
                        src="{{ asset('assets/editorial/telecommunications-mast-at-sunset.jpg') }}"
                        width="1920"
                        height="1440"
                        loading="lazy"
                        decoding="async"
                        alt="Torre reticulada de telecomunicaciones recortada contra el cielo crepuscular"
                    >
                </div>
                <figcaption class="rc-caption">Estaciones repetidoras propias con respaldo eléctrico autónomo.</figcaption>
            </figure>
        </div>

        <div class="rc-split">
            <div class="rc-split__media" data-reveal>
                <h2>Factibilidad en calle</h2>
            </div>
            <div class="rc-split__content" data-reveal style="--s:1">
                <p class="rc-lead">
                    ¿Tu colonia o privada aún no figura en el listado? Nuestros técnicos verifican distancia a caja de distribución óptica o línea de vista hacia torre sin costo de revisión.
                </p>
                <div style="display: flex; flex-wrap: wrap; gap: 0.85rem; margin-top: 1.75rem;">
                    <a href="{{ route('reservaciones.create', ['tipo_servicio' => 'visita_tecnica']) }}" class="rc-btn rc-btn--gold">
                        Solicitar visita de factibilidad
                    </a>
                    <a href="{{ route('contacto') }}" class="rc-btn">
                        Escribir a ingeniería
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<section aria-label="Contratación directa">
    <a href="{{ route('reservaciones.create') }}" class="rc-cta-band">
        <div class="rc-cta-band__top">
            <h2 class="rc-cta-band__heading">Agenda hoy</h2>
            <p class="rc-cta-band__lead">
                Confirmamos disponibilidad exacta en tu domicilio y programamos cuadrilla en menos de 48&nbsp;horas.
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
