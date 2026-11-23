<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DetMovcajaRequest extends FormRequest
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
			'movcaja_id' => 'required',
			'tipo' => 'required|string',
			'mediopago_id' => 'required',
			'mediopago' => 'required|string',
			'numero' => 'required|string',
			'tarjeta' => 'required|string',
			'banco' => 'required|string',
			'billeteradigital' => 'required|string',
			'pos' => 'required|string',
			'monto' => 'required',
			'comentario' => 'required|string',
			'situacion' => 'required|string',
			'sucursal_id' => 'required',
        ];
    }
}
