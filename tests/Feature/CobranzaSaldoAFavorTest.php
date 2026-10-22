<?php

namespace Tests\Feature;

use App\Actions\Cobranza\AnularPago;
use App\Actions\Cobranza\GenerarCargosMensuales;
use App\Actions\Cobranza\RegistrarPago;
use App\Models\Cargo;
use App\Models\Cliente;
use App\Models\Pago;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CobranzaSaldoAFavorTest extends TestCase
{
    use RefreshDatabase;

    public function test_pago_mayor_a_deuda_liquida_fifo_y_acredita_excedente_a_saldo_a_favor(): void
    {
        $cobrador = User::factory()->create(['rol' => 'cobrador']);
        $cliente = Cliente::factory()->create(['saldo_a_favor' => 0.00]);

        $cargoAntiguo = Cargo::factory()->create([
            'cliente_id' => $cliente->id,
            'monto' => 300.00,
            'monto_pagado' => 0.00,
            'fecha_vencimiento' => now()->subMonths(2),
            'estado' => 'pendiente',
        ]);

        $cargoReciente = Cargo::factory()->create([
            'cliente_id' => $cliente->id,
            'monto' => 200.00,
            'monto_pagado' => 0.00,
            'fecha_vencimiento' => now()->subMonth(),
            'estado' => 'pendiente',
        ]);

        $action = app(RegistrarPago::class);
        $pago = $action->handle($cliente, [
            'monto' => 700.00,
            'metodo_pago' => 'transferencia',
            'referencia' => 'TR-998877',
            'permitir_saldo_a_favor' => true,
        ], $cobrador);

        $cargoAntiguo->refresh();
        $cargoReciente->refresh();
        $cliente->refresh();

        $this->assertEquals('pagado', $cargoAntiguo->estado);
        $this->assertEquals(300.00, (float) $cargoAntiguo->monto_pagado);

        $this->assertEquals('pagado', $cargoReciente->estado);
        $this->assertEquals(200.00, (float) $cargoReciente->monto_pagado);

        $this->assertEquals(700.00, (float) $pago->monto);
        $this->assertEquals(200.00, (float) $pago->monto_saldo_favor);
        $this->assertEquals(200.00, (float) $cliente->saldo_a_favor);
    }

    public function test_generar_cargos_mensuales_aplica_automaticamente_saldo_a_favor(): void
    {
        $plan = Plan::factory()->create(['precio_mensual' => 500.00]);
        $cliente = Cliente::factory()->create([
            'plan_id' => $plan->id,
            'estado' => 'activo',
            'saldo_a_favor' => 200.00,
        ]);

        $action = app(GenerarCargosMensuales::class);
        $creados = $action->handle(now()->startOfMonth());

        $this->assertEquals(1, $creados);

        $cargo = Cargo::where('cliente_id', $cliente->id)->first();
        $this->assertNotNull($cargo);
        $this->assertEquals(500.00, (float) $cargo->monto);
        $this->assertEquals(200.00, (float) $cargo->monto_pagado);
        $this->assertEquals('parcial', $cargo->estado);

        $cliente->refresh();
        $this->assertEquals(0.00, (float) $cliente->saldo_a_favor);
    }

    public function test_anular_pago_con_saldo_a_favor_revierte_cargos_y_saldo_del_cliente(): void
    {
        $cobrador = User::factory()->create(['rol' => 'cobrador']);
        $cliente = Cliente::factory()->create(['saldo_a_favor' => 0.00]);

        $cargo = Cargo::factory()->create([
            'cliente_id' => $cliente->id,
            'monto' => 400.00,
            'monto_pagado' => 0.00,
            'estado' => 'pendiente',
        ]);

        $actionRegistrar = app(RegistrarPago::class);
        $pago = $actionRegistrar->handle($cliente, [
            'monto' => 600.00,
            'metodo_pago' => 'efectivo',
            'permitir_saldo_a_favor' => true,
        ], $cobrador);

        $cargo->refresh();
        $cliente->refresh();

        $this->assertEquals('pagado', $cargo->estado);
        $this->assertEquals(200.00, (float) $cliente->saldo_a_favor);

        // Anular el pago
        $actionAnular = app(AnularPago::class);
        $actionAnular->handle($pago, 'Aclaración de pago duplicado');

        $cargo->refresh();
        $cliente->refresh();
        $pago->refresh();

        $this->assertEquals('anulado', $pago->estado);
        $this->assertEquals('pendiente', $cargo->estado);
        $this->assertEquals(0.00, (float) $cargo->monto_pagado);
        $this->assertEquals(0.00, (float) $cliente->saldo_a_favor);
    }
}
