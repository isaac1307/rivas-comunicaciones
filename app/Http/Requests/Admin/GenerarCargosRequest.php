<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class GenerarCargosRequest extends FormRequest
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
            'periodo' => ['nullable', 'regex:/^\d{4}-(0[1-9]|1[0-2])$/'],
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
            'periodo.regex' => 'El formato del periodo debe ser YYYY-MM (ej. 2026-10).',
        ];
    }
}
