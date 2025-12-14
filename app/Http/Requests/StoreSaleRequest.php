<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSaleRequest extends FormRequest
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
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.price' => 'required|numeric|min:0',
            'products.*.subtotal' => 'required|numeric|min:0',
            'total' => 'required|numeric|min:0',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'products.required' => 'La venta debe tener al menos un producto.',
            'products.min' => 'La venta debe tener al menos un producto.',
            'products.*.product_id.required' => 'El ID del producto es requerido.',
            'products.*.product_id.exists' => 'El producto seleccionado no existe.',
            'products.*.quantity.required' => 'La cantidad es requerida.',
            'products.*.quantity.integer' => 'La cantidad debe ser un número entero.',
            'products.*.quantity.min' => 'La cantidad debe ser mayor a 0.',
            'products.*.price.required' => 'El precio es requerido.',
            'products.*.price.numeric' => 'El precio debe ser un número válido.',
            'products.*.price.min' => 'El precio no puede ser negativo.',
            'products.*.subtotal.required' => 'El subtotal es requerido.',
            'products.*.subtotal.numeric' => 'El subtotal debe ser un número válido.',
            'products.*.subtotal.min' => 'El subtotal no puede ser negativo.',
            'total.required' => 'El total es requerido.',
            'total.numeric' => 'El total debe ser un número válido.',
            'total.min' => 'El total no puede ser negativo.',
        ];
    }
}
