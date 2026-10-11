<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateReservacionRequest extends FormRequest
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
            'estado' => ['required', 'in:pendiente,programada,completada,cancelada'],
            'fecha_solicitada' => ['nullable', 'date'],
            'plan_id' => ['nullable', 'exists:planes,id'],
            'observaciones' => ['nullable', 'string', 'max:1500'],
        ];
    }

    public function messages(): array
    {
        return [
            'estado.required' => 'El estado de la reservación es obligatorio.',
            'estado.in' => 'El estado seleccionado no es válido.',
            'plan_id.exists' => 'El plan seleccionado no es válido.',
        ];
    }
}
