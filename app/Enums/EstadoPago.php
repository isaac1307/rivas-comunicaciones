<?php

namespace App\Enums;

enum EstadoPago: string
{
    case Aplicado = 'aplicado';
    case Anulado = 'anulado';

    public function label(): string
    {
        return match ($this) {
            self::Aplicado => 'Aplicado',
            self::Anulado => 'Anulado',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Aplicado => 'bg-success',
            self::Anulado => 'bg-danger',
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
