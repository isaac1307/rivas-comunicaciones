<?php

namespace App\Observers;

use App\Models\Ticket;
use Illuminate\Support\Str;

class TicketObserver
{
    /**
     * Handle the Ticket "creating" event.
     */
    public function creating(Ticket $ticket): void
    {
        if (empty($ticket->folio)) {
            $ticket->folio = $this->generarFolioUnico();
        }
    }

    /**
     * Genera un folio único en formato TKT-YYYYMMDD-XXXX.
     */
    protected function generarFolioUnico(): string
    {
        $fecha = now()->format('Ymd');

        do {
            $aleatorio = strtoupper(Str::random(4));
            $folio = "TKT-{$fecha}-{$aleatorio}";
        } while (Ticket::where('folio', $folio)->exists());

        return $folio;
    }
}
