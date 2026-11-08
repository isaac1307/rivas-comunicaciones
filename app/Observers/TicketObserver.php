<?php

namespace App\Observers;

use App\Models\Ticket;
use App\Traits\GeneraFolioUnico;

class TicketObserver
{
    use GeneraFolioUnico;

    /**
     * Handle the Ticket "creating" event.
     */
    public function creating(Ticket $ticket): void
    {
        if (empty($ticket->folio)) {
            $ticket->folio = $this->generarFolioParaModelo('TKT', Ticket::class);
        }
    }
}
