<?php

namespace App\Models;

use App\Enums\EstadoCargo;
use App\Traits\Auditable;
use Database\Factories\CargoFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $cliente_id
 * @property int|null $plan_id
 * @property string $tipo
 * @property string $periodo
 * @property string $concepto
 * @property string $monto
 * @property string $monto_pagado
 * @property Carbon $fecha_vencimiento
 * @property string $estado
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Cargo extends Model
{
    /** @use HasFactory<CargoFactory> */
    use Auditable, HasFactory;

    /**
     * Estados de un cargo que aún tienen saldo por cobrar.
     *
     * @var list<string>
     */
    public const ESTADOS_PENDIENTES = ['pendiente', 'parcial'];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cargos';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'cliente_id',
        'plan_id',
        'tipo',
        'periodo',
        'concepto',
        'monto',
        'monto_pagado',
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
            'monto' => 'decimal:2',
            'monto_pagado' => 'decimal:2',
            'fecha_vencimiento' => 'date',
        ];
    }

    /**
     * Cliente al que pertenece el cargo.
     *
     * @return BelongsTo<Cliente, $this>
     */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    /**
     * Plan con el que se calculó el cargo.
     *
     * @return BelongsTo<Plan, $this>
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'plan_id');
    }

    /**
     * Pagos que han abonado a este cargo.
     *
     * @return BelongsToMany<Pago, $this>
     */
    public function pagos(): BelongsToMany
    {
        return $this->belongsToMany(Pago::class, 'aplicaciones_pago', 'cargo_id', 'pago_id')
            ->withPivot('monto_aplicado')
            ->withTimestamps();
    }

    /**
     * Saldo pendiente del cargo expresado en centavos enteros (sin errores de punto flotante).
     */
    public function saldoEnCentavos(): int
    {
        if ($this->estado === EstadoCargo::Cancelado->value) {
            return 0;
        }

        $montoCentavos = (int) round((float) $this->monto * 100);
        $pagadoCentavos = (int) round((float) $this->monto_pagado * 100);

        return max(0, $montoCentavos - $pagadoCentavos);
    }

    /**
     * Saldo pendiente por cubrir del cargo.
     */
    public function saldo(): float
    {
        return round($this->saldoEnCentavos() / 100, 2);
    }

    /**
     * Indica si el cargo tiene saldo y ya pasó su fecha de vencimiento.
     */
    public function estaVencido(): bool
    {
        return in_array($this->estado, self::ESTADOS_PENDIENTES, true)
            && $this->fecha_vencimiento->lt(today());
    }

    /**
     * Recalcula el estado del cargo a partir del monto pagado.
     */
    public function recalcularEstado(): void
    {
        if ($this->estado === EstadoCargo::Cancelado->value) {
            return;
        }

        $pagadoCentavos = (int) round((float) $this->monto_pagado * 100);
        $montoCentavos = (int) round((float) $this->monto * 100);

        $this->estado = match (true) {
            $pagadoCentavos <= 0 => EstadoCargo::Pendiente->value,
            $pagadoCentavos < $montoCentavos => EstadoCargo::Parcial->value,
            default => EstadoCargo::Pagado->value,
        };
    }

    /**
     * Scope para cargos con saldo por cobrar.
     *
     * @param  Builder<Cargo>  $query
     * @return Builder<Cargo>
     */
    public function scopePendientes(Builder $query): Builder
    {
        return $query->whereIn('estado', self::ESTADOS_PENDIENTES);
    }

    /**
     * Scope para cargos con saldo cuya fecha de vencimiento ya pasó.
     *
     * @param  Builder<Cargo>  $query
     * @return Builder<Cargo>
     */
    public function scopeVencidos(Builder $query): Builder
    {
        return $query->whereIn('estado', self::ESTADOS_PENDIENTES)
            ->whereDate('fecha_vencimiento', '<', today());
    }
}
