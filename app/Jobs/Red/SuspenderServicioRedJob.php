<?php

namespace App\Jobs\Red;

use App\Models\Router;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SuspenderServicioRedJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Router $router,
        public string $ipCliente,
        public string|int $folioCliente
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $ip = $this->router->ip_address;
        $user = $this->router->api_user;
        $pass = $this->router->api_password;
        $timeout = (int) config('mikrotik.timeout', config('services.mikrotik.timeout', 5));

        try {
            $response = Http::withBasicAuth($user, $pass)
                ->timeout($timeout)
                ->put("http://{$ip}/rest/ip/firewall/address-list", [
                    'list' => 'morosos',
                    'address' => $this->ipCliente,
                    'comment' => "Suspendido por sistema - Cliente: {$this->folioCliente}",
                ]);

            if ($response->successful()) {
                Log::info("MikroTik [{$this->router->nombre}]: Servicio suspendido exitosamente para cliente {$this->folioCliente} (IP: {$this->ipCliente}).", [
                    'router_id' => $this->router->id,
                    'router_nombre' => $this->router->nombre,
                    'status' => $response->status(),
                    'respuesta' => $response->json(),
                ]);
            } else {
                Log::error("MikroTik [{$this->router->nombre}]: Falló la suspensión para cliente {$this->folioCliente} (IP: {$this->ipCliente}).", [
                    'router_id' => $this->router->id,
                    'router_nombre' => $this->router->nombre,
                    'status' => $response->status(),
                    'error' => $response->body(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::error("MikroTik [{$this->router->nombre}]: Excepción al suspender servicio para cliente {$this->folioCliente} (IP: {$this->ipCliente}): {$e->getMessage()}", [
                'router_id' => $this->router->id,
                'router_nombre' => $this->router->nombre,
                'ip_cliente' => $this->ipCliente,
                'folio_cliente' => $this->folioCliente,
                'excepcion' => $e->getMessage(),
            ]);
        }
    }
}
