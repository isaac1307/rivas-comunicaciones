<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\PasskeyUser;
use Laravel\Fortify\PasskeyAuthenticatable;
use Laravel\Fortify\TwoFactorAuthenticatable;

/**
 * @property int $id
 * @property string $nombre_usuario
 * @property string $nombre_completo
 * @property string $name
 * @property string|null $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string $rol
 * @property string $estado
 * @property Carbon|null $ultimo_acceso
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property Carbon|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class User extends Authenticatable implements MustVerifyEmail, PasskeyUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, PasskeyAuthenticatable, TwoFactorAuthenticatable;

    /**
     * Roles disponibles en el sistema y su etiqueta visible.
     *
     * @var array<string, string>
     */
    public const ROLES = [
        'administrador' => 'Administrador',
        'operador' => 'Operador',
        'cobrador' => 'Cobrador',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'usuarios';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nombre_usuario',
        'nombre_completo',
        'name',
        'email',
        'password',
        'rol',
        'estado',
        'ultimo_acceso',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'ultimo_acceso' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::saving(function (User $user) {
            if (empty($user->nombre_usuario) && ! empty($user->email)) {
                $user->nombre_usuario = explode('@', $user->email)[0].'_'.rand(100, 999);
            }
            if (empty($user->nombre_completo) && ! empty($user->name)) {
                $user->nombre_completo = $user->name;
            }
            if (empty($user->name) && ! empty($user->nombre_completo)) {
                $user->name = $user->nombre_completo;
            }
        });
    }

    /**
     * Mutator & Accessor for name attribute to maintain compatibility.
     *
     * @return Attribute<string, string>
     */
    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value, array $attributes) => $value ?: ($attributes['nombre_completo'] ?? ''),
            set: fn (?string $value) => [
                'name' => $value,
                'nombre_completo' => $value,
            ],
        );
    }

    /**
     * Get user's activity logs.
     *
     * @return HasMany<Bitacora, $this>
     */
    public function bitacoras(): HasMany
    {
        return $this->hasMany(Bitacora::class, 'usuario_id');
    }

    /**
     * Check if user is administrator.
     */
    public function isAdmin(): bool
    {
        return $this->rol === 'administrador';
    }

    /**
     * Check if user is operator.
     */
    public function isOperador(): bool
    {
        return $this->rol === 'operador';
    }

    /**
     * Check if user is a billing collector.
     */
    public function isCobrador(): bool
    {
        return $this->rol === 'cobrador';
    }

    /**
     * Pagos registrados por el usuario.
     *
     * @return HasMany<Pago, $this>
     */
    public function pagosRegistrados(): HasMany
    {
        return $this->hasMany(Pago::class, 'usuario_id');
    }

    /**
     * Nombre de la ruta de inicio del panel según el rol del usuario.
     */
    public function rutaPanel(): string
    {
        return $this->isCobrador() ? 'admin.cobranza.index' : 'admin.dashboard';
    }

    /**
     * Etiqueta legible del rol.
     */
    public function rolEtiqueta(): string
    {
        return self::ROLES[$this->rol] ?? ucfirst($this->rol);
    }

    /**
     * Check if user is active.
     */
    public function isActivo(): bool
    {
        return $this->estado === 'activo';
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        $name = $this->nombre_completo ?: ($this->nombre_usuario ?: 'Usuario');
        $initials = Str::initials($name, true);

        return Str::length($initials) > 1
            ? Str::substr($initials, 0, 1).Str::substr($initials, -1)
            : $initials;
    }
}
