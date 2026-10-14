<?php

namespace App\Actions\Cobranza;

use App\Models\Cargo;
use App\Models\Pago;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AnularPago
{
    /**
     * Anula un pago y revierte los abonos que hizo a cada cargo.
     *
     * @throws ValidationException Si el pago ya estaba anulado.
     */
    public function handle(Pago $pago, string $motivo): void
    {
        DB::transaction(function () use ($pago, $motivo) {
            $pago = Pago::whereKey($pago->id)->lockForUpdate()->firstOrFail();

            if ($pago->estaAnulado()) {
                throw ValidationException::withMessages([
                    'motivo_anulacion' => 'Este pago ya fue anulado anteriormente.',
                ]);
            }

            $pago->load('cargos');

            foreach ($pago->cargos as $cargo) {
                /** @var Cargo $cargo */
                $aplicado = (float) $cargo->getRelationValue('pivot')->monto_aplicado;

                $cargo->monto_pagado = (string) max(round((float) $cargo->monto_pagado - $aplicado, 2), 0);
                $cargo->recalcularEstado();
                $cargo->save();
            }

            $pago->update([
                'estado' => 'anulado',
                'motivo_anulacion' => $motivo,
            ]);
        });
    }
}
