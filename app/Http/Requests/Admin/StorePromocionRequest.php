<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePromocionRequest extends FormRequest
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
            'titulo' => ['required', 'string', 'max:200'],
            'descripcion' => ['required', 'string'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_vencimiento' => ['required', 'date', 'after_or_equal:fecha_inicio'],
            'estado' => ['required', 'in:activa,inactiva'],
            'planes' => ['nullable', 'array'],
            'planes.*' => ['exists:planes,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'titulo.required' => 'El título de la promoción es obligatorio.',
            'descripcion.required' => 'La descripción es obligatoria.',
            'fecha_inicio.required' => 'La fecha de inicio es obligatoria.',
            'fecha_vencimiento.required' => 'La fecha de vencimiento es obligatoria.',
            'fecha_vencimiento.after_or_equal' => 'La fecha de vencimiento debe ser posterior o igual a la fecha de inicio.',
            'estado.required' => 'El estado es obligatorio.',
        ];
    }
}
