<?php

namespace App\Actions\Red;

use App\Models\Plan;
use App\Models\Router;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SincronizarPlanesMikrotik
{
    /**
     * Sincroniza y aprovisiona los planes de velocidad y reglas de corte en el router MikroTik vía REST API.
     *
     * @param  Collection<int, Plan>|null  $planes
     * @return array{
     *     success: bool,
     *     mensaje: string,
     *     detalles: array<string, int>,
     *     errores: list<string>
     * }
     */
    public function handle(Router $router, ?Collection $planes = null): array
    {
        $planes = $planes ?? Plan::activo()->orderBy('velocidad_descarga', 'asc')->get();
        $ip = $router->ip_address;
        $user = $router->api_user;
        $pass = $router->api_password;
        $timeout = (int) config('mikrotik.timeout', config('services.mikrotik.timeout', 5));

        $detalles = [
            'ppp_profiles' => 0,
            'queue_types' => 0,
            'queue_simples' => 0,
            'firewall_rules' => 0,
        ];
        $errores = [];

        try {
            // Paso 0: Verificación de estado del router
            $ping = Http::withBasicAuth($user, $pass)
                ->timeout($timeout)
                ->get("http://{$ip}/rest/system/resource");

            if (! $ping->successful()) {
                $status = $ping->status();
                $motivo = match ($status) {
                    401 => 'Error 401 (No autorizado): Credenciales de RouterOS incorrectas.',
                    403 => 'Error 403 (Acceso denegado): Falta de permisos en el grupo del usuario MikroTik.',
                    default => "El router respondió con código HTTP {$status}.",
                };

                return [
                    'success' => false,
                    'mensaje' => "No se pudo sincronizar con el nodo '{$router->nombre}': {$motivo}",
                    'detalles' => $detalles,
                    'errores' => [$motivo],
                ];
            }

            // Paso 1: Aprovisionar Reglas de Firewall para corte de morosos
            $this->aprovisionarReglaFirewall($ip, $user, $pass, $timeout, [
                'chain' => 'forward',
                'action' => 'drop',
                'src-address-list' => 'morosos',
                'comment' => 'ISP Rivas - Bloqueo Morosos (Subida)',
            ], $detalles, $errores);

            $this->aprovisionarReglaFirewall($ip, $user, $pass, $timeout, [
                'chain' => 'forward',
                'action' => 'drop',
                'dst-address-list' => 'morosos',
                'comment' => 'ISP Rivas - Bloqueo Morosos (Bajada)',
            ], $detalles, $errores);

            // Paso 2: Aprovisionar cada Plan de Velocidad
            foreach ($planes as $plan) {
                $rateLimit = "{$plan->velocidad_carga}M/{$plan->velocidad_descarga}M";
                $comentario = "ISP Rivas - Plan ID #{$plan->id} ({$plan->nombre})";

                // A) PPP Profile
                $this->aprovisionarPppProfile($ip, $user, $pass, $timeout, $plan, $rateLimit, $comentario, $detalles, $errores);

                // B) Queue Types (PCQ)
                $this->aprovisionarQueueTypes($ip, $user, $pass, $timeout, $plan, $detalles, $errores);

                // C) Simple Queue Template (Plantilla de Plan)
                $this->aprovisionarSimpleQueueTemplate($ip, $user, $pass, $timeout, $plan, $rateLimit, $comentario, $detalles, $errores);
            }

            $totalConfigurados = $detalles['ppp_profiles'] + $detalles['queue_types'] + $detalles['queue_simples'];
            $mensaje = "¡Aprovisionamiento completado con éxito en '{$router->nombre}'! Se configuraron {$detalles['ppp_profiles']} perfiles PPP, {$detalles['queue_types']} tipos PCQ, {$detalles['queue_simples']} plantillas de cola y {$detalles['firewall_rules']} reglas de corte.";

            Log::info("MikroTik [{$router->nombre}]: Planes sincronizados exitosamente.", [
                'router_id' => $router->id,
                'detalles' => $detalles,
                'errores_count' => count($errores),
            ]);

            return [
                'success' => true,
                'mensaje' => $mensaje,
                'detalles' => $detalles,
                'errores' => $errores,
            ];
        } catch (\Throwable $e) {
            Log::error("MikroTik [{$router->nombre}]: Excepción durante la sincronización de planes: {$e->getMessage()}", [
                'router_id' => $router->id,
                'exception' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'mensaje' => "Fallo de conexión con el nodo '{$router->nombre}' ({$ip}): {$e->getMessage()}",
                'detalles' => $detalles,
                'errores' => [$e->getMessage()],
            ];
        }
    }

    /**
     * @param  array<string, mixed>  $datos
     * @param  array<string, int>  $detalles
     * @param  list<string>  $errores
     */
    protected function aprovisionarReglaFirewall(string $ip, string $user, string $pass, int $timeout, array $datos, array &$detalles, array &$errores): void
    {
        try {
            $check = Http::withBasicAuth($user, $pass)
                ->timeout($timeout)
                ->get("http://{$ip}/rest/ip/firewall/filter", [
                    'comment' => $datos['comment'],
                ]);

            $reglas = $check->json();
            if (! is_array($reglas) || empty($reglas)) {
                $create = Http::withBasicAuth($user, $pass)
                    ->timeout($timeout)
                    ->put("http://{$ip}/rest/ip/firewall/filter", $datos);

                if ($create->successful()) {
                    $detalles['firewall_rules']++;
                } else {
                    $errores[] = "Error al crear regla firewall '{$datos['comment']}': {$create->body()}";
                }
            } else {
                $detalles['firewall_rules']++;
            }
        } catch (\Throwable $e) {
            $errores[] = "Excepción en regla firewall '{$datos['comment']}': {$e->getMessage()}";
        }
    }

    /**
     * @param  array<string, int>  $detalles
     * @param  list<string>  $errores
     */
    protected function aprovisionarPppProfile(string $ip, string $user, string $pass, int $timeout, Plan $plan, string $rateLimit, string $comentario, array &$detalles, array &$errores): void
    {
        try {
            $check = Http::withBasicAuth($user, $pass)
                ->timeout($timeout)
                ->get("http://{$ip}/rest/ppp/profile", [
                    'name' => $plan->nombre,
                ]);

            $profiles = $check->json();
            $existente = (is_array($profiles) && ! empty($profiles)) ? $profiles[0] : null;
            $id = is_array($existente) ? ($existente['.id'] ?? $existente['id'] ?? null) : null;

            if (! empty($id)) {
                $patch = Http::withBasicAuth($user, $pass)
                    ->timeout($timeout)
                    ->patch("http://{$ip}/rest/ppp/profile/{$id}", [
                        'rate-limit' => $rateLimit,
                        'comment' => $comentario,
                    ]);

                if ($patch->successful()) {
                    $detalles['ppp_profiles']++;
                } else {
                    $errores[] = "Error al actualizar PPP Profile '{$plan->nombre}': {$patch->body()}";
                }
            } else {
                $put = Http::withBasicAuth($user, $pass)
                    ->timeout($timeout)
                    ->put("http://{$ip}/rest/ppp/profile", [
                        'name' => $plan->nombre,
                        'rate-limit' => $rateLimit,
                        'comment' => $comentario,
                    ]);

                if ($put->successful()) {
                    $detalles['ppp_profiles']++;
                } else {
                    $errores[] = "Error al crear PPP Profile '{$plan->nombre}': {$put->body()}";
                }
            }
        } catch (\Throwable $e) {
            $errores[] = "Excepción en PPP Profile '{$plan->nombre}': {$e->getMessage()}";
        }
    }

    /**
     * @param  array<string, int>  $detalles
     * @param  list<string>  $errores
     */
    protected function aprovisionarQueueTypes(string $ip, string $user, string $pass, int $timeout, Plan $plan, array &$detalles, array &$errores): void
    {
        $downName = "PCQ_DOWN_{$plan->velocidad_descarga}M";
        $upName = "PCQ_UP_{$plan->velocidad_carga}M";

        // Downloader
        $this->asegurarQueueType($ip, $user, $pass, $timeout, [
            'name' => $downName,
            'kind' => 'pcq',
            'pcq-rate' => "{$plan->velocidad_descarga}M",
            'pcq-classifier' => 'dst-address',
        ], $detalles, $errores);

        // Uploader
        $this->asegurarQueueType($ip, $user, $pass, $timeout, [
            'name' => $upName,
            'kind' => 'pcq',
            'pcq-rate' => "{$plan->velocidad_carga}M",
            'pcq-classifier' => 'src-address',
        ], $detalles, $errores);
    }

    /**
     * @param  array<string, mixed>  $datos
     * @param  array<string, int>  $detalles
     * @param  list<string>  $errores
     */
    protected function asegurarQueueType(string $ip, string $user, string $pass, int $timeout, array $datos, array &$detalles, array &$errores): void
    {
        try {
            $check = Http::withBasicAuth($user, $pass)
                ->timeout($timeout)
                ->get("http://{$ip}/rest/queue/type", [
                    'name' => $datos['name'],
                ]);

            $types = $check->json();
            $existente = (is_array($types) && ! empty($types)) ? $types[0] : null;
            $id = is_array($existente) ? ($existente['.id'] ?? $existente['id'] ?? null) : null;

            if (empty($id)) {
                $put = Http::withBasicAuth($user, $pass)
                    ->timeout($timeout)
                    ->put("http://{$ip}/rest/queue/type", $datos);

                if ($put->successful()) {
                    $detalles['queue_types']++;
                } else {
                    $errores[] = "Error al crear Queue Type '{$datos['name']}': {$put->body()}";
                }
            } else {
                $detalles['queue_types']++;
            }
        } catch (\Throwable $e) {
            $errores[] = "Excepción en Queue Type '{$datos['name']}': {$e->getMessage()}";
        }
    }

    /**
     * @param  array<string, int>  $detalles
     * @param  list<string>  $errores
     */
    protected function aprovisionarSimpleQueueTemplate(string $ip, string $user, string $pass, int $timeout, Plan $plan, string $rateLimit, string $comentario, array &$detalles, array &$errores): void
    {
        $queueName = "PLANTILLA-PLAN-{$plan->id}";

        try {
            $check = Http::withBasicAuth($user, $pass)
                ->timeout($timeout)
                ->get("http://{$ip}/rest/queue/simple", [
                    'name' => $queueName,
                ]);

            $queues = $check->json();
            $existente = (is_array($queues) && ! empty($queues)) ? $queues[0] : null;
            $id = is_array($existente) ? ($existente['.id'] ?? $existente['id'] ?? null) : null;

            if (! empty($id)) {
                $patch = Http::withBasicAuth($user, $pass)
                    ->timeout($timeout)
                    ->patch("http://{$ip}/rest/queue/simple/{$id}", [
                        'max-limit' => $rateLimit,
                        'comment' => "Plantilla {$plan->nombre} ({$rateLimit})",
                    ]);

                if ($patch->successful()) {
                    $detalles['queue_simples']++;
                } else {
                    $errores[] = "Error al actualizar Cola Plantilla '{$queueName}': {$patch->body()}";
                }
            } else {
                $put = Http::withBasicAuth($user, $pass)
                    ->timeout($timeout)
                    ->put("http://{$ip}/rest/queue/simple", [
                        'name' => $queueName,
                        'max-limit' => $rateLimit,
                        'comment' => "Plantilla {$plan->nombre} ({$rateLimit})",
                    ]);

                if ($put->successful()) {
                    $detalles['queue_simples']++;
                } else {
                    $errores[] = "Error al crear Cola Plantilla '{$queueName}': {$put->body()}";
                }
            }
        } catch (\Throwable $e) {
            $errores[] = "Excepción en Cola Plantilla '{$queueName}': {$e->getMessage()}";
        }
    }
}
