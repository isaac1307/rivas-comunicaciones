<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class AnularPagoRequest extends FormRequest
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
            'motivo_anulacion' => ['required', 'string', 'min:5', 'max:300'],
        ];
    }

    /**
     * Mensajes personalizados de error.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'motivo_anulacion.required' => 'Debe especificar el motivo por el cual se anula el pago.',
            'motivo_anulacion.min' => 'El motivo debe tener al menos 5 caracteres.',
            'motivo_anulacion.max' => 'El motivo no puede exceder 300 caracteres.',
        ];
    }
}
