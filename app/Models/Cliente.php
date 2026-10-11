<?php

namespace App\Models;

use App\Traits\Auditable;
use Database\Factories\ClienteFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $nombre_completo
 * @property string $direccion
 * @property string $telefono
 * @property string|null $correo_electronico
 * @property int|null $plan_id
 * @property Carbon $fecha_alta
 * @property string $estado
 * @property string|null $notas
 */
class Cliente extends Model
{
    /** @use HasFactory<ClienteFactory> */
    use Auditable, HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'clientes';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nombre_completo',
        'direccion',
        'telefono',
        'correo_electronico',
        'plan_id',
        'fecha_alta',
        'estado',
        'notas',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha_alta' => 'date',
        ];
    }

    /**
     * Plan de servicio contratado por el cliente.
     *
     * @return BelongsTo<Plan, $this>
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'plan_id');
    }

    /**
     * Tickets de soporte generados por el cliente.
     *
     * @return HasMany<Ticket, $this>
     */
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'cliente_id');
    }

    /**
     * Scope para clientes activos.
     *
     * @param  Builder<Cliente>  $query
     * @return Builder<Cliente>
     */
    public function scopeActivo(Builder $query): Builder
    {
        return $query->where('estado', 'activo');
    }
}
