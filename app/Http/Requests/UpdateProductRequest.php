<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
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
        $productId = $this->route('product')->id;
        
        return [
            'codigo' => 'required|string|max:255|unique:products,codigo,' . $productId,
            'nombre' => 'required|string|max:255|unique:products,nombre,' . $productId,
            'precio' => 'required|numeric|min:0',
            'existencias' => 'required|integer|min:0',
            'providers' => 'nullable|array',
            'providers.*' => 'exists:providers,id',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'codigo.required' => 'El código del producto es obligatorio.',
            'codigo.string' => 'El código debe ser un texto válido.',
            'codigo.max' => 'El código no puede tener más de 255 caracteres.',
            'codigo.unique' => 'Ya existe un producto con este código.',
            'nombre.required' => 'El nombre del producto es obligatorio.',
            'nombre.string' => 'El nombre debe ser un texto válido.',
            'nombre.max' => 'El nombre no puede tener más de 255 caracteres.',
            'nombre.unique' => 'Ya existe un producto con este nombre.',
            'precio.required' => 'El precio del producto es obligatorio.',
            'precio.numeric' => 'El precio debe ser un número válido.',
            'precio.min' => 'El precio no puede ser menor a 0.',
            'existencias.required' => 'Las existencias del producto son obligatorias.',
            'existencias.integer' => 'Las existencias deben ser un número entero.',
            'existencias.min' => 'Las existencias no pueden ser menores a 0.',
            'providers.array' => 'Los proveedores deben ser un array.',
            'providers.*.exists' => 'Uno o más proveedores seleccionados no existen.',
        ];
    }
}

