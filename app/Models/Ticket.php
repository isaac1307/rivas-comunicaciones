<?php

namespace App\Models;

use App\Observers\TicketObserver;
use App\Traits\Auditable;
use Database\Factories\TicketFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $folio
 * @property int $cliente_id
 * @property string $categoria
 * @property string $descripcion
 * @property string $estado
 * @property Carbon|null $fecha_resolucion
 * @property string|null $observaciones_admin
 */
#[ObservedBy(TicketObserver::class)]
class Ticket extends Model
{
    /** @use HasFactory<TicketFactory> */
    use Auditable, HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tickets';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'folio',
        'cliente_id',
        'categoria',
        'descripcion',
        'estado',
        'fecha_resolucion',
        'observaciones_admin',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha_resolucion' => 'datetime',
        ];
    }

    /**
     * Cliente que reportó el ticket.
     *
     * @return BelongsTo<Cliente, $this>
     */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }
}
