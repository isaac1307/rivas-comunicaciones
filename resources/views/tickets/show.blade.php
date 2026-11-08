@extends('layouts.app')

@section('title', 'Ticket de Soporte ' . $ticket->folio)

@section('content')
<section class="rc-page-head">
    <div class="rc-wrap rc-page-head__grid">
        <div class="rc-page-head__title">
            <h1 class="rc-folio-title">{{ $ticket->folio }}</h1>
        </div>
        <div class="rc-page-head__desc">
            <p class="rc-lead">
                Reporte técnico abierto el {{ $ticket->created_at->format('d/m/Y') }} a las {{ $ticket->created_at->format('H:i') }}&nbsp;horas.
            </p>
        </div>
    </div>
</section>

<section class="rc-section rc-section--bg" style="padding-top: 1rem;">
    <div class="rc-wrap rc-split">
        <div class="rc-split__media">
            <h2>Estatus del incidente</h2>
            <p style="margin-top: 1rem;">
                <span class="rc-status-pill rc-status-pill--accent">
                    {{ ucfirst(str_replace('_', ' ', $ticket->estado)) }}
                </span>
            </p>
            <p class="rc-muted" style="margin-top: 1.25rem;">
                Nuestro equipo actualiza este expediente conforme avanza la revisión en nodo o la visita en domicilio.
            </p>

            <div class="rc-no-print" style="display: flex; flex-wrap: wrap; gap: 0.75rem; margin-top: 2rem;">
                <button type="button" class="rc-btn" onclick="window.print()">Imprimir comprobante</button>
                <a href="{{ route('tickets.create') }}" class="rc-btn rc-btn--gold">Nuevo reporte</a>
            </div>
        </div>

        <div class="rc-split__content">
            <div class="rc-dossier">
                <h2>Expediente técnico</h2>

                <dl class="rc-dl rc-tabular">
                    <div>
                        <dt>Titular del contrato</dt>
                        <dd>{{ $ticket->cliente->nombre_completo }}</dd>
                    </div>

                    <div>
                        <dt>Plan asociado</dt>
                        <dd>{{ $ticket->cliente->plan ? $ticket->cliente->plan->nombre : 'Sin plan registrado' }}</dd>
                    </div>

                    <div>
                        <dt>Categoría</dt>
                        <dd>{{ ucfirst(str_replace('_', ' ', $ticket->categoria)) }}</dd>
                    </div>

                    <div>
                        <dt>Fecha de resolución</dt>
                        <dd>{{ $ticket->fecha_resolucion ? $ticket->fecha_resolucion->format('d/m/Y H:i') : 'En atención por ingeniería' }}</dd>
                    </div>
                </dl>

                <div style="margin-top: 1.5rem;">
                    <p class="rc-small rc-muted" style="margin-bottom: 0.35rem;">Descripción reportada</p>
                    <p>{{ $ticket->descripcion }}</p>
                </div>

                @if ($ticket->observaciones_admin)
                    <div style="margin-top: 2rem; padding: 1.25rem; background-color: var(--bg);">
                        <p class="rc-small" style="color: var(--accent); font-weight: 600; margin-bottom: 0.35rem;">
                            Dictamen del equipo técnico
                        </p>
                        <p>{{ $ticket->observaciones_admin }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
@endsection
