<?php

namespace Tests\Feature;

use App\Actions\Cobranza\GenerarCargosMensuales;
use App\Models\Cargo;
use App\Models\Cliente;
use App\Models\Pago;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CobranzaTest extends TestCase
{
    use RefreshDatabase;

    public function test_cobrador_can_view_cobranza_index(): void
    {
        $cobrador = User::factory()->create(['rol' => 'cobrador']);
        Cliente::factory()->count(3)->create();

        $response = $this->actingAs($cobrador)->get(route('admin.cobranza.index'));

        $response->assertOk();
        $response->assertSee('Control de Cobranza');
    }

    public function test_cobrador_cannot_access_commercial_or_security_modules(): void
    {
        $cobrador = User::factory()->create(['rol' => 'cobrador']);

        $this->actingAs($cobrador)->get(route('admin.dashboard'))->assertForbidden();
        $this->actingAs($cobrador)->get(route('admin.planes.index'))->assertForbidden();
        $this->actingAs($cobrador)->get(route('admin.clientes.index'))->assertForbidden();
        $this->actingAs($cobrador)->get(route('admin.bitacoras.index'))->assertForbidden();
        $this->actingAs($cobrador)->get(route('admin.usuarios.index'))->assertForbidden();
    }

    public function test_operador_cannot_access_cobranza_module(): void
    {
        $operador = User::factory()->create(['rol' => 'operador']);

        $response = $this->actingAs($operador)->get(route('admin.cobranza.index'));

        $response->assertForbidden();
    }

    public function test_cobrador_can_view_client_ledger(): void
    {
        $cobrador = User::factory()->create(['rol' => 'cobrador']);
        $plan = Plan::factory()->create(['precio_mensual' => 450.00]);
        $cliente = Cliente::factory()->create(['plan_id' => $plan->id]);

        Cargo::factory()->create([
            'cliente_id' => $cliente->id,
            'plan_id' => $plan->id,
            'monto' => 450.00,
            'monto_pagado' => 0,
            'estado' => 'pendiente',
        ]);

        $response = $this->actingAs($cobrador)->get(route('admin.cobranza.show', $cliente));

        $response->assertOk();
        $response->assertSee('Estado de Cuenta');
        $response->assertSee($cliente->nombre_completo);
        $response->assertSee('450.00');
    }

    public function test_cobrador_can_record_payment_and_distribute_to_charges(): void
    {
        $cobrador = User::factory()->create(['rol' => 'cobrador']);
        $plan = Plan::factory()->create(['precio_mensual' => 500.00]);
        $cliente = Cliente::factory()->create(['plan_id' => $plan->id]);

        $cargo1 = Cargo::factory()->create([
            'cliente_id' => $cliente->id,
            'monto' => 500.00,
            'monto_pagado' => 0,
            'fecha_vencimiento' => now()->subMonth(),
            'estado' => 'pendiente',
        ]);

        $cargo2 = Cargo::factory()->create([
            'cliente_id' => $cliente->id,
            'monto' => 500.00,
            'monto_pagado' => 0,
            'fecha_vencimiento' => now(),
            'estado' => 'pendiente',
        ]);

        // Pago que liquida completamente el cargo1 y abona la mitad del cargo2 (750 total)
        $response = $this->actingAs($cobrador)->post(route('admin.pagos.store', $cliente), [
            'monto' => 750.00,
            'metodo_pago' => 'efectivo',
            'referencia' => 'REC-001',
            'notas' => 'Pago parcial de dos mensualidades.',
        ]);

        $response->assertRedirect(route('admin.cobranza.show', $cliente));

        $pago = Pago::where('cliente_id', $cliente->id)->first();
        $this->assertNotNull($pago);
        $this->assertStringStartsWith('PAG-', $pago->folio);
        $this->assertEquals(750.00, (float) $pago->monto);

        $cargo1->refresh();
        $cargo2->refresh();

        $this->assertEquals('pagado', $cargo1->estado);
        $this->assertEquals(500.00, (float) $cargo1->monto_pagado);

        $this->assertEquals('parcial', $cargo2->estado);
        $this->assertEquals(250.00, (float) $cargo2->monto_pagado);
        $this->assertEquals(250.00, $cargo2->saldo());
    }

    public function test_payment_cannot_exceed_client_balance(): void
    {
        $cobrador = User::factory()->create(['rol' => 'cobrador']);
        $cliente = Cliente::factory()->create();

        Cargo::factory()->create([
            'cliente_id' => $cliente->id,
            'monto' => 300.00,
            'monto_pagado' => 0,
            'estado' => 'pendiente',
        ]);

        $response = $this->actingAs($cobrador)->post(route('admin.pagos.store', $cliente), [
            'monto' => 500.00, // Excede los 300 de saldo
            'metodo_pago' => 'efectivo',
        ]);

        $response->assertSessionHasErrors('monto');
        $this->assertEquals(0, Pago::count());
    }

    public function test_payment_receipt_can_be_viewed(): void
    {
        $cobrador = User::factory()->create(['rol' => 'cobrador']);
        $cliente = Cliente::factory()->create();
        $pago = Pago::factory()->create([
            'cliente_id' => $cliente->id,
            'usuario_id' => $cobrador->id,
            'monto' => 400.00,
        ]);

        $response = $this->actingAs($cobrador)->get(route('admin.pagos.recibo', $pago));

        $response->assertOk();
        $response->assertSee('RECIBO DE PAGO');
        $response->assertSee($pago->folio);
    }

    public function test_cobrador_cannot_void_payment_but_admin_can(): void
    {
        $cobrador = User::factory()->create(['rol' => 'cobrador']);
        $admin = User::factory()->create(['rol' => 'administrador']);
        $cliente = Cliente::factory()->create();

        $cargo = Cargo::factory()->create([
            'cliente_id' => $cliente->id,
            'monto' => 500.00,
            'monto_pagado' => 500.00,
            'estado' => 'pagado',
        ]);

        $pago = Pago::factory()->create([
            'cliente_id' => $cliente->id,
            'usuario_id' => $cobrador->id,
            'monto' => 500.00,
            'estado' => 'aplicado',
        ]);
        $pago->cargos()->attach($cargo->id, ['monto_aplicado' => 500.00]);

        // Cobrador intenta anular
        $this->actingAs($cobrador)->post(route('admin.pagos.anular', $pago), [
            'motivo_anulacion' => 'Error de captura',
        ])->assertForbidden();

        // Administrador anula el pago
        $response = $this->actingAs($admin)->post(route('admin.pagos.anular', $pago), [
            'motivo_anulacion' => 'Cheque sin fondos devuelto por el banco.',
        ]);

        $response->assertSessionHas('success');
        $pago->refresh();
        $cargo->refresh();

        $this->assertEquals('anulado', $pago->estado);
        $this->assertEquals('pendiente', $cargo->estado);
        $this->assertEquals(0, (float) $cargo->monto_pagado);
        $this->assertEquals(500.00, $cargo->saldo());
    }

    public function test_bulk_monthly_charges_generation_is_idempotent(): void
    {
        $cobrador = User::factory()->create(['rol' => 'cobrador']);
        $plan = Plan::factory()->create(['precio_mensual' => 600.00]);
        $cliente = Cliente::factory()->create([
            'plan_id' => $plan->id,
            'estado' => 'activo',
        ]);

        $action = app(GenerarCargosMensuales::class);
        $creados1 = $action->handle(now()->startOfMonth());
        $this->assertEquals(1, $creados1);

        // Si se vuelve a ejecutar en el mismo periodo, no debe duplicar
        $creados2 = $action->handle(now()->startOfMonth());
        $this->assertEquals(0, $creados2);

        $this->assertEquals(1, Cargo::where('cliente_id', $cliente->id)->count());
    }

    public function test_manual_extra_charge_creation(): void
    {
        $cobrador = User::factory()->create(['rol' => 'cobrador']);
        $cliente = Cliente::factory()->create();

        $response = $this->actingAs($cobrador)->post(route('admin.cargos.store', $cliente), [
            'tipo' => 'instalacion',
            'periodo' => now()->format('Y-m'),
            'concepto' => 'Instalación de fibra óptica domicilio',
            'monto' => 800.00,
            'fecha_vencimiento' => now()->addDays(5)->toDateString(),
        ]);

        $response->assertRedirect(route('admin.cobranza.show', $cliente));
        $this->assertDatabaseHas('cargos', [
            'cliente_id' => $cliente->id,
            'tipo' => 'instalacion',
            'monto' => 800.00,
        ]);
    }
}
