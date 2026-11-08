<?php

namespace App\Observers;

use App\Models\Pago;
use App\Traits\GeneraFolioUnico;

class PagoObserver
{
    use GeneraFolioUnico;

    /**
     * Handle the Pago "creating" event.
     */
    public function creating(Pago $pago): void
    {
        if (empty($pago->folio)) {
            $pago->folio = $this->generarFolioParaModelo('PAG', Pago::class);
        }
    }
}
