<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeguridadEndpointsPublicosTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_crear_ticket_no_expone_padron_de_clientes(): void
    {
        $cliente = Cliente::factory()->create([
            'nombre_completo' => 'Cliente Super Secreto PII',
            'telefono' => '7778889900',
            'direccion' => 'Calle Muy Privada #999',
            'estado' => 'activo',
        ]);

        $response = $this->get(route('tickets.create'));

        $response->assertOk();
        $response->assertDontSee('Cliente Super Secreto PII');
        $response->assertDontSee('Calle Muy Privada #999');
        $response->assertSee('Teléfono Registrado en tu Contrato');
    }

    public function test_crear_ticket_con_telefono_valido_asocia_cliente(): void
    {
        $cliente = Cliente::factory()->create([
            'telefono' => '7771234567',
            'estado' => 'activo',
        ]);

        $response = $this->post(route('tickets.store'), [
            'telefono' => '7771234567',
            'categoria' => 'falla_servicio',
            'descripcion' => 'Luz roja parpadeando en la ONT desde ayer.',
        ]);

        $ticket = Ticket::where('cliente_id', $cliente->id)->first();
        $this->assertNotNull($ticket);
        $this->assertStringStartsWith('TKT-', $ticket->folio);
        $response->assertRedirect(route('tickets.show', $ticket->folio));
    }

    public function test_crear_ticket_con_telefono_inexistente_arroja_error(): void
    {
        $response = $this->post(route('tickets.store'), [
            'telefono' => '9990001122',
            'categoria' => 'soporte_tecnico',
            'descripcion' => 'Requiero soporte para cambiar mi contraseña de red wifi.',
        ]);

        $response->assertSessionHasErrors('telefono');
        $this->assertEquals(0, Ticket::count());
    }

    public function test_rutas_publicas_tienen_middleware_throttle(): void
    {
        $routeReservaciones = app('router')->getRoutes()->getByName('reservaciones.store');
        $routeTickets = app('router')->getRoutes()->getByName('tickets.store');
        $routeContacto = app('router')->getRoutes()->getByName('contacto.store');

        $this->assertNotNull($routeReservaciones);
        $this->assertNotNull($routeTickets);
        $this->assertNotNull($routeContacto);

        $this->assertContains('throttle:10,1', $routeReservaciones->gatherMiddleware());
        $this->assertContains('throttle:10,1', $routeTickets->gatherMiddleware());
        $this->assertContains('throttle:10,1', $routeContacto->gatherMiddleware());
    }
}
