<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'codigo' => $this->codigo ? strtoupper(trim($this->codigo)) : null,
            'nombre' => $this->nombre ? trim($this->nombre) : null,
            'descripcion' => $this->descripcion ? trim($this->descripcion) : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'codigo' => [
                'required',
                'string',
                'max:50',
                'unique:productos,codigo'
            ],

            'nombre' => [
                'required',
                'string',
                'max:80',
                Rule::unique('productos')
                    ->where(fn ($query) => $query
                        ->where('marca_id', $this->marca_id)
                        ->where('categoria_id', $this->categoria_id)
                        ->where('tipo_unidad_id', $this->tipo_unidad_id)
                    )
            ],
            
            'descripcion' => 'nullable|string|max:255',

            'precio_compra' => 'required|numeric|min:0',
            'precio_venta' => 'required|numeric|min:0|gte:precio_compra',

            'marca_id' => 'required|exists:marcas,id',
            'tipo_unidad_id' => 'required|exists:tipo_unidades,id',
            'categoria_id' => 'required|exists:categorias,id',

            'stock_minimo' => 'nullable|integer|min:0',
            'stock_maximo' => 'nullable|integer|min:0|gte:stock_minimo',
        ];
    }

    public function attributes(): array
    {
        return [
            'codigo' => 'código del producto',
            'nombre' => 'nombre del producto',
            'descripcion' => 'descripción',
            'precio_compra' => 'precio de compra',
            'precio_venta' => 'precio de venta',
            'marca_id' => 'marca',
            'tipo_unidad_id' => 'tipo de unidad',
            'categoria_id' => 'categoría',
            'stock_minimo' => 'stock mínimo',
            'stock_maximo' => 'stock máximo',
        ];
    }

    public function messages(): array
    {
        return [
            'codigo.required' => 'Debes ingresar un código para el producto.',
            'codigo.unique' => 'Este código ya existe. Debe ser único.',

            'nombre.required' => 'Debes ingresar el nombre del producto.',

            'precio_compra.required' => 'Debes indicar el precio de compra.',
            'precio_compra.numeric' => 'El precio de compra debe ser un número.',
            'precio_compra.min' => 'El precio de compra no puede ser negativo.',

            'precio_venta.required' => 'Debes indicar el precio de venta.',
            'precio_venta.numeric' => 'El precio de venta debe ser un número.',
            'precio_venta.min' => 'El precio de venta no puede ser negativo.',
            'precio_venta.gte' => 'El precio de venta debe ser mayor o igual al precio de compra.',

            'marca_id.required' => 'Debes seleccionar una marca.',
            'marca_id.exists' => 'La marca seleccionada no es válida.',

            'tipo_unidad_id.required' => 'Debes seleccionar un tipo de unidad.',
            'tipo_unidad_id.exists' => 'El tipo de unidad seleccionado no es válido.',

            'categoria_id.required' => 'Debes seleccionar una categoría.',
            'categoria_id.exists' => 'La categoría seleccionada no es válida.',
        ];
    }
}