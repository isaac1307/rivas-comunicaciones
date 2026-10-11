<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreTicketPublicRequest extends FormRequest
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
            'cliente_id' => ['required', 'exists:clientes,id'],
            'categoria' => ['required', 'in:falla_servicio,soporte_tecnico,consulta,cambio_plan,cancelacion'],
            'descripcion' => ['required', 'string', 'min:10', 'max:2000'],
        ];
    }

    /**
     * Mensajes personalizados en español.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'cliente_id.required' => 'Debe seleccionar o indicar su cuenta de cliente.',
            'cliente_id.exists' => 'El cliente seleccionado no se encuentra en el sistema.',
            'categoria.required' => 'La categoría del reporte es obligatoria.',
            'categoria.in' => 'La categoría seleccionada no es válida.',
            'descripcion.required' => 'Por favor describa el motivo de su ticket o reporte.',
            'descripcion.min' => 'La descripción debe tener al menos 10 caracteres.',
        ];
    }
}
