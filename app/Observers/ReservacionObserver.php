<?php

namespace App\Observers;

use App\Models\Reservacion;
use App\Traits\GeneraFolioUnico;

class ReservacionObserver
{
    use GeneraFolioUnico;

    /**
     * Handle the Reservacion "creating" event.
     */
    public function creating(Reservacion $reservacion): void
    {
        if (empty($reservacion->folio)) {
            $reservacion->folio = $this->generarFolioParaModelo('RES', Reservacion::class);
        }
    }
}
