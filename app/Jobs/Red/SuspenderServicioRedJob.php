<?php

namespace App\Jobs\Red;

use App\Actions\Red\MikrotikRestClient;
use App\Models\Router;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class SuspenderServicioRedJob implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    /**
     * Número máximo de intentos antes de enviar el trabajo a failed_jobs.
     */
    public int $tries = 5;

    /**
     * Segundos durante los cuales se mantiene el bloqueo de unicidad.
     */
    public int $uniqueFor = 120;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Router $router,
        public string $ipCliente,
        public string|int $folioCliente
    ) {}

    /**
     * Identificador único del trabajo en cola para evitar colisiones por IP en el mismo router.
     */
    public function uniqueId(): string
    {
        return "mikrotik-suspender-{$this->router->id}-{$this->ipCliente}";
    }

    /**
     * Escalado progresivo entre reintentos (en segundos).
     *
     * @return list<int>
     */
    public function backoff(): array
    {
        return [10, 30, 60, 300];
    }

    /**
     * Execute the job.
     */
    public function handle(MikrotikRestClient $client = new MikrotikRestClient): void
    {
        try {
            $response = $client->agregarAAddressList(
                $this->router,
                $this->ipCliente,
                "Suspendido por sistema - Cliente: {$this->folioCliente}",
                'morosos'
            );

            if (! $response->successful() && $response->status() !== 400) {
                $mensajeError = "MikroTik [{$this->router->nombre}]: Falló la suspensión para cliente {$this->folioCliente} (IP: {$this->ipCliente}). HTTP {$response->status()}: {$response->body()}";

                Log::error($mensajeError, [
                    'router_id' => $this->router->id,
                    'status' => $response->status(),
                    'error' => $response->body(),
                ]);

                if ($this->job && $this->job->getConnectionName() !== 'sync') {
                    throw new RuntimeException($mensajeError);
                }

                return;
            }

            // Cerrar conexiones TCP/UDP activas para que el corte no espere el timeout de NAT/FastTrack
            $cerradas = 0;
            try {
                $cerradas = $client->cerrarConexionesActivas($this->router, $this->ipCliente);
            } catch (Throwable $e) {
                Log::warning("MikroTik [{$this->router->nombre}]: No se pudieron cerrar conexiones activas tras suspender {$this->ipCliente}: {$e->getMessage()}");
            }

            Log::info("MikroTik [{$this->router->nombre}]: Servicio suspendido para cliente {$this->folioCliente} (IP: {$this->ipCliente}). Conexiones cerradas: {$cerradas}.");
        } catch (Throwable $e) {
            Log::error("MikroTik [{$this->router->nombre}]: Excepción al suspender servicio para cliente {$this->folioCliente} (IP: {$this->ipCliente}): {$e->getMessage()}", [
                'router_id' => $this->router->id,
                'router_nombre' => $this->router->nombre,
                'ip_cliente' => $this->ipCliente,
                'folio_cliente' => $this->folioCliente,
                'excepcion' => $e->getMessage(),
            ]);

            if ($this->job && $this->job->getConnectionName() !== 'sync') {
                throw $e;
            }
        }
    }

    /**
     * Maneja el fallo definitivo tras agotar todos los reintentos.
     */
    public function failed(?Throwable $exception): void
    {
        Log::critical("MikroTik [{$this->router->nombre}]: Fallo definitivo en SuspenderServicioRedJob para cliente {$this->folioCliente} (IP: {$this->ipCliente}).", [
            'router_id' => $this->router->id,
            'ip_cliente' => $this->ipCliente,
            'folio_cliente' => $this->folioCliente,
            'error' => $exception?->getMessage(),
        ]);
    }
}
