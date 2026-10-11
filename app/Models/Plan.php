<?php

namespace App\Models;

use App\Traits\Auditable;
use Database\Factories\PlanFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $nombre
 * @property int $velocidad_descarga
 * @property int $velocidad_carga
 * @property float $precio_mensual
 * @property string|null $descripcion
 * @property string $estado
 */
class Plan extends Model
{
    /** @use HasFactory<PlanFactory> */
    use Auditable, HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'planes';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nombre',
        'velocidad_descarga',
        'velocidad_carga',
        'precio_mensual',
        'descripcion',
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
            'velocidad_descarga' => 'integer',
            'velocidad_carga' => 'integer',
            'precio_mensual' => 'decimal:2',
        ];
    }

    /**
     * Clientes inscritos en este plan.
     *
     * @return HasMany<Cliente, $this>
     */
    public function clientes(): HasMany
    {
        return $this->hasMany(Cliente::class, 'plan_id');
    }

    /**
     * Reservaciones asociadas a este plan.
     *
     * @return HasMany<Reservacion, $this>
     */
    public function reservaciones(): HasMany
    {
        return $this->hasMany(Reservacion::class, 'plan_id');
    }

    /**
     * Promociones aplicables a este plan.
     *
     * @return BelongsToMany<Promocion, $this>
     */
    public function promociones(): BelongsToMany
    {
        return $this->belongsToMany(Promocion::class, 'promocion_plan');
    }

    /**
     * Scope para planes activos.
     *
     * @param  Builder<Plan>  $query
     * @return Builder<Plan>
     */
    public function scopeActivo(Builder $query): Builder
    {
        return $query->where('estado', 'activo');
    }
}
