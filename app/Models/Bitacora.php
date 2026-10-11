<?php

namespace App\Models;

use Database\Factories\BitacoraFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int|null $usuario_id
 * @property string $accion
 * @property string $descripcion
 * @property string|null $tabla_afectada
 * @property int|null $registro_id
 * @property string|null $ip_origen
 */
class Bitacora extends Model
{
    /** @use HasFactory<BitacoraFactory> */
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'bitacoras';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'usuario_id',
        'accion',
        'descripcion',
        'tabla_afectada',
        'registro_id',
        'ip_origen',
    ];

    /**
     * Usuario que realizó la acción auditada.
     *
     * @return BelongsTo<User, $this>
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
