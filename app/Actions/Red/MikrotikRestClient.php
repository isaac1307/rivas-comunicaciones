<?php

namespace App\Actions\Red;

use App\Models\Router;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class MikrotikRestClient
{
    /**
     * Construye una petición HTTP autenticada y configurada para la REST API del RouterOS.
     */
    public function request(Router $router, ?int $timeout = null): PendingRequest
    {
        $timeout = $timeout ?? (int) config('mikrotik.timeout', config('services.mikrotik.timeout', 5));

        $request = Http::baseUrl($router->restBaseUrl())
            ->withBasicAuth($router->api_user, $router->api_password)
            ->timeout($timeout);

        if ($router->usar_tls && ! $router->verificar_certificado) {
            $request = $request->withoutVerifying();
        }

        return $request;
    }

    /**
     * Consulta los recursos del sistema en /rest/system/resource.
     */
    public function obtenerRecursosSistema(Router $router, ?int $timeout = null): Response
    {
        return $this->request($router, $timeout)->get('/system/resource');
    }

    /**
     * Busca los registros de una IP en una address-list del firewall.
     */
    public function buscarEnAddressList(Router $router, string $ipCliente, string $lista = 'morosos'): Response
    {
        return $this->request($router)->get('/ip/firewall/address-list', [
            'list' => $lista,
            'address' => $ipCliente,
        ]);
    }

    /**
     * Agrega una dirección IP a una address-list del firewall.
     */
    public function agregarAAddressList(Router $router, string $ipCliente, string $comentario, string $lista = 'morosos'): Response
    {
        return $this->request($router)->put('/ip/firewall/address-list', [
            'list' => $lista,
            'address' => $ipCliente,
            'comment' => $comentario,
        ]);
    }

    /**
     * Elimina un registro de una address-list por su identificador interno (.id).
     */
    public function eliminarDeAddressListPorId(Router $router, string $id): Response
    {
        return $this->request($router)->delete("/ip/firewall/address-list/{$id}");
    }

    /**
     * Cierra todas las conexiones activas en /ip/firewall/connection originadas por la IP del cliente.
     */
    public function cerrarConexionesActivas(Router $router, string $ipCliente): int
    {
        $connsRes = $this->request($router)->get('/ip/firewall/connection', [
            'src-address' => $ipCliente,
        ]);

        $conexiones = $connsRes->json();
        $cerradas = 0;

        if (is_array($conexiones) && ! empty($conexiones)) {
            foreach ($conexiones as $conn) {
                $id = is_array($conn) ? ($conn['.id'] ?? $conn['id'] ?? null) : null;
                if (is_string($id) && $id !== '') {
                    $del = $this->request($router)->delete("/ip/firewall/connection/{$id}");
                    if ($del->successful()) {
                        $cerradas++;
                    }
                }
            }
        }

        return $cerradas;
    }

    /**
     * Desconecta sesiones activas PPPoE asociadas a la IP o nombre de usuario del cliente.
     */
    public function desconectarSesionPppoe(Router $router, string $ipCliente, ?string $usuarioPppoe = null): int
    {
        $query = ! empty($usuarioPppoe) ? ['name' => $usuarioPppoe] : ['address' => $ipCliente];
        $res = $this->request($router)->get('/ppp/active', $query);

        $sesiones = $res->json();
        $desconectadas = 0;

        if (is_array($sesiones) && ! empty($sesiones)) {
            foreach ($sesiones as $sesion) {
                $id = is_array($sesion) ? ($sesion['.id'] ?? $sesion['id'] ?? null) : null;
                if (is_string($id) && $id !== '') {
                    $del = $this->request($router)->delete("/ppp/active/{$id}");
                    if ($del->successful()) {
                        $desconectadas++;
                    }
                }
            }
        }

        return $desconectadas;
    }

    /**
     * Busca una Simple Queue por target (IP del cliente).
     */
    public function buscarSimpleQueuePorTarget(Router $router, string $ipCliente): Response
    {
        return $this->request($router)->get('/queue/simple', [
            'target' => $ipCliente,
        ]);
    }

    /**
     * Actualiza una Simple Queue existente por su identificador (.id).
     *
     * @param  array<string, mixed>  $datos
     */
    public function actualizarSimpleQueue(Router $router, string $id, array $datos): Response
    {
        return $this->request($router)->patch("/queue/simple/{$id}", $datos);
    }

    /**
     * Crea una nueva Simple Queue.
     *
     * @param  array<string, mixed>  $datos
     */
    public function crearSimpleQueue(Router $router, array $datos): Response
    {
        return $this->request($router)->put('/queue/simple', $datos);
    }
}
