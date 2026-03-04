<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'password_confirmation' => 'required',
            'role' => 'required|exists:roles,name',
            'almacen_id' => 'required_if:role,GERENTE,VENDEDOR|nullable|exists:almacenes,id',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es requerido',
            'email.required' => 'El email es requerido',
            'email.unique' => 'El email ya está registrado',
            'password.required' => 'La contraseña es requerida',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres',
            'password.confirmed' => 'Las contraseñas no coinciden',
            'password_confirmation.required' => 'La confirmación de contraseña es requerida',
            'role.required' => 'El rol es requerido',
            'almacen_id.required_if' => 'El almacén es obligatorio para Gerentes y Vendedores',
            'almacen_id.exists' => 'El almacén seleccionado no existe',
        ];
    }

    public function validated($key = null, $default = null)
    {
        $validated = parent::validated($key, $default);
        // Remover password_confirmation y role del resultado validado
        unset($validated['password_confirmation']);
        unset($validated['role']);
        return $validated;
    }
}
