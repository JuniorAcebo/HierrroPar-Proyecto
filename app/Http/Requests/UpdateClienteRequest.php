<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateClienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'nombre_completo'  => $this->nombre_completo  ? trim($this->nombre_completo)                : null,
            'direccion'        => $this->direccion         ? trim($this->direccion)                      : null,
            'numero_documento' => $this->numero_documento  ? strtoupper(trim($this->numero_documento))   : null,
        ]);
    }

    public function rules(): array
    {
        $personaId = $this->route('cliente')->persona_id;

        return [
            'nombre_completo'  => ['required', 'string', 'max:255'],
            'tipo_persona'     => ['required', 'in:natural,juridica'],
            'telefono'         => ['nullable', 'string', 'max:20'],
            'direccion'        => ['nullable', 'string', 'max:255'],
            'documento_id'     => ['required', 'exists:documentos,id'],
            'numero_documento' => [
                'required',
                'string',
                'max:50',
                Rule::unique('personas', 'numero_documento')
                    ->ignore($personaId)
                    ->where(fn ($query) => $query->where('documento_id', $this->documento_id))
            ],
            'grupo_cliente_id' => ['required', 'exists:grupos_clientes,id'],
        ];
    }

    public function attributes(): array
    {
        return [
            'nombre_completo'  => 'nombre completo',
            'tipo_persona'     => 'tipo de persona',
            'telefono'         => 'teléfono',
            'direccion'        => 'dirección',
            'documento_id'     => 'tipo de documento',
            'numero_documento' => 'número de documento',
            'grupo_cliente_id' => 'grupo de cliente',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre_completo.required'  => 'El nombre completo es obligatorio.',
            'nombre_completo.max'       => 'El nombre no puede superar los 255 caracteres.',

            'tipo_persona.required'     => 'El tipo de persona es obligatorio.',
            'tipo_persona.in'           => 'El tipo de persona debe ser Natural o Jurídica.',

            'documento_id.required'     => 'Debe seleccionar un tipo de documento.',
            'documento_id.exists'       => 'El tipo de documento seleccionado no es válido.',

            'numero_documento.required' => 'El número de documento es obligatorio.',
            'numero_documento.unique'   => 'Este número de documento ya está registrado para ese tipo de documento.',
            'numero_documento.max'      => 'El número de documento no puede superar los 50 caracteres.',

            'grupo_cliente_id.required' => 'Debe seleccionar un grupo de cliente.',
            'grupo_cliente_id.exists'   => 'El grupo seleccionado no es válido.',
        ];
    }
}