<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Plan;
use App\Models\Router;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ClienteRedWispTest extends TestCase
{
    use RefreshDatabase;

    public function test_creacion_de_cliente_con_parametros_wisp(): void
    {
        $admin = User::factory()->create(['rol' => 'administrador']);
        $plan = Plan::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.clientes.store'), [
            'nombre_completo' => 'Cliente WISP Tecnico',
            'telefono' => '7773332211',
            'direccion' => 'Calle Pino Suárez #10, Col. Alta',
            'fecha_alta' => now()->toDateString(),
            'plan_id' => $plan->id,
            'estado' => 'activo',
            'serie_ont' => 'HWTC-1234ABCD',
            'usuario_pppoe' => 'pppoe_cliente_01',
            'coordenadas_gps' => '18.9242, -99.2215',
            'notas' => 'Instalación en poste 14 con drop de 80m.',
        ]);

        $response->assertRedirect(route('admin.clientes.index'));

        $this->assertDatabaseHas('clientes', [
            'nombre_completo' => 'Cliente WISP Tecnico',
            'serie_ont' => 'HWTC-1234ABCD',
            'usuario_pppoe' => 'pppoe_cliente_01',
            'coordenadas_gps' => '18.9242, -99.2215',
            'estado' => 'activo',
        ]);
    }

    public function test_cambio_de_estado_a_suspendido_y_edicion_de_parametros(): void
    {
        $admin = User::factory()->create(['rol' => 'administrador']);
        $cliente = Cliente::factory()->create([
            'estado' => 'activo',
            'serie_ont' => 'HWTC-OLD',
        ]);

        $response = $this->actingAs($admin)->put(route('admin.clientes.update', $cliente), [
            'nombre_completo' => $cliente->nombre_completo,
            'telefono' => $cliente->telefono,
            'direccion' => $cliente->direccion,
            'fecha_alta' => $cliente->fecha_alta->toDateString(),
            'estado' => 'suspendido',
            'serie_ont' => 'HWTC-NEW-9988',
            'usuario_pppoe' => 'usr_pppoe_suspendido',
            'coordenadas_gps' => '18.9200, -99.2200',
        ]);

        $response->assertRedirect(route('admin.clientes.index'));

        $cliente->refresh();
        $this->assertEquals('suspendido', $cliente->estado);
        $this->assertTrue($cliente->isSuspendido());
        $this->assertEquals('HWTC-NEW-9988', $cliente->serie_ont);
        $this->assertEquals('usr_pppoe_suspendido', $cliente->usuario_pppoe);
    }

    public function test_busqueda_de_cliente_por_serie_ont_y_usuario_pppoe(): void
    {
        $admin = User::factory()->create(['rol' => 'administrador']);

        $cliente1 = Cliente::factory()->create([
            'nombre_completo' => 'Juan Perez',
            'serie_ont' => 'ZTEG-55443322',
            'usuario_pppoe' => 'juan_perez_net',
        ]);

        $cliente2 = Cliente::factory()->create([
            'nombre_completo' => 'Maria Garcia',
            'serie_ont' => 'HWTC-11223344',
            'usuario_pppoe' => 'maria_garcia_net',
        ]);

        // Búsqueda por número de serie ONT
        $responseOnt = $this->actingAs($admin)->get(route('admin.clientes.index', ['buscar' => '55443322']));
        $responseOnt->assertOk();
        $responseOnt->assertSee('Juan Perez');
        $responseOnt->assertDontSee('Maria Garcia');

        // Búsqueda por usuario PPPoE
        $responsePppoe = $this->actingAs($admin)->get(route('admin.clientes.index', ['buscar' => 'maria_garcia_net']));
        $responsePppoe->assertOk();
        $responsePppoe->assertSee('Maria Garcia');
        $responsePppoe->assertDontSee('Juan Perez');
    }

    public function test_suspender_servicio_cliente_agrega_a_morosos_y_cierra_conexiones(): void
    {
        Http::fake([
            'http://192.168.1.116/rest/ip/firewall/address-list*' => Http::response([], 200),
            'http://192.168.1.116/rest/ip/firewall/connection*' => Http::response([
                ['.id' => '*1', 'src-address' => '10.10.10.254'],
            ], 200),
            'http://192.168.1.116/rest/ppp/active*' => Http::response([], 200),
        ]);

        $admin = User::factory()->create(['rol' => 'administrador']);
        $router = Router::factory()->create([
            'ip_address' => '192.168.1.116',
            'api_user' => 'admin',
            'api_password' => 'admin123',
            'estado' => 'activo',
        ]);
        $cliente = Cliente::factory()->create([
            'estado' => 'activo',
            'usuario_pppoe' => '10.10.10.254',
            'router_id' => $router->id,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.clientes.suspender', $cliente));

        $response->assertSessionHas('success');
        $cliente->refresh();
        $this->assertEquals('suspendido', $cliente->estado);
    }

    public function test_reconectar_servicio_cliente_remueve_de_morosos(): void
    {
        Http::fake([
            'http://192.168.1.116/rest/ip/firewall/address-list*' => Http::response([
                ['.id' => '*10', 'address' => '10.10.10.254', 'list' => 'morosos'],
            ], 200),
        ]);

        $admin = User::factory()->create(['rol' => 'administrador']);
        $router = Router::factory()->create([
            'ip_address' => '192.168.1.116',
            'api_user' => 'admin',
            'api_password' => 'admin123',
            'estado' => 'activo',
        ]);
        $cliente = Cliente::factory()->create([
            'estado' => 'suspendido',
            'usuario_pppoe' => '10.10.10.254',
            'router_id' => $router->id,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.clientes.reconectar', $cliente));

        $response->assertSessionHas('success');
        $cliente->refresh();
        $this->assertEquals('activo', $cliente->estado);
    }
}
