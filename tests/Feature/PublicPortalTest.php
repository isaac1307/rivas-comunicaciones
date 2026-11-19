<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Plan;
use App\Models\Promocion;
use App\Models\Reservacion;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_page_renders_successfully(): void
    {
        $plan = Plan::factory()->create();
        $promocion = Promocion::factory()->create();
        $promocion->planes()->attach($plan);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('Rivas Comunicaciones');
        $response->assertSee($plan->nombre);
    }

    public function test_catalogo_planes_renders(): void
    {
        $plan = Plan::factory()->create([
            'nombre' => 'Plan Fibra Test 100M',
            'estado' => 'activo',
        ]);

        $response = $this->get(route('planes.index'));

        $response->assertOk();
        $response->assertSee('Plan Fibra Test 100M');
    }

    public function test_promociones_page_renders(): void
    {
        $promocion = Promocion::factory()->create([
            'titulo' => 'Promocion Especial de Apertura',
            'estado' => 'activa',
            'fecha_inicio' => now()->subDay()->toDateString(),
            'fecha_vencimiento' => now()->addDays(10)->toDateString(),
        ]);

        $response = $this->get(route('promociones.index'));

        $response->assertOk();
        $response->assertSee('Promocion Especial de Apertura');
    }

    public function test_nosotros_and_cobertura_pages_render(): void
    {
        $this->get(route('nosotros'))->assertOk()->assertSee('Rivas Comunicaciones');
        $this->get(route('cobertura'))->assertOk()->assertSee('Zonas de Cobertura');
    }

    public function test_contacto_form_creates_message(): void
    {
        $datos = [
            'nombre' => 'María Test',
            'correo_electronico' => 'maria@test.com',
            'telefono' => '7771234567',
            'asunto' => 'Duda sobre cobertura',
            'mensaje' => 'Hola, quisiera saber si tienen cobertura en la colonia Bellavista.',
        ];

        $response = $this->post(route('contacto.store'), $datos);

        $response->assertRedirect(route('contacto'));
        $this->assertDatabaseHas('mensajes_contacto', [
            'correo_electronico' => 'maria@test.com',
            'asunto' => 'Duda sobre cobertura',
        ]);
    }

    public function test_solicitud_reservacion_creates_record_and_generates_folio(): void
    {
        $plan = Plan::factory()->create();

        $datos = [
            'nombre_solicitante' => 'Roberto Solicitante',
            'telefono' => '7779876543',
            'direccion' => 'Calle Flores #123, Col. Centro',
            'correo_electronico' => 'roberto@test.com',
            'tipo_servicio' => 'instalacion_nueva',
            'plan_id' => $plan->id,
            'fecha_solicitada' => now()->addDays(2)->toDateString(),
            'observaciones' => 'Favor de llamar antes de acudir.',
        ];

        $response = $this->post(route('reservaciones.store'), $datos);

        $reservacion = Reservacion::where('correo_electronico', 'roberto@test.com')->first();
        $this->assertNotNull($reservacion);
        $this->assertStringStartsWith('RES-', $reservacion->folio);

        $response->assertRedirect(route('reservaciones.show', $reservacion->folio));
    }

    public function test_consulta_reservacion_by_folio(): void
    {
        $reservacion = Reservacion::factory()->create([
            'nombre_solicitante' => 'Ana Consulta',
        ]);

        $response = $this->get(route('reservaciones.show', $reservacion->folio));

        $response->assertOk();
        $response->assertSee($reservacion->folio);
        $response->assertSee('Ana Consulta');
    }

    public function test_generar_ticket_soporte_creates_record_and_generates_folio(): void
    {
        $cliente = Cliente::factory()->create();

        $datos = [
            'cliente_id' => $cliente->id,
            'categoria' => 'falla_servicio',
            'descripcion' => 'Módem no enciende tras tormenta eléctrica.',
        ];

        $response = $this->post(route('tickets.store'), $datos);

        $ticket = Ticket::where('cliente_id', $cliente->id)->first();
        $this->assertNotNull($ticket);
        $this->assertStringStartsWith('TKT-', $ticket->folio);

        $response->assertRedirect(route('tickets.show', $ticket->folio));
    }

    public function test_consulta_ticket_by_folio(): void
    {
        $ticket = Ticket::factory()->create();

        $response = $this->get(route('tickets.show', $ticket->folio));

        $response->assertOk();
        $response->assertSee($ticket->folio);
        $response->assertSee($ticket->cliente->nombre_completo);
    }
}
