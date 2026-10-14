<?php

namespace App\Models;

use App\Observers\PagoObserver;
use App\Traits\Auditable;
use Database\Factories\PagoFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $folio
 * @property int $cliente_id
 * @property int|null $usuario_id
 * @property string $monto
 * @property string $metodo_pago
 * @property string|null $referencia
 * @property Carbon $fecha_pago
 * @property string|null $notas
 * @property string $estado
 * @property string|null $motivo_anulacion
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[ObservedBy(PagoObserver::class)]
class Pago extends Model
{
    /** @use HasFactory<PagoFactory> */
    use Auditable, HasFactory;

    /**
     * Métodos de pago aceptados y su etiqueta visible.
     *
     * @var array<string, string>
     */
    public const METODOS = [
        'efectivo' => 'Efectivo',
        'transferencia' => 'Transferencia',
        'deposito' => 'Depósito bancario',
        'tarjeta' => 'Tarjeta',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'pagos';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'folio',
        'cliente_id',
        'usuario_id',
        'monto',
        'metodo_pago',
        'referencia',
        'fecha_pago',
        'notas',
        'estado',
        'motivo_anulacion',
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
            'fecha_pago' => 'datetime',
        ];
    }

    /**
     * Cliente que realizó el pago.
     *
     * @return BelongsTo<Cliente, $this>
     */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    /**
     * Usuario (cobrador o administrador) que registró el pago.
     *
     * @return BelongsTo<User, $this>
     */
    public function cobrador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    /**
     * Cargos liquidados o abonados con este pago.
     *
     * @return BelongsToMany<Cargo, $this>
     */
    public function cargos(): BelongsToMany
    {
        return $this->belongsToMany(Cargo::class, 'aplicaciones_pago', 'pago_id', 'cargo_id')
            ->withPivot('monto_aplicado')
            ->withTimestamps();
    }

    /**
     * Etiqueta legible del método de pago.
     */
    public function metodoEtiqueta(): string
    {
        return self::METODOS[$this->metodo_pago] ?? ucfirst($this->metodo_pago);
    }

    /**
     * Indica si el pago fue anulado.
     */
    public function estaAnulado(): bool
    {
        return $this->estado === 'anulado';
    }

    /**
     * Scope para pagos vigentes (no anulados).
     *
     * @param  Builder<Pago>  $query
     * @return Builder<Pago>
     */
    public function scopeAplicados(Builder $query): Builder
    {
        return $query->where('estado', 'aplicado');
    }
}
