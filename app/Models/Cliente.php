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
 * @property int|null $router_id
 * @property Carbon $fecha_alta
 * @property string $estado
 * @property string $saldo_a_favor
 * @property string|null $notas
 * @property string|null $serie_ont
 * @property string|null $usuario_pppoe
 * @property string|null $coordenadas_gps
 * @property string|null $ip_asignada
 * @property float|string|null $total_cargos
 * @property float|string|null $total_pagado
 * @property int|null $cargos_vencidos_count
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
        'router_id',
        'fecha_alta',
        'estado',
        'saldo_a_favor',
        'notas',
        'serie_ont',
        'usuario_pppoe',
        'coordenadas_gps',
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
            'saldo_a_favor' => 'decimal:2',
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
     * Enrutador MikroTik (nodo) asignado al cliente.
     *
     * @return BelongsTo<Router, $this>
     */
    public function router(): BelongsTo
    {
        return $this->belongsTo(Router::class, 'router_id');
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
     * Cargos (adeudos) generados al cliente.
     *
     * @return HasMany<Cargo, $this>
     */
    public function cargos(): HasMany
    {
        return $this->hasMany(Cargo::class, 'cliente_id');
    }

    /**
     * Pagos realizados por el cliente.
     *
     * @return HasMany<Pago, $this>
     */
    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class, 'cliente_id');
    }

    /**
     * Reservaciones de instalación asociadas al cliente.
     *
     * @return HasMany<Reservacion, $this>
     */
    public function reservaciones(): HasMany
    {
        return $this->hasMany(Reservacion::class, 'cliente_id');
    }

    /**
     * Saldo pendiente calculado a partir de los agregados cargados con scopeConSaldo().
     */
    public function saldoPendiente(): float
    {
        return round((float) $this->total_cargos - (float) $this->total_pagado, 2);
    }

    /**
     * Indica si el cliente tiene historial de cobranza que impide eliminarlo.
     */
    public function tieneHistorialDeCobranza(): bool
    {
        return $this->cargos()->exists() || $this->pagos()->exists();
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

    /**
     * Agrega los totales de cobranza (cargado, pagado y cargos vencidos) en la misma consulta.
     *
     * @param  Builder<Cliente>  $query
     * @return Builder<Cliente>
     */
    public function scopeConSaldo(Builder $query): Builder
    {
        return $query
            ->withSum(['cargos as total_cargos' => fn (Builder $q) => $q->where('estado', '!=', 'cancelado')], 'monto')
            ->withSum(['cargos as total_pagado' => fn (Builder $q) => $q->where('estado', '!=', 'cancelado')], 'monto_pagado')
            ->withCount(['cargos as cargos_vencidos_count' => fn (Builder $q) => $q->whereIn('estado', Cargo::ESTADOS_PENDIENTES)->whereDate('fecha_vencimiento', '<', today())]);
    }

    /**
     * Scope para clientes con al menos un cargo con saldo.
     *
     * @param  Builder<Cliente>  $query
     * @return Builder<Cliente>
     */
    public function scopeConAdeudo(Builder $query): Builder
    {
        return $query->whereHas('cargos', fn (Builder $q) => $q->pendientes());
    }

    /**
     * Scope para clientes morosos (con cargos vencidos sin pagar).
     *
     * @param  Builder<Cliente>  $query
     * @return Builder<Cliente>
     */
    public function scopeMorosos(Builder $query): Builder
    {
        return $query->whereHas('cargos', fn (Builder $q) => $q->vencidos());
    }

    /**
     * Scope para clientes en estado suspendido.
     *
     * @param  Builder<Cliente>  $query
     * @return Builder<Cliente>
     */
    public function scopeSuspendido(Builder $query): Builder
    {
        return $query->where('estado', 'suspendido');
    }

    /**
     * Scope para búsqueda flexible por nombre, teléfono, dirección, ONT o usuario PPPoE.
     *
     * @param  Builder<Cliente>  $query
     * @return Builder<Cliente>
     */
    public function scopeBuscar(Builder $query, string $buscar): Builder
    {
        return $query->where(function (Builder $q) use ($buscar) {
            $q->where('nombre_completo', 'like', "%{$buscar}%")
                ->orWhere('telefono', 'like', "%{$buscar}%")
                ->orWhere('direccion', 'like', "%{$buscar}%")
                ->orWhere('serie_ont', 'like', "%{$buscar}%")
                ->orWhere('usuario_pppoe', 'like', "%{$buscar}%");
        });
    }

    /**
     * Indica si el cliente está en estado suspendido.
     */
    public function isSuspendido(): bool
    {
        return $this->estado === 'suspendido';
    }

    /**
     * Obtiene la IP asignada del cliente para el control en MikroTik (usando usuario_pppoe por defecto).
     */
    public function getIpAsignadaAttribute(): ?string
    {
        return $this->attributes['ip_asignada'] ?? $this->usuario_pppoe;
    }

    /**
     * Permite fijar dinámicamente la IP asignada.
     */
    public function setIpAsignadaAttribute(?string $value): void
    {
        $this->attributes['ip_asignada'] = $value;
    }
}
