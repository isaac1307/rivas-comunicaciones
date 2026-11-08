<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;

trait GeneraFolioUnico
{
    /**
     * Genera un folio único en formato PREFIJO-YYYYMMDD-XXXX (4 letras mayúsculas aleatorias).
     *
     * @param  class-string<Model>  $modelClass
     */
    protected function generarFolioParaModelo(string $prefijo, string $modelClass): string
    {
        $fecha = now()->format('Ymd');
        $alfabeto = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $maxIndice = strlen($alfabeto) - 1;

        do {
            $sufijo = '';
            for ($i = 0; $i < 4; $i++) {
                $sufijo .= $alfabeto[random_int(0, $maxIndice)];
            }
            $folio = "{$prefijo}-{$fecha}-{$sufijo}";
        } while ($modelClass::query()->where('folio', $folio)->exists());

        return $folio;
    }
}
