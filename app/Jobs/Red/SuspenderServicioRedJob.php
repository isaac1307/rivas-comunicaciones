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

            if (! $response->successful() && $response->status() !== 400) {
                Log::error("MikroTik [{$this->router->nombre}]: Falló la suspensión para cliente {$this->folioCliente} (IP: {$this->ipCliente}).", [
                    'router_id' => $this->router->id,
                    'status' => $response->status(),
                    'error' => $response->body(),
                ]);
            }

            // Cerrar conexiones TCP/UDP activas para que el corte no espere el timeout de NAT/FastTrack
            $connsRes = Http::withBasicAuth($user, $pass)
                ->timeout($timeout)
                ->get("http://{$ip}/rest/ip/firewall/connection", [
                    'src-address' => $this->ipCliente,
                ]);

            $conexiones = $connsRes->json();
            $cerradas = 0;
            if (is_array($conexiones) && ! empty($conexiones)) {
                foreach ($conexiones as $conn) {
                    $id = $conn['.id'] ?? $conn['id'] ?? null;
                    if ($id) {
                        $del = Http::withBasicAuth($user, $pass)
                            ->timeout($timeout)
                            ->delete("http://{$ip}/rest/ip/firewall/connection/{$id}");
                        if ($del->successful()) {
                            $cerradas++;
                        }
                    }
                }
            }

            Log::info("MikroTik [{$this->router->nombre}]: Servicio suspendido para cliente {$this->folioCliente} (IP: {$this->ipCliente}). Conexiones cerradas: {$cerradas}.");
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
