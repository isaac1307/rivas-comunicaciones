<?php

namespace App\Jobs\Red;

use App\Models\Router;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ReconectarServicioRedJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Router $router,
        public string $ipCliente
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
            // Paso A: Consultar si la IP está en la lista de morosos
            $response = Http::withBasicAuth($user, $pass)
                ->timeout($timeout)
                ->get("http://{$ip}/rest/ip/firewall/address-list", [
                    'list' => 'morosos',
                    'address' => $this->ipCliente,
                ]);

            if (! $response->successful()) {
                Log::error("MikroTik [{$this->router->nombre}]: Error al consultar lista de morosos para IP {$this->ipCliente}.", [
                    'router_id' => $this->router->id,
                    'status' => $response->status(),
                    'error' => $response->body(),
                ]);

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

            if (empty($id)) {
                Log::warning("MikroTik [{$this->router->nombre}]: No se pudo obtener el identificador (.id) para la IP {$this->ipCliente} en address-list.", [
                    'router_id' => $this->router->id,
                    'registro' => $primerRegistro,
                ]);

                return;
            }

            // Paso C: Eliminar de la lista de morosos para reconectar el servicio
            $deleteResponse = Http::withBasicAuth($user, $pass)
                ->timeout($timeout)
                ->delete("http://{$ip}/rest/ip/firewall/address-list/{$id}");

            if ($deleteResponse->successful()) {
                Log::info("MikroTik [{$this->router->nombre}]: Servicio reconectado exitosamente para la IP {$this->ipCliente} (Registro {$id} eliminado de morosos).");
            } else {
                Log::error("MikroTik [{$this->router->nombre}]: Falló la eliminación del registro {$id} para IP {$this->ipCliente}.", [
                    'router_id' => $this->router->id,
                    'status' => $deleteResponse->status(),
                    'error' => $deleteResponse->body(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::error("MikroTik [{$this->router->nombre}]: Excepción al reconectar servicio para IP {$this->ipCliente}: {$e->getMessage()}", [
                'router_id' => $this->router->id,
                'ip_cliente' => $this->ipCliente,
                'excepcion' => $e->getMessage(),
            ]);
        }
    }
}
