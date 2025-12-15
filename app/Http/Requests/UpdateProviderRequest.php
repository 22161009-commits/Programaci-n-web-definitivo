<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProviderRequest extends FormRequest
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
        $providerId = $this->route('provider')->id;
        
        return [
            'name' => 'required|string|max:255|unique:providers,name,' . $providerId,
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            'contact_name' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'products' => 'nullable|array',
            'products.*' => 'exists:products,id',
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
            'name.required' => 'El nombre del proveedor es obligatorio.',
            'name.string' => 'El nombre debe ser un texto válido.',
            'name.max' => 'El nombre no puede tener más de 255 caracteres.',
            'name.unique' => 'Ya existe un proveedor con este nombre.',
            'email.email' => 'El email debe tener un formato válido.',
            'phone.max' => 'El teléfono no puede tener más de 20 caracteres.',
            'contact_name.max' => 'El nombre de contacto no puede tener más de 255 caracteres.',
            'products.array' => 'Los productos deben ser un array.',
            'products.*.exists' => 'Uno o más productos seleccionados no existen.',
        ];
    }
}
