<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUsuarioRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nombre_usuario' => ['required', 'string', 'max:50', 'alpha_dash', Rule::unique('usuarios', 'nombre_usuario')],
            'nombre_completo' => ['required', 'string', 'max:150'],
            'email' => ['nullable', 'email', 'max:150', Rule::unique('usuarios', 'email')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'rol' => ['required', Rule::in(array_keys(User::ROLES))],
            'estado' => ['required', Rule::in(['activo', 'inactivo'])],
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
            'nombre_usuario.required' => 'El nombre de usuario es obligatorio.',
            'nombre_usuario.alpha_dash' => 'El nombre de usuario solo puede contener letras, números, guiones y guiones bajos.',
            'nombre_usuario.unique' => 'Ese nombre de usuario ya está registrado.',
            'nombre_completo.required' => 'El nombre completo es obligatorio.',
            'email.email' => 'El correo electrónico debe tener un formato válido.',
            'email.unique' => 'Ese correo electrónico ya está registrado por otro usuario.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'La confirmación de la contraseña no coincide.',
            'rol.required' => 'Debe asignar un rol al usuario.',
            'rol.in' => 'El rol seleccionado no es válido.',
            'estado.required' => 'El estado de la cuenta es obligatorio.',
        ];
    }
}
