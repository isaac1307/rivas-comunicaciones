@extends('layouts.admin')

@section('title', 'Control de Cobranza por Cliente')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h2 class="fw-bold mb-1">Control de Cobranza</h2>
        <p class="text-muted small mb-0">Gestión de estados de cuenta, cargos mensuales y recepción de pagos por cliente.</p>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalGenerarCargos">
            <i class="bi bi-calendar-plus me-1"></i> Generar Mensualidades
        </button>
    </div>
</div>

<!-- Tarjetas de Métricas -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3 bg-white p-3 h-100 border-start border-danger border-4">
            <span class="text-muted small text-uppercase fw-semibold">Total por Cobrar</span>
            <div class="fs-4 fw-bold text-danger mt-1">${{ number_format($metricas['totalPorCobrar'], 2) }}</div>
            <span class="text-muted small">Saldo acumulado pendiente</span>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3 bg-white p-3 h-100 border-start border-success border-4">
            <span class="text-muted small text-uppercase fw-semibold">Recaudado este Mes</span>
            <div class="fs-4 fw-bold text-success mt-1">${{ number_format($metricas['totalRecaudadoMes'], 2) }}</div>
            <span class="text-muted small">{{ now()->locale('es')->translatedFormat('F Y') }}</span>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3 bg-white p-3 h-100 border-start border-warning border-4">
            <span class="text-muted small text-uppercase fw-semibold">Clientes con Adeudo</span>
            <div class="fs-4 fw-bold text-warning mt-1">{{ $metricas['clientesConAdeudo'] }}</div>
            <span class="text-muted small">Tienen al menos un cargo pendiente</span>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3 bg-white p-3 h-100 border-start border-danger border-4">
            <span class="text-muted small text-uppercase fw-semibold">Clientes Morosos</span>
            <div class="fs-4 fw-bold text-danger mt-1">{{ $metricas['clientesMorosos'] }}</div>
            <span class="text-muted small">Con fecha de vencimiento superada</span>
        </div>
    </div>
</div>

<!-- Filtros y Búsqueda -->
<div class="card border-0 shadow-sm rounded-3 bg-white mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('admin.cobranza.index') }}" class="row g-2 align-items-center">
            <div class="col-md-5">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="buscar" value="{{ $busqueda }}" class="form-control border-start-0" placeholder="Buscar por nombre, teléfono o dirección...">
                </div>
            </div>
            <div class="col-md-5">
                <div class="btn-group btn-group-sm w-100" role="group">
                    <a href="{{ route('admin.cobranza.index', ['buscar' => $busqueda, 'filtro' => 'todos']) }}" class="btn {{ $filtro === 'todos' ? 'btn-primary' : 'btn-outline-secondary' }}">Todos</a>
                    <a href="{{ route('admin.cobranza.index', ['buscar' => $busqueda, 'filtro' => 'con_adeudo']) }}" class="btn {{ $filtro === 'con_adeudo' ? 'btn-warning' : 'btn-outline-secondary' }}">Con Adeudo</a>
                    <a href="{{ route('admin.cobranza.index', ['buscar' => $busqueda, 'filtro' => 'morosos']) }}" class="btn {{ $filtro === 'morosos' ? 'btn-danger' : 'btn-outline-secondary' }}">Morosos</a>
                    <a href="{{ route('admin.cobranza.index', ['buscar' => $busqueda, 'filtro' => 'al_corriente']) }}" class="btn {{ $filtro === 'al_corriente' ? 'btn-success' : 'btn-outline-secondary' }}">Al Corriente</a>
                </div>
            </div>
            <div class="col-md-2 text-md-end">
                @if ($busqueda !== '' || $filtro !== 'todos')
                    <a href="{{ route('admin.cobranza.index') }}" class="btn btn-outline-secondary btn-sm w-100">Limpiar</a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Listado de Clientes con Estado de Cuenta -->
<div class="card border-0 shadow-sm rounded-3 bg-white">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small text-uppercase text-muted">
                    <tr>
                        <th>Cliente</th>
                        <th>Plan Contratado</th>
                        <th class="text-end">Total Cargos</th>
                        <th class="text-end">Total Pagado</th>
                        <th class="text-end">Saldo Pendiente</th>
                        <th>Estado de Cuenta</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody class="small">
                    @forelse ($clientes as $cli)
                        @php
                            $saldo = $cli->saldoPendiente();
                            $vencidos = $cli->cargos_vencidos_count ?? 0;
                        @endphp
                        <tr>
                            <td>
                                <div class="fw-bold text-dark">{{ $cli->nombre_completo }}</div>
                                <div class="text-muted" style="font-size: 0.8rem;">
                                    <i class="bi bi-telephone me-1"></i>{{ $cli->telefono }}
                                </div>
                            </td>
                            <td>
                                @if ($cli->plan)
                                    <span class="badge bg-primary-subtle text-primary">{{ $cli->plan->nombre }}</span>
                                    <div class="text-muted" style="font-size: 0.75rem;">${{ number_format($cli->plan->precio_mensual, 2) }}/mes</div>
                                @else
                                    <span class="text-muted small">Sin plan asignado</span>
                                @endif
                            </td>
                            <td class="text-end text-muted">${{ number_format((float) ($cli->total_cargos ?? 0), 2) }}</td>
                            <td class="text-end text-muted">${{ number_format((float) ($cli->total_pagado ?? 0), 2) }}</td>
                            <td class="text-end">
                                <span class="fw-bold fs-6 {{ $saldo > 0 ? 'text-danger' : 'text-success' }}">
                                    ${{ number_format($saldo, 2) }}
                                </span>
                            </td>
                            <td>
                                @if ($vencidos > 0)
                                    <span class="badge bg-danger">
                                        <i class="bi bi-exclamation-triangle-fill me-1"></i> Moroso ({{ $vencidos }} vencido{{ $vencidos > 1 ? 's' : '' }})
                                    </span>
                                @elseif ($saldo > 0)
                                    <span class="badge bg-warning text-dark">
                                        <i class="bi bi-clock-history me-1"></i> Pendiente
                                    </span>
                                @else
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle-fill me-1"></i> Al corriente
                                    </span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.cobranza.show', $cli) }}" class="btn btn-sm {{ $saldo > 0 ? 'btn-primary' : 'btn-outline-secondary' }}">
                                    <i class="bi bi-cash-coin me-1"></i> {{ $saldo > 0 ? 'Cobrar / Detalle' : 'Ver Estado' }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No se encontraron clientes que coincidan con los criterios.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if ($clientes->hasPages())
        <div class="card-footer bg-white border-0 pt-3">
            {{ $clientes->links() }}
        </div>
    @endif
</div>

<!-- Modal para Generación Masiva de Mensualidades -->
<div class="modal fade" id="modalGenerarCargos" tabindex="-1" aria-labelledby="modalGenerarCargosLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('admin.cargos.generar-masivo') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="modalGenerarCargosLabel">
                    <i class="bi bi-calendar-plus text-primary me-2"></i> Generar Mensualidades
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small">
                    Esta acción generará automáticamente el cargo mensual a todos los clientes <strong>activos</strong> que tengan un plan asignado.
                    Si un cliente ya tiene cargo generado para el periodo seleccionado, <strong>no se duplicará</strong>.
                </p>
                <div class="mb-3">
                    <label for="periodo" class="form-label fw-semibold small">Periodo (Año-Mes)</label>
                    <input type="month" class="form-control" id="periodo" name="periodo" value="{{ now()->format('Y-m') }}" required>
                    <div class="form-text">Por defecto se genera el mes en curso ({{ now()->format('Y-m') }}).</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="bi bi-lightning-charge-fill me-1"></i> Generar Cargos Ahora
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
