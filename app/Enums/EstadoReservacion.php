<?php

namespace App\Enums;

enum EstadoReservacion: string
{
    case Pendiente = 'pendiente';
    case Programada = 'programada';
    case Completada = 'completada';
    case Cancelada = 'cancelada';

    public function label(): string
    {
        return match ($this) {
            self::Pendiente => 'Pendiente',
            self::Programada => 'Programada',
            self::Completada => 'Completada',
            self::Cancelada => 'Cancelada',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Pendiente => 'bg-warning text-dark',
            self::Programada => 'bg-info text-dark',
            self::Completada => 'bg-success',
            self::Cancelada => 'bg-danger',
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
