<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Cobranza: genera las mensualidades el día 1 de cada mes
|--------------------------------------------------------------------------
| Requiere el cron del sistema: * * * * * php artisan schedule:run
*/
Schedule::command('cobranza:generar-cargos')
    ->monthlyOn(1, '00:10')
    ->withoutOverlapping();
