@extends('layouts.app')

@section('title', 'Agendar Instalación o Visita Técnica')
@section('meta_description', 'Agenda en línea tu instalación de fibra óptica FTTH o visita de factibilidad en Morelos. Recibe folio inmediato de seguimiento.')

@section('content')
@php
    $tipoActual = old('tipo_servicio', request('tipo_servicio', 'instalacion_nueva'));
@endphp

<section class="rc-page-head">
    <div class="rc-wrap rc-page-head__grid">
        <div class="rc-page-head__title">
            <h1 class="rc-lines">
                <span class="rc-line"><span data-line style="--i:0">Agendar</span></span>
                <span class="rc-line"><span data-line style="--i:1">instalación</span></span>
            </h1>
        </div>
        <div class="rc-page-head__desc">
            <p class="rc-lead">
                Registra tu domicilio para programar tendido nuevo o visita de factibilidad. Al enviar obtendrás un folio único (RES-) para consultar el avance.
            </p>
        </div>
    </div>
</section>

<section class="rc-section rc-section--bg" style="padding-top: 1rem;">
    <div class="rc-wrap rc-split">
        <div class="rc-split__media" data-reveal>
            <h2>Proceso de alta</h2>
            <p class="rc-muted" style="margin-top: 0.75rem;">
                Sin pagos por adelantado en línea: liquidas únicamente cuando el enlace queda operando en tu domicilio.
            </p>

            <dl class="rc-dl" style="margin-top: 1.75rem;">
                <div>
                    <dt>Confirmación de poste</dt>
                    <dd>Verificamos puerto óptico libre o línea de vista en tu calle.</dd>
                </div>
                <div>
                    <dt>Coordinación telefónica</dt>
                    <dd>Acordamos contigo la ventana exacta antes de desplazar cuadrilla.</dd>
                </div>
                <div>
                    <dt>Instalación limpia</dt>
                    <dd>Tendido exterior, roseta interior, terminal ONT y prueba de velocidad.</dd>
                </div>
                <div>
                    <dt>Folio auditable</dt>
                    <dd>Consulta estatus en todo momento con tu código RES-.</dd>
                </div>
            </dl>

            <figure style="margin-top: 2rem;">
                <div class="rc-media-frame" style="aspect-ratio: 16 / 10;">
                    <img
                        src="{{ asset('assets/editorial/zona-metropolitana-de-cuernavaca-vista-civac-2019.jpg') }}"
                        width="1341"
                        height="893"
                        loading="lazy"
                        decoding="async"
                        alt="Vista aérea nocturna del valle de Cuernavaca y el corredor industrial CIVAC en Jiutepec"
                    >
                </div>
                <figcaption class="rc-caption">Cuadrillas locales activas de lunes a sábado en Morelos.</figcaption>
            </figure>
        </div>

        <div class="rc-split__content" data-reveal style="--s:1">
            <div class="rc-form-surface">
                <h2 style="margin-bottom: 0.5rem;">Orden de programación</h2>
                <p class="rc-muted" style="margin-bottom: 2rem;">
                    Completa los datos de contacto y ubicación donde realizaremos el trabajo.
                </p>

                <form action="{{ route('reservaciones.store') }}" method="POST" data-rc-form>
                    @csrf

                    <fieldset class="rc-fieldset">
                        <legend class="rc-label">Tipo de solicitud</legend>
                        <div class="rc-radio-grid">
                            <label class="rc-radio-option">
                                <input
                                    type="radio"
                                    name="tipo_servicio"
                                    value="instalacion_nueva"
                                    {{ $tipoActual === 'instalacion_nueva' ? 'checked' : '' }}
                                    required
                                >
                                <span>
                                    <strong>Instalación nueva</strong>
                                    <span class="rc-hint">Contratación residencial o pyme</span>
                                </span>
                            </label>

                            <label class="rc-radio-option">
                                <input
                                    type="radio"
                                    name="tipo_servicio"
                                    value="visita_tecnica"
                                    {{ $tipoActual === 'visita_tecnica' ? 'checked' : '' }}
                                >
                                <span>
                                    <strong>Visita técnica</strong>
                                    <span class="rc-hint">Factibilidad o línea de vista</span>
                                </span>
                            </label>

                            <label class="rc-radio-option">
                                <input
                                    type="radio"
                                    name="tipo_servicio"
                                    value="atencion_personalizada"
                                    {{ $tipoActual === 'atencion_personalizada' ? 'checked' : '' }}
                                >
                                <span>
                                    <strong>Proyecto especial</strong>
                                    <span class="rc-hint">Comercios, privadas o enlaces</span>
                                </span>
                            </label>
                        </div>
                        @error('tipo_servicio')
                            <span class="rc-error-msg">{{ $message }}</span>
                        @enderror
                    </fieldset>

                    <div class="rc-field-group">
                        <label for="plan_id" class="rc-label">Plan de internet deseado (opcional)</label>
                        <select
                            id="plan_id"
                            name="plan_id"
                            class="rc-select @error('plan_id') is-invalid @enderror"
                        >
                            <option value="">Definir junto con el asesor técnico</option>
                            @foreach ($planes as $plan)
                                <option
                                    value="{{ $plan->id }}"
                                    {{ (string) old('plan_id', $planSeleccionadoId) === (string) $plan->id ? 'selected' : '' }}
                                >
                                    {{ $plan->nombre }} — {{ $plan->velocidad_descarga }} Mbps (${{ number_format((float) $plan->precio_mensual, 0) }}/mes)
                                </option>
                            @endforeach
                        </select>
                        @error('plan_id')
                            <span class="rc-error-msg">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="rc-field-row">
                        <div class="rc-field-group">
                            <label for="nombre_solicitante" class="rc-label">Nombre completo del titular</label>
                            <input
                                type="text"
                                id="nombre_solicitante"
                                name="nombre_solicitante"
                                class="rc-input @error('nombre_solicitante') is-invalid @enderror"
                                value="{{ old('nombre_solicitante') }}"
                                autocomplete="name"
                                placeholder="Ej. Carlos Mendoza"
                                required
                            >
                            @error('nombre_solicitante')
                                <span class="rc-error-msg">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="rc-field-group">
                            <label for="telefono" class="rc-label">Teléfono a 10 dígitos</label>
                            <input
                                type="tel"
                                id="telefono"
                                name="telefono"
                                class="rc-input @error('telefono') is-invalid @enderror"
                                value="{{ old('telefono') }}"
                                inputmode="tel"
                                autocomplete="tel"
                                placeholder="7771234567"
                                required
                            >
                            @error('telefono')
                                <span class="rc-error-msg">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="rc-field-row">
                        <div class="rc-field-group">
                            <label for="correo_electronico" class="rc-label">Correo electrónico (opcional)</label>
                            <input
                                type="email"
                                id="correo_electronico"
                                name="correo_electronico"
                                class="rc-input @error('correo_electronico') is-invalid @enderror"
                                value="{{ old('correo_electronico') }}"
                                autocomplete="email"
                                autocapitalize="none"
                                spellcheck="false"
                                placeholder="carlos@ejemplo.com"
                            >
                            @error('correo_electronico')
                                <span class="rc-error-msg">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="rc-field-group">
                            <label for="fecha_solicitada" class="rc-label">Fecha sugerida de visita</label>
                            <input
                                type="date"
                                id="fecha_solicitada"
                                name="fecha_solicitada"
                                class="rc-input @error('fecha_solicitada') is-invalid @enderror"
                                value="{{ old('fecha_solicitada', date('Y-m-d', strtotime('+1 day'))) }}"
                                min="{{ date('Y-m-d') }}"
                            >
                            @error('fecha_solicitada')
                                <span class="rc-error-msg">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="rc-field-group">
                        <label for="direccion" class="rc-label">Dirección completa de instalación</label>
                        <input
                            type="text"
                            id="direccion"
                            name="direccion"
                            class="rc-input @error('direccion') is-invalid @enderror"
                            value="{{ old('direccion') }}"
                            autocomplete="street-address"
                            placeholder="Calle y número, colonia, municipio y código postal"
                            required
                        >
                        @error('direccion')
                            <span class="rc-error-msg">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="rc-field-group">
                        <label for="observaciones" class="rc-label">Referencias de ubicación u horario (opcional)</label>
                        <textarea
                            id="observaciones"
                            name="observaciones"
                            rows="3"
                            class="rc-textarea @error('observaciones') is-invalid @enderror"
                            placeholder="Entre qué calles se ubica, color de portón o caseta de acceso..."
                        >{{ old('observaciones') }}</textarea>
                        @error('observaciones')
                            <span class="rc-error-msg">{{ $message }}</span>
                        @enderror
                    </div>

                    <button type="submit" class="rc-btn rc-btn--gold rc-btn--full">
                        <span>Generar folio de reservación</span>
                        <span aria-hidden="true">→</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection
