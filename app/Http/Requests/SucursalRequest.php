<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SucursalRequest extends FormRequest
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
            'razonsocial' => 'required|string|max:255',
            'nombrecomercial' => 'required|string|max:255',
            'direccion' => 'nullable|string',
            'ruc' => 'required|string|max:11', // Ejemplo de validación de longitud para RUC
            'email' => 'nullable|email',
            'logo' => 'nullable|file|mimes:jpg,jpeg,png',
            'situacion' => '',
            'facturacion' => '',
            'tipo' => 'required|string',
            'venta_mayorista' => '',
            'empresa_id' => 'required|integer',
            'ubigeo_id' => 'required|integer',
            'business_intelligence_url' => '',
        ];
    }
    
}
