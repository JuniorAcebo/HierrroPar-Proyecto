<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTipoUnidadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $tipoUnidadId = $this->route('tipoUnidad')?->id;

        return [
            'nombre'      => [
                'required', 'string', 'max:100',
                Rule::unique('tipo_unidades', 'nombre')->ignore($tipoUnidadId),
            ],
            'descripcion' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre del tipo de unidad es obligatorio.',
            'nombre.unique'   => 'Ya existe otro tipo de unidad con ese nombre.',
            'nombre.max'      => 'El nombre no puede superar los 100 caracteres.',
            'descripcion.max' => 'La descripción no puede superar los 255 caracteres.',
        ];
    }
}