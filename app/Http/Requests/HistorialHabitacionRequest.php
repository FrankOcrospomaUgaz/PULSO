<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class HistorialHabitacionRequest extends FormRequest
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
			'situacion_pago' => 'required|string',
			'situacion_checkout' => 'required|string',
			'movhabitacion_id' => 'required',
			'relturnocaja_id' => 'required',
			'sucursal_id' => 'required',
        ];
    }
}
