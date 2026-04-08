<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMarcaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $marcaId = $this->route('marca')?->id;

        return [
            'nombre'      => [
                'required', 'string', 'max:100',
                Rule::unique('marcas', 'nombre')->ignore($marcaId),
            ],
            'descripcion' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre de la marca es obligatorio.',
            'nombre.unique'   => 'Ya existe otra marca con ese nombre.',
            'nombre.max'      => 'El nombre no puede superar los 100 caracteres.',
            'descripcion.max' => 'La descripción no puede superar los 255 caracteres.',
        ];
    }
}