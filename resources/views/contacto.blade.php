@extends('layouts.app')

@section('title', 'Contacto y Atención Directa')
@section('meta_description', 'Oficina central y líneas directas de Rivas Comunicaciones en Cuernavaca, Morelos. Ventas residenciales, enlaces empresariales y guardia técnica.')

@section('content')
<section class="rc-page-head">
    <div class="rc-wrap rc-page-head__grid">
        <div class="rc-page-head__title">
            <h1 class="rc-lines">
                <span class="rc-line"><span data-line style="--i:0">Contacto</span></span>
                <span class="rc-line"><span data-line style="--i:1">directo</span></span>
            </h1>
        </div>
        <div class="rc-page-head__desc">
            <p class="rc-lead">
                Escríbenos para cotizar enlaces comerciales, validar cobertura en tu calle o resolver cualquier duda administrativa con nuestro equipo en Cuernavaca.
            </p>
        </div>
    </div>
</section>

<section class="rc-section rc-section--bg" style="padding-top: 1rem;">
    <div class="rc-wrap rc-split">
        <div class="rc-split__media" data-reveal>
            <h2>Oficina y teléfonos</h2>
            <p class="rc-muted" style="margin-top: 0.75rem;">
                Atendemos presencialmente y por vía telefónica desde nuestra base operativa en Morelos.
            </p>

            <dl class="rc-dl rc-tabular" style="margin-top: 1.75rem;">
                <div>
                    <dt>Domicilio operativo</dt>
                    <dd>Av. Telecomunicaciones 100, Col. Centro, Cuernavaca, Morelos</dd>
                </div>
                <div>
                    <dt>Horario en ventanilla</dt>
                    <dd>Lun–Vie 8:00–19:00 · Sáb 9:00–15:00</dd>
                </div>
                <div>
                    <dt>Contrataciones y ventas</dt>
                    <dd>(777)&nbsp;123-4567</dd>
                </div>
                <div>
                    <dt>Guardia técnica 24/7</dt>
                    <dd>(777)&nbsp;987-6543</dd>
                </div>
            </dl>

            <div style="margin-top: 2rem; margin-bottom: 2.5rem;">
                <h3>¿Ya eres suscriptor y presentas una falla?</h3>
                <p class="rc-muted" style="margin-top: 0.4rem;">
                    Levanta un reporte con el teléfono de tu contrato para canalizarlo directo a monitoreo de nodo sin esperar respuesta por correo.
                </p>
                <p style="margin-top: 1rem;">
                    <a href="{{ route('tickets.create') }}" class="rc-link rc-link--gold">Generar ticket de soporte técnico</a>
                </p>
            </div>

            <figure>
                <div class="rc-media-frame" style="aspect-ratio: 16 / 10;">
                    <img
                        src="{{ asset('assets/editorial/vista-en-la-cima-de-las-montanas.jpg') }}"
                        width="1920"
                        height="2560"
                        loading="lazy"
                        decoding="async"
                        alt="Laderas y formaciones rocosas de la sierra de Loma Bonita en Tepoztlán, Morelos, bajo cielo despejado"
                    >
                </div>
                <figcaption class="rc-caption">Cobertura regional en valle y sierra desde nuestra sede en Cuernavaca.</figcaption>
            </figure>
        </div>

        <div class="rc-split__content" data-reveal style="--s:1">
            <div class="rc-form-surface">
                <h2 style="margin-bottom: 0.5rem;">Envíanos un mensaje</h2>
                <p class="rc-muted" style="margin-bottom: 2rem;">
                    Respondemos en horario hábil el mismo día de recepción.
                </p>

                <form action="{{ route('contacto.store') }}" method="POST" data-rc-form>
                    @csrf

                    <div class="rc-field-row">
                        <div class="rc-field-group">
                            <label for="nombre" class="rc-label">Nombre completo</label>
                            <input
                                type="text"
                                id="nombre"
                                name="nombre"
                                class="rc-input @error('nombre') is-invalid @enderror"
                                value="{{ old('nombre') }}"
                                autocomplete="name"
                                placeholder="Ej. María Hernández"
                                required
                            >
                            @error('nombre')
                                <span class="rc-error-msg">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="rc-field-group">
                            <label for="correo_electronico" class="rc-label">Correo electrónico</label>
                            <input
                                type="email"
                                id="correo_electronico"
                                name="correo_electronico"
                                class="rc-input @error('correo_electronico') is-invalid @enderror"
                                value="{{ old('correo_electronico') }}"
                                autocomplete="email"
                                autocapitalize="none"
                                spellcheck="false"
                                placeholder="nombre@dominio.com"
                                required
                            >
                            @error('correo_electronico')
                                <span class="rc-error-msg">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="rc-field-row">
                        <div class="rc-field-group">
                            <label for="telefono" class="rc-label">Teléfono de contacto (opcional)</label>
                            <input
                                type="tel"
                                id="telefono"
                                name="telefono"
                                class="rc-input @error('telefono') is-invalid @enderror"
                                value="{{ old('telefono') }}"
                                inputmode="tel"
                                autocomplete="tel"
                                placeholder="777 123 4567"
                            >
                            @error('telefono')
                                <span class="rc-error-msg">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="rc-field-group">
                            <label for="asunto" class="rc-label">Asunto</label>
                            <input
                                type="text"
                                id="asunto"
                                name="asunto"
                                class="rc-input @error('asunto') is-invalid @enderror"
                                value="{{ old('asunto') }}"
                                placeholder="Ej. Factibilidad comercial en CIVAC"
                                required
                            >
                            @error('asunto')
                                <span class="rc-error-msg">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="rc-field-group">
                        <label for="mensaje" class="rc-label">Mensaje o consulta</label>
                        <textarea
                            id="mensaje"
                            name="mensaje"
                            rows="5"
                            class="rc-textarea @error('mensaje') is-invalid @enderror"
                            placeholder="Indica colonia, municipio o los detalles de tu requerimiento..."
                            required
                        >{{ old('mensaje') }}</textarea>
                        @error('mensaje')
                            <span class="rc-error-msg">{{ $message }}</span>
                        @enderror
                    </div>

                    <button type="submit" class="rc-btn rc-btn--gold rc-btn--full">
                        <span>Enviar mensaje</span>
                        <span aria-hidden="true">→</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

<section class="rc-section rc-section--surface">
    <div class="rc-wrap">
        <div class="rc-proof__header">
            <div class="rc-proof__title" data-reveal>
                <h2>Presencia en Morelos</h2>
            </div>
            <div class="rc-proof__aside" data-reveal style="--s:1">
                <p class="rc-muted">
                    Nuestras cuadrillas parten diariamente desde Cuernavaca hacia corredores habitacionales, parques industriales y localidades serranas del estado.
                </p>
            </div>
        </div>

        <div class="rc-territory-pair" style="margin-bottom: 2.5rem;">
            <figure class="rc-territory-pair__map" data-reveal style="--s:1">
                <div class="rc-media-frame">
                    <img
                        src="{{ asset('assets/editorial/cuernavaca-1968.webp') }}"
                        class="rc-drift"
                        width="1156"
                        height="1812"
                        loading="lazy"
                        decoding="async"
                        alt="Carta topográfica histórica de Cuernavaca y sus barrancas elaborada en 1968 por el Servicio Geológico de Estados Unidos"
                    >
                </div>
                <figcaption class="rc-caption">Trazo urbano y cañadas de Cuernavaca donde operan nuestros anillos ópticos troncales.</figcaption>
            </figure>

            <figure class="rc-territory-pair__valley" data-reveal style="--s:2">
                <div class="rc-media-frame">
                    <img
                        src="{{ asset('assets/editorial/zona-metropolitana-de-cuernavaca-vista-civac-2019.jpg') }}"
                        class="rc-drift"
                        width="1341"
                        height="893"
                        loading="lazy"
                        decoding="async"
                        alt="Vista aérea de la zona industrial CIVAC en Jiutepec y el valle metropolitano de Cuernavaca"
                    >
                </div>
                <figcaption class="rc-caption">Atención comercial dedicada para naves industriales y oficinas en CIVAC y Jiutepec.</figcaption>
            </figure>
        </div>

        <figure data-reveal style="--s:3">
            <div class="rc-media-frame" style="aspect-ratio: 21 / 9;">
                <img
                    src="{{ asset('assets/editorial/telecommunications-mast-at-sunset.jpg') }}"
                    class="rc-drift"
                    width="1920"
                    height="1440"
                    loading="lazy"
                    decoding="async"
                    alt="Torre de telecomunicaciones iluminada con balizas rojas al atardecer sobre cielo despejado"
                >
            </div>
            <figcaption class="rc-caption">Infraestructura de transmisión supervisada por nuestra guardia técnica los 365 días del año.</figcaption>
        </figure>
    </div>
</section>

<section class="rc-cta-band">
    <a href="{{ route('reservaciones.create') }}" class="rc-cta-band__link">
        <div class="rc-wrap rc-cta-band__inner">
            <div class="rc-cta-band__top" data-reveal>
                <p class="rc-cta-band__lead">
                    ¿Ya ubicaste tu paquete residencial o comercial? Programa tu instalación directamente en línea y recibe tu folio al instante.
                </p>
                <svg class="rc-cta-band__arrow" viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="3" aria-hidden="true">
                    <path d="M10 38L38 10M38 10H14M38 10V34"/>
                </svg>
            </div>
            <div data-reveal style="--s:1">
                <h2 class="rc-cta-band__heading">Contratar</h2>
            </div>
        </div>
    </a>
</section>
@endsection
