<?php

namespace App\Console\Commands;

use App\Actions\Cobranza\GenerarCargosMensuales;
use Carbon\CarbonImmutable;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Throwable;

#[Signature('cobranza:generar-cargos {--periodo= : Periodo a generar en formato YYYY-MM (por defecto, el mes actual)}')]
#[Description('Genera la mensualidad del periodo para cada cliente activo con plan asignado')]
class GenerarCargosMensualesCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(GenerarCargosMensuales $generarCargos): int
    {
        $opcionPeriodo = $this->option('periodo');

        try {
            $periodo = is_string($opcionPeriodo) && $opcionPeriodo !== ''
                ? CarbonImmutable::createFromFormat('!Y-m', $opcionPeriodo)
                : CarbonImmutable::now()->startOfMonth();
        } catch (Throwable) {
            $periodo = null;
        }

        if (! $periodo instanceof CarbonImmutable) {
            $this->error('El periodo debe tener el formato YYYY-MM, por ejemplo 2026-10.');

            return self::FAILURE;
        }

        $creados = $generarCargos->handle($periodo);

        $this->info("Se generaron {$creados} cargo(s) de mensualidad para el periodo {$periodo->format('Y-m')}.");

        return self::SUCCESS;
    }
}
