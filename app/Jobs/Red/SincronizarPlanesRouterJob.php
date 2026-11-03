<?php

namespace App\Jobs\Red;

use App\Actions\Red\SincronizarPlanesMikrotik;
use App\Models\Router;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SincronizarPlanesRouterJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Router $router
    ) {}

    /**
     * Execute the job.
     */
    public function handle(SincronizarPlanesMikrotik $action): void
    {
        Log::info("SincronizarPlanesRouterJob: Iniciando aprovisionamiento en background para router '{$this->router->nombre}' ({$this->router->ip_address}).");

        $resultado = $action->handle($this->router);

        if ($resultado['success']) {
            Log::info("SincronizarPlanesRouterJob completado exitosamente para router '{$this->router->nombre}'.", $resultado);
        } else {
            Log::warning("SincronizarPlanesRouterJob finalizó con advertencias para router '{$this->router->nombre}'.", $resultado);
        }
    }
}
