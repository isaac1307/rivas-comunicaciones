<?php

namespace App\Http\Requests\Admin;

use App\Enums\EstadoTicket;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTicketRequest extends FormRequest
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
            'cliente_id' => ['required', 'exists:clientes,id'],
            'categoria' => ['required', 'in:falla_servicio,soporte_tecnico,consulta,cambio_plan,cancelacion'],
            'descripcion' => ['required', 'string'],
            'estado' => ['required', Rule::enum(EstadoTicket::class)],
            'observaciones_admin' => ['nullable', 'string', 'max:1500'],
        ];
    }

    public function messages(): array
    {
        return [
            'cliente_id.required' => 'Debe asociar un cliente al ticket.',
            'cliente_id.exists' => 'El cliente seleccionado no existe.',
            'categoria.required' => 'La categoría del ticket es obligatoria.',
            'descripcion.required' => 'La descripción es obligatoria.',
            'estado.required' => 'El estado es obligatorio.',
        ];
    }
}
