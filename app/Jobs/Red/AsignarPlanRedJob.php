<?php

namespace App\Jobs\Red;

use App\Actions\Red\MikrotikRestClient;
use App\Models\Plan;
use App\Models\Router;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class AsignarPlanRedJob implements ShouldBeUniqueUntilProcessing, ShouldQueue
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
        public string $ipCliente,
        public string $nombreCliente,
        public Router $router,
        public Plan $plan
    ) {}

    /**
     * Identificador único del trabajo en cola para evitar duplicar la misma asignación exacta.
     */
    public function uniqueId(): string
    {
        return "mikrotik-asignar-plan-{$this->router->id}-{$this->ipCliente}-{$this->plan->id}-".md5($this->nombreCliente);
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
        // Construir límites de velocidad en formato MikroTik Upload/Download (ej. "10M/20M")
        $maxLimit = "{$this->plan->velocidad_carga}M/{$this->plan->velocidad_descarga}M";

        try {
            // Paso A: Consultar si ya existe una Simple Queue asociada a la IP del cliente
            $response = $client->buscarSimpleQueuePorTarget($this->router, $this->ipCliente);

            if (! $response->successful()) {
                $mensajeError = "MikroTik [{$this->router->nombre}]: Error al consultar Simple Queue para {$this->nombreCliente} (IP: {$this->ipCliente}). HTTP {$response->status()}: {$response->body()}";

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

            $queues = $response->json();
            $colaExistente = (is_array($queues) && ! empty($queues)) ? $queues[0] : null;
            $id = is_array($colaExistente) ? ($colaExistente['.id'] ?? $colaExistente['id'] ?? null) : null;

            if (is_string($id) && $id !== '') {
                // Paso B: Si ya existe, actualizar velocidad mediante PATCH
                $patchResponse = $client->actualizarSimpleQueue($this->router, $id, [
                    'name' => $this->nombreCliente,
                    'max-limit' => $maxLimit,
                ]);

                if ($patchResponse->successful()) {
                    Log::info("MikroTik [{$this->router->nombre}]: Plan actualizado con éxito para {$this->nombreCliente} (IP: {$this->ipCliente}, Límite: {$maxLimit}).", [
                        'queue_id' => $id,
                        'plan' => $this->plan->nombre,
                    ]);
                } else {
                    $mensajeError = "MikroTik [{$this->router->nombre}]: Falló la actualización de la cola {$id} para {$this->nombreCliente}. HTTP {$patchResponse->status()}: {$patchResponse->body()}";

                    Log::error($mensajeError, [
                        'status' => $patchResponse->status(),
                        'error' => $patchResponse->body(),
                    ]);

                    if ($this->job && $this->job->getConnectionName() !== 'sync') {
                        throw new RuntimeException($mensajeError);
                    }
                }
            } else {
                // Paso C: Si no existe, crear nueva Simple Queue mediante PUT
                $putResponse = $client->crearSimpleQueue($this->router, [
                    'name' => $this->nombreCliente,
                    'target' => $this->ipCliente,
                    'max-limit' => $maxLimit,
                ]);

                if ($putResponse->successful()) {
                    Log::info("MikroTik [{$this->router->nombre}]: Plan asignado (nueva cola creada) para {$this->nombreCliente} (IP: {$this->ipCliente}, Límite: {$maxLimit}).", [
                        'plan' => $this->plan->nombre,
                        'respuesta' => $putResponse->json(),
                    ]);
                } else {
                    $mensajeError = "MikroTik [{$this->router->nombre}]: Falló la creación de la cola para {$this->nombreCliente} (IP: {$this->ipCliente}). HTTP {$putResponse->status()}: {$putResponse->body()}";

                    Log::error($mensajeError, [
                        'status' => $putResponse->status(),
                        'error' => $putResponse->body(),
                    ]);

                    if ($this->job && $this->job->getConnectionName() !== 'sync') {
                        throw new RuntimeException($mensajeError);
                    }
                }
            }
        } catch (Throwable $e) {
            Log::error("MikroTik [{$this->router->nombre}]: Excepción al configurar plan/ancho de banda para {$this->nombreCliente} (IP: {$this->ipCliente}): {$e->getMessage()}", [
                'router_id' => $this->router->id,
                'ip_cliente' => $this->ipCliente,
                'plan_id' => $this->plan->id,
                'max_limit' => $maxLimit,
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
        Log::critical("MikroTik [{$this->router->nombre}]: Fallo definitivo en AsignarPlanRedJob para {$this->nombreCliente} (IP: {$this->ipCliente}).", [
            'router_id' => $this->router->id,
            'ip_cliente' => $this->ipCliente,
            'plan_id' => $this->plan->id,
            'error' => $exception?->getMessage(),
        ]);
    }
}
