<?php

namespace App\Actions\Red;

use App\Models\Plan;
use App\Models\Router;
use Illuminate\Database\Eloquent\Collection;

class GenerarScriptMikrotik
{
    /**
     * Genera un script ejecutable para la consola de MikroTik RouterOS (WinBox Terminal / SSH)
     * que aprovisiona automáticamente los servicios, reglas de firewall para morosos,
     * perfiles PPP, tipos de cola PCQ y plantillas de control de velocidad de todos los planes activos.
     *
     * @param  Collection<int, Plan>|null  $planes
     */
    public function handle(Router $router, ?Collection $planes = null): string
    {
        $planes = $planes ?? Plan::activo()->orderBy('velocidad_descarga', 'asc')->get();
        $fecha = now()->format('Y-m-d H:i:s');
        $usuario = auth()->user() ? auth()->user()->name : 'Sistema ISP Rivas';

        $lineas = [];

        $lineas[] = '# =====================================================================';
        $lineas[] = '# ISP RIVAS COMUNICACIONES - APROVISIONAMIENTO DE PLANES Y REGLAS DE RED';
        $lineas[] = "# Nodo: {$router->nombre} (IP: {$router->ip_address})";
        $lineas[] = "# Generado el: {$fecha} por {$usuario}";
        $lineas[] = '# Compatible con: MikroTik RouterOS v7.x y v6.x';
        $lineas[] = '# =====================================================================';
        $lineas[] = '';
        $lineas[] = '/log info "=== Iniciando aprovisionamiento automatico ISP Rivas ==="';
        $lineas[] = '';

        // 1. Servicios REST y API
        $lineas[] = '# ---------------------------------------------------------------------';
        $lineas[] = '# 1. HABILITAR SERVICIO REST API Y SERVIDOR WEB (Puerto 80 y 8728)';
        $lineas[] = '# ---------------------------------------------------------------------';
        $lineas[] = '/ip service set www disabled=no port=80';
        $lineas[] = '/ip service set api disabled=no port=8728';
        $lineas[] = '';

        // 2. Reglas de Firewall para Morosos
        $lineas[] = '# ---------------------------------------------------------------------';
        $lineas[] = '# 2. REGLAS DE CORTE DE INTERNET PARA CLIENTES CON CARGOS VENCIDOS';
        $lineas[] = '# (Bloquea trafico de subida y bajada a IPs listadas en address-list morosos)';
        $lineas[] = '# ---------------------------------------------------------------------';
        $lineas[] = ':if ([:len [/ip firewall filter find comment="ISP Rivas - Bloqueo Morosos (Subida)"]] = 0) do={';
        $lineas[] = '    /ip firewall filter add chain=forward action=drop src-address-list=morosos comment="ISP Rivas - Bloqueo Morosos (Subida)" place-before=0';
        $lineas[] = '}';
        $lineas[] = ':if ([:len [/ip firewall filter find comment="ISP Rivas - Bloqueo Morosos (Bajada)"]] = 0) do={';
        $lineas[] = '    /ip firewall filter add chain=forward action=drop dst-address-list=morosos comment="ISP Rivas - Bloqueo Morosos (Bajada)" place-before=0';
        $lineas[] = '}';
        $lineas[] = '';

        // 3. Perfiles PPP (para conexiones PPPoE)
        $lineas[] = '# ---------------------------------------------------------------------';
        $lineas[] = '# 3. PERFILES PPP (PPPoE / CLIENTES FIBRA Y WIRELESS)';
        $lineas[] = '# ---------------------------------------------------------------------';
        foreach ($planes as $plan) {
            $nombreEscapado = addcslashes($plan->nombre, '"');
            $rateLimit = "{$plan->velocidad_carga}M/{$plan->velocidad_descarga}M";
            $precio = number_format((float) $plan->precio_mensual, 2);
            $comentario = "ISP Rivas - Plan #{$plan->id} ({$plan->velocidad_carga}M Up / {$plan->velocidad_descarga}M Down - \${$precio}/mes)";

            $lineas[] = "# Plan #{$plan->id}: {$plan->nombre}";
            $lineas[] = ":if ([:len [/ppp profile find name=\"{$nombreEscapado}\"]] = 0) do={";
            $lineas[] = "    /ppp profile add name=\"{$nombreEscapado}\" rate-limit=\"{$rateLimit}\" comment=\"{$comentario}\"";
            $lineas[] = '} else={';
            $lineas[] = "    /ppp profile set [find name=\"{$nombreEscapado}\"] rate-limit=\"{$rateLimit}\" comment=\"{$comentario}\"";
            $lineas[] = '}';
        }
        $lineas[] = '';

        // 4. Tipos de Cola PCQ (Traffic Shaper por velocidad)
        $lineas[] = '# ---------------------------------------------------------------------';
        $lineas[] = '# 4. TIPOS DE COLA PCQ (GESTION EQUITATIVA DE ANCHO DE BANDA)';
        $lineas[] = '# ---------------------------------------------------------------------';
        foreach ($planes as $plan) {
            $downName = "PCQ_DOWN_{$plan->velocidad_descarga}M";
            $upName = "PCQ_UP_{$plan->velocidad_carga}M";

            $lineas[] = ":if ([:len [/queue type find name=\"{$downName}\"]] = 0) do={";
            $lineas[] = "    /queue type add name=\"{$downName}\" kind=pcq pcq-rate={$plan->velocidad_descarga}M pcq-classifier=dst-address";
            $lineas[] = '} else={';
            $lineas[] = "    /queue type set [find name=\"{$downName}\"] pcq-rate={$plan->velocidad_descarga}M";
            $lineas[] = '}';

            $lineas[] = ":if ([:len [/queue type find name=\"{$upName}\"]] = 0) do={";
            $lineas[] = "    /queue type add name=\"{$upName}\" kind=pcq pcq-rate={$plan->velocidad_carga}M pcq-classifier=src-address";
            $lineas[] = '} else={';
            $lineas[] = "    /queue type set [find name=\"{$upName}\"] pcq-rate={$plan->velocidad_carga}M";
            $lineas[] = '}';
        }
        $lineas[] = '';

        // 5. Colas Simples Padre / Plantillas de Planes
        $lineas[] = '# ---------------------------------------------------------------------';
        $lineas[] = '# 5. COLAS SIMPLES PADRE / PLANTILLAS DE CONTROL DE VELOCIDAD';
        $lineas[] = '# ---------------------------------------------------------------------';
        foreach ($planes as $plan) {
            $colaNombre = "PLANTILLA-PLAN-{$plan->id}";
            $rateLimit = "{$plan->velocidad_carga}M/{$plan->velocidad_descarga}M";
            $comentario = "Plantilla {$plan->nombre} ({$rateLimit})";

            $lineas[] = ":if ([:len [/queue simple find name=\"{$colaNombre}\"]] = 0) do={";
            $lineas[] = "    /queue simple add name=\"{$colaNombre}\" max-limit={$rateLimit} comment=\"{$comentario}\"";
            $lineas[] = '} else={';
            $lineas[] = "    /queue simple set [find name=\"{$colaNombre}\"] max-limit={$rateLimit} comment=\"{$comentario}\"";
            $lineas[] = '}';
        }
        $lineas[] = '';

        $lineas[] = '/log info "=== Aprovisionamiento ISP Rivas completado exitosamente ==="';
        $lineas[] = '';

        return implode("\r\n", $lineas);
    }
}
