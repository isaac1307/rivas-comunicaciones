<?php

namespace App\Actions\Red;

use App\Models\Cliente;
use App\Models\Router;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GestionarEstadoServicioCliente
{
    /**
     * Suspende el servicio de internet del cliente en MikroTik agregándolo a la lista de morosos
     * y cerrando sus conexiones activas para un corte instantáneo.
     *
     * @return array{success: bool, mensaje: string}
     */
    public function suspender(Cliente $cliente, ?Router $router = null): array
    {
        $ipCliente = $cliente->ip_asignada;

        if (empty($ipCliente)) {
            return [
                'success' => false,
                'mensaje' => "El cliente #{$cliente->id} no tiene una dirección IP asignada para aplicar el corte en MikroTik.",
            ];
        }

        $router = $router ?? $cliente->router ?? Router::query()->where('estado', 'activo')->first();

        if (! $router instanceof Router || ! $router->isActivo()) {
            return [
                'success' => false,
                'mensaje' => "No hay ningún router MikroTik activo asociado para aplicar la suspensión del cliente #{$cliente->id}.",
            ];
        }

        $ip = $router->ip_address;
        $user = $router->api_user;
        $pass = $router->api_password;
        $timeout = (int) config('mikrotik.timeout', config('services.mikrotik.timeout', 5));

        try {
            // Paso 1: Asegurar que la IP esté en la lista de morosos
            $check = Http::withBasicAuth($user, $pass)
                ->timeout($timeout)
                ->get("http://{$ip}/rest/ip/firewall/address-list", [
                    'list' => 'morosos',
                    'address' => $ipCliente,
                ]);

            $registros = $check->json();
            $yaEstaEnLista = is_array($registros) && ! empty($registros);

            if (! $yaEstaEnLista) {
                $response = Http::withBasicAuth($user, $pass)
                    ->timeout($timeout)
                    ->put("http://{$ip}/rest/ip/firewall/address-list", [
                        'list' => 'morosos',
                        'address' => $ipCliente,
                        'comment' => "Suspendido por sistema - Cliente #{$cliente->id} ({$cliente->nombre_completo})",
                    ]);

                if (! $response->successful() && $response->status() !== 400) {
                    $errorMsg = $response->body();
                    Log::error("MikroTik [{$router->nombre}]: Falló al agregar IP {$ipCliente} a morosos: {$errorMsg}");

                    return [
                        'success' => false,
                        'mensaje' => "MikroTik respondió con error HTTP {$response->status()}: {$errorMsg}",
                    ];
                }
            }

            // Paso 2: Cerrar conexiones TCP/UDP activas para que el corte sea instantáneo
            $conexionesCerradas = $this->cerrarConexionesActivas($router, $ipCliente);

            // Paso 3: Desconectar sesión PPPoE activa si existe
            $this->desconectarSesionPppoe($router, $ipCliente, $cliente->usuario_pppoe);

            // Paso 4: Actualizar estado del cliente en la base de datos si no estaba suspendido
            if ($cliente->estado !== 'suspendido') {
                $cliente->update(['estado' => 'suspendido']);
            }

            Log::info("MikroTik [{$router->nombre}]: Servicio suspendido para cliente #{$cliente->id} (IP: {$ipCliente}). Conexiones cerradas: {$conexionesCerradas}.");

            return [
                'success' => true,
                'mensaje' => "¡Servicio suspendido con éxito en el nodo '{$router->nombre}'! La IP {$ipCliente} fue agregada a la lista de morosos y se cerraron {$conexionesCerradas} conexiones activas.",
            ];
        } catch (\Throwable $e) {
            Log::error("MikroTik [{$router->nombre}]: Excepción al suspender servicio para cliente #{$cliente->id} (IP: {$ipCliente}): {$e->getMessage()}");

            return [
                'success' => false,
                'mensaje' => "No se pudo conectar al router '{$router->nombre}' ({$ip}): {$e->getMessage()}",
            ];
        }
    }

    /**
     * Reconecta el servicio de internet del cliente en MikroTik removiéndolo de la lista de morosos.
     *
     * @return array{success: bool, mensaje: string}
     */
    public function reconectar(Cliente $cliente, ?Router $router = null): array
    {
        $ipCliente = $cliente->ip_asignada;

        if (empty($ipCliente)) {
            return [
                'success' => false,
                'mensaje' => "El cliente #{$cliente->id} no tiene una dirección IP asignada.",
            ];
        }

        $router = $router ?? $cliente->router ?? Router::query()->where('estado', 'activo')->first();

        if (! $router instanceof Router || ! $router->isActivo()) {
            return [
                'success' => false,
                'mensaje' => 'No hay ningún router MikroTik activo asociado.',
            ];
        }

        $ip = $router->ip_address;
        $user = $router->api_user;
        $pass = $router->api_password;
        $timeout = (int) config('mikrotik.timeout', config('services.mikrotik.timeout', 5));

        try {
            // Paso 1: Consultar la IP en la lista de morosos
            $response = Http::withBasicAuth($user, $pass)
                ->timeout($timeout)
                ->get("http://{$ip}/rest/ip/firewall/address-list", [
                    'list' => 'morosos',
                    'address' => $ipCliente,
                ]);

            $registros = $response->json();

            if (is_array($registros) && ! empty($registros)) {
                foreach ($registros as $reg) {
                    $id = $reg['.id'] ?? $reg['id'] ?? null;
                    if ($id) {
                        Http::withBasicAuth($user, $pass)
                            ->timeout($timeout)
                            ->delete("http://{$ip}/rest/ip/firewall/address-list/{$id}");
                    }
                }
            }

            // Paso 2: Actualizar estado del cliente a activo
            if ($cliente->estado !== 'activo') {
                $cliente->update(['estado' => 'activo']);
            }

            Log::info("MikroTik [{$router->nombre}]: Servicio reconectado para cliente #{$cliente->id} (IP: {$ipCliente}). Removido de morosos.");

            return [
                'success' => true,
                'mensaje' => "¡Servicio reconectado exitosamente en el nodo '{$router->nombre}'! La IP {$ipCliente} fue retirada de la lista de morosos y el tráfico vuelve a fluir.",
            ];
        } catch (\Throwable $e) {
            Log::error("MikroTik [{$router->nombre}]: Excepción al reconectar servicio para cliente #{$cliente->id} (IP: {$ipCliente}): {$e->getMessage()}");

            return [
                'success' => false,
                'mensaje' => "No se pudo conectar al router '{$router->nombre}' ({$ip}): {$e->getMessage()}",
            ];
        }
    }

    /**
     * Elimina las conexiones abiertas en /ip/firewall/connection asociadas a la IP del cliente.
     */
    protected function cerrarConexionesActivas(Router $router, string $ipCliente): int
    {
        $ip = $router->ip_address;
        $user = $router->api_user;
        $pass = $router->api_password;
        $timeout = (int) config('mikrotik.timeout', config('services.mikrotik.timeout', 4));
        $cerradas = 0;

        try {
            $connsRes = Http::withBasicAuth($user, $pass)
                ->timeout($timeout)
                ->get("http://{$ip}/rest/ip/firewall/connection", [
                    'src-address' => $ipCliente,
                ]);

            $conexiones = $connsRes->json();
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
        } catch (\Throwable $e) {
            Log::warning("MikroTik [{$router->nombre}]: No se pudieron cerrar conexiones activas para {$ipCliente}: {$e->getMessage()}");
        }

        return $cerradas;
    }

    /**
     * Desconecta sesiones activas PPPoE para obligar a renegociar o detener la sesión.
     */
    protected function desconectarSesionPppoe(Router $router, string $ipCliente, ?string $usuarioPppoe): void
    {
        $ip = $router->ip_address;
        $user = $router->api_user;
        $pass = $router->api_password;
        $timeout = (int) config('mikrotik.timeout', config('services.mikrotik.timeout', 4));

        try {
            $query = $usuarioPppoe ? ['name' => $usuarioPppoe] : ['address' => $ipCliente];
            $res = Http::withBasicAuth($user, $pass)
                ->timeout($timeout)
                ->get("http://{$ip}/rest/ppp/active", $query);

            $sesiones = $res->json();
            if (is_array($sesiones) && ! empty($sesiones)) {
                foreach ($sesiones as $sesion) {
                    $id = $sesion['.id'] ?? $sesion['id'] ?? null;
                    if ($id) {
                        Http::withBasicAuth($user, $pass)
                            ->timeout($timeout)
                            ->delete("http://{$ip}/rest/ppp/active/{$id}");
                    }
                }
            }
        } catch (\Throwable) {
            // Ignorar si no hay sesión PPPoE activa
        }
    }
}
