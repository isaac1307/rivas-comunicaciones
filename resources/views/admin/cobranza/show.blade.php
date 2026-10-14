@extends('layouts.admin')

@section('title', 'Estado de Cuenta - ' . $cliente->nombre_completo)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <a href="{{ route('admin.cobranza.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
            <i class="bi bi-arrow-left me-1"></i> Volver a Cobranza
        </a>
        <h2 class="fw-bold mb-1">Estado de Cuenta</h2>
        <span class="badge bg-light text-dark border">Cliente #{{ $cliente->id }} • {{ $cliente->nombre_completo }}</span>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.cargos.create', $cliente) }}" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-plus-circle me-1"></i> Agregar Cargo Extra
        </a>
        @if ($totales['saldoPendiente'] > 0)
            <a href="#seccion-pago" class="btn btn-success btn-sm">
                <i class="bi bi-cash-stack me-1"></i> Registrar Pago
            </a>
        @endif
    </div>
</div>

<!-- Tarjetas Resumen del Cliente -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-3 bg-white p-3 h-100">
            <span class="text-muted small">Plan de Internet</span>
            @if ($cliente->plan)
                <div class="fs-5 fw-bold text-primary mt-1">{{ $cliente->plan->nombre }}</div>
                <div class="text-muted small">${{ number_format($cliente->plan->precio_mensual, 2) }}/mes</div>
            @else
                <div class="fs-6 text-muted mt-1">Sin plan contratado</div>
            @endif
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-3 bg-white p-3 h-100">
            <span class="text-muted small">Contacto</span>
            <div class="fs-6 fw-bold text-dark mt-1"><i class="bi bi-telephone text-primary me-1"></i> {{ $cliente->telefono }}</div>
            <div class="text-muted small text-truncate"><i class="bi bi-geo-alt me-1"></i> {{ $cliente->direccion }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-3 bg-white p-3 h-100">
            <span class="text-muted small">Total Histórico</span>
            <div class="small mt-1">Facturado: <strong>${{ number_format($totales['totalFacturado'], 2) }}</strong></div>
            <div class="small text-success">Pagado: <strong>${{ number_format($totales['totalPagado'], 2) }}</strong></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-3 bg-white p-3 h-100 border-start {{ $totales['saldoPendiente'] > 0 ? 'border-danger' : 'border-success' }} border-4">
            <span class="text-muted small text-uppercase fw-semibold">Saldo Pendiente</span>
            <div class="fs-3 fw-bold {{ $totales['saldoPendiente'] > 0 ? 'text-danger' : 'text-success' }} mt-1">
                ${{ number_format($totales['saldoPendiente'], 2) }}
            </div>
            @if ($totales['cargosVencidos'] > 0)
                <span class="badge bg-danger small mt-1">
                    <i class="bi bi-exclamation-circle me-1"></i> {{ $totales['cargosVencidos'] }} cargo(s) vencido(s)
                </span>
            @elseif ($totales['saldoPendiente'] > 0)
                <span class="badge bg-warning text-dark small mt-1">Al corriente pero con saldo</span>
            @else
                <span class="badge bg-success small mt-1"><i class="bi bi-check2-circle me-1"></i> Cuenta al día</span>
            @endif
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Formulario de Registro de Pago -->
    <div class="col-lg-5" id="seccion-pago">
        <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
            <div class="card-header bg-white border-0 pt-3 pb-0">
                <h5 class="fw-bold mb-1"><i class="bi bi-cash-coin text-success me-2"></i>Registrar Pago</h5>
                <p class="text-muted small mb-0">El monto se aplicará automáticamente a los cargos pendientes más antiguos.</p>
            </div>
            <div class="card-body">
                @if ($totales['saldoPendiente'] > 0)
                    <form method="POST" action="{{ route('admin.pagos.store', $cliente) }}">
                        @csrf
                        <div class="mb-3">
                            <label for="monto" class="form-label fw-semibold small">Monto a Cobrar ($)</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" min="0.01" max="{{ $totales['saldoPendiente'] }}" 
                                       class="form-control form-control-lg fw-bold text-success" 
                                       id="monto" name="monto" value="{{ old('monto', $totales['saldoPendiente']) }}" required>
                            </div>
                            <div class="form-text d-flex justify-content-between">
                                <span>Saldo pendiente máximo: ${{ number_format($totales['saldoPendiente'], 2) }}</span>
                                <a href="javascript:void(0)" onclick="document.getElementById('monto').value = '{{ $totales['saldoPendiente'] }}'" class="text-decoration-none small">
                                    Liquidar todo
                                </a>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="metodo_pago" class="form-label fw-semibold small">Método de Pago</label>
                            <select class="form-select" id="metodo_pago" name="metodo_pago" required>
                                @foreach ($metodosPago as $valor => $etiqueta)
                                    <option value="{{ $valor }}" {{ old('metodo_pago', 'efectivo') === $valor ? 'selected' : '' }}>
                                        {{ $etiqueta }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="referencia" class="form-label fw-semibold small">Referencia / Folio de comprobante (opcional)</label>
                            <input type="text" class="form-control form-control-sm" id="referencia" name="referencia" value="{{ old('referencia') }}" placeholder="Núm. de autorización, cheque o transferencia">
                        </div>

                        <div class="mb-3">
                            <label for="notas" class="form-label fw-semibold small">Notas u Observaciones (opcional)</label>
                            <textarea class="form-control form-control-sm" id="notas" name="notas" rows="2" placeholder="Detalles de la cobranza...">{{ old('notas') }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-success w-100 fw-semibold">
                            <i class="bi bi-check2-circle me-1"></i> Confirmar y Aplicar Pago
                        </button>
                    </form>
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-check-circle-fill text-success fs-1"></i>
                        <h6 class="fw-bold mt-2">¡El cliente está al corriente!</h6>
                        <p class="text-muted small">No tiene saldos pendientes por cobrar en este momento.</p>
                        <a href="{{ route('admin.cargos.create', $cliente) }}" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-plus-circle me-1"></i> Generar un cargo extraordinario
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Desglose de Cargos Pendientes e Históricos -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
            <div class="card-header bg-white border-0 pt-3 pb-0 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0"><i class="bi bi-receipt text-primary me-2"></i>Historial de Cargos</h5>
                <span class="badge bg-light text-dark border">{{ $cargos->count() }} registro(s)</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light small text-uppercase text-muted">
                            <tr>
                                <th>Concepto</th>
                                <th>Periodo</th>
                                <th>Vencimiento</th>
                                <th class="text-end">Monto</th>
                                <th class="text-end">Saldo</th>
                                <th>Estado</th>
                                @if (auth()->check() && auth()->user()->isAdmin())
                                    <th class="text-end">Acción</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="small">
                            @forelse ($cargos as $cargo)
                                <tr class="{{ $cargo->estaVencido() ? 'table-danger' : '' }}">
                                    <td>
                                        <div class="fw-semibold">{{ $cargo->concepto }}</div>
                                        <span class="badge bg-light text-secondary border text-capitalize" style="font-size: 0.7rem;">
                                            {{ $cargo->tipo }}
                                        </span>
                                    </td>
                                    <td>{{ $cargo->periodo }}</td>
                                    <td>{{ $cargo->fecha_vencimiento->format('d/m/Y') }}</td>
                                    <td class="text-end text-muted">${{ number_format((float) $cargo->monto, 2) }}</td>
                                    <td class="text-end fw-bold {{ $cargo->saldo() > 0 ? 'text-danger' : 'text-success' }}">
                                        ${{ number_format($cargo->saldo(), 2) }}
                                    </td>
                                    <td>
                                        @if ($cargo->estado === 'pagado')
                                            <span class="badge bg-success">Pagado</span>
                                        @elseif ($cargo->estado === 'parcial')
                                            <span class="badge bg-warning text-dark">Abonado</span>
                                        @elseif ($cargo->estado === 'cancelado')
                                            <span class="badge bg-secondary">Cancelado</span>
                                        @elseif ($cargo->estaVencido())
                                            <span class="badge bg-danger">Vencido</span>
                                        @else
                                            <span class="badge bg-warning text-dark">Pendiente</span>
                                        @endif
                                    </td>
                                    @if (auth()->check() && auth()->user()->isAdmin())
                                        <td class="text-end">
                                            @if ($cargo->estado !== 'cancelado' && (float) $cargo->monto_pagado <= 0)
                                                <form action="{{ route('admin.cargos.cancelar', $cargo) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Seguro que deseas cancelar este cargo?');">
                                                    @csrf
                                                    <button type="submit" class="btn btn-outline-danger btn-sm py-0 px-1" title="Cancelar Cargo">
                                                        <i class="bi bi-x"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-3">No hay cargos generados para este cliente.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Historial de Pagos Realizados -->
<div class="card border-0 shadow-sm rounded-3 bg-white mb-4">
    <div class="card-header bg-white border-0 pt-3 pb-0 d-flex justify-content-between align-items-center">
        <div>
            <h5 class="fw-bold mb-0"><i class="bi bi-clock-history text-success me-2"></i>Historial de Pagos Recibidos</h5>
            <p class="text-muted small mb-0">Recibos emitidos, método de pago y cobrador responsable.</p>
        </div>
        <span class="badge bg-light text-dark border">{{ $pagos->count() }} pago(s)</span>
    </div>
    <div class="card-body p-0 mt-2">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small text-uppercase text-muted">
                    <tr>
                        <th>Folio</th>
                        <th>Fecha y Hora</th>
                        <th>Método</th>
                        <th>Referencia</th>
                        <th>Recibido por</th>
                        <th class="text-end">Monto</th>
                        <th>Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody class="small">
                    @forelse ($pagos as $pago)
                        <tr class="{{ $pago->estaAnulado() ? 'opacity-50 text-decoration-line-through' : '' }}">
                            <td>
                                <span class="fw-bold text-dark">{{ $pago->folio }}</span>
                            </td>
                            <td>{{ $pago->fecha_pago->format('d/m/Y H:i') }}</td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    {{ $pago->metodoEtiqueta() }}
                                </span>
                            </td>
                            <td class="text-muted">{{ $pago->referencia ?: '—' }}</td>
                            <td>
                                <span class="text-muted small">
                                    <i class="bi bi-person me-1"></i>{{ $pago->cobrador?->nombre_completo ?: ($pago->cobrador?->nombre_usuario ?: 'Sistema') }}
                                </span>
                            </td>
                            <td class="text-end fw-bold {{ $pago->estaAnulado() ? 'text-secondary' : 'text-success' }}">
                                ${{ number_format((float) $pago->monto, 2) }}
                            </td>
                            <td>
                                @if ($pago->estaAnulado())
                                    <span class="badge bg-danger" title="Motivo: {{ $pago->motivo_anulacion }}">Anulado</span>
                                @else
                                    <span class="badge bg-success">Aplicado</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.pagos.recibo', $pago) }}" target="_blank" class="btn btn-outline-primary btn-sm py-0 px-2" title="Imprimir Recibo">
                                    <i class="bi bi-printer me-1"></i> Recibo
                                </a>

                                @if (auth()->check() && auth()->user()->isAdmin() && ! $pago->estaAnulado())
                                    <button type="button" class="btn btn-outline-danger btn-sm py-0 px-2" data-bs-toggle="modal" data-bs-target="#modalAnularPago{{ $pago->id }}" title="Anular Pago">
                                        <i class="bi bi-x-circle"></i>
                                    </button>

                                    <!-- Modal Anulación de Pago -->
                                    <div class="modal fade" id="modalAnularPago{{ $pago->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered text-start">
                                            <form method="POST" action="{{ route('admin.pagos.anular', $pago) }}" class="modal-content">
                                                @csrf
                                                <div class="modal-header">
                                                    <h6 class="modal-title fw-bold text-danger">
                                                        <i class="bi bi-exclamation-triangle-fill me-2"></i> Anular Pago {{ $pago->folio }}
                                                    </h6>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p class="small text-muted mb-2">
                                                        Al anular este pago de <strong>${{ number_format((float) $pago->monto, 2) }}</strong>,
                                                        los cargos cubiertos volverán a quedar con saldo pendiente automáticamente.
                                                    </p>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold small">Motivo de anulación:</label>
                                                        <textarea name="motivo_anulacion" class="form-control form-control-sm" rows="3" required placeholder="Ej: Error en captura de monto / cheque rebotado"></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                                                    <button type="submit" class="btn btn-danger btn-sm">Confirmar Anulación</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-3">No hay pagos registrados para este cliente.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
