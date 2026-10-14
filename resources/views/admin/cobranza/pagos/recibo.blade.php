<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recibo de Pago {{ $pago->folio }} - Rivas Comunicaciones</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background-color: #f8fafc;
            font-family: system-ui, -apple-system, sans-serif;
            color: #1e293b;
        }
        .receipt-card {
            max-width: 750px;
            margin: 30px auto;
            background: #fff;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .watermark-anulado {
            position: absolute;
            top: 40%;
            left: 20%;
            transform: rotate(-30deg);
            font-size: 5rem;
            font-weight: 900;
            color: rgba(220, 53, 69, 0.2);
            text-transform: uppercase;
            pointer-events: none;
        }
        @media print {
            body {
                background: #fff;
            }
            .receipt-card {
                box-shadow: none;
                margin: 0;
                padding: 0;
                max-width: 100%;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="text-center my-3 no-print">
        <button onclick="window.print()" class="btn btn-primary btn-sm me-2">
            <i class="bi bi-printer me-1"></i> Imprimir Recibo
        </button>
        <button onclick="window.close()" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-x me-1"></i> Cerrar
        </button>
    </div>

    <div class="receipt-card position-relative border">
        @if ($pago->estaAnulado())
            <div class="watermark-anulado">ANULADO</div>
        @endif

        <!-- Encabezado de la Empresa -->
        <div class="d-flex justify-content-between align-items-start border-bottom pb-4 mb-4">
            <div>
                <h4 class="fw-bold text-primary mb-1 d-flex align-items-center gap-2">
                    <i class="bi bi-hdd-network"></i> RIVAS COMUNICACIONES
                </h4>
                <div class="text-muted small">Servicios de Internet de Alta Velocidad y Telecomunicaciones</div>
                <div class="text-muted small">Rivas, Nicaragua • Tel: (+505) 2563-0000</div>
            </div>
            <div class="text-end">
                <span class="badge {{ $pago->estaAnulado() ? 'bg-danger' : 'bg-success' }} fs-6 px-3 py-2">
                    {{ $pago->estaAnulado() ? 'RECIBO ANULADO' : 'RECIBO DE PAGO' }}
                </span>
                <div class="fw-bold fs-5 text-dark mt-2">{{ $pago->folio }}</div>
                <div class="text-muted small">Fecha: {{ $pago->fecha_pago->format('d/m/Y h:i A') }}</div>
            </div>
        </div>

        <!-- Datos del Cliente y Cobrador -->
        <div class="row g-3 mb-4">
            <div class="col-sm-7">
                <div class="p-3 bg-light rounded-3">
                    <span class="text-muted small text-uppercase fw-semibold d-block">Datos del Suscriptor</span>
                    <strong class="fs-6 text-dark d-block">{{ $pago->cliente->nombre_completo }}</strong>
                    <div class="small text-muted"><i class="bi bi-telephone me-1"></i> {{ $pago->cliente->telefono }}</div>
                    <div class="small text-muted"><i class="bi bi-geo-alt me-1"></i> {{ $pago->cliente->direccion }}</div>
                    @if ($pago->cliente->plan)
                        <div class="small text-primary mt-1"><i class="bi bi-router me-1"></i> Plan: {{ $pago->cliente->plan->nombre }} (${{ number_format($pago->cliente->plan->precio_mensual, 2) }}/mes)</div>
                    @endif
                </div>
            </div>
            <div class="col-sm-5">
                <div class="p-3 bg-light rounded-3 h-100">
                    <span class="text-muted small text-uppercase fw-semibold d-block">Detalles de Cobro</span>
                    <div class="small mt-1">Método: <strong>{{ $pago->metodoEtiqueta() }}</strong></div>
                    @if ($pago->referencia)
                        <div class="small">Referencia: <strong>{{ $pago->referencia }}</strong></div>
                    @endif
                    <div class="small text-muted mt-2">
                        Atendido por: <strong>{{ $pago->cobrador?->nombre_completo ?: ($pago->cobrador?->nombre_usuario ?: 'Personal en turno') }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Desglose de Cargos Cubiertos -->
        <div class="mb-4">
            <h6 class="fw-bold mb-2">Desglose de Conceptos Aplicados</h6>
            <div class="table-responsive">
                <table class="table table-bordered table-sm align-middle mb-0">
                    <thead class="table-light small">
                        <tr>
                            <th>Concepto</th>
                            <th>Periodo</th>
                            <th class="text-end">Costo Total</th>
                            <th class="text-end">Monto Abonado / Liquidado</th>
                        </tr>
                    </thead>
                    <tbody class="small">
                        @forelse ($pago->cargos as $cargo)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $cargo->concepto }}</div>
                                    <span class="text-muted" style="font-size: 0.75rem;">Cargo #{{ $cargo->id }}</span>
                                </td>
                                <td>{{ $cargo->periodo }}</td>
                                <td class="text-end text-muted">${{ number_format((float) $cargo->monto, 2) }}</td>
                                <td class="text-end fw-bold text-success">${{ number_format((float) $cargo->pivot->monto_aplicado, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">Pago general aplicado a saldo.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="3" class="text-end text-uppercase fs-6">Total Recibido:</th>
                            <th class="text-end text-success fs-5 fw-bold">${{ number_format((float) $pago->monto, 2) }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        @if ($pago->notas)
            <div class="mb-4 p-2 bg-light rounded small">
                <strong>Notas:</strong> {{ $pago->notas }}
            </div>
        @endif

        @if ($pago->estaAnulado())
            <div class="alert alert-danger small mb-4">
                <strong>Motivo de Anulación:</strong> {{ $pago->motivo_anulacion }}
            </div>
        @endif

        <!-- Pie y Firma -->
        <div class="row pt-4 mt-4 border-top text-center text-muted small">
            <div class="col-6">
                <div class="border-top pt-2 mx-auto" style="width: 70%;">
                    Firma del Cliente
                </div>
            </div>
            <div class="col-6">
                <div class="border-top pt-2 mx-auto" style="width: 70%;">
                    Sello / Firma de Cobranza
                </div>
            </div>
            <div class="col-12 mt-4" style="font-size: 0.75rem;">
                Comprobante de pago generado por el sistema Rivas Comunicaciones. Conserve este recibo para cualquier aclaración de su servicio de internet.
            </div>
        </div>
    </div>
</div>

</body>
</html>
