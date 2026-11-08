<?php

namespace App\Models;

use App\Traits\Auditable;
use Database\Factories\RouterFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Crypt;

/**
 * @property int $id
 * @property string $nombre
 * @property string $ip_address
 * @property string $api_user
 * @property string $api_password
 * @property bool $usar_tls
 * @property bool $verificar_certificado
 * @property string $estado
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Cliente> $clientes
 */
class Router extends Model
{
    /** @use HasFactory<RouterFactory> */
    use Auditable, HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'routers';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nombre',
        'ip_address',
        'api_user',
        'api_password',
        'usar_tls',
        'verificar_certificado',
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
            'usar_tls' => 'boolean',
            'verificar_certificado' => 'boolean',
        ];
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'api_password',
    ];

    /**
     * Encripta automáticamente al guardar y desencripta al leer la contraseña del API.
     *
     * @return Attribute<string, string>
     */
    protected function apiPassword(): Attribute
    {
        return Attribute::make(
            get: function (?string $value): ?string {
                if ($value === null || $value === '') {
                    return $value;
                }

                try {
                    return Crypt::decryptString($value);
                } catch (\Throwable) {
                    return $value;
                }
            },
            set: function (?string $value): ?string {
                if ($value === null || $value === '') {
                    return $value;
                }

                return Crypt::encryptString($value);
            },
        );
    }

    /**
     * Clientes conectados o aprovisionados en este nodo/router.
     *
     * @return HasMany<Cliente, $this>
     */
    public function clientes(): HasMany
    {
        return $this->hasMany(Cliente::class, 'router_id');
    }

    /**
     * Indica si el router se encuentra en estado activo.
     */
    public function isActivo(): bool
    {
        return $this->estado === 'activo';
    }

    /**
     * Devuelve la URL base del API REST de RouterOS (http o https según usar_tls).
     */
    public function restBaseUrl(): string
    {
        $scheme = $this->usar_tls ? 'https' : 'http';

        return "{$scheme}://{$this->ip_address}/rest";
    }
}
