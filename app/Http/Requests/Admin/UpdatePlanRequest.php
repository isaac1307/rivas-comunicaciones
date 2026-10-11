<?php

namespace App\Http\Requests\Admin;

use App\Models\Plan;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePlanRequest extends FormRequest
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
        $plan = $this->route('plan');
        $planId = $plan instanceof Plan ? $plan->id : (is_numeric($plan) ? (int) $plan : 0);

        return [
            'nombre' => ['required', 'string', 'max:100', Rule::unique('planes', 'nombre')->ignore($planId)],
            'velocidad_descarga' => ['required', 'integer', 'min:1'],
            'velocidad_carga' => ['required', 'integer', 'min:1'],
            'precio_mensual' => ['required', 'numeric', 'min:0'],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'estado' => ['required', 'in:activo,inactivo'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre del plan es obligatorio.',
            'nombre.unique' => 'Ya existe un plan registrado con ese nombre.',
            'velocidad_descarga.required' => 'La velocidad de descarga es obligatoria.',
            'velocidad_carga.required' => 'La velocidad de carga es obligatoria.',
            'precio_mensual.required' => 'El precio mensual es obligatorio.',
            'estado.required' => 'El estado es obligatorio.',
        ];
    }
}
