<?php

namespace App\Traits;

use App\Models\Bitacora;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

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
            $valoresNuevos = static::filtrarAtributosAudit($model->getAttributes());

            static::registrarBitacora($model, 'creacion', Str::limit($desc, 290), null, $valoresNuevos);
        });

        static::updated(function (Model $model) {
            $cambiosNuevos = static::filtrarAtributosAudit($model->getDirty());

            if (empty($cambiosNuevos)) {
                return;
            }

            $cambiosAnteriores = static::filtrarAtributosAudit(
                array_intersect_key($model->getOriginal(), $cambiosNuevos)
            );

            $identificador = static::obtenerIdentificadorAudit($model);
            $tabla = $model->getTable();
            $campos = implode(', ', array_keys($cambiosNuevos));
            $desc = "Actualización en {$tabla} #{$model->getKey()}".($identificador ? " ({$identificador})" : '').": modificó [{$campos}]";

            static::registrarBitacora($model, 'actualizacion', Str::limit($desc, 290), $cambiosAnteriores, $cambiosNuevos);
        });

        static::deleted(function (Model $model) {
            $identificador = static::obtenerIdentificadorAudit($model);
            $tabla = $model->getTable();
            $desc = "Baja en {$tabla} #{$model->getKey()}".($identificador ? " ({$identificador})" : '');
            $valoresAnteriores = static::filtrarAtributosAudit($model->getOriginal());

            static::registrarBitacora($model, 'eliminacion', Str::limit($desc, 290), $valoresAnteriores, null);
        });
    }

    /**
     * Filtra atributos sensibles o irrelevantes para la auditoría.
     *
     * @param  array<string, mixed>  $atributos
     * @return array<string, mixed>
     */
    protected static function filtrarAtributosAudit(array $atributos): array
    {
        $ignorar = [
            'password',
            'api_password',
            'remember_token',
            'ultimo_acceso',
            'two_factor_recovery_codes',
            'two_factor_secret',
            'two_factor_confirmed_at',
            'updated_at',
        ];

        return array_diff_key($atributos, array_flip($ignorar));
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
     *
     * @param  array<string, mixed>|null  $valoresAnteriores
     * @param  array<string, mixed>|null  $valoresNuevos
     */
    protected static function registrarBitacora(
        Model $model,
        string $accion,
        string $descripcion,
        ?array $valoresAnteriores = null,
        ?array $valoresNuevos = null
    ): void {
        try {
            $ip = request()->ip();
            if (empty($ip) && app()->runningInConsole()) {
                $ip = 'CLI';
            }

            Bitacora::create([
                'usuario_id' => Auth::id(),
                'accion' => $accion,
                'descripcion' => $descripcion,
                'tabla_afectada' => $model->getTable(),
                'registro_id' => $model->getKey(),
                'valores_anteriores' => $valoresAnteriores,
                'valores_nuevos' => $valoresNuevos,
                'ip_origen' => $ip,
            ]);
        } catch (Throwable $e) {
            Log::critical('Fallo al registrar evento en la bitácora de auditoría.', [
                'accion' => $accion,
                'tabla' => $model->getTable(),
                'registro_id' => $model->getKey(),
                'error' => $e->getMessage(),
            ]);
        }
    }
}
