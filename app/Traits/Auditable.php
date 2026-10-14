<?php

namespace App\Traits;

use App\Models\Bitacora;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

trait Auditable
{
    /**
     * Boot the auditable trait for a model.
     */
    public static function bootAuditable(): void
    {
        static::created(function (Model $model) {
            $identificador = static::obtenerIdentificadorAudit($model);
            $tabla = $model->getTable();
            $desc = "Alta en {$tabla} #{$model->getKey()}".($identificador ? " ({$identificador})" : '');
            static::registrarBitacora($model, 'creacion', Str::limit($desc, 290));
        });

        static::updated(function (Model $model) {
            $ignorar = ['updated_at', 'remember_token', 'ultimo_acceso', 'two_factor_recovery_codes', 'two_factor_secret'];
            $cambios = array_diff_key($model->getDirty(), array_flip($ignorar));

            if (empty($cambios)) {
                return;
            }

            $identificador = static::obtenerIdentificadorAudit($model);
            $tabla = $model->getTable();
            $campos = implode(', ', array_keys($cambios));
            $desc = "Actualización en {$tabla} #{$model->getKey()}".($identificador ? " ({$identificador})" : '').": modificó [{$campos}]";
            static::registrarBitacora($model, 'actualizacion', Str::limit($desc, 290));
        });

        static::deleted(function (Model $model) {
            $identificador = static::obtenerIdentificadorAudit($model);
            $tabla = $model->getTable();
            $desc = "Baja en {$tabla} #{$model->getKey()}".($identificador ? " ({$identificador})" : '');
            static::registrarBitacora($model, 'eliminacion', Str::limit($desc, 290));
        });
    }

    /**
     * Obtiene una etiqueta descriptiva legible del modelo (nombre, folio, concepto, etc.).
     */
    protected static function obtenerIdentificadorAudit(Model $model): ?string
    {
        if (isset($model->folio)) {
            return (string) $model->folio;
        }

        if (isset($model->nombre_completo)) {
            return (string) $model->nombre_completo;
        }

        if (isset($model->nombre)) {
            return (string) $model->nombre;
        }

        if (isset($model->concepto)) {
            return (string) $model->concepto;
        }

        if (isset($model->titulo)) {
            return (string) $model->titulo;
        }

        return null;
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
