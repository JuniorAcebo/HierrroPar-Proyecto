<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAjusteStockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'cantidad' => $this->cantidad !== null ? floatval($this->cantidad) : null,
            'motivo'   => $this->motivo ? trim($this->motivo) : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'producto_id' => 'required|exists:productos,id',
            'almacen_id'  => 'required|exists:almacenes,id',
            'cantidad'    => 'required|numeric|min:0',
            'tipo_ajuste' => 'required|in:sumar,restar,fijar',
            'motivo'      => 'nullable|string|max:255',
        ];
    }

    public function attributes(): array
    {
        return [
            'producto_id' => 'producto',
            'almacen_id'  => 'almacén',
            'cantidad'    => 'cantidad',
            'tipo_ajuste' => 'tipo de ajuste',
            'motivo'      => 'motivo del ajuste',
        ];
    }

    public function messages(): array
    {
        return [
            'producto_id.required' => 'Debes seleccionar un producto.',
            'producto_id.exists'   => 'El producto seleccionado no es válido.',
            
            'almacen_id.required'  => 'Debes seleccionar un almacén.',
            'almacen_id.exists'    => 'El almacén seleccionado no es válido.',

            'cantidad.required'    => 'Debes indicar la cantidad a ajustar.',
            'cantidad.numeric'     => 'La cantidad debe ser un número.',
            'cantidad.min'         => 'La cantidad no puede ser negativa.',

            'tipo_ajuste.required' => 'Debes indicar el tipo de ajuste.',
            'tipo_ajuste.in'       => 'El tipo de ajuste debe ser sumar, restar o fijar.',

            'motivo.string'        => 'El motivo debe ser texto.',
            'motivo.max'           => 'El motivo no puede exceder 255 caracteres.',
        ];
    }
}