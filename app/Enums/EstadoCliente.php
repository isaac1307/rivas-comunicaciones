<?php

namespace App\Enums;

enum EstadoCliente: string
{
    case Activo = 'activo';
    case Suspendido = 'suspendido';
    case Inactivo = 'inactivo';

    public function label(): string
    {
        return match ($this) {
            self::Activo => 'Activo',
            self::Suspendido => 'Suspendido',
            self::Inactivo => 'Inactivo',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Activo => 'bg-success',
            self::Suspendido => 'bg-warning text-dark',
            self::Inactivo => 'bg-secondary',
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
