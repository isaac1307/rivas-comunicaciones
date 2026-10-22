<?php

namespace App\Enums;

enum EstadoCargo: string
{
    case Pendiente = 'pendiente';
    case Parcial = 'parcial';
    case Pagado = 'pagado';
    case Cancelado = 'cancelado';

    public function label(): string
    {
        return match ($this) {
            self::Pendiente => 'Pendiente',
            self::Parcial => 'Pago Parcial',
            self::Pagado => 'Pagado',
            self::Cancelado => 'Cancelado',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Pendiente => 'bg-warning text-dark',
            self::Parcial => 'bg-info text-dark',
            self::Pagado => 'bg-success',
            self::Cancelado => 'bg-secondary',
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
