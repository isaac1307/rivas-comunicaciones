<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreReservacionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nombre_solicitante' => ['required', 'string', 'max:200'],
            'telefono' => ['required', 'string', 'max:15'],
            'direccion' => ['required', 'string', 'max:300'],
            'correo_electronico' => ['nullable', 'email', 'max:150'],
            'tipo_servicio' => ['required', 'in:instalacion_nueva,visita_tecnica,atencion_personalizada'],
            'plan_id' => ['nullable', 'exists:planes,id'],
            'fecha_solicitada' => ['nullable', 'date', 'after_or_equal:today'],
            'observaciones' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Mensajes personalizados de validación en español.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nombre_solicitante.required' => 'El nombre completo del solicitante es obligatorio.',
            'telefono.required' => 'El número de teléfono es obligatorio.',
            'direccion.required' => 'La dirección del servicio es obligatoria.',
            'correo_electronico.email' => 'El correo electrónico debe ser una dirección válida.',
            'tipo_servicio.required' => 'Debe seleccionar un tipo de servicio.',
            'tipo_servicio.in' => 'El tipo de servicio seleccionado no es válido.',
            'plan_id.exists' => 'El plan seleccionado no existe.',
            'fecha_solicitada.after_or_equal' => 'La fecha solicitada debe ser hoy o una fecha futura.',
        ];
    }
}
