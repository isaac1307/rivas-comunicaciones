<?php

namespace App\Enums;

enum RolUsuario: string
{
    case Administrador = 'administrador';
    case Operador = 'operador';
    case Cobrador = 'cobrador';

    public function label(): string
    {
        return match ($this) {
            self::Administrador => 'Administrador',
            self::Operador => 'Operador',
            self::Cobrador => 'Cobrador',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Administrador => 'bg-danger',
            self::Operador => 'bg-info text-dark',
            self::Cobrador => 'bg-success',
        };
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
