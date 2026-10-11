<?php

namespace App\Observers;

use App\Models\Reservacion;
use Illuminate\Support\Str;

class ReservacionObserver
{
    /**
     * Handle the Reservacion "creating" event.
     */
    public function creating(Reservacion $reservacion): void
    {
        if (empty($reservacion->folio)) {
            $reservacion->folio = $this->generarFolioUnico();
        }
    }

    /**
     * Genera un folio único en formato RES-YYYYMMDD-XXXX.
     */
    protected function generarFolioUnico(): string
    {
        $fecha = now()->format('Ymd');

        do {
            $aleatorio = strtoupper(Str::random(4));
            $folio = "RES-{$fecha}-{$aleatorio}";
        } while (Reservacion::where('folio', $folio)->exists());

        return $folio;
    }
}
