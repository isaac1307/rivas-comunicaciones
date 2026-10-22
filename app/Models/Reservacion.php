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
 * @property int|null $cliente_id
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
        'cliente_id',
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

    /**
     * Cliente generado o vinculado a la reservación.
     *
     * @return BelongsTo<Cliente, $this>
     */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    /**
     * Devuelve el teléfono parcialmente enmascarado para vistas públicas.
     */
    public function telefonoEnmascarado(): string
    {
        $tel = preg_replace('/\D/', '', (string) $this->telefono) ?? '';
        if (strlen($tel) < 4) {
            return '***-***-****';
        }

        $ultimos4 = substr($tel, -4);

        return '***-***-'.$ultimos4;
    }

    /**
     * Devuelve la dirección parcialmente enmascarada para vistas públicas.
     */
    public function direccionEnmascarada(): string
    {
        $dir = trim((string) $this->direccion);
        $partes = explode(',', $dir);

        if (count($partes) > 1) {
            return 'Ubicación: '.trim($partes[1]).' (Número exterior y calle resguardados)';
        }

        return substr($dir, 0, min(8, strlen($dir))).'*** (Domicilio exacto resguardado por privacidad)';
    }

    /**
     * Devuelve el nombre con inicial y asteriscos si se requiere.
     */
    public function nombreEnmascarado(): string
    {
        $partes = explode(' ', trim((string) $this->nombre_solicitante));
        if (count($partes) <= 1) {
            return $this->nombre_solicitante;
        }

        return $partes[0].' '.substr($partes[1], 0, 1).'***';
    }
}
