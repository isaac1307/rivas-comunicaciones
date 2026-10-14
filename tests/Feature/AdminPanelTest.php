<?php

namespace Tests\Feature;

use App\Models\Bitacora;
use App\Models\Cliente;
use App\Models\Plan;
use App\Models\Promocion;
use App\Models\Reservacion;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_login_with_valid_credentials(): void
    {
        $admin = User::factory()->create([
            'nombre_usuario' => 'admin_test',
            'password' => Hash::make('password123'),
            'rol' => 'administrador',
            'estado' => 'activo',
        ]);

        $response = $this->post(route('admin.login.submit'), [
            'nombre_usuario' => 'admin_test',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);
    }

    public function test_admin_login_fails_with_invalid_credentials(): void
    {
        User::factory()->create([
            'nombre_usuario' => 'admin_test',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post(route('admin.login.submit'), [
            'nombre_usuario' => 'admin_test',
            'password' => 'wrong_password',
        ]);

        $response->assertSessionHasErrors('nombre_usuario');
        $this->assertGuest();
    }

    public function test_guest_redirected_to_admin_login(): void
    {
        $response = $this->get(route('admin.dashboard'));

        $response->assertRedirect(route('login'));
    }

    public function test_admin_dashboard_shows_metrics(): void
    {
        $admin = User::factory()->create(['rol' => 'administrador']);
        Cliente::factory()->count(3)->create(['estado' => 'activo']);
        Ticket::factory()->count(2)->create(['estado' => 'abierto']);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('Panel de Control');
    }

    public function test_admin_can_create_plan(): void
    {
        $admin = User::factory()->create(['rol' => 'administrador']);

        $datos = [
            'nombre' => 'Plan Nuevo 300 Megas',
            'velocidad_descarga' => 300,
            'velocidad_carga' => 150,
            'precio_mensual' => 799.00,
            'descripcion' => 'Fibra dedicada simétrica.',
            'estado' => 'activo',
        ];

        $response = $this->actingAs($admin)->post(route('admin.planes.store'), $datos);

        $response->assertRedirect(route('admin.planes.index'));
        $this->assertDatabaseHas('planes', [
            'nombre' => 'Plan Nuevo 300 Megas',
            'velocidad_descarga' => 300,
        ]);
    }

    public function test_admin_can_update_plan(): void
    {
        $admin = User::factory()->create(['rol' => 'administrador']);
        $plan = Plan::factory()->create(['nombre' => 'Plan Original']);

        $response = $this->actingAs($admin)->put(route('admin.planes.update', $plan), [
            'nombre' => 'Plan Modificado',
            'velocidad_descarga' => 250,
            'velocidad_carga' => 125,
            'precio_mensual' => 650.00,
            'estado' => 'activo',
        ]);

        $response->assertRedirect(route('admin.planes.index'));
        $this->assertDatabaseHas('planes', [
            'id' => $plan->id,
            'nombre' => 'Plan Modificado',
        ]);
    }

    public function test_admin_can_create_promocion_and_sync_planes(): void
    {
        $admin = User::factory()->create(['rol' => 'administrador']);
        $plan1 = Plan::factory()->create();
        $plan2 = Plan::factory()->create();

        $datos = [
            'titulo' => 'Promo Primavera',
            'descripcion' => 'Mes gratis al contratar.',
            'fecha_inicio' => now()->toDateString(),
            'fecha_vencimiento' => now()->addDays(20)->toDateString(),
            'estado' => 'activa',
            'planes' => [$plan1->id, $plan2->id],
        ];

        $response = $this->actingAs($admin)->post(route('admin.promociones.store'), $datos);

        $response->assertRedirect(route('admin.promociones.index'));
        $promocion = Promocion::where('titulo', 'Promo Primavera')->first();
        $this->assertNotNull($promocion);
        $this->assertCount(2, $promocion->planes);
    }

    public function test_admin_can_create_cliente(): void
    {
        $admin = User::factory()->create(['rol' => 'administrador']);
        $plan = Plan::factory()->create();

        $datos = [
            'nombre_completo' => 'Nuevo Cliente ISP',
            'direccion' => 'Calle Principal 45',
            'telefono' => '7775551234',
            'correo_electronico' => 'nuevo@cliente.com',
            'plan_id' => $plan->id,
            'fecha_alta' => now()->toDateString(),
            'estado' => 'activo',
            'notas' => 'Instalación aprobada.',
        ];

        $response = $this->actingAs($admin)->post(route('admin.clientes.store'), $datos);

        $response->assertRedirect(route('admin.clientes.index'));
        $this->assertDatabaseHas('clientes', [
            'nombre_completo' => 'Nuevo Cliente ISP',
            'telefono' => '7775551234',
        ]);
    }

    public function test_admin_can_update_reservacion_estado(): void
    {
        $admin = User::factory()->create(['rol' => 'administrador']);
        $reservacion = Reservacion::factory()->create(['estado' => 'pendiente']);

        $response = $this->actingAs($admin)->put(route('admin.reservaciones.update', $reservacion), [
            'estado' => 'programada',
            'fecha_solicitada' => now()->addDay()->toDateString(),
            'observaciones' => 'Cuadrilla 2 asignada.',
        ]);

        $response->assertRedirect(route('admin.reservaciones.show', $reservacion));
        $this->assertDatabaseHas('reservaciones', [
            'id' => $reservacion->id,
            'estado' => 'programada',
        ]);
    }

    public function test_admin_can_update_ticket_estado_and_observaciones(): void
    {
        $admin = User::factory()->create(['rol' => 'administrador']);
        $ticket = Ticket::factory()->create(['estado' => 'abierto']);

        $response = $this->actingAs($admin)->put(route('admin.tickets.update', $ticket), [
            'categoria' => $ticket->categoria,
            'estado' => 'resuelto',
            'observaciones_admin' => 'Fibra fusionada nuevamente en poste.',
        ]);

        $response->assertRedirect(route('admin.tickets.show', $ticket));
        $ticket->refresh();
        $this->assertEquals('resuelto', $ticket->estado);
        $this->assertNotNull($ticket->fecha_resolucion);
    }

    public function test_operador_cannot_access_bitacoras(): void
    {
        $operador = User::factory()->create(['rol' => 'operador']);

        $response = $this->actingAs($operador)->get(route('admin.bitacoras.index'));

        $response->assertForbidden();
    }

    public function test_administrador_can_view_bitacoras(): void
    {
        $admin = User::factory()->create(['rol' => 'administrador']);

        $response = $this->actingAs($admin)->get(route('admin.bitacoras.index'));

        $response->assertOk();
        $response->assertSee('Bitácora de Auditoría');
    }

    public function test_completing_reservacion_automatically_creates_active_cliente(): void
    {
        $admin = User::factory()->create(['rol' => 'administrador']);
        $plan = Plan::factory()->create(['nombre' => 'Plan Fibra 100M']);
        $reservacion = Reservacion::factory()->create([
            'nombre_solicitante' => 'Carlos Prospecto Nuevo',
            'telefono' => '5551239999',
            'direccion' => 'Calle Las Rosas #45, Col. Centro',
            'correo_electronico' => 'carlos@prospecto.com',
            'plan_id' => $plan->id,
            'estado' => 'programada',
        ]);

        $response = $this->actingAs($admin)->put(route('admin.reservaciones.update', $reservacion), [
            'estado' => 'completada',
            'fecha_solicitada' => now()->toDateString(),
            'plan_id' => $plan->id,
            'observaciones' => 'Instalación de fibra óptica finalizada y validada con módem activo.',
        ]);

        $response->assertRedirect(route('admin.reservaciones.show', $reservacion));
        $response->assertSessionHas('success');

        $reservacion->refresh();
        $this->assertEquals('completada', $reservacion->estado);
        $this->assertNotNull($reservacion->cliente_id);

        $cliente = Cliente::find($reservacion->cliente_id);
        $this->assertNotNull($cliente);
        $this->assertEquals('Carlos Prospecto Nuevo', $cliente->nombre_completo);
        $this->assertEquals('5551239999', $cliente->telefono);
        $this->assertEquals('activo', $cliente->estado);
        $this->assertEquals($plan->id, $cliente->plan_id);

        // Verificar que se muestre en el listado de Clientes Activos
        $clientesResponse = $this->actingAs($admin)->get(route('admin.clientes.index'));
        $clientesResponse->assertOk();
        $clientesResponse->assertSee('Carlos Prospecto Nuevo');
        $clientesResponse->assertSee('Clientes Activos');
    }

    public function test_completing_reservacion_activates_existing_inactive_cliente(): void
    {
        $admin = User::factory()->create(['rol' => 'administrador']);
        $plan = Plan::factory()->create();
        $clienteInactivo = Cliente::factory()->create([
            'nombre_completo' => 'Cliente Reactivado',
            'telefono' => '7771112233',
            'estado' => 'inactivo',
        ]);

        $reservacion = Reservacion::factory()->create([
            'nombre_solicitante' => 'Cliente Reactivado',
            'telefono' => '7771112233',
            'plan_id' => $plan->id,
            'estado' => 'pendiente',
        ]);

        $this->actingAs($admin)->put(route('admin.reservaciones.update', $reservacion), [
            'estado' => 'completada',
            'plan_id' => $plan->id,
        ]);

        $reservacion->refresh();
        $clienteInactivo->refresh();

        $this->assertEquals($clienteInactivo->id, $reservacion->cliente_id);
        $this->assertEquals('activo', $clienteInactivo->estado);
        $this->assertEquals($plan->id, $clienteInactivo->plan_id);
    }

    public function test_administrador_can_filter_and_export_bitacoras(): void
    {
        $admin = User::factory()->create(['rol' => 'administrador', 'nombre_usuario' => 'admin_audit']);
        $operador = User::factory()->create(['rol' => 'operador', 'nombre_usuario' => 'operador_audit']);

        Bitacora::create([
            'usuario_id' => $admin->id,
            'accion' => 'creacion',
            'descripcion' => 'Alta en planes #10 (Plan Fibra 500)',
            'tabla_afectada' => 'planes',
            'registro_id' => 10,
            'ip_origen' => '192.168.1.100',
        ]);

        Bitacora::create([
            'usuario_id' => $operador->id,
            'accion' => 'actualizacion',
            'descripcion' => 'Actualización en tickets #5: modificó [estado]',
            'tabla_afectada' => 'tickets',
            'registro_id' => 5,
            'ip_origen' => '192.168.1.101',
        ]);

        // Ver index con filtros
        $response = $this->actingAs($admin)->get(route('admin.bitacoras.index', [
            'accion' => 'creacion',
            'tabla' => 'planes',
        ]));

        $response->assertOk();
        $response->assertSee('Plan Fibra 500');
        $response->assertDontSee('tickets #5');

        // Búsqueda libre
        $searchResponse = $this->actingAs($admin)->get(route('admin.bitacoras.index', [
            'buscar' => '192.168.1.101',
        ]));
        $searchResponse->assertOk();
        $searchResponse->assertSee('tickets #5');

        // Exportación CSV
        $exportResponse = $this->actingAs($admin)->get(route('admin.bitacoras.export', [
            'accion' => 'creacion',
        ]));
        $exportResponse->assertOk();
        $exportResponse->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_login_and_logout_record_security_audit_events(): void
    {
        $user = User::factory()->create([
            'nombre_usuario' => 'test_audit_login',
            'password' => Hash::make('password123'),
            'rol' => 'administrador',
            'estado' => 'activo',
        ]);

        $this->post(route('admin.login.submit'), [
            'nombre_usuario' => 'test_audit_login',
            'password' => 'password123',
        ]);

        $this->assertDatabaseHas('bitacoras', [
            'usuario_id' => $user->id,
            'accion' => 'acceso',
            'tabla_afectada' => 'usuarios',
        ]);

        $this->actingAs($user)->post(route('admin.logout'));

        $this->assertDatabaseHas('bitacoras', [
            'usuario_id' => $user->id,
            'accion' => 'cierre_sesion',
            'tabla_afectada' => 'usuarios',
        ]);
    }
}
