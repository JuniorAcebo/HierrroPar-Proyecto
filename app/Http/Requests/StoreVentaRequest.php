<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVentaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fecha_hora' => 'required|date|before_or_equal:now',
            'numero_comprobante' => 'nullable|string|max:255|unique:ventas,numero_comprobante',
            'total' => 'required|numeric|min:0.01',
            'cliente_id' => 'required|exists:clientes,id',
            'estado_comprobante' => 'required|in:boleta,factura',
            'almacen_id' => 'required|exists:almacenes,id',
            
            // Arrays de productos con validación de tamaño coincidente
            'arrayidproducto' => 'required|array|min:1',
            'arrayidproducto.*' => 'required|integer|exists:productos,id',
            
            'arraycantidad' => 'required|array|size:' . count($this->arrayidproducto ?? []),
            'arraycantidad.*' => 'required|numeric|min:1|max:999999', // Cambiado a min:1 por sugerencia técnica
            
            'arrayprecioventa' => 'required|array|size:' . count($this->arrayidproducto ?? []),
            'arrayprecioventa.*' => 'required|numeric|min:0.01|max:999999',
            
            'arraydescuento' => 'nullable|array|size:' . count($this->arrayidproducto ?? []),
            'arraydescuento.*' => 'nullable|numeric|min:0|max:999999',
        ];
    }

    public function messages(): array
    {
        return [
            'fecha_hora.required' => 'La fecha y hora son obligatorias',
            'numero_comprobante.unique' => 'Este número de comprobante ya existe',
            'total.min' => 'El total debe ser mayor a 0',
            'cliente_id.required' => 'Debe seleccionar un cliente',
            'estado_comprobante.required' => 'Debe seleccionar un tipo de comprobante',
            'arrayidproducto.required' => 'Debe agregar al menos un producto a la venta',
        ];
    }

    protected function prepareForValidation()
    {
        $rawIds = $this->arrayidproducto ?? [];
        $rawCants = $this->arraycantidad ?? [];
        $rawPrecios = $this->arrayprecioventa ?? [];
        $rawDescs = $this->arraydescuento ?? [];

        $sanitizedIds = [];
        $sanitizedCants = [];
        $sanitizedPrecios = [];
        $sanitizedDescs = [];

        foreach ($rawIds as $key => $val) {
            if (empty($val)) continue;
            $sanitizedIds[] = (int)$val;
            $sanitizedCants[] = floatval($rawCants[$key] ?? 0);
            $sanitizedPrecios[] = floatval($rawPrecios[$key] ?? 0);
            $sanitizedDescs[] = floatval($rawDescs[$key] ?? 0);
        }

        $this->merge([
            'arrayidproducto' => $sanitizedIds,
            'arraycantidad' => $sanitizedCants,
            'arrayprecioventa' => $sanitizedPrecios,
            'arraydescuento' => $sanitizedDescs,
            'total' => floatval($this->total ?? 0)
        ]);
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->arrayidproducto) {
                // Validación 1: Productos duplicados
                $duplicates = array_diff_assoc($this->arrayidproducto, array_unique($this->arrayidproducto));
                if (!empty($duplicates)) {
                    foreach ($duplicates as $index => $pid) {
                        $validator->errors()->add("arrayidproducto.{$index}", "No puede añadir el mismo producto varias veces.");
                    }
                }

                // Validación 2: El descuento no sea mayor al subtotal
                foreach ($this->arrayidproducto as $index => $productoId) {
                    $cantidad = $this->arraycantidad[$index] ?? 0;
                    $precio = $this->arrayprecioventa[$index] ?? 0;
                    $descuento = $this->arraydescuento[$index] ?? 0;
                    
                    $subtotal = $cantidad * $precio;
                    
                    if ($descuento > $subtotal) {
                        $validator->errors()->add(
                            "arraydescuento.{$index}", 
                            "El descuento no puede ser mayor al subtotal del producto"
                        );
                    }
                }
            }
        });
    }
}
