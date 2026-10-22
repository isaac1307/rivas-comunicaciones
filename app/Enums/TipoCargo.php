<?php

namespace App\Enums;

enum TipoCargo: string
{
    case Mensualidad = 'mensualidad';
    case Instalacion = 'instalacion';
    case Reconexion = 'reconexion';
    case Otro = 'otro';

    public function label(): string
    {
        return match ($this) {
            self::Mensualidad => 'Mensualidad',
            self::Instalacion => 'Instalación',
            self::Reconexion => 'Reconexión',
            self::Otro => 'Otro',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Mensualidad => 'bg-primary',
            self::Instalacion => 'bg-info text-dark',
            self::Reconexion => 'bg-warning text-dark',
            self::Otro => 'bg-secondary',
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
