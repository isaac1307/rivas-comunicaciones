<?php

namespace App\Enums;

enum EstadoTicket: string
{
    case Abierto = 'abierto';
    case EnProceso = 'en_proceso';
    case Resuelto = 'resuelto';
    case Cerrado = 'cerrado';

    public function label(): string
    {
        return match ($this) {
            self::Abierto => 'Abierto',
            self::EnProceso => 'En Proceso',
            self::Resuelto => 'Resuelto',
            self::Cerrado => 'Cerrado',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Abierto => 'bg-danger',
            self::EnProceso => 'bg-warning text-dark',
            self::Resuelto => 'bg-success',
            self::Cerrado => 'bg-secondary',
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
