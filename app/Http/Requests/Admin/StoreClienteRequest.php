<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreClienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nombre_completo' => ['required', 'string', 'max:200'],
            'direccion' => ['required', 'string', 'max:300'],
            'telefono' => ['required', 'string', 'max:15'],
            'correo_electronico' => ['nullable', 'email', 'max:150'],
            'plan_id' => ['nullable', 'exists:planes,id'],
            'fecha_alta' => ['required', 'date'],
            'estado' => ['required', 'in:activo,inactivo'],
            'notas' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre_completo.required' => 'El nombre completo del cliente es obligatorio.',
            'direccion.required' => 'La dirección es obligatoria.',
            'telefono.required' => 'El teléfono es obligatorio.',
            'correo_electronico.email' => 'El correo electrónico debe tener un formato válido.',
            'plan_id.exists' => 'El plan seleccionado no es válido.',
            'fecha_alta.required' => 'La fecha de alta es obligatoria.',
            'estado.required' => 'El estado es obligatorio.',
        ];
    }
}
