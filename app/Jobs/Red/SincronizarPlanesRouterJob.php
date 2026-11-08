<?php

namespace App\Jobs\Red;

use App\Actions\Red\SincronizarPlanesMikrotik;
use App\Models\Router;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class SincronizarPlanesRouterJob implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    /**
     * Número máximo de intentos antes de enviar el trabajo a failed_jobs.
     */
    public int $tries = 5;

    /**
     * Segundos durante los cuales se mantiene el bloqueo de unicidad.
     */
    public int $uniqueFor = 180;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Router $router
    ) {}

    /**
     * Identificador único del trabajo en cola por router.
     */
    public function uniqueId(): string
    {
        return "mikrotik-sincronizar-planes-{$this->router->id}";
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
    public function handle(SincronizarPlanesMikrotik $action = new SincronizarPlanesMikrotik): void
    {
        Log::info("SincronizarPlanesRouterJob: Iniciando aprovisionamiento en background para router '{$this->router->nombre}' ({$this->router->ip_address}).");

        $resultado = $action->handle($this->router);

        if ($resultado['success']) {
            Log::info("SincronizarPlanesRouterJob completado exitosamente para router '{$this->router->nombre}'.", $resultado);
        } else {
            Log::warning("SincronizarPlanesRouterJob finalizó con advertencias para router '{$this->router->nombre}'.", $resultado);

            if ($this->job && $this->job->getConnectionName() !== 'sync') {
                throw new RuntimeException($resultado['mensaje']);
            }
        }
    }

    /**
     * Maneja el fallo definitivo tras agotar todos los reintentos.
     */
    public function failed(?Throwable $exception): void
    {
        Log::critical("MikroTik [{$this->router->nombre}]: Fallo definitivo en SincronizarPlanesRouterJob.", [
            'router_id' => $this->router->id,
            'error' => $exception?->getMessage(),
        ]);
    }
}
