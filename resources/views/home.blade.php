@extends('layouts.app')

@section('title', 'Fibra Óptica en Morelos')
@section('meta_description', 'Internet por fibra óptica FTTH y radioenlace en Cuernavaca, Jiutepec, Temixco y Morelos. Instalación local en 24 a 48 horas sin plazo forzoso.')

@section('content')
@php
    $planesJson = $planesDestacados->map(fn ($p) => [
        'id' => $p->id,
        'name' => $p->nombre,
        'down' => (int) $p->velocidad_descarga,
        'up' => (int) $p->velocidad_carga,
        'price' => number_format((float) $p->precio_mensual, 0),
        'url' => route('reservaciones.create', ['plan_id' => $p->id]),
    ])->values();
    $planInicial = $planesDestacados->firstWhere('velocidad_descarga', '>=', 100) ?? $planesDestacados->first();
@endphp

<!-- 0. HERO — Shouted headline, rising photograph -->
<section class="rc-hero rc-wrap">
    <div class="rc-hero__headline-wrap">
        <h1 class="rc-hero__title rc-lines">
            <span class="rc-line"><span data-line style="--i:0">Fibra óptica</span></span>
            <span class="rc-line"><span data-line style="--i:1">en Morelos</span></span>
        </h1>
    </div>

    <div class="rc-hero__stage">
        <div class="rc-hero__left" data-load style="--d:340ms">
            <p class="rc-measure">
                Tendido directo hasta tu domicilio por Rivas Comunicaciones. Brigadas propias instalan en 24 a 48&nbsp;horas en seis municipios del estado, sin plazo forzoso.
            </p>
        </div>

        <figure class="rc-hero__photo rc-shutter">
            <img
                src="{{ asset('assets/editorial/zona-metropolitana-de-cuernavaca-vista-civac-2019.jpg') }}"
                width="1341"
                height="893"
                fetchpriority="high"
                decoding="async"
                alt="Vista aérea al anochecer del valle metropolitano de Cuernavaca y Jiutepec iluminado bajo la sierra de Morelos"
            >
        </figure>

        <div class="rc-hero__right" data-load style="--d:420ms">
            <p class="rc-muted rc-tabular">
                Desde ${{ $planesDestacados->count() ? number_format((float) $planesDestacados->min('precio_mensual'), 0) : '349' }} al mes · Cuernavaca y valle
            </p>
            <a href="{{ route('reservaciones.create') }}" class="rc-btn rc-btn--gold">Contratar plan</a>
        </div>
    </div>
</section>

<!-- 1. SHOWCASE — Close-up with column card (two full-bleed chapters, card steps col 2 -> col 3) -->
<section class="rc-showcase" aria-label="Infraestructura óptica y rural">
    <!-- Frame 1: Direct FTTH Optical Fiber -->
    <div class="rc-showcase__frame">
        <img
            class="rc-showcase__bg rc-drift"
            src="{{ asset('assets/editorial/fiber-optic-illuminated.jpg') }}"
            width="1920"
            height="1203"
            loading="lazy"
            decoding="async"
            alt="Conductor de fibra óptica enrollado sobre fondo oscuro transmitiendo luz láser en ambos extremos"
        >
        <div class="rc-wrap rc-showcase__grid">
            <article class="rc-showcase__card" data-reveal>
                <div class="rc-showcase__card-body">
                    <h2>Tramo directo</h2>
                    <p>Hilo óptico continuo FTTH desde nuestro nodo MikroTik hasta tu router dentro de casa.</p>
                    <p class="rc-muted">La señal viaja en pulsos de luz sobre vidrio, inmune a lluvia eléctrica y a interferencia coaxial.</p>
                    <figure class="rc-showcase__thumb">
                        <img
                            src="{{ asset('assets/editorial/ribboncutout1.jpg') }}"
                            width="1920"
                            height="496"
                            loading="lazy"
                            decoding="async"
                            alt="Corte transversal de cable troncal de fibra óptica mostrando sus tubos protectores y cintas de vidrio"
                        >
                    </figure>
                </div>
                <a href="{{ route('cobertura') }}" class="rc-showcase__card-foot">
                    <span>Revisar cobertura urbana</span>
                    <span aria-hidden="true">→</span>
                </a>
            </article>
        </div>
    </div>

    <!-- Frame 2: Line-of-Sight High-Altitude Wireless Link (with real muted looping video) -->
    <div class="rc-showcase__frame rc-showcase__frame--step">
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
            <article class="rc-showcase__card" data-reveal style="--s:1">
                <div class="rc-showcase__card-body">
                    <h3>Enlace rural en altura</h3>
                    <p>Radioenlace punto a punto para lomas, rancherías y fraccionamientos fuera del poste urbano.</p>
                    <p class="rc-muted">Alineación directa con nuestras torres locales y monitoreo permanente de latencia.</p>
                    <figure class="rc-showcase__thumb">
                        <img
                            src="{{ asset('assets/editorial/telecommunications-mast-at-sunset.jpg') }}"
                            width="1920"
                            height="1440"
                            loading="lazy"
                            decoding="async"
                            alt="Torre reticulada de telecomunicaciones recortada contra el cielo crepuscular sobre una cresta arbolada"
                        >
                    </figure>
                </div>
                <a href="{{ route('reservaciones.create', ['tipo_servicio' => 'visita_tecnica']) }}" class="rc-showcase__card-foot">
                    <span>Agendar visita de factibilidad</span>
                    <span aria-hidden="true">→</span>
                </a>
            </article>
        </div>
    </div>
</section>

<!-- 2. PRICING — Plans that step up (bar-chart rising tiles) -->
<section class="rc-section rc-section--bg" id="planes">
    <div class="rc-wrap">
        <div class="rc-pricing__intro">
            <div class="rc-pricing__head" data-reveal>
                <h2>Tarifas claras</h2>
            </div>
            <div class="rc-pricing__sub" data-reveal style="--s:1">
                <p class="rc-muted rc-measure">
                    Mensualidad fija en pesos mexicanos con equipo en comodato. Elige según el caudal real que consume tu domicilio o negocio.
                </p>
            </div>
        </div>

        @if($planesDestacados->count() > 0)
            <div class="rc-steps-row">
                @foreach($planesDestacados as $index => $plan)
                    @php
                        $stepNum = min(4, $index + 1);
                    @endphp
                    <article
                        class="rc-plan-tile rc-plan-tile--step-{{ $stepNum }}"
                        data-plan-tile-id="{{ $plan->id }}"
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
                                @if($plan->descripcion)
                                    <li>{{ $plan->descripcion }}</li>
                                @endif
                                @if($stepNum >= 2)
                                    <li>Router doble banda incluido en comodato</li>
                                @endif
                                @if($stepNum >= 3)
                                    <li>Prioridad de tráfico para videollamadas y juego</li>
                                @endif
                                @if($stepNum >= 4)
                                    <li>Atención preferente para oficinas y comercios</li>
                                @endif
                            </ul>
                        </div>
                        <a href="{{ route('reservaciones.create', ['plan_id' => $plan->id]) }}" class="rc-plan-tile__action">
                            <span>Contratar este plan</span>
                            <span aria-hidden="true">→</span>
                        </a>
                    </article>
                @endforeach
            </div>
        @else
            <div class="rc-promo-block">
                <p>Estamos actualizando el tabulador comercial de esta temporada. Solicita cotización directa con nuestro equipo en Cuernavaca.</p>
                <div>
                    <a href="{{ route('contacto') }}" class="rc-btn rc-btn--gold">Solicitar cotización</a>
                </div>
            </div>
        @endif

        @if($promociones->count() > 0)
            <div class="rc-promos-strip" aria-label="Beneficios vigentes de temporada">
                @foreach($promociones as $promoIndex => $promo)
                    <article class="rc-promo-block" data-reveal style="--s:{{ $promoIndex }}">
                        <div>
                            <h3>{{ $promo->titulo }}</h3>
                            <p class="rc-muted" style="margin-top: 0.5rem;">{{ $promo->descripcion }}</p>
                        </div>
                        <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 1rem;">
                            <span class="rc-small rc-muted rc-tabular">
                                Vigente hasta el {{ $promo->fecha_vencimiento->format('d/m/Y') }}
                            </span>
                            <a href="{{ route('reservaciones.create') }}" class="rc-link rc-link--gold">Aplicar al contratar</a>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</section>

<!-- 3. HOW-IT-WORKS + ONE LIVE THING — Many into one (Interactive 144-channel optical caudal instrument) -->
<section
    class="rc-section rc-section--surface"
    data-caudal-calc
    data-plans='@json($planesJson)'
>
    <div class="rc-wrap">
        <div class="rc-caudal__top">
            <div class="rc-caudal__intro" data-reveal>
                <h2>Caudal en vivo</h2>
                <p class="rc-muted" style="margin-top: 1rem;">
                    Ajusta cuántos equipos operan al mismo tiempo en tu casa o taller. El diagrama enciende los canales ópticos requeridos y señala el plan que los absorbe con holgura.
                </p>
            </div>

            <div class="rc-caudal__controls">
                <div class="rc-LoadControl" data-reveal style="--s:0">
                    <div>
                        <span class="rc-LoadControl__label">Pantallas 4K</span>
                        <span class="rc-LoadControl__hint">Streaming simultáneo (25&nbsp;Mbps c/u)</span>
                    </div>
                    <div class="rc-LoadControl__stepper">
                        <button type="button" class="rc-step-btn" data-load-key="tv" data-load-step="-1" aria-label="Menos pantallas 4K">−</button>
                        <span class="rc-LoadControl__val" data-load-val="tv">2</span>
                        <button type="button" class="rc-step-btn" data-load-key="tv" data-load-step="1" aria-label="Más pantallas 4K">+</button>
                    </div>
                </div>

                <div class="rc-LoadControl" data-reveal style="--s:1">
                    <div>
                        <span class="rc-LoadControl__label">Videollamadas</span>
                        <span class="rc-LoadControl__hint">Trabajo y clases en vivo (15&nbsp;Mbps c/u)</span>
                    </div>
                    <div class="rc-LoadControl__stepper">
                        <button type="button" class="rc-step-btn" data-load-key="calls" data-load-step="-1" aria-label="Menos videollamadas">−</button>
                        <span class="rc-LoadControl__val" data-load-val="calls">2</span>
                        <button type="button" class="rc-step-btn" data-load-key="calls" data-load-step="1" aria-label="Más videollamadas">+</button>
                    </div>
                </div>

                <div class="rc-LoadControl" data-reveal style="--s:2">
                    <div>
                        <span class="rc-LoadControl__label">Juego y nube</span>
                        <span class="rc-LoadControl__hint">Descargas y consolas (45&nbsp;Mbps c/u)</span>
                    </div>
                    <div class="rc-LoadControl__stepper">
                        <button type="button" class="rc-step-btn" data-load-key="cloud" data-load-step="-1" aria-label="Menos consolas o nube">−</button>
                        <span class="rc-LoadControl__val" data-load-val="cloud">1</span>
                        <button type="button" class="rc-step-btn" data-load-key="cloud" data-load-step="1" aria-label="Más consolas o nube">+</button>
                    </div>
                </div>

                <div class="rc-LoadControl" data-reveal style="--s:3">
                    <div>
                        <span class="rc-LoadControl__label">Cámaras IP</span>
                        <span class="rc-LoadControl__hint">Monitoreo continuo (10&nbsp;Mbps c/u)</span>
                    </div>
                    <div class="rc-LoadControl__stepper">
                        <button type="button" class="rc-step-btn" data-load-key="cams" data-load-step="-1" aria-label="Menos cámaras IP">−</button>
                        <span class="rc-LoadControl__val" data-load-val="cams">2</span>
                        <button type="button" class="rc-step-btn" data-load-key="cams" data-load-step="1" aria-label="Más cámaras IP">+</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Blueprint SVG: Many into one (144 filled 12x12 squares: scatter -> 3-row trunk ribbon -> ordered symmetric rows) -->
        <div class="rc-diagram-wrap" data-reveal style="--s:1">
            <svg class="rc-diagram-svg" viewBox="0 0 1200 260" role="img" aria-label="Diagrama óptico de 144 pulsos que convergen en un troncal de fibra y se ordenan al llegar a tu red">
                @php
                    $cells = [];
                    /* Left third (48 scattered squares in x: 16..370, y: 18..230) */
                    for ($i = 0; $i < 48; $i++) {
                        $col = $i % 12;
                        $row = intdiv($i, 12);
                        $jitterX = (($i * 37) % 19) - 9;
                        $jitterY = (($i * 53) % 34) - 17;
                        $x = 20 + ($col * 29) + $jitterX;
                        $y = 34 + ($row * 54) + $jitterY;
                        $cells[] = ['x' => max(8, min(375, $x)), 'y' => max(12, min(236, $y)), 'zone' => 'scatter'];
                    }
                    /* Middle third (48 compressed ribbon squares: 16 cols x 3 tight rows at y: 102, 122, 142) */
                    for ($i = 0; $i < 48; $i++) {
                        $col = $i % 16;
                        $row = intdiv($i, 16);
                        $x = 424 + ($col * 22);
                        $y = 102 + ($row * 20);
                        $cells[] = ['x' => $x, 'y' => $y, 'zone' => 'ribbon'];
                    }
                    /* Right third (48 neat ordered rows: 12 cols x 4 even rows in x: 830..1170) */
                    for ($i = 0; $i < 48; $i++) {
                        $col = $i % 12;
                        $row = intdiv($i, 12);
                        $x = 836 + ($col * 29);
                        $y = 58 + ($row * 42);
                        $cells[] = ['x' => $x, 'y' => $y, 'zone' => 'ordered'];
                    }
                @endphp
                @foreach($cells as $idx => $c)
                    <rect
                        x="{{ $c['x'] }}"
                        y="{{ $c['y'] }}"
                        width="12"
                        height="12"
                        fill="{{ $idx < 68 ? '#c9a45a' : ($c['zone'] === 'scatter' ? '#575049' : '#3a342e') }}"
                        opacity="{{ $idx < 68 ? ($c['zone'] === 'ordered' ? '1' : '0.88') : '0.55' }}"
                        data-optical-cell
                        data-zone="{{ $c['zone'] }}"
                    />
                @endforeach
            </svg>
        </div>

        <div class="rc-caudal__readout" data-reveal style="--s:2" aria-live="polite">
            <div class="rc-caudal__readout-main">
                <div class="rc-caudal__mbps" data-caudal-mbps>167 Mbps</div>
                <p class="rc-muted" style="margin-top: 0.4rem;">Caudal estimado con margen de estabilidad</p>
            </div>
            <div class="rc-caudal__readout-plan">
                <div>
                    <h3 data-caudal-plan-name>{{ $planInicial?->nombre ?? 'Plan Fibra Familiar 100 Megas' }}</h3>
                    <p class="rc-muted rc-tabular" style="margin-top: 0.25rem;" data-caudal-plan-price>
                        ${{ $planInicial ? number_format((float) $planInicial->precio_mensual, 0) : '499' }} / mes
                    </p>
                </div>
                <a
                    href="{{ $planInicial ? route('reservaciones.create', ['plan_id' => $planInicial->id]) : route('reservaciones.create') }}"
                    class="rc-btn rc-btn--gold"
                    data-caudal-plan-link
                >Agendar con este plan</a>
            </div>
        </div>

        <ul class="rc-caudal__stages">
            <li data-reveal style="--s:0">
                <h3>Captación</h3>
                <p class="rc-muted" style="margin-top: 0.35rem;">Cada pantalla, cámara y llamada en casa genera ráfagas dispares al mismo instante.</p>
            </li>
            <li data-reveal style="--s:1">
                <h3>Fusión</h3>
                <p class="rc-muted" style="margin-top: 0.35rem;">El hilo óptico concentra los paquetes en un solo canal físico sin pérdida por distancia.</p>
            </li>
            <li data-reveal style="--s:2">
                <h3>Entrega</h3>
                <p class="rc-muted" style="margin-top: 0.35rem;">Nuestro nodo MikroTik reparte caudal ordenado con latencia estable hacia tu domicilio.</p>
            </li>
        </ul>
    </div>
</section>

<!-- 4. PROOF — Recognition as a table (Operational Coverage Ledger + Archival Map + Direct Folio Lookup) -->
<section class="rc-section rc-section--bg">
    <div class="rc-wrap">
        <div class="rc-proof__header">
            <h2 class="rc-proof__title" data-reveal>Red activa en Morelos</h2>
            <div class="rc-proof__aside" data-reveal style="--s:1">
                <p class="rc-muted">
                    Operamos infraestructura propia en el valle de Cuernavaca y la zona conurbada sur. Cada alta y reporte se controla por folio único.
                </p>
            </div>
        </div>

        <table class="rc-ledger rc-tabular" data-reveal style="--s:1">
            <thead>
                <tr>
                    <th scope="col">Corredor</th>
                    <th scope="col">Municipio y sectores</th>
                    <th scope="col">Medio físico</th>
                    <th scope="col">Tiempo de alta</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="rc-ledger__zone">Valle Norte</td>
                    <td>Cuernavaca (Centro, Reforma, Teopanzolco, Lomas)</td>
                    <td>Fibra óptica FTTH hasta domicilio</td>
                    <td>24 a 36 horas</td>
                </tr>
                <tr>
                    <td class="rc-ledger__zone"></td>
                    <td>Jiutepec y corredor industrial CIVAC</td>
                    <td>Fibra óptica FTTH residencial y comercial</td>
                    <td>24 a 36 horas</td>
                </tr>
                <tr>
                    <td class="rc-ledger__zone"></td>
                    <td>Tepoztlán (cabecera y lomas de valle)</td>
                    <td>Radioenlace punto a punto en altura</td>
                    <td>24 a 48 horas</td>
                </tr>
                <tr class="rc-ledger__spacer" aria-hidden="true"><td colspan="4"></td></tr>
                <tr>
                    <td class="rc-ledger__zone">Conurbación Sur</td>
                    <td>Temixco (Centro, Burgos y fraccionamientos)</td>
                    <td>Fibra óptica FTTH y nodo híbrido</td>
                    <td>24 a 48 horas</td>
                </tr>
                <tr>
                    <td class="rc-ledger__zone"></td>
                    <td>Emiliano Zapata</td>
                    <td>Fibra óptica FTTH residencial</td>
                    <td>24 a 48 horas</td>
                </tr>
                <tr>
                    <td class="rc-ledger__zone"></td>
                    <td>Xochitepec y Yautepec</td>
                    <td>Troncal óptica y radioenlace rural</td>
                    <td>48 horas</td>
                </tr>
            </tbody>
        </table>

        <div class="rc-territory-pair">
            <figure class="rc-territory-pair__map" data-reveal style="--s:0">
                <div class="rc-media-frame">
                    <img
                        src="{{ asset('assets/editorial/cuernavaca-1968.webp') }}"
                        width="1156"
                        height="1812"
                        loading="lazy"
                        decoding="async"
                        alt="Plano cartográfico histórico de las avenidas y barrancas de Cuernavaca trazado por el servicio geológico en 1968"
                    >
                </div>
                <figcaption class="rc-caption">Traza urbana de Cuernavaca (USGS, 1968), eje matriz de nuestra red metropolitana.</figcaption>
            </figure>

            <figure class="rc-territory-pair__valley" data-reveal style="--s:1">
                <div class="rc-media-frame">
                    <img
                        src="{{ asset('assets/editorial/vista-en-la-cima-de-las-montanas.jpg') }}"
                        width="1920"
                        height="2560"
                        loading="lazy"
                        decoding="async"
                        alt="Laderas y formaciones rocosas de la sierra de Loma Bonita en Tepoztlán, Morelos, bajo cielo despejado"
                    >
                </div>
                <figcaption class="rc-caption">Relieve serrano de Morelos cubierto por nuestros enlaces de línea de vista.</figcaption>
            </figure>
        </div>

        <!-- Direct Folio Tracker (RES- / TKT-) -->
        <div class="rc-folio-panel" data-reveal style="--s:2">
            <div class="rc-folio-panel__copy">
                <h3>Consulta tu folio</h3>
                <p class="rc-muted" style="margin-top: 0.4rem;">
                    Revisa el avance de tu instalación (RES-) o reporte técnico (TKT-) sin llamadas ni esperas en línea.
                </p>
            </div>
            <form action="{{ route('home') }}" method="GET" class="rc-folio-panel__form" data-folio-form>
                <div class="rc-folio-panel__field">
                    <label for="home-folio-input" class="rc-label">Número de folio</label>
                    <input
                        type="text"
                        id="home-folio-input"
                        name="folio"
                        class="rc-input"
                        placeholder="RES-20261108-ABCD"
                        autocorrect="off"
                        autocapitalize="characters"
                        spellcheck="false"
                        required
                    >
                </div>
                <button type="submit" class="rc-btn rc-btn--gold">Consultar estatus</button>
            </form>
        </div>
    </div>
</section>

<!-- 5. CTA — One word is the button (entire gold band is a single link) -->
<section aria-label="Contratación directa">
    <a href="{{ route('reservaciones.create') }}" class="rc-cta-band">
        <div class="rc-cta-band__top">
            <h2 class="rc-cta-band__heading">Agenda hoy</h2>
            <p class="rc-cta-band__lead">
                Un técnico local confirma factibilidad en tu calle y deja tu conexión operando en menos de 48&nbsp;horas.
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
