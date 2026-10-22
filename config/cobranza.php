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

    /*
    |--------------------------------------------------------------------------
    | Permitir saldo a favor en pagos excedentes
    |--------------------------------------------------------------------------
    */
    'permitir_saldo_a_favor' => (bool) env('COBRANZA_PERMITIR_SALDO_A_FAVOR', false),

    /*
    |--------------------------------------------------------------------------
    | Facturar clientes en estado suspendido
    |--------------------------------------------------------------------------
    */
    'facturar_suspendidos' => (bool) env('COBRANZA_FACTURAR_SUSPENDIDOS', false),

    /*
    |--------------------------------------------------------------------------
    | Generar cargo inicial automático al completar reservación
    |--------------------------------------------------------------------------
    */
    'generar_cargo_al_completar_reservacion' => (bool) env('COBRANZA_CARGO_AL_COMPLETAR_RESERVACION', true),

];
