<?php

namespace Tests\Feature;

use App\Jobs\Red\SincronizarPlanesRouterJob;
use App\Models\Cliente;
use App\Models\Plan;
use App\Models\Router;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class AdminRouterControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrador_puede_ver_listado_de_routers(): void
    {
        $admin = User::factory()->create(['rol' => 'administrador']);
        $router = Router::factory()->create([
            'nombre' => 'Nodo Costa Chica',
            'ip_address' => '192.168.10.1',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.routers.index'));

        $response->assertStatus(200);
        $response->assertSee('Nodo Costa Chica');
        $response->assertSee('192.168.10.1');
        $response->assertSee('Nodos y Routers MikroTik');
    }

    public function test_operador_o_cobrador_no_pueden_administrar_routers(): void
    {
        $cobrador = User::factory()->create(['rol' => 'cobrador']);

        $response = $this->actingAs($cobrador)->get(route('admin.routers.index'));

        $response->assertStatus(403);
    }

    public function test_administrador_puede_crear_nuevo_router_con_contrasena_encriptada(): void
    {
        $admin = User::factory()->create(['rol' => 'administrador']);

        $response = $this->actingAs($admin)->post(route('admin.routers.store'), [
            'nombre' => 'Nodo Starlink Pinotepa 2',
            'ip_address' => '10.200.1.1',
            'api_user' => 'admin_starlink',
            'api_password' => 'super_password_mikrotik',
            'estado' => 'activo',
        ]);

        $response->assertRedirect(route('admin.routers.index'));

        $router = Router::where('nombre', 'Nodo Starlink Pinotepa 2')->firstOrFail();
        $this->assertEquals('admin_starlink', $router->api_user);
        $this->assertEquals('super_password_mikrotik', $router->api_password);
    }

    public function test_administrador_puede_actualizar_router(): void
    {
        $admin = User::factory()->create(['rol' => 'administrador']);
        $router = Router::factory()->create([
            'nombre' => 'Nodo Viejo',
            'api_password' => 'clave_original',
        ]);

        $response = $this->actingAs($admin)->put(route('admin.routers.update', $router), [
            'nombre' => 'Nodo Renovado',
            'ip_address' => '10.200.1.5',
            'api_user' => 'admin_nuevo',
            'api_password' => '', // Mantener la anterior
            'estado' => 'activo',
        ]);

        $response->assertRedirect(route('admin.routers.index'));

        $router->refresh();
        $this->assertEquals('Nodo Renovado', $router->nombre);
        $this->assertEquals('clave_original', $router->api_password);
    }

    public function test_no_se_puede_eliminar_router_con_clientes_asociados(): void
    {
        $admin = User::factory()->create(['rol' => 'administrador']);
        $router = Router::factory()->create();
        Cliente::factory()->create(['router_id' => $router->id]);

        $response = $this->actingAs($admin)->delete(route('admin.routers.destroy', $router));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('routers', ['id' => $router->id]);
    }

    public function test_probar_conexion_mikrotik_ejecuta_peticion_http_exitosa(): void
    {
        $admin = User::factory()->create(['rol' => 'administrador']);
        $router = Router::factory()->create([
            'ip_address' => '192.168.1.116',
            'api_user' => 'admin',
            'api_password' => 'secret',
        ]);

        Http::fake([
            'http://192.168.1.116/rest/system/resource' => Http::response([
                'version' => '7.15.2 (stable)',
                'board-name' => 'CCR2004-1G-12S+2XS',
                'uptime' => '5w3d2h',
            ], 200),
        ]);

        $response = $this->actingAs($admin)->post(route('admin.routers.test-conexion', $router));

        $response->assertSessionHas('success');
    }

    public function test_probar_conexion_mikrotik_muestra_error_cuando_credenciales_son_invalidas(): void
    {
        $admin = User::factory()->create(['rol' => 'administrador']);
        $router = Router::factory()->create([
            'ip_address' => '192.168.1.116',
            'api_user' => 'admin',
            'api_password' => 'password_erroneo',
        ]);

        Http::fake([
            'http://192.168.1.116/rest/system/resource' => Http::response(['error' => 401, 'message' => 'Unauthorized'], 401),
        ]);

        $response = $this->actingAs($admin)->post(route('admin.routers.test-conexion', $router));

        $response->assertSessionHas('error');
        $this->assertStringContainsString('Error 401 (No autorizado)', (string) session('error'));
    }

    public function test_probar_conexion_mikrotik_maneja_fallo_de_red(): void
    {
        $admin = User::factory()->create(['rol' => 'administrador']);
        $router = Router::factory()->create([
            'ip_address' => '192.168.1.250',
            'api_user' => 'admin',
            'api_password' => 'secret',
        ]);

        Http::fake([
            'http://192.168.1.250/rest/system/resource' => function () {
                throw new ConnectionException('Connection refused');
            },
        ]);

        $response = $this->actingAs($admin)->post(route('admin.routers.test-conexion', $router));

        $response->assertSessionHas('error');
        $this->assertStringContainsString('No se pudo conectar al router', (string) session('error'));
    }

    public function test_vistas_de_clientes_incluyen_selector_de_router_y_ficha_tecnica(): void
    {
        $admin = User::factory()->create(['rol' => 'administrador']);
        $router = Router::factory()->create(['nombre' => 'Nodo Central']);
        $plan = Plan::factory()->create(['nombre' => 'Plan 30M']);

        // Vista create
        $createResponse = $this->actingAs($admin)->get(route('admin.clientes.create'));
        $createResponse->assertStatus(200);
        $createResponse->assertSee('Nodo Central');
        $createResponse->assertSee('Nodo / Router MikroTik Asignado');

        // Vista show
        $cliente = Cliente::factory()->create([
            'router_id' => $router->id,
            'plan_id' => $plan->id,
            'usuario_pppoe' => '192.168.1.99',
            'serie_ont' => 'ONT-TEST-1234',
        ]);

        $showResponse = $this->actingAs($admin)->get(route('admin.clientes.show', $cliente));
        $showResponse->assertStatus(200);
        $showResponse->assertSee('Nodo Central');
        $showResponse->assertSee('192.168.1.99');
        $showResponse->assertSee('Parámetros Técnicos de Red / MikroTik');
    }

    public function test_registro_de_router_despacha_job_de_sincronizacion_si_esta_marcado(): void
    {
        Queue::fake();

        $admin = User::factory()->create(['rol' => 'administrador']);

        $response = $this->actingAs($admin)->post(route('admin.routers.store'), [
            'nombre' => 'Nodo Nuevo Starlink',
            'ip_address' => '192.168.1.200',
            'api_user' => 'admin',
            'api_password' => 'secret123',
            'estado' => 'activo',
            'auto_aprovisionar_planes' => '1',
        ]);

        $response->assertRedirect(route('admin.routers.index'));
        $this->assertDatabaseHas('routers', ['nombre' => 'Nodo Nuevo Starlink']);

        Queue::assertPushed(SincronizarPlanesRouterJob::class);
    }

    public function test_administrador_puede_sincronizar_planes_en_mikrotik_via_api(): void
    {
        $admin = User::factory()->create(['rol' => 'administrador']);
        $router = Router::factory()->create([
            'ip_address' => '192.168.1.116',
            'api_user' => 'admin',
            'api_password' => 'secret123',
            'estado' => 'activo',
        ]);
        $plan = Plan::factory()->create([
            'nombre' => 'Plan 50M Especial',
            'velocidad_descarga' => 50,
            'velocidad_carga' => 25,
            'estado' => 'activo',
        ]);

        Http::fake([
            'http://192.168.1.116/rest/system/resource' => Http::response(['version' => '7.15.2', 'board-name' => 'CHR'], 200),
            'http://192.168.1.116/rest/ip/firewall/filter*' => Http::response([], 200),
            'http://192.168.1.116/rest/ppp/profile*' => Http::response([], 200),
            'http://192.168.1.116/rest/queue/type*' => Http::response([], 200),
            'http://192.168.1.116/rest/queue/simple*' => Http::response([], 200),
        ]);

        $response = $this->actingAs($admin)->post(route('admin.routers.sincronizar-planes', $router));

        $response->assertSessionHas('success');
        $this->assertStringContainsString('Aprovisionamiento completado con éxito', (string) session('success'));
    }

    public function test_administrador_puede_ver_y_descargar_script_cli_mikrotik(): void
    {
        $admin = User::factory()->create(['rol' => 'administrador']);
        $router = Router::factory()->create([
            'nombre' => 'Nodo Costa',
            'ip_address' => '192.168.1.116',
        ]);
        $plan = Plan::factory()->create([
            'nombre' => 'Plan Fibra 100M',
            'velocidad_descarga' => 100,
            'velocidad_carga' => 50,
            'estado' => 'activo',
        ]);

        // Vista script
        $viewResponse = $this->actingAs($admin)->get(route('admin.routers.script', $router));
        $viewResponse->assertStatus(200);
        $viewResponse->assertSee('Script de Aprovisionamiento MikroTik');
        $viewResponse->assertSee('Plan Fibra 100M');
        $viewResponse->assertSee('PCQ_DOWN_100M');
        $viewResponse->assertSee('/ppp profile');

        // Descarga script .rsc
        $downloadResponse = $this->actingAs($admin)->get(route('admin.routers.descargar-script', $router));
        $downloadResponse->assertStatus(200);
        $downloadResponse->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
        $this->assertStringContainsString('attachment; filename="rivas_planes_nodo-costa.rsc"', (string) $downloadResponse->headers->get('Content-Disposition'));
        $this->assertStringContainsString('ISP RIVAS COMUNICACIONES', $downloadResponse->getContent());
    }
}
