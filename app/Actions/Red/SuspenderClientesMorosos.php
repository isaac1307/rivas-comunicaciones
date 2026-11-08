<?php

namespace App\Actions\Red;

use App\Enums\EstadoCliente;
use App\Jobs\Red\SuspenderServicioRedJob;
use App\Models\Cargo;
use App\Models\Cliente;
use App\Models\Router;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class SuspenderClientesMorosos
{
    /**
     * Suspende en base de datos y despacha el corte en MikroTik para todos los clientes
     * activos con cargos vencidos más allá de los días de gracia configurados.
     *
     * @return int Número de clientes suspendidos.
     */
    public function handle(?int $diasGracia = null): int
    {
        $diasGracia = max(0, $diasGracia ?? (int) config('cobranza.dias_gracia', 0));
        $fechaLimite = today()->subDays($diasGracia);
        $suspendidos = 0;

        Cliente::query()
            ->where('estado', EstadoCliente::Activo->value)
            ->whereHas('cargos', function (Builder $q) use ($fechaLimite) {
                $q->whereIn('estado', Cargo::ESTADOS_PENDIENTES)
                    ->whereDate('fecha_vencimiento', '<', $fechaLimite);
            })
            ->with('router')
            ->orderBy('id')
            ->chunkById(200, function ($clientes) use ($fechaLimite, &$suspendidos) {
                foreach ($clientes as $cliente) {
                    $fueSuspendido = DB::transaction(function () use ($cliente, $fechaLimite) {
                        /** @var Cliente|null $clienteBloqueado */
                        $clienteBloqueado = Cliente::whereKey($cliente->id)->lockForUpdate()->first();

                        if (! $clienteBloqueado || $clienteBloqueado->estado !== EstadoCliente::Activo->value) {
                            return false;
                        }

                        $sigueMoroso = $clienteBloqueado->cargos()
                            ->whereIn('estado', Cargo::ESTADOS_PENDIENTES)
                            ->whereDate('fecha_vencimiento', '<', $fechaLimite)
                            ->exists();

                        if (! $sigueMoroso) {
                            return false;
                        }

                        $clienteBloqueado->estado = EstadoCliente::Suspendido->value;
                        $clienteBloqueado->save();

                        return true;
                    });

                    if ($fueSuspendido) {
                        $suspendidos++;

                        $router = $cliente->router;
                        if (! empty($cliente->ip_asignada) && $router instanceof Router && $router->isActivo()) {
                            SuspenderServicioRedJob::dispatch($router, $cliente->ip_asignada, (string) $cliente->id);
                        }
                    }
                }
            });

        return $suspendidos;
    }
}
