<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
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
        return [
            'codigo' => 'required|string|regex:/^[0-9]+$/|max:255|unique:products,codigo',
            'nombre' => 'required|string|max:255|unique:products,nombre',
            'precio' => 'required|numeric|gt:0',
            'existencias' => 'required|integer|gt:0',
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
            'codigo.regex' => 'El código solo puede contener números.',
            'codigo.max' => 'El código no puede tener más de 255 caracteres.',
            'codigo.unique' => 'Ya existe un producto con este código.',
            'nombre.required' => 'El nombre del producto es obligatorio.',
            'nombre.string' => 'El nombre debe ser un texto válido.',
            'nombre.max' => 'El nombre no puede tener más de 255 caracteres.',
            'nombre.unique' => 'Ya existe un producto con este nombre.',
            'precio.required' => 'El precio del producto es obligatorio.',
            'precio.numeric' => 'El precio debe ser un número válido.',
            'precio.gt' => 'El precio debe ser mayor a 0.',
            'existencias.required' => 'Las existencias del producto son obligatorias.',
            'existencias.integer' => 'Las existencias deben ser un número entero.',
            'existencias.gt' => 'Las existencias deben ser mayores a 0 al crear un producto nuevo.',
            'providers.array' => 'Los proveedores deben ser un array.',
            'providers.*.exists' => 'Uno o más proveedores seleccionados no existen.',
        ];
    }
}

