<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Día de vencimiento de la mensualidad
    |--------------------------------------------------------------------------
    |
    | Día del mes en que vence cada mensualidad generada. Pasada esta fecha,
    | los cargos con saldo se consideran vencidos y el cliente aparece como moroso.
    |
    */

    'dia_vencimiento' => (int) env('COBRANZA_DIA_VENCIMIENTO', 10),

];
