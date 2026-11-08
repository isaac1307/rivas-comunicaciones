<?php

namespace App\Actions\Red;

use App\Enums\EstadoCliente;
use App\Models\Cliente;
use App\Models\Router;
use Illuminate\Support\Facades\Log;
use Throwable;

class GestionarEstadoServicioCliente
{
    public function __construct(
        protected MikrotikRestClient $client = new MikrotikRestClient
    ) {}

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

        $cliente->loadMissing('router');
        $router = $router ?? $cliente->router;

        if (! $router instanceof Router || ! $router->isActivo()) {
            return [
                'success' => false,
                'mensaje' => "El cliente #{$cliente->id} no tiene un router MikroTik activo asignado para aplicar la suspensión.",
            ];
        }

        try {
            // Paso 1: Asegurar que la IP esté en la lista de morosos
            $check = $this->client->buscarEnAddressList($router, $ipCliente, 'morosos');

            $registros = $check->json();
            $yaEstaEnLista = is_array($registros) && ! empty($registros);

            if (! $yaEstaEnLista) {
                $response = $this->client->agregarAAddressList(
                    $router,
                    $ipCliente,
                    "Suspendido por sistema - Cliente #{$cliente->id} ({$cliente->nombre_completo})",
                    'morosos'
                );

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
            $conexionesCerradas = 0;
            try {
                $conexionesCerradas = $this->client->cerrarConexionesActivas($router, $ipCliente);
            } catch (Throwable $e) {
                Log::warning("MikroTik [{$router->nombre}]: No se pudieron cerrar conexiones activas para {$ipCliente}: {$e->getMessage()}");
            }

            // Paso 3: Desconectar sesión PPPoE activa si existe
            try {
                $this->client->desconectarSesionPppoe($router, $ipCliente, $cliente->usuario_pppoe);
            } catch (Throwable) {
                // Ignorar si no hay sesión PPPoE activa
            }

            // Paso 4: Actualizar estado del cliente en la base de datos si no estaba suspendido
            if ($cliente->estado !== EstadoCliente::Suspendido->value) {
                $cliente->update(['estado' => EstadoCliente::Suspendido->value]);
            }

            Log::info("MikroTik [{$router->nombre}]: Servicio suspendido para cliente #{$cliente->id} (IP: {$ipCliente}). Conexiones cerradas: {$conexionesCerradas}.");

            return [
                'success' => true,
                'mensaje' => "¡Servicio suspendido con éxito en el nodo '{$router->nombre}'! La IP {$ipCliente} fue agregada a la lista de morosos y se cerraron {$conexionesCerradas} conexiones activas.",
            ];
        } catch (Throwable $e) {
            Log::error("MikroTik [{$router->nombre}]: Excepción al suspender servicio para cliente #{$cliente->id} (IP: {$ipCliente}): {$e->getMessage()}");

            return [
                'success' => false,
                'mensaje' => "No se pudo conectar al router '{$router->nombre}' ({$router->ip_address}): {$e->getMessage()}",
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

        $cliente->loadMissing('router');
        $router = $router ?? $cliente->router;

        if (! $router instanceof Router || ! $router->isActivo()) {
            return [
                'success' => false,
                'mensaje' => "El cliente #{$cliente->id} no tiene un router MikroTik activo asignado.",
            ];
        }

        try {
            // Paso 1: Consultar la IP en la lista de morosos
            $response = $this->client->buscarEnAddressList($router, $ipCliente, 'morosos');

            $registros = $response->json();

            if (is_array($registros) && ! empty($registros)) {
                foreach ($registros as $reg) {
                    $id = is_array($reg) ? ($reg['.id'] ?? $reg['id'] ?? null) : null;
                    if (is_string($id) && $id !== '') {
                        $this->client->eliminarDeAddressListPorId($router, $id);
                    }
                }
            }

            // Paso 2: Actualizar estado del cliente a activo
            if ($cliente->estado !== EstadoCliente::Activo->value) {
                $cliente->update(['estado' => EstadoCliente::Activo->value]);
            }

            Log::info("MikroTik [{$router->nombre}]: Servicio reconectado para cliente #{$cliente->id} (IP: {$ipCliente}). Removido de morosos.");

            return [
                'success' => true,
                'mensaje' => "¡Servicio reconectado exitosamente en el nodo '{$router->nombre}'! La IP {$ipCliente} fue retirada de la lista de morosos y el tráfico vuelve a fluir.",
            ];
        } catch (Throwable $e) {
            Log::error("MikroTik [{$router->nombre}]: Excepción al reconectar servicio para cliente #{$cliente->id} (IP: {$ipCliente}): {$e->getMessage()}");

            return [
                'success' => false,
                'mensaje' => "No se pudo conectar al router '{$router->nombre}' ({$router->ip_address}): {$e->getMessage()}",
            ];
        }
    }
}
