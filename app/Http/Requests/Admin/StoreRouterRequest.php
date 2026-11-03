<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreRouterRequest extends FormRequest
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
            'api_password' => ['required', 'string'],
            'estado' => ['required', 'in:activo,inactivo'],
            'auto_aprovisionar_planes' => ['sometimes', 'boolean'],
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
            'api_password.required' => 'La contraseña del API es obligatoria.',
            'estado.required' => 'El estado es obligatorio.',
        ];
    }
}
