<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCargoRequest extends FormRequest
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
            'tipo' => ['required', Rule::in(['mensualidad', 'instalacion', 'reconexion', 'otro'])],
            'periodo' => ['required', 'regex:/^\d{4}-(0[1-9]|1[0-2])$/'],
            'concepto' => ['required', 'string', 'max:200'],
            'monto' => ['required', 'numeric', 'min:0.01'],
            'fecha_vencimiento' => ['required', 'date'],
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
            'tipo.required' => 'El tipo de cargo es obligatorio.',
            'tipo.in' => 'El tipo de cargo seleccionado no es válido.',
            'periodo.required' => 'El periodo es obligatorio.',
            'periodo.regex' => 'El formato del periodo debe ser YYYY-MM (ej. 2026-10).',
            'concepto.required' => 'El concepto del cargo es obligatorio.',
            'monto.required' => 'El monto es obligatorio.',
            'monto.numeric' => 'El monto debe ser un valor numérico.',
            'monto.min' => 'El monto debe ser mayor a cero.',
            'fecha_vencimiento.required' => 'La fecha de vencimiento es obligatoria.',
            'fecha_vencimiento.date' => 'La fecha de vencimiento no es válida.',
        ];
    }
}
