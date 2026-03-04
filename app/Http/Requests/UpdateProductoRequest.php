<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $producto = $this->route('producto');

        return [
            'nombre' => [
                'required',
                'max:80',
                Rule::unique('productos', 'nombre')->ignore($producto->id),
            ],

            'descripcion' => 'nullable|string|max:255',

            'precio_compra' => 'required|numeric|min:0',
            'precio_venta'  => 'required|numeric|min:0|gte:precio_compra',

            'stock_minimo' => 'nullable|integer|min:0',
            'stock_maximo' => 'nullable|integer|min:0|gte:stock_minimo',

            'marca_id'       => 'required|integer|exists:marcas,id',
            'tipo_unidad_id' => 'required|integer|exists:tipo_unidades,id',
            'categoria_id'   => 'required|integer|exists:categorias,id',
        ];
    }

    public function attributes(): array
    {
        return [
            'nombre'          => 'nombre del producto',
            'descripcion'     => 'descripción',
            'precio_compra'   => 'precio de compra',
            'precio_venta'    => 'precio de venta',
            'stock_minimo'    => 'stock mínimo',
            'stock_maximo'    => 'stock máximo',
            'marca_id'        => 'marca',
            'tipo_unidad_id'  => 'tipo de unidad',
            'categoria_id'    => 'categoría',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre del producto es obligatorio.',
            'nombre.unique'   => 'Ya existe un producto con este nombre.',

            'precio_compra.required' => 'El precio de compra es obligatorio.',
            'precio_compra.min'      => 'El precio de compra no puede ser negativo.',

            'precio_venta.required'  => 'El precio de venta es obligatorio.',
            'precio_venta.min'       => 'El precio de venta no puede ser negativo.',
            'precio_venta.gte'       => 'El precio de venta debe ser mayor o igual al precio de compra.',

            'stock_minimo.min' => 'El stock mínimo no puede ser negativo.',
            'stock_maximo.min' => 'El stock máximo no puede ser negativo.',
            'stock_maximo.gte' => 'El stock máximo debe ser mayor o igual al stock mínimo.',

            'marca_id.required'       => 'Debe seleccionar una marca.',
            'tipo_unidad_id.required' => 'Debe seleccionar un tipo de unidad.',
            'categoria_id.required'   => 'Debe seleccionar una categoría.',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (
                $this->precio_compra !== null &&
                $this->precio_venta !== null &&
                $this->precio_venta < $this->precio_compra
            ) {
                $validator->errors()->add(
                    'precio_venta',
                    'El precio de venta no puede ser menor al precio de compra.'
                );
            }
        });
    }
}