<?php

namespace Database\Seeders;

use App\Actions\Cobranza\GenerarCargosMensuales;
use App\Actions\Cobranza\RegistrarPago;
use App\Models\Cargo;
use App\Models\Cliente;
use App\Models\MensajeContacto;
use App\Models\Plan;
use App\Models\Promocion;
use App\Models\Reservacion;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with demo data.
     */
    public function run(): void
    {
        // 1. Usuarios del Sistema (Admin y Operador)
        $admin = User::firstOrCreate(
            ['nombre_usuario' => 'admin'],
            [
                'nombre_completo' => 'Administrador General Rivas',
                'name' => 'Administrador General Rivas',
                'email' => 'admin@rivascomunicaciones.com',
                'password' => Hash::make('admin123'),
                'rol' => 'administrador',
                'estado' => 'activo',
            ]
        );

        $operador = User::firstOrCreate(
            ['nombre_usuario' => 'operador'],
            [
                'nombre_completo' => 'Operador Técnico Juan Pérez',
                'name' => 'Operador Técnico Juan Pérez',
                'email' => 'operador@rivascomunicaciones.com',
                'password' => Hash::make('operador123'),
                'rol' => 'operador',
                'estado' => 'activo',
            ]
        );

        $cobrador = User::firstOrCreate(
            ['nombre_usuario' => 'cobrador'],
            [
                'nombre_completo' => 'Cobrador Andrés Morales',
                'name' => 'Cobrador Andrés Morales',
                'email' => 'cobranza@rivascomunicaciones.com',
                'password' => Hash::make('cobrador123'),
                'rol' => 'cobrador',
                'estado' => 'activo',
            ]
        );

        // 2. Planes de Internet
        $plan50 = Plan::firstOrCreate(
            ['nombre' => 'Plan Fibra Básico 50 Megas'],
            [
                'velocidad_descarga' => 50,
                'velocidad_carga' => 25,
                'precio_mensual' => 349.00,
                'descripcion' => 'Ideal para navegación básica, redes sociales y música en streaming en 2 a 3 dispositivos.',
                'estado' => 'activo',
            ]
        );

        $plan100 = Plan::firstOrCreate(
            ['nombre' => 'Plan Fibra Familiar 100 Megas'],
            [
                'velocidad_descarga' => 100,
                'velocidad_carga' => 50,
                'precio_mensual' => 499.00,
                'descripcion' => 'Excelente para familias, videollamadas escolares y streaming simultáneo en Full HD y 4K.',
                'estado' => 'activo',
            ]
        );

        $plan200 = Plan::firstOrCreate(
            ['nombre' => 'Plan Fibra Gamer 200 Megas'],
            [
                'velocidad_descarga' => 200,
                'velocidad_carga' => 100,
                'precio_mensual' => 699.00,
                'descripcion' => 'Baja latencia optimizada para gaming competitivo en línea, descargas ultrarrápidas y trabajo remoto intensivo.',
                'estado' => 'activo',
            ]
        );

        $plan500 = Plan::firstOrCreate(
            ['nombre' => 'Plan Fibra Pro 500 Megas'],
            [
                'velocidad_descarga' => 500,
                'velocidad_carga' => 250,
                'precio_mensual' => 999.00,
                'descripcion' => 'Máxima potencia para oficinas y usuarios exigentes con alta demanda de ancho de banda simultáneo.',
                'estado' => 'activo',
            ]
        );

        // 3. Promociones Comerciales
        $promoApertura = Promocion::firstOrCreate(
            ['titulo' => 'Instalación Gratis + Primer Mes al 50%'],
            [
                'descripcion' => 'Aprovecha costo de instalación $0 y paga solo la mitad de tu primera mensualidad al contratar por 1 año.',
                'fecha_inicio' => now()->subDays(10)->toDateString(),
                'fecha_vencimiento' => now()->addDays(45)->toDateString(),
                'estado' => 'activa',
            ]
        );
        $promoApertura->planes()->syncWithoutDetaching([$plan100->id, $plan200->id]);

        $promoGamer = Promocion::firstOrCreate(
            ['titulo' => 'Doble de Velocidad por 3 Meses'],
            [
                'descripcion' => 'Contrata el plan de 100 Megas y navega a 200 Megas durante tus primeros 90 días de servicio.',
                'fecha_inicio' => now()->subDays(5)->toDateString(),
                'fecha_vencimiento' => now()->addDays(20)->toDateString(),
                'estado' => 'activa',
            ]
        );
        $promoGamer->planes()->syncWithoutDetaching([$plan100->id]);

        // 4. Clientes de Ejemplo
        $cliente1 = Cliente::firstOrCreate(
            ['telefono' => '7771234567'],
            [
                'nombre_completo' => 'María Elena Sánchez Rosas',
                'direccion' => 'Av. Revolución #45, Col. Centro, C.P. 62000',
                'correo_electronico' => 'maria.sanchez@ejemplo.com',
                'plan_id' => $plan100->id,
                'fecha_alta' => now()->subMonths(6)->toDateString(),
                'estado' => 'activo',
                'notas' => 'ONT Huawei GPON #HW-78923. Cliente cumplido.',
            ]
        );

        $cliente2 = Cliente::firstOrCreate(
            ['telefono' => '7779876543'],
            [
                'nombre_completo' => 'Alejandro Morales Cruz',
                'direccion' => 'Calle Nogales #12, Fracc. Los Arcos',
                'correo_electronico' => 'alejandro.morales@ejemplo.com',
                'plan_id' => $plan200->id,
                'fecha_alta' => now()->subMonths(2)->toDateString(),
                'estado' => 'activo',
                'notas' => 'Requiere IP fija para servidor de cámaras.',
            ]
        );

        // 5. Reservaciones de Ejemplo (Folios RES-YYYYMMDD-XXXX autogenerados por Observer)
        if (Reservacion::count() === 0) {
            Reservacion::create([
                'nombre_solicitante' => 'Laura Patricia Gómez',
                'telefono' => '7774567890',
                'direccion' => 'Calle Magnolia #33, Col. Bellavista',
                'correo_electronico' => 'laura.gomez@ejemplo.com',
                'tipo_servicio' => 'instalacion_nueva',
                'plan_id' => $plan100->id,
                'fecha_solicitada' => now()->addDays(2)->toDateString(),
                'observaciones' => 'Casa blanca de 2 pisos, portón negro. Preferente por la mañana.',
                'estado' => 'pendiente',
            ]);

            Reservacion::create([
                'nombre_solicitante' => 'Héctor Jiménez Ortiz',
                'telefono' => '7776543210',
                'direccion' => 'Av. Cuauhtémoc #88, Col. Plan de Ayala',
                'correo_electronico' => 'hector.j@ejemplo.com',
                'tipo_servicio' => 'instalacion_nueva',
                'plan_id' => $plan200->id,
                'fecha_solicitada' => now()->addDays(1)->toDateString(),
                'observaciones' => 'Cuadrilla técnica asignada: Unidad 04.',
                'estado' => 'programada',
            ]);
        }

        // 6. Tickets de Soporte de Ejemplo (Folios TKT-YYYYMMDD-XXXX autogenerados por Observer)
        if (Ticket::count() === 0) {
            Ticket::create([
                'cliente_id' => $cliente1->id,
                'categoria' => 'falla_servicio',
                'descripcion' => 'Luz LOS en rojo en el módem de fibra desde la tormenta de ayer en la noche.',
                'estado' => 'en_proceso',
                'observaciones_admin' => 'Técnico en ruta para revisión de splitter en poste.',
            ]);

            Ticket::create([
                'cliente_id' => $cliente2->id,
                'categoria' => 'soporte_tecnico',
                'descripcion' => 'Solicitud de cambio de nombre de red WiFi (SSID) y contraseña.',
                'estado' => 'resuelto',
                'fecha_resolucion' => now()->subHours(3),
                'observaciones_admin' => 'Cambio realizado exitosamente de forma remota.',
            ]);
        }

        // 7. Mensajes de Contacto
        if (MensajeContacto::count() === 0) {
            MensajeContacto::create([
                'nombre' => 'Esteban Fuentes',
                'correo_electronico' => 'esteban.f@empresa.com',
                'telefono' => '7772345678',
                'asunto' => 'Cotización de enlace empresarial simétrico',
                'mensaje' => 'Buen día, requerimos cotizar un enlace de 500 Megas simétricos para nuestras oficinas con 40 computadoras.',
                'leido' => false,
            ]);
        }

        // 8. Control de Cobranza (Cargos Mensuales y Pagos Demo)
        if (Cargo::count() === 0) {
            $generarCargos = app(GenerarCargosMensuales::class);
            // Generar mes actual
            $generarCargos->handle(now()->startOfMonth());

            // Registrar un pago demo realizado por cliente 1 atendido por el cobrador
            if ($cliente1->plan) {
                $cargoCliente1 = $cliente1->cargos()->first();
                if ($cargoCliente1) {
                    $registrarPago = app(RegistrarPago::class);
                    $registrarPago->handle($cliente1, [
                        'monto' => (float) $cargoCliente1->monto,
                        'metodo_pago' => 'efectivo',
                        'referencia' => 'REC-CAJA-001',
                        'fecha_pago' => now(),
                        'notas' => 'Pago en ventanilla mensualidad al corriente.',
                    ], $cobrador);
                }
            }

            // Generar un cargo vencido del mes anterior para cliente 2 para mostrar moroso
            if ($cliente2->plan) {
                $mesAnterior = now()->subMonth();
                Cargo::create([
                    'cliente_id' => $cliente2->id,
                    'plan_id' => $cliente2->plan_id,
                    'tipo' => 'mensualidad',
                    'periodo' => $mesAnterior->format('Y-m'),
                    'concepto' => 'Mensualidad '.$mesAnterior->format('F Y').' - '.$cliente2->plan->nombre,
                    'monto' => $cliente2->plan->precio_mensual,
                    'monto_pagado' => 0,
                    'fecha_vencimiento' => $mesAnterior->startOfMonth()->addDays(9)->toDateString(),
                    'estado' => 'pendiente',
                ]);
            }
        }
    }
}
