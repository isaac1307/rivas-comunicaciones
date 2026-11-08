@extends('layouts.app')

@section('title', 'Reservación ' . $reservacion->folio)

@section('content')
<section class="rc-page-head">
    <div class="rc-wrap rc-page-head__grid">
        <div class="rc-page-head__title">
            <h1 class="rc-folio-title">{{ $reservacion->folio }}</h1>
        </div>
        <div class="rc-page-head__desc">
            <p class="rc-lead">
                Expediente de programación registrado el {{ $reservacion->created_at->format('d/m/Y') }} a las {{ $reservacion->created_at->format('H:i') }}&nbsp;horas.
            </p>
        </div>
    </div>
</section>

<section class="rc-section rc-section--bg" style="padding-top: 1rem;">
    <div class="rc-wrap rc-split">
        <div class="rc-split__media">
            <h2>Estado actual</h2>
            <p style="margin-top: 1rem;">
                <span class="rc-status-pill rc-status-pill--accent">
                    {{ str_replace('_', ' ', ucfirst($reservacion->estado)) }}
                </span>
            </p>
            <p class="rc-muted" style="margin-top: 1.25rem;">
                Conserva este código para cualquier confirmación telefónica con nuestra mesa de despacho en Cuernavaca. Por seguridad, tu teléfono y domicilio exacto se muestran parcialmente resguardados.
            </p>

            <div class="rc-no-print" style="display: flex; flex-wrap: wrap; gap: 0.75rem; margin-top: 2rem;">
                <button type="button" class="rc-btn" onclick="window.print()">Imprimir comprobante</button>
                <a href="{{ route('reservaciones.create') }}" class="rc-btn rc-btn--gold">Nueva solicitud</a>
            </div>
        </div>

        <div class="rc-split__content">
            <div class="rc-dossier">
                <h2>Ficha de solicitud</h2>

                <dl class="rc-dl rc-tabular">
                    <div>
                        <dt>Solicitante</dt>
                        <dd>{{ $reservacion->nombre_solicitante }}</dd>
                    </div>

                    <div>
                        <dt>Teléfono registrado</dt>
                        <dd>{{ $reservacion->telefonoEnmascarado() }}</dd>
                    </div>

                    <div>
                        <dt>Correo electrónico</dt>
                        <dd>{{ $reservacion->correo_electronico ? substr($reservacion->correo_electronico, 0, 3).'***@***' : 'No especificado' }}</dd>
                    </div>

                    <div>
                        <dt>Tipo de servicio</dt>
                        <dd>{{ ucfirst(str_replace('_', ' ', $reservacion->tipo_servicio)) }}</dd>
                    </div>

                    <div>
                        <dt>Plan solicitado</dt>
                        <dd>
                            @if ($reservacion->plan)
                                {{ $reservacion->plan->nombre }} ({{ $reservacion->plan->velocidad_descarga }}&nbsp;Mbps · ${{ number_format((float) $reservacion->plan->precio_mensual, 0) }}/mes)
                            @else
                                Por definir con el asesor técnico
                            @endif
                        </dd>
                    </div>

                    <div>
                        <dt>Fecha solicitada</dt>
                        <dd>{{ $reservacion->fecha_solicitada ? $reservacion->fecha_solicitada->format('d/m/Y') : 'Por agendar' }}</dd>
                    </div>
                </dl>

                <div style="margin-top: 1.5rem;">
                    <p class="rc-small rc-muted" style="margin-bottom: 0.35rem;">Domicilio registrado</p>
                    <p>{{ $reservacion->direccionEnmascarada() }}</p>
                </div>

                @if ($reservacion->observaciones)
                    <div style="margin-top: 1.5rem;">
                        <p class="rc-small rc-muted" style="margin-bottom: 0.35rem;">Observaciones de acceso</p>
                        <p class="rc-muted">{{ $reservacion->observaciones }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
@endsection
