<?php

namespace App\Traits;

use App\Models\Bitacora;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

trait Auditable
{
    /**
     * Boot the auditable trait for a model.
     */
    public static function bootAuditable(): void
    {
        static::created(function (Model $model) {
            static::registrarBitacora($model, 'creacion', 'Creación de registro en '.$model->getTable().' #'.$model->getKey());
        });

        static::updated(function (Model $model) {
            static::registrarBitacora($model, 'actualizacion', 'Actualización de registro en '.$model->getTable().' #'.$model->getKey());
        });

        static::deleted(function (Model $model) {
            static::registrarBitacora($model, 'eliminacion', 'Eliminación de registro en '.$model->getTable().' #'.$model->getKey());
        });
    }

    /**
     * Registra un evento en la bitácora del sistema.
     */
    protected static function registrarBitacora(Model $model, string $accion, string $descripcion): void
    {
        try {
            Bitacora::create([
                'usuario_id' => Auth::id(),
                'accion' => $accion,
                'descripcion' => $descripcion,
                'tabla_afectada' => $model->getTable(),
                'registro_id' => $model->getKey(),
                'ip_origen' => request()->ip(),
            ]);
        } catch (\Throwable $e) {
            // Evitar interrumpir la operación principal si falla el log de auditoría
        }
    }
}
