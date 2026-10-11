<?php

namespace App\Models;

use App\Traits\Auditable;
use Database\Factories\MensajeContactoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $nombre
 * @property string $correo_electronico
 * @property string|null $telefono
 * @property string $asunto
 * @property string $mensaje
 * @property bool $leido
 */
class MensajeContacto extends Model
{
    /** @use HasFactory<MensajeContactoFactory> */
    use Auditable, HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'mensajes_contacto';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nombre',
        'correo_electronico',
        'telefono',
        'asunto',
        'mensaje',
        'leido',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'leido' => 'boolean',
        ];
    }
}
