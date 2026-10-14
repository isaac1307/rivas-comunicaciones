<?php

namespace App\Actions\Reservaciones;

use App\Models\Cliente;
use App\Models\Reservacion;
use Illuminate\Support\Facades\DB;

class CompletarReservacion
{
    /**
     * Actualiza la reservación y, si su estado es completada, registra o activa
     * automáticamente al solicitante en el catálogo de clientes activos.
     *
     * @param  array<string, mixed>  $datos
     */
    public function handle(Reservacion $reservacion, array $datos): ?Cliente
    {
        return DB::transaction(function () use ($reservacion, $datos) {
            $reservacion->update($datos);

            if ($reservacion->estado !== 'completada') {
                return null;
            }

            // Buscar si ya existe el cliente por ID previo, teléfono o correo
            $cliente = null;

            if ($reservacion->cliente_id) {
                $cliente = Cliente::find($reservacion->cliente_id);
            }

            if (! $cliente && ! empty($reservacion->telefono)) {
                $cliente = Cliente::where('telefono', $reservacion->telefono)->first();
            }

            if (! $cliente && ! empty($reservacion->correo_electronico)) {
                $cliente = Cliente::where('correo_electronico', $reservacion->correo_electronico)->first();
            }

            if ($cliente) {
                $cliente->update([
                    'estado' => 'activo',
                    'plan_id' => $reservacion->plan_id ?: $cliente->plan_id,
                    'direccion' => $reservacion->direccion ?: $cliente->direccion,
                ]);
            } else {
                $cliente = Cliente::create([
                    'nombre_completo' => $reservacion->nombre_solicitante,
                    'direccion' => $reservacion->direccion,
                    'telefono' => $reservacion->telefono,
                    'correo_electronico' => $reservacion->correo_electronico,
                    'plan_id' => $reservacion->plan_id,
                    'fecha_alta' => now()->toDateString(),
                    'estado' => 'activo',
                    'notas' => "Alta automática al completar con éxito la instalación (Folio: {$reservacion->folio}).".($reservacion->observaciones ? " Observaciones: {$reservacion->observaciones}" : ''),
                ]);
            }

            if ($reservacion->cliente_id !== $cliente->id) {
                $reservacion->cliente_id = $cliente->id;
                $reservacion->save();
            }

            return $cliente;
        });
    }
}
