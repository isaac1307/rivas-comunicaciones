<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\Reservacion;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FolioAndAuditObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_reservacion_observer_generates_unique_folio_format(): void
    {
        $reservacion = Reservacion::factory()->create();

        $this->assertNotEmpty($reservacion->folio);
        $this->assertMatchesRegularExpression('/^RES-\d{8}-[A-Z0-9]{4}$/', $reservacion->folio);
    }

    public function test_ticket_observer_generates_unique_folio_format(): void
    {
        $ticket = Ticket::factory()->create();

        $this->assertNotEmpty($ticket->folio);
        $this->assertMatchesRegularExpression('/^TKT-\d{8}-[A-Z0-9]{4}$/', $ticket->folio);
    }

    public function test_auditable_trait_logs_changes_in_bitacora(): void
    {
        $admin = User::factory()->create(['rol' => 'administrador']);
        $this->actingAs($admin);

        // Creación
        $plan = Plan::create([
            'nombre' => 'Plan Auditoría Test',
            'velocidad_descarga' => 100,
            'velocidad_carga' => 50,
            'precio_mensual' => 399.00,
            'estado' => 'activo',
        ]);

        $this->assertDatabaseHas('bitacoras', [
            'usuario_id' => $admin->id,
            'accion' => 'creacion',
            'tabla_afectada' => 'planes',
            'registro_id' => $plan->id,
        ]);

        // Actualización
        $plan->update(['precio_mensual' => 450.00]);

        $this->assertDatabaseHas('bitacoras', [
            'usuario_id' => $admin->id,
            'accion' => 'actualizacion',
            'tabla_afectada' => 'planes',
            'registro_id' => $plan->id,
        ]);

        // Eliminación
        $planId = $plan->id;
        $plan->delete();

        $this->assertDatabaseHas('bitacoras', [
            'usuario_id' => $admin->id,
            'accion' => 'eliminacion',
            'tabla_afectada' => 'planes',
            'registro_id' => $planId,
        ]);
    }
}
