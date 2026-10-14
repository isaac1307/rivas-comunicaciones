<?php

namespace Tests\Feature;

use App\Models\Cargo;
use App\Models\Cliente;
use App\Models\Plan;
use App\Models\Promocion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class UsuarioYSeguridadTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_manage_users(): void
    {
        $admin = User::factory()->create(['rol' => 'administrador']);

        // Ver listado
        $response = $this->actingAs($admin)->get(route('admin.usuarios.index'));
        $response->assertOk();
        $response->assertSee('Usuarios del Sistema');

        // Crear nuevo cobrador
        $crearResponse = $this->actingAs($admin)->post(route('admin.usuarios.store'), [
            'nombre_completo' => 'Nuevo Cobrador',
            'nombre_usuario' => 'cobrador_nuevo',
            'email' => 'cobrador@nuevo.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'rol' => 'cobrador',
            'estado' => 'activo',
        ]);

        $crearResponse->assertRedirect(route('admin.usuarios.index'));
        $this->assertDatabaseHas('usuarios', [
            'nombre_usuario' => 'cobrador_nuevo',
            'rol' => 'cobrador',
        ]);
    }

    public function test_operador_and_cobrador_cannot_access_user_management(): void
    {
        $operador = User::factory()->create(['rol' => 'operador']);
        $cobrador = User::factory()->create(['rol' => 'cobrador']);

        $this->actingAs($operador)->get(route('admin.usuarios.index'))->assertForbidden();
        $this->actingAs($cobrador)->get(route('admin.usuarios.index'))->assertForbidden();
    }

    public function test_cannot_delete_self_or_last_admin(): void
    {
        $admin = User::factory()->create(['rol' => 'administrador']);

        // Intento de eliminarse a sí mismo
        $responseSelf = $this->actingAs($admin)->delete(route('admin.usuarios.destroy', $admin));
        $responseSelf->assertSessionHas('error');
        $this->assertDatabaseHas('usuarios', ['id' => $admin->id]);

        // Crear otro admin
        $admin2 = User::factory()->create(['rol' => 'administrador']);
        // Ahora sí puede eliminar al segundo admin
        $responseOther = $this->actingAs($admin)->delete(route('admin.usuarios.destroy', $admin2));
        $responseOther->assertSessionHas('success');
        $this->assertDatabaseMissing('usuarios', ['id' => $admin2->id]);
    }

    public function test_operador_cannot_delete_planes_promociones_or_clientes(): void
    {
        $operador = User::factory()->create(['rol' => 'operador']);
        $plan = Plan::factory()->create();
        $promo = Promocion::factory()->create();
        $cliente = Cliente::factory()->create();

        $this->actingAs($operador)->delete(route('admin.planes.destroy', $plan))->assertForbidden();
        $this->actingAs($operador)->delete(route('admin.promociones.destroy', $promo))->assertForbidden();
        $this->actingAs($operador)->delete(route('admin.clientes.destroy', $cliente))->assertForbidden();

        $this->assertDatabaseHas('planes', ['id' => $plan->id]);
        $this->assertDatabaseHas('promociones', ['id' => $promo->id]);
        $this->assertDatabaseHas('clientes', ['id' => $cliente->id]);
    }

    public function test_cannot_delete_client_with_billing_history(): void
    {
        $admin = User::factory()->create(['rol' => 'administrador']);
        $cliente = Cliente::factory()->create();

        Cargo::factory()->create([
            'cliente_id' => $cliente->id,
            'monto' => 350.00,
        ]);

        $response = $this->actingAs($admin)->delete(route('admin.clientes.destroy', $cliente));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('clientes', ['id' => $cliente->id]);
    }

    public function test_login_rate_limiting_blocks_after_too_many_failed_attempts(): void
    {
        RateLimiter::clear('intruso|127.0.0.1');

        User::factory()->create([
            'nombre_usuario' => 'intruso',
            'password' => Hash::make('correct_pass'),
        ]);

        // 5 intentos fallidos
        for ($i = 0; $i < 5; $i++) {
            $this->post(route('admin.login.submit'), [
                'nombre_usuario' => 'intruso',
                'password' => 'wrong_pass',
            ]);
        }

        // El sexto intento debe ser bloqueado por RateLimiter
        $response = $this->post(route('admin.login.submit'), [
            'nombre_usuario' => 'intruso',
            'password' => 'wrong_pass',
        ]);

        $response->assertSessionHasErrors('nombre_usuario');
        $errores = session('errors')->get('nombre_usuario');
        $this->assertStringContainsString('Demasiados intentos fallidos', $errores[0]);
    }

    public function test_cobrador_login_redirects_directly_to_cobranza(): void
    {
        User::factory()->create([
            'nombre_usuario' => 'cobrador_login',
            'password' => Hash::make('password123'),
            'rol' => 'cobrador',
            'estado' => 'activo',
        ]);

        $response = $this->post(route('admin.login.submit'), [
            'nombre_usuario' => 'cobrador_login',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('admin.cobranza.index'));
    }
}
