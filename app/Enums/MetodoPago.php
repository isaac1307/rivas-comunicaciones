<?php

namespace App\Enums;

enum MetodoPago: string
{
    case Efectivo = 'efectivo';
    case Transferencia = 'transferencia';
    case Deposito = 'deposito';
    case Tarjeta = 'tarjeta';

    public function label(): string
    {
        return match ($this) {
            self::Efectivo => 'Efectivo',
            self::Transferencia => 'Transferencia',
            self::Deposito => 'Depósito Bancario',
            self::Tarjeta => 'Tarjeta',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Efectivo => 'bg-success',
            self::Transferencia => 'bg-info text-dark',
            self::Deposito => 'bg-primary',
            self::Tarjeta => 'bg-dark',
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
