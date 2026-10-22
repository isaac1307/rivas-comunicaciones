<?php

namespace App\Http\Requests;

use App\Models\Cliente;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

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
            'telefono' => ['nullable', 'string', 'max:20'],
            'cliente_id' => ['nullable', 'exists:clientes,id'],
            'categoria' => ['required', 'in:falla_servicio,soporte_tecnico,consulta,cambio_plan,cancelacion'],
            'descripcion' => ['required', 'string', 'min:10', 'max:2000'],
        ];
    }

    /**
     * Validación posterior para verificar teléfono registrado cuando no se envía cliente_id.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $clienteId = $this->input('cliente_id');
            $telefono = trim((string) $this->input('telefono', ''));

            if (! empty($clienteId)) {
                return;
            }

            if ($telefono === '') {
                $validator->errors()->add('telefono', 'Debe ingresar el número telefónico asociado a su cuenta.');

                return;
            }

            $cliente = Cliente::where('telefono', $telefono)
                ->whereIn('estado', ['activo', 'suspendido'])
                ->first();

            if (! $cliente) {
                $validator->errors()->add(
                    'telefono',
                    'No encontramos una cuenta activa asociada a este número telefónico. Verifica tus datos o contáctanos por WhatsApp/teléfono.'
                );
            }
        });
    }

    /**
     * Mensajes personalizados en español.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'cliente_id.exists' => 'El cliente indicado no se encuentra en el sistema.',
            'categoria.required' => 'La categoría del reporte es obligatoria.',
            'categoria.in' => 'La categoría seleccionada no es válida.',
            'descripcion.required' => 'Por favor describa el motivo de su ticket o reporte.',
            'descripcion.min' => 'La descripción debe tener al menos 10 caracteres.',
        ];
    }
}
