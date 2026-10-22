<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Credenciales MikroTik RouterOS v7 REST API
    |--------------------------------------------------------------------------
    |
    | Parámetros de conexión para la automatización de cortes y reconexiones
    | mediante la API REST nativa de RouterOS v7.
    |
    */

    'ip' => env('MIKROTIK_IP', '192.168.1.116'),
    'user' => env('MIKROTIK_USER', 'admin'),
    'pass' => env('MIKROTIK_PASS', ''),
    'timeout' => (int) env('MIKROTIK_TIMEOUT', 5),

];
