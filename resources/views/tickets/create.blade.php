@extends('layouts.app')

@section('title', 'Soporte Técnico y Reporte de Fallas')
@section('meta_description', 'Mesa de soporte técnico de Rivas Comunicaciones. Levanta un ticket con el teléfono de tu contrato o consulta el avance de tu folio TKT-.')

@section('content')
<section class="rc-page-head">
    <div class="rc-wrap rc-page-head__grid">
        <div class="rc-page-head__title">
            <h1 class="rc-lines">
                <span class="rc-line"><span data-line style="--i:0">Soporte</span></span>
                <span class="rc-line"><span data-line style="--i:1">técnico</span></span>
            </h1>
        </div>
        <div class="rc-page-head__desc">
            <p class="rc-lead">
                Reporta intermitencias, cortes de fibra o ajustes de red directamente a nuestro centro de monitoreo en Morelos.
            </p>
        </div>
    </div>
</section>

<section class="rc-section rc-section--bg" style="padding-top: 1rem;">
    <div class="rc-wrap rc-split">
        <div class="rc-split__media" data-reveal>
            <h2>Revisión rápida en equipo</h2>
            <p class="rc-muted" style="margin-top: 0.75rem;">
                Incluir el estado de los indicadores luminosos de tu terminal óptica acelera el diagnóstico desde nuestro nodo.
            </p>

            <dl class="rc-dl" style="margin-top: 1.75rem;">
                <div>
                    <dt>Indicador PON fijo en verde</dt>
                    <dd>El hilo óptico tiene potencia; revisamos sesión PPPoE o Wi-Fi local.</dd>
                </div>
                <div>
                    <dt>Indicador LOS en rojo</dt>
                    <dd>Posible corte físico en acometida exterior; enviamos brigada a calle.</dd>
                </div>
                <div>
                    <dt>Equipo apagado</dt>
                    <dd>Verifica contacto eléctrico y eliminador de corriente de 12&nbsp;V.</dd>
                </div>
                <div>
                    <dt>Línea de guardia 24/7</dt>
                    <dd>(777)&nbsp;987-6543 para emergencias operativas.</dd>
                </div>
            </dl>

            <div style="margin-top: 2.5rem;">
                <h3>¿Ya cuentas con un folio TKT-?</h3>
                <p class="rc-muted" style="margin-top: 0.4rem;">
                    Consulta el dictamen técnico o estado actual de tu reporte.
                </p>
                <form action="{{ route('home') }}" method="GET" data-folio-form style="margin-top: 1rem; display: flex; flex-wrap: wrap; gap: 8px; align-items: flex-end;">
                    <div style="flex: 1 1 200px;">
                        <label for="support-folio-lookup" class="rc-label">Folio de seguimiento</label>
                        <input
                            type="text"
                            id="support-folio-lookup"
                            name="folio"
                            class="rc-input"
                            placeholder="TKT-20261108-ABCD"
                            autocorrect="off"
                            autocapitalize="characters"
                            spellcheck="false"
                            required
                        >
                    </div>
                    <button type="submit" class="rc-btn">Consultar</button>
                </form>
            </div>
        </div>

        <div class="rc-split__content" data-reveal style="--s:1">
            <div class="rc-form-surface">
                <h2 style="margin-bottom: 0.5rem;">Levantar reporte</h2>
                <p class="rc-muted" style="margin-bottom: 2rem;">
                    Identificamos tu cuenta de servicio mediante el número telefónico titular sin exponer datos de padrón en pantalla.
                </p>

                <form action="{{ route('tickets.store') }}" method="POST" data-rc-form>
                    @csrf

                    <div class="rc-field-group">
                        <label for="telefono" class="rc-label">Teléfono Registrado en tu Contrato</label>
                        <input
                            type="tel"
                            id="telefono"
                            name="telefono"
                            class="rc-input @error('telefono') is-invalid @enderror"
                            value="{{ old('telefono') }}"
                            inputmode="tel"
                            autocomplete="tel"
                            autocorrect="off"
                            placeholder="Ej. 7771234567 (10 dígitos)"
                            required
                        >
                        <span class="rc-hint">Ingresa los 10 dígitos asociados a tu contrato activo o suspendido.</span>
                        @error('telefono')
                            <span class="rc-error-msg">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="rc-field-group">
                        <label for="categoria" class="rc-label">Categoría del incidente</label>
                        <select
                            id="categoria"
                            name="categoria"
                            class="rc-select @error('categoria') is-invalid @enderror"
                            required
                        >
                            <option value="falla_servicio" {{ old('categoria') === 'falla_servicio' ? 'selected' : '' }}>Falla de servicio (sin conexión o intermitencia)</option>
                            <option value="soporte_tecnico" {{ old('categoria') === 'soporte_tecnico' ? 'selected' : '' }}>Soporte técnico (contraseña Wi-Fi, puertos o cobertura interna)</option>
                            <option value="consulta" {{ old('categoria') === 'consulta' ? 'selected' : '' }}>Consulta de facturación o estado de cuenta</option>
                            <option value="cambio_plan" {{ old('categoria') === 'cambio_plan' ? 'selected' : '' }}>Cambio o aumento de velocidad</option>
                            <option value="cancelacion" {{ old('categoria') === 'cancelacion' ? 'selected' : '' }}>Aclaración contractual o baja</option>
                        </select>
                        @error('categoria')
                            <span class="rc-error-msg">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="rc-field-group">
                        <label for="descripcion" class="rc-label">Descripción detallada del problema</label>
                        <textarea
                            id="descripcion"
                            name="descripcion"
                            rows="5"
                            class="rc-textarea @error('descripcion') is-invalid @enderror"
                            placeholder="Indica desde qué hora ocurre, qué luces muestra tu módem (PON/LOS) y si afecta a todos tus dispositivos..."
                            required
                        >{{ old('descripcion') }}</textarea>
                        @error('descripcion')
                            <span class="rc-error-msg">{{ $message }}</span>
                        @enderror
                    </div>

                    <button type="submit" class="rc-btn rc-btn--gold rc-btn--full">
                        <span>Registrar ticket de soporte</span>
                        <span aria-hidden="true">→</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection
