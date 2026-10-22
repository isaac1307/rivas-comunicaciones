<?php

namespace Tests\Feature;

use App\Models\Bitacora;
use App\Models\Cliente;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BitacoraJsonDiffTest extends TestCase
{
    use RefreshDatabase;

    public function test_creacion_de_modelo_registra_valores_nuevos_en_bitacora(): void
    {
        $plan = Plan::create([
            'nombre' => 'Plan Fibra Auditoria 100M',
            'velocidad_descarga' => 100,
            'velocidad_carga' => 50,
            'precio_mensual' => 450.00,
            'descripcion' => 'Plan para pruebas de auditoría',
            'estado' => 'activo',
        ]);

        $bitacora = Bitacora::where('tabla_afectada', 'planes')
            ->where('registro_id', $plan->id)
            ->where('accion', 'creacion')
            ->latest()
            ->first();

        $this->assertNotNull($bitacora);
        $this->assertIsArray($bitacora->valores_nuevos);
        $this->assertNull($bitacora->valores_anteriores);
        $this->assertEquals('Plan Fibra Auditoria 100M', $bitacora->valores_nuevos['nombre']);
        $this->assertContains($bitacora->ip_origen, ['CLI', '127.0.0.1']);
    }

    public function test_actualizacion_registra_diff_json_sin_campos_sensibles(): void
    {
        $admin = User::factory()->create([
            'rol' => 'administrador',
            'password' => bcrypt('secreto123'),
        ]);

        $cliente = Cliente::factory()->create([
            'nombre_completo' => 'Nombre Original',
            'telefono' => '7771112233',
            'estado' => 'activo',
        ]);

        $this->actingAs($admin);

        $cliente->update([
            'nombre_completo' => 'Nombre Modificado',
            'telefono' => '7774445566',
        ]);

        $bitacora = Bitacora::where('tabla_afectada', 'clientes')
            ->where('registro_id', $cliente->id)
            ->where('accion', 'actualizacion')
            ->latest()
            ->first();

        $this->assertNotNull($bitacora);
        $this->assertIsArray($bitacora->valores_anteriores);
        $this->assertIsArray($bitacora->valores_nuevos);

        // Verifica que capturó los cambios de los campos modificados
        $this->assertEquals('Nombre Original', $bitacora->valores_anteriores['nombre_completo']);
        $this->assertEquals('Nombre Modificado', $bitacora->valores_nuevos['nombre_completo']);
        $this->assertEquals('7771112233', $bitacora->valores_anteriores['telefono']);
        $this->assertEquals('7774445566', $bitacora->valores_nuevos['telefono']);

        // Verifica que no se auditan campos ruidosos o sensibles
        $this->assertArrayNotHasKey('updated_at', $bitacora->valores_anteriores);
        $this->assertArrayNotHasKey('updated_at', $bitacora->valores_nuevos);
        $this->assertArrayNotHasKey('password', $bitacora->valores_nuevos);
    }
}
