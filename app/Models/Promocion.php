<?php

namespace App\Models;

use App\Traits\Auditable;
use Database\Factories\PromocionFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $titulo
 * @property string $descripcion
 * @property Carbon $fecha_inicio
 * @property Carbon $fecha_vencimiento
 * @property string $estado
 */
class Promocion extends Model
{
    /** @use HasFactory<PromocionFactory> */
    use Auditable, HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'promociones';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'titulo',
        'descripcion',
        'fecha_inicio',
        'fecha_vencimiento',
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
            'fecha_inicio' => 'date',
            'fecha_vencimiento' => 'date',
        ];
    }

    /**
     * Planes incluidos en la promoción.
     *
     * @return BelongsToMany<Plan, $this>
     */
    public function planes(): BelongsToMany
    {
        return $this->belongsToMany(Plan::class, 'promocion_plan');
    }

    /**
     * Scope para promociones activas.
     *
     * @param  Builder<Promocion>  $query
     * @return Builder<Promocion>
     */
    public function scopeActiva(Builder $query): Builder
    {
        return $query->where('estado', 'activa');
    }

    /**
     * Scope para promociones vigentes por fecha y estado.
     *
     * @param  Builder<Promocion>  $query
     * @return Builder<Promocion>
     */
    public function scopeVigente(Builder $query): Builder
    {
        $hoy = now()->toDateString();

        return $query->where('estado', 'activa')
            ->where('fecha_inicio', '<=', $hoy)
            ->where('fecha_vencimiento', '>=', $hoy);
    }
}
