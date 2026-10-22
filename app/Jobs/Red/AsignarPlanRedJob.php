<?php

namespace App\Jobs\Red;

use App\Models\Plan;
use App\Models\Router;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AsignarPlanRedJob implements ShouldQueue
{
    use Queueable;

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
     * Execute the job.
     */
    public function handle(): void
    {
        $ip = $this->router->ip_address;
        $user = $this->router->api_user;
        $pass = $this->router->api_password;
        $timeout = (int) config('mikrotik.timeout', config('services.mikrotik.timeout', 5));

        // Construir límites de velocidad en formato MikroTik Upload/Download (ej. "10M/20M")
        $maxLimit = "{$this->plan->velocidad_carga}M/{$this->plan->velocidad_descarga}M";

        try {
            // Paso A: Consultar si ya existe una Simple Queue asociada a la IP del cliente
            $response = Http::withBasicAuth($user, $pass)
                ->timeout($timeout)
                ->get("http://{$ip}/rest/queue/simple", [
                    'target' => $this->ipCliente,
                ]);

            if (! $response->successful()) {
                Log::error("MikroTik [{$this->router->nombre}]: Error al consultar cola simple para IP {$this->ipCliente}.", [
                    'router_id' => $this->router->id,
                    'status' => $response->status(),
                    'error' => $response->body(),
                ]);

                return;
            }

            $colas = $response->json();
            $colaExistente = (is_array($colas) && ! empty($colas)) ? $colas[0] : null;
            $id = is_array($colaExistente) ? ($colaExistente['.id'] ?? $colaExistente['id'] ?? null) : null;

            if (! empty($id)) {
                // Paso B: Si ya existe, actualizar velocidad mediante PATCH
                $patchResponse = Http::withBasicAuth($user, $pass)
                    ->timeout($timeout)
                    ->patch("http://{$ip}/rest/queue/simple/{$id}", [
                        'name' => $this->nombreCliente,
                        'max-limit' => $maxLimit,
                    ]);

                if ($patchResponse->successful()) {
                    Log::info("MikroTik [{$this->router->nombre}]: Plan actualizado con éxito para {$this->nombreCliente} (IP: {$this->ipCliente}, Límite: {$maxLimit}).", [
                        'queue_id' => $id,
                        'plan' => $this->plan->nombre,
                    ]);
                } else {
                    Log::error("MikroTik [{$this->router->nombre}]: Falló la actualización de la cola {$id} para {$this->nombreCliente}.", [
                        'status' => $patchResponse->status(),
                        'error' => $patchResponse->body(),
                    ]);
                }
            } else {
                // Paso C: Si no existe, crear nueva Simple Queue mediante PUT
                $putResponse = Http::withBasicAuth($user, $pass)
                    ->timeout($timeout)
                    ->put("http://{$ip}/rest/queue/simple", [
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
                    Log::error("MikroTik [{$this->router->nombre}]: Falló la creación de la cola para {$this->nombreCliente} (IP: {$this->ipCliente}).", [
                        'status' => $putResponse->status(),
                        'error' => $putResponse->body(),
                    ]);
                }
            }
        } catch (\Throwable $e) {
            Log::error("MikroTik [{$this->router->nombre}]: Excepción al configurar plan/ancho de banda para {$this->nombreCliente} (IP: {$this->ipCliente}): {$e->getMessage()}", [
                'router_id' => $this->router->id,
                'ip_cliente' => $this->ipCliente,
                'plan_id' => $this->plan->id,
                'max_limit' => $maxLimit,
                'excepcion' => $e->getMessage(),
            ]);
        }
    }
}
