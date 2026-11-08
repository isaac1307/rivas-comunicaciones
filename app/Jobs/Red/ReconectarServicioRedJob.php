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

class ReconectarServicioRedJob implements ShouldBeUnique, ShouldQueue
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
        public string $ipCliente
    ) {}

    /**
     * Identificador único del trabajo en cola para evitar colisiones por IP en el mismo router.
     */
    public function uniqueId(): string
    {
        return "mikrotik-reconectar-{$this->router->id}-{$this->ipCliente}";
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
            // Paso A: Consultar si la IP está en la lista de morosos
            $response = $client->buscarEnAddressList($this->router, $this->ipCliente, 'morosos');

            if (! $response->successful()) {
                $mensajeError = "MikroTik [{$this->router->nombre}]: Error al consultar lista de morosos para IP {$this->ipCliente}. HTTP {$response->status()}: {$response->body()}";

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

            $registros = $response->json();

            // Paso B: Verificar si existen registros
            if (! is_array($registros) || empty($registros)) {
                Log::info("MikroTik [{$this->router->nombre}]: La IP {$this->ipCliente} no se encuentra en la lista de morosos (el servicio ya está activo).");

                return;
            }

            $primerRegistro = is_array($registros[0] ?? null) ? $registros[0] : [];
            $id = $primerRegistro['.id'] ?? $primerRegistro['id'] ?? null;

            if (! is_string($id) || $id === '') {
                Log::warning("MikroTik [{$this->router->nombre}]: No se pudo obtener el identificador (.id) para la IP {$this->ipCliente} en address-list.", [
                    'router_id' => $this->router->id,
                    'registro' => $primerRegistro,
                ]);

                return;
            }

            // Paso C: Eliminar de la lista de morosos para reconectar el servicio
            $deleteResponse = $client->eliminarDeAddressListPorId($this->router, $id);

            if ($deleteResponse->successful()) {
                Log::info("MikroTik [{$this->router->nombre}]: Servicio reconectado exitosamente para la IP {$this->ipCliente} (Registro {$id} eliminado de morosos).");
            } else {
                $mensajeError = "MikroTik [{$this->router->nombre}]: Falló la eliminación del registro {$id} para IP {$this->ipCliente}. HTTP {$deleteResponse->status()}: {$deleteResponse->body()}";

                Log::error($mensajeError, [
                    'router_id' => $this->router->id,
                    'status' => $deleteResponse->status(),
                    'error' => $deleteResponse->body(),
                ]);

                if ($this->job && $this->job->getConnectionName() !== 'sync') {
                    throw new RuntimeException($mensajeError);
                }
            }
        } catch (Throwable $e) {
            Log::error("MikroTik [{$this->router->nombre}]: Excepción al reconectar servicio para IP {$this->ipCliente}: {$e->getMessage()}", [
                'router_id' => $this->router->id,
                'ip_cliente' => $this->ipCliente,
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
        Log::critical("MikroTik [{$this->router->nombre}]: Fallo definitivo en ReconectarServicioRedJob para IP {$this->ipCliente}.", [
            'router_id' => $this->router->id,
            'ip_cliente' => $this->ipCliente,
            'error' => $exception?->getMessage(),
        ]);
    }
}
