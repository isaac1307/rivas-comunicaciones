<?php

namespace App\Models;

use App\Observers\ReservacionObserver;
use App\Traits\Auditable;
use Database\Factories\ReservacionFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $folio
 * @property string $nombre_solicitante
 * @property string $telefono
 * @property string $direccion
 * @property string|null $correo_electronico
 * @property string $tipo_servicio
 * @property int|null $plan_id
 * @property Carbon|null $fecha_solicitada
 * @property string|null $observaciones
 * @property string $estado
 */
#[ObservedBy(ReservacionObserver::class)]
class Reservacion extends Model
{
    /** @use HasFactory<ReservacionFactory> */
    use Auditable, HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'reservaciones';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'folio',
        'nombre_solicitante',
        'telefono',
        'direccion',
        'correo_electronico',
        'tipo_servicio',
        'plan_id',
        'fecha_solicitada',
        'observaciones',
        'estado',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha_solicitada' => 'date',
        ];
    }

    /**
     * Plan seleccionado para la reservación.
     *
     * @return BelongsTo<Plan, $this>
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'plan_id');
    }
}
