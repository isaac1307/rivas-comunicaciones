<?php

namespace App\Observers;

use App\Models\Pago;
use Illuminate\Support\Str;

class PagoObserver
{
    /**
     * Handle the Pago "creating" event.
     */
    public function creating(Pago $pago): void
    {
        if (empty($pago->folio)) {
            $pago->folio = $this->generarFolioUnico();
        }
    }

    /**
     * Genera un folio único en formato PAG-YYYYMMDD-XXXX.
     */
    protected function generarFolioUnico(): string
    {
        $fecha = now()->format('Ymd');

        do {
            $aleatorio = strtoupper(Str::random(4));
            $folio = "PAG-{$fecha}-{$aleatorio}";
        } while (Pago::where('folio', $folio)->exists());

        return $folio;
    }
}
