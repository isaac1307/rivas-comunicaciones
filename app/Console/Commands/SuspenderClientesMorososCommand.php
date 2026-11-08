<?php

namespace App\Console\Commands;

use App\Actions\Red\SuspenderClientesMorosos;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('red:suspender-morosos {--dias-gracia= : Días de gracia posteriores al vencimiento antes de suspender}')]
#[Description('Suspende automáticamente a los clientes activos con cargos vencidos y despacha el corte en MikroTik')]
class SuspenderClientesMorososCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(SuspenderClientesMorosos $action): int
    {
        $opcionDias = $this->option('dias-gracia');
        $diasGracia = is_numeric($opcionDias) ? max(0, (int) $opcionDias) : null;

        $suspendidos = $action->handle($diasGracia);

        $this->info("Se suspendieron {$suspendidos} cliente(s) con adeudos vencidos.");

        return self::SUCCESS;
    }
}
