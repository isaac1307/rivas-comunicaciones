<?php

namespace App\Http\Requests\Admin;

use App\Models\Pago;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePagoRequest extends FormRequest
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
            'monto' => ['required', 'numeric', 'min:0.01'],
            'metodo_pago' => ['required', Rule::in(array_keys(Pago::METODOS))],
            'referencia' => ['nullable', 'string', 'max:100'],
            'fecha_pago' => ['nullable', 'date'],
            'notas' => ['nullable', 'string', 'max:1000'],
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
            'monto.required' => 'El monto del pago es obligatorio.',
            'monto.numeric' => 'El monto debe ser un valor numérico.',
            'monto.min' => 'El monto a pagar debe ser mayor a cero.',
            'metodo_pago.required' => 'Debe seleccionar un método de pago.',
            'metodo_pago.in' => 'El método de pago seleccionado no es válido.',
            'fecha_pago.date' => 'La fecha de pago debe ser una fecha válida.',
        ];
    }
}
