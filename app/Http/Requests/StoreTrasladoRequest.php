<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTrasladoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'origen_almacen_id' => 'required|exists:almacenes,id|different:destino_almacen_id',
            'destino_almacen_id' => 'required|exists:almacenes,id',

            'fecha_hora' => 'required|date',

            'costo_envio' => 'required|numeric|min:0',

            // productos
            'arrayidproducto' => 'required|array|min:1',
            'arrayidproducto.*' => 'exists:productos,id',

            // cantidades
            'arraycantidad' => 'required|array|min:1',
            'arraycantidad.*' => 'numeric|min:0.0001',
        ];
    }

    public function messages(): array
    {
        return [

            'fecha_hora.required' => 'La fecha y hora es requerida',

            'origen_almacen_id.required' => 'Debe seleccionar un almacén origen',
            'origen_almacen_id.exists' => 'El almacén origen seleccionado no existe',
            'origen_almacen_id.different' => 'El almacén de origen y destino no pueden ser iguales',

            'destino_almacen_id.required' => 'Debe seleccionar un almacén destino',
            'destino_almacen_id.exists' => 'El almacén destino seleccionado no existe',

            'costo_envio.required' => 'El costo de envío es requerido',
            'costo_envio.numeric' => 'El costo de envío debe ser un número',
            'costo_envio.min' => 'El costo de envío no puede ser negativo',

            // productos
            'arrayidproducto.required' => 'Debe agregar al menos un producto al traslado.',
            'arrayidproducto.min' => 'Debe agregar al menos un producto al traslado.',
            'arrayidproducto.*.exists' => 'Uno de los productos seleccionados no existe',

            // cantidades
            'arraycantidad.required' => 'Las cantidades son requeridas',
            'arraycantidad.*.numeric' => 'Las cantidades deben ser números',
            'arraycantidad.*.min' => 'La cantidad debe ser mayor a 0',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'arraycantidad' => array_map('floatval', $this->arraycantidad ?? []),
        ]);
    }
}