<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRouterRequest extends FormRequest
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
            'nombre' => ['required', 'string', 'max:100'],
            'ip_address' => ['required', 'string', 'max:45'],
            'api_user' => ['required', 'string', 'max:50'],
            'api_password' => ['nullable', 'string'],
            'usar_tls' => ['sometimes', 'boolean'],
            'verificar_certificado' => ['sometimes', 'boolean'],
            'estado' => ['required', 'in:activo,inactivo'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre del nodo/router es obligatorio.',
            'ip_address.required' => 'La dirección IP o host del router es obligatoria.',
            'api_user.required' => 'El usuario del API de RouterOS es obligatorio.',
            'estado.required' => 'El estado es obligatorio.',
        ];
    }
}
