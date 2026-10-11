<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreContactoRequest extends FormRequest
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
            'nombre' => ['required', 'string', 'max:150'],
            'correo_electronico' => ['required', 'email', 'max:150'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'asunto' => ['required', 'string', 'max:200'],
            'mensaje' => ['required', 'string', 'min:10', 'max:3000'],
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
            'nombre.required' => 'El nombre es obligatorio.',
            'correo_electronico.required' => 'El correo electrónico es obligatorio.',
            'correo_electronico.email' => 'El correo electrónico no tiene un formato válido.',
            'asunto.required' => 'El asunto es obligatorio.',
            'mensaje.required' => 'El mensaje no puede estar vacío.',
            'mensaje.min' => 'El mensaje debe tener al menos 10 caracteres.',
        ];
    }
}
