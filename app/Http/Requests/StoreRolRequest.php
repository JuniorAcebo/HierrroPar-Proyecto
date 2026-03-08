<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRolRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:30|unique:roles,name',
            'permisos' => 'required|array|min:1',
            'permisos.*' => 'exists:permisos,id'
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del rol es obligatorio.',
            'name.unique' => 'Este rol ya existe.',
            'name.max' => 'El nombre del rol no debe superar los 30 caracteres.',

            'permisos.required' => 'Debe seleccionar al menos un permiso.',
            'permisos.array' => 'Los permisos deben enviarse como lista.',
            'permisos.min' => 'Debe seleccionar al menos un permiso.',
            'permisos.*.exists' => 'Uno de los permisos seleccionados no existe.'
        ];
    }
}