<?php

namespace Tests\Feature;

use App\Actions\Cobranza\AnularPago;
use App\Actions\Cobranza\RegistrarPago;
use App\Jobs\Red\AsignarPlanRedJob;
use App\Jobs\Red\ReconectarServicioRedJob;
use App\Jobs\Red\SuspenderServicioRedJob;
use App\Models\Cargo;
use App\Models\Cliente;
use App\Models\Plan;
use App\Models\Router;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class MikrotikJobsTest extends TestCase
{
    use RefreshDatabase;

    public function test_router_modelo_encripta_y_desencripta_api_password_automaticamente(): void
    {
        $router = Router::create([
            'nombre' => 'Nodo Pinotepa Centro',
            'ip_address' => '192.168.1.116',
            'api_user' => 'admin',
            'api_password' => 'clave_super_secreta_mikrotik',
            'estado' => 'activo',
        ]);

        $this->assertEquals('clave_super_secreta_mikrotik', $router->api_password);

        // Verificar directamente en la base de datos que no está en texto plano
        $rawPassword = DB::table('routers')->where('id', $router->id)->value('api_password');
        $this->assertNotEquals('clave_super_secreta_mikrotik', $rawPassword);
        $this->assertNotEmpty($rawPassword);

        // Al recargar el modelo, debe desencriptarse con exactitud
        $routerRecargado = Router::findOrFail($router->id);
        $this->assertEquals('clave_super_secreta_mikrotik', $routerRecargado->api_password);
        $this->assertTrue($routerRecargado->isActivo());
    }

    public function test_suspender_servicio_red_job_envia_put_con_credenciales_dinamicas_del_router(): void
    {
        $router = Router::factory()->create([
            'nombre' => 'Nodo Pinotepa',
            'ip_address' => '10.50.1.1',
            'api_user' => 'operador_red',
            'api_password' => 'pass_router_nodo_1',
            'estado' => 'activo',
        ]);

        Http::fake([
            'http://10.50.1.1/rest/ip/firewall/address-list' => Http::response([
                '.id' => '*10',
                'list' => 'morosos',
                'address' => '10.20.30.40',
                'comment' => 'Suspendido por sistema - Cliente: CLI-001',
            ], 200),
        ]);

        $job = new SuspenderServicioRedJob($router, '10.20.30.40', 'CLI-001');
        $job->handle();

        Http::assertSent(function (Request $request) {
            return $request->method() === 'PUT'
                && $request->url() === 'http://10.50.1.1/rest/ip/firewall/address-list'
                && $request->hasHeader('Authorization', 'Basic '.base64_encode('operador_red:pass_router_nodo_1'))
                && $request['list'] === 'morosos'
                && $request['address'] === '10.20.30.40'
                && $request['comment'] === 'Suspendido por sistema - Cliente: CLI-001';
        });
    }

    public function test_reconectar_servicio_red_job_consulta_id_y_ejecuta_delete_con_router_dinamico(): void
    {
        $router = Router::factory()->create([
            'nombre' => 'Nodo Puerto Escondido',
            'ip_address' => '10.60.1.1',
            'api_user' => 'admin_puerto',
            'api_password' => 'puerto_pass_99',
            'estado' => 'activo',
        ]);

        Http::fake([
            'http://10.60.1.1/rest/ip/firewall/address-list*' => function (Request $request) {
                if ($request->method() === 'GET') {
                    return Http::response([
                        [
                            '.id' => '*2B',
                            'list' => 'morosos',
                            'address' => '10.20.30.40',
                        ],
                    ], 200);
                }

                if ($request->method() === 'DELETE' && str_ends_with($request->url(), '/*2B')) {
                    return Http::response(null, 200);
                }

                return Http::response(null, 404);
            },
        ]);

        $job = new ReconectarServicioRedJob($router, '10.20.30.40');
        $job->handle();

        Http::assertSent(function (Request $request) {
            return $request->method() === 'GET'
                && str_contains($request->url(), 'http://10.60.1.1/rest/ip/firewall/address-list')
                && str_contains($request->url(), 'list=morosos')
                && str_contains($request->url(), 'address=10.20.30.40')
                && $request->hasHeader('Authorization', 'Basic '.base64_encode('admin_puerto:puerto_pass_99'));
        });

        Http::assertSent(function (Request $request) {
            return $request->method() === 'DELETE'
                && $request->url() === 'http://10.60.1.1/rest/ip/firewall/address-list/*2B';
        });
    }

    public function test_reconectar_servicio_red_job_no_borra_si_no_esta_en_lista_morosos(): void
    {
        $router = Router::factory()->create([
            'ip_address' => '10.70.1.1',
        ]);

        Http::fake([
            'http://10.70.1.1/rest/ip/firewall/address-list*' => Http::response([], 200),
        ]);

        $job = new ReconectarServicioRedJob($router, '10.20.30.40');
        $job->handle();

        Http::assertSent(fn (Request $request) => $request->method() === 'GET');
        Http::assertNotSent(fn (Request $request) => $request->method() === 'DELETE');
    }

    public function test_registrar_pago_despacha_reconectar_servicio_red_job_con_router_asignado(): void
    {
        Queue::fake();

        $router = Router::factory()->create(['estado' => 'activo']);
        $cobrador = User::factory()->create(['rol' => 'cobrador']);
        $cliente = Cliente::factory()->create([
            'router_id' => $router->id,
            'usuario_pppoe' => '192.168.10.55',
            'saldo_a_favor' => 0.00,
        ]);

        Cargo::factory()->create([
            'cliente_id' => $cliente->id,
            'monto' => 350.00,
            'monto_pagado' => 0.00,
            'fecha_vencimiento' => now()->subDays(5),
            'estado' => 'pendiente',
        ]);

        $action = app(RegistrarPago::class);
        $action->handle($cliente, [
            'monto' => 350.00,
            'metodo_pago' => 'efectivo',
            'fecha_pago' => now(),
        ], $cobrador);

        Queue::assertPushed(ReconectarServicioRedJob::class, function (ReconectarServicioRedJob $job) use ($router) {
            return $job->ipCliente === '192.168.10.55'
                && $job->router->id === $router->id;
        });
    }

    public function test_anular_pago_despacha_suspender_servicio_red_job_con_router_asignado(): void
    {
        Queue::fake();

        $router = Router::factory()->create(['estado' => 'activo']);
        $cobrador = User::factory()->create(['rol' => 'cobrador']);
        $cliente = Cliente::factory()->create([
            'router_id' => $router->id,
            'usuario_pppoe' => '192.168.10.77',
            'saldo_a_favor' => 0.00,
        ]);

        Cargo::factory()->create([
            'cliente_id' => $cliente->id,
            'monto' => 400.00,
            'monto_pagado' => 0.00,
            'fecha_vencimiento' => now()->subDays(10),
            'estado' => 'pendiente',
        ]);

        $pago = app(RegistrarPago::class)->handle($cliente, [
            'monto' => 400.00,
            'metodo_pago' => 'transferencia',
        ], $cobrador);

        // Reset queue to isolate anulación
        Queue::fake();

        app(AnularPago::class)->handle($pago, 'Comprobante falso o rechazado por el banco');

        Queue::assertPushed(SuspenderServicioRedJob::class, function (SuspenderServicioRedJob $job) use ($router, $cliente) {
            return $job->ipCliente === '192.168.10.77'
                && $job->router->id === $router->id
                && (string) $job->folioCliente === (string) $cliente->id;
        });
    }

    public function test_asignar_plan_red_job_actualiza_cola_existente_con_patch(): void
    {
        $router = Router::factory()->create([
            'ip_address' => '10.80.1.1',
            'api_user' => 'admin',
            'api_password' => 'secret_pass',
            'estado' => 'activo',
        ]);

        $plan = Plan::factory()->create([
            'nombre' => 'Plan Fibra 50 Megas',
            'velocidad_carga' => 15,
            'velocidad_descarga' => 50,
        ]);

        Http::fake([
            'http://10.80.1.1/rest/queue/simple*' => function (Request $request) {
                if ($request->method() === 'GET') {
                    return Http::response([
                        [
                            '.id' => '*3C',
                            'name' => 'Juan Perez',
                            'target' => '10.80.10.20',
                            'max-limit' => '5M/10M',
                        ],
                    ], 200);
                }

                if ($request->method() === 'PATCH' && str_ends_with($request->url(), '/*3C')) {
                    return Http::response([
                        '.id' => '*3C',
                        'name' => 'Juan Perez',
                        'max-limit' => '15M/50M',
                    ], 200);
                }

                return Http::response(null, 404);
            },
        ]);

        $job = new AsignarPlanRedJob('10.80.10.20', 'Juan Perez', $router, $plan);
        $job->handle();

        Http::assertSent(function (Request $request) {
            return $request->method() === 'PATCH'
                && $request->url() === 'http://10.80.1.1/rest/queue/simple/*3C'
                && $request['name'] === 'Juan Perez'
                && $request['max-limit'] === '15M/50M';
        });
    }

    public function test_asignar_plan_red_job_crea_nueva_cola_con_put_si_no_existe(): void
    {
        $router = Router::factory()->create([
            'ip_address' => '10.80.1.1',
            'api_user' => 'admin',
            'api_password' => 'secret_pass',
            'estado' => 'activo',
        ]);

        $plan = Plan::factory()->create([
            'nombre' => 'Plan Básico 20 Megas',
            'velocidad_carga' => 10,
            'velocidad_descarga' => 20,
        ]);

        Http::fake([
            'http://10.80.1.1/rest/queue/simple*' => function (Request $request) {
                if ($request->method() === 'GET') {
                    return Http::response([], 200);
                }

                if ($request->method() === 'PUT') {
                    return Http::response([
                        '.id' => '*4D',
                        'name' => 'Maria Lopez',
                        'target' => '10.80.10.21',
                        'max-limit' => '10M/20M',
                    ], 200);
                }

                return Http::response(null, 404);
            },
        ]);

        $job = new AsignarPlanRedJob('10.80.10.21', 'Maria Lopez', $router, $plan);
        $job->handle();

        Http::assertSent(function (Request $request) {
            return $request->method() === 'PUT'
                && $request->url() === 'http://10.80.1.1/rest/queue/simple'
                && $request['name'] === 'Maria Lopez'
                && $request['target'] === '10.80.10.21'
                && $request['max-limit'] === '10M/20M';
        });
    }

    public function test_cliente_controller_despacha_asignar_plan_red_job_al_crear_y_actualizar(): void
    {
        Queue::fake();

        $admin = User::factory()->create(['rol' => 'administrador']);
        $router = Router::factory()->create(['estado' => 'activo']);
        $plan = Plan::factory()->create([
            'velocidad_carga' => 10,
            'velocidad_descarga' => 30,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.clientes.store'), [
            'nombre_completo' => 'Carlos Hernandez',
            'direccion' => 'Av. Independencia #45',
            'telefono' => '9541234567',
            'plan_id' => $plan->id,
            'router_id' => $router->id,
            'usuario_pppoe' => '192.168.1.50',
            'fecha_alta' => now()->toDateString(),
            'estado' => 'activo',
        ]);

        $response->assertRedirect(route('admin.clientes.index'));

        Queue::assertPushed(AsignarPlanRedJob::class, function (AsignarPlanRedJob $job) use ($router, $plan) {
            return $job->ipCliente === '192.168.1.50'
                && $job->nombreCliente === 'Carlos Hernandez'
                && $job->router->id === $router->id
                && $job->plan->id === $plan->id;
        });

        $cliente = Cliente::where('telefono', '9541234567')->firstOrFail();
        $nuevoPlan = Plan::factory()->create([
            'velocidad_carga' => 20,
            'velocidad_descarga' => 80,
        ]);

        $updateResponse = $this->actingAs($admin)->put(route('admin.clientes.update', $cliente), [
            'nombre_completo' => 'Carlos Hernandez Editado',
            'direccion' => 'Av. Independencia #45',
            'telefono' => '9541234567',
            'plan_id' => $nuevoPlan->id,
            'router_id' => $router->id,
            'usuario_pppoe' => '192.168.1.50',
            'fecha_alta' => now()->toDateString(),
            'estado' => 'activo',
        ]);

        $updateResponse->assertRedirect(route('admin.clientes.index'));

        Queue::assertPushed(AsignarPlanRedJob::class, function (AsignarPlanRedJob $job) use ($nuevoPlan) {
            return $job->plan->id === $nuevoPlan->id
                && $job->nombreCliente === 'Carlos Hernandez Editado';
        });
    }
}
