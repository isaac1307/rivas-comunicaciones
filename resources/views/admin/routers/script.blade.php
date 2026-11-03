@extends('layouts.admin')

@section('title', 'Script MikroTik - ' . $router->nombre)

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.routers.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
        <i class="bi bi-arrow-left me-1"></i> Volver a Nodos MikroTik
    </a>
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h2 class="fw-bold mb-1">
                <i class="bi bi-terminal-fill text-primary me-2"></i> Script de Aprovisionamiento MikroTik
            </h2>
            <p class="text-muted small mb-0">
                Script RouterOS CLI para configurar planes de velocidad, colas y reglas de corte en el nodo <strong>{{ $router->nombre }}</strong> (<span class="font-monospace text-primary">{{ $router->ip_address }}</span>).
            </p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-success btn-sm fw-bold shadow-sm" id="btnCopiarScript" onclick="copiarAlPortapapeles()">
                <i class="bi bi-clipboard-check me-1" id="iconoCopiar"></i> <span id="textoCopiar">Copiar al Portapapeles</span>
            </button>
            <a href="{{ route('admin.routers.descargar-script', $router) }}" class="btn btn-outline-primary btn-sm fw-bold shadow-sm">
                <i class="bi bi-download me-1"></i> Descargar archivo .rsc
            </a>
            <form action="{{ route('admin.routers.sincronizar-planes', $router) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-primary btn-sm fw-bold shadow-sm" title="Ejecutar aprovisionamiento directo por REST API">
                    <i class="bi bi-arrow-repeat me-1"></i> Sincronizar vía API
                </button>
            </form>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Columna Izquierda: Instrucciones y Resumen de Planes -->
    <div class="col-lg-4">
        <!-- Tarjeta de Instrucciones de WinBox -->
        <div class="card border-0 shadow-sm rounded-3 bg-white mb-3">
            <div class="card-header bg-primary text-white py-3 rounded-top-3">
                <h6 class="fw-bold mb-0">
                    <i class="bi bi-info-circle me-1"></i> ¿Cómo aplicarlo en MikroTik?
                </h6>
            </div>
            <div class="card-body small">
                <ol class="ps-3 mb-0 text-muted d-flex flex-column gap-2">
                    <li>
                        <strong>Abre WinBox</strong> y conéctate al router en <span class="font-monospace text-dark fw-bold">{{ $router->ip_address }}</span> con tu usuario administrador.
                    </li>
                    <li>
                        En el menú vertical izquierdo, haz clic en <span class="badge bg-dark text-white font-monospace">New Terminal</span>.
                    </li>
                    <li>
                        Haz clic en el botón verde superior <strong>"Copiar al Portapapeles"</strong> de esta pantalla.
                    </li>
                    <li>
                        En la terminal negra de WinBox, haz <strong>clic derecho</strong> y presiona <strong>Paste</strong>.
                    </li>
                    <li>
                        ¡Listo! El script verificará y creará automáticamente los perfiles de velocidad y las reglas de suspensión.
                    </li>
                </ol>
            </div>
        </div>

        <!-- Tarjeta de Planes Incluidos -->
        <div class="card border-0 shadow-sm rounded-3 bg-white">
            <div class="card-header bg-light py-3">
                <h6 class="fw-bold text-dark mb-0">
                    <i class="bi bi-speedometer2 text-primary me-1"></i> Planes que se configurarán ({{ $planes->count() }})
                </h6>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush small">
                    @forelse ($planes as $plan)
                        <li class="list-group-item d-flex justify-content-between align-items-center py-2 px-3">
                            <div>
                                <span class="fw-bold text-dark">{{ $plan->nombre }}</span>
                                <div class="text-muted small" style="font-size: 0.75rem;">
                                    ID #{{ $plan->id }} &bull; ${{ number_format((float) $plan->precio_mensual, 2) }}/mes
                                </div>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-primary-subtle text-primary font-monospace">
                                    <i class="bi bi-arrow-down"></i> {{ $plan->velocidad_descarga }}M / <i class="bi bi-arrow-up"></i> {{ $plan->velocidad_carga }}M
                                </span>
                            </div>
                        </li>
                    @empty
                        <li class="list-group-item text-muted text-center py-3">
                            No hay planes activos registrados en el catálogo.
                        </li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

    <!-- Columna Derecha: Visor de Script RouterOS -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-3 bg-dark text-white">
            <div class="card-header bg-black bg-opacity-50 py-2 px-3 d-flex justify-content-between align-items-center border-bottom border-secondary">
                <div class="d-flex align-items-center gap-2">
                    <span class="rounded-circle bg-danger d-inline-block" style="width: 10px; height: 10px;"></span>
                    <span class="rounded-circle bg-warning d-inline-block" style="width: 10px; height: 10px;"></span>
                    <span class="rounded-circle bg-success d-inline-block" style="width: 10px; height: 10px;"></span>
                    <span class="font-monospace text-muted small ms-2">MikroTik RouterOS Terminal Script (.rsc)</span>
                </div>
                <div>
                    <span class="badge bg-secondary font-monospace" style="font-size: 0.7rem;">RouterOS v7 & v6</span>
                </div>
            </div>
            <div class="card-body p-0 position-relative">
                <textarea id="rawScriptContent" class="d-none">{{ $script }}</textarea>
                <pre class="m-0 p-3 text-light font-monospace small" style="max-height: 600px; overflow-y: auto; white-space: pre-wrap; word-break: break-all; line-height: 1.5; font-size: 0.85rem; background-color: #121820;"><code class="language-bash">{{ $script }}</code></pre>
            </div>
            <div class="card-footer bg-black bg-opacity-50 py-2 px-3 d-flex justify-content-between align-items-center border-top border-secondary small">
                <span class="text-muted">
                    <i class="bi bi-shield-check text-success me-1"></i> Script idempotente: es seguro ejecutarlo múltiples veces sin duplicar reglas.
                </span>
                <button type="button" class="btn btn-sm btn-outline-light" onclick="copiarAlPortapapeles()">
                    <i class="bi bi-clipboard me-1"></i> Copiar Código
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function copiarAlPortapapeles() {
        const scriptText = document.getElementById('rawScriptContent').value;
        const textoCopiar = document.getElementById('textoCopiar');
        const iconoCopiar = document.getElementById('iconoCopiar');
        const btn = document.getElementById('btnCopiarScript');

        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(scriptText).then(mostrarExitoCopiado).catch(copiarFallback);
        } else {
            copiarFallback();
        }

        function copiarFallback() {
            const textarea = document.createElement('textarea');
            textarea.value = scriptText;
            textarea.style.position = 'fixed';
            textarea.style.opacity = '0';
            document.body.appendChild(textarea);
            textarea.select();
            try {
                document.execCommand('copy');
                mostrarExitoCopiado();
            } catch (err) {
                alert('No se pudo copiar automáticamente. Por favor selecciona el texto y presiona Ctrl+C.');
            }
            document.body.removeChild(textarea);
        }

        function mostrarExitoCopiado() {
            textoCopiar.innerText = '¡Copiado con Éxito!';
            btn.classList.remove('btn-success');
            btn.classList.add('btn-light', 'text-success');
            iconoCopiar.classList.remove('bi-clipboard-check');
            iconoCopiar.classList.add('bi-check-all');

            setTimeout(() => {
                textoCopiar.innerText = 'Copiar al Portapapeles';
                btn.classList.remove('btn-light', 'text-success');
                btn.classList.add('btn-success');
                iconoCopiar.classList.remove('bi-check-all');
                iconoCopiar.classList.add('bi-clipboard-check');
            }, 3000);
        }
    }
</script>
@endpush
