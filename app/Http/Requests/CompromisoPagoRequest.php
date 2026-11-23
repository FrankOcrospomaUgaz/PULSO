<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CompromisoPagoRequest extends FormRequest
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
			'contrato_id' => 'required',
			'personamaestro_id' => 'required',
			'ambiente_id' => 'required',
			'fecha_vencimiento' => 'required',
			'numero_cuota' => 'required',
			'monto' => 'required',
			'forma_pago' => 'string',
			'estado' => 'required|string',
        ];
    }
}
