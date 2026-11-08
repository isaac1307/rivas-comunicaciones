<?php

namespace App\Http\Requests\Admin;

use App\Enums\EstadoTicket;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTicketRequest extends FormRequest
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
            'categoria' => ['required', 'in:falla_servicio,soporte_tecnico,consulta,cambio_plan,cancelacion'],
            'estado' => ['required', Rule::enum(EstadoTicket::class)],
            'observaciones_admin' => ['nullable', 'string', 'max:2000'],
            'fecha_resolucion' => ['nullable', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'categoria.required' => 'La categoría es obligatoria.',
            'estado.required' => 'El estado es obligatorio.',
        ];
    }
}
