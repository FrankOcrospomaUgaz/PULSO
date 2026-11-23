<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MovimientoRequest extends FormRequest
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
			'numero' => 'required|string',
			'fecha' => 'required',
			'usuario_id' => 'required',
			'usuario' => 'required|string',
			'persona' => 'required|string',
			'responsable_id' => 'required',
			'responsable' => 'required|string',
			'comentario' => 'required|string',
			'situacion' => 'required|string',
			'tipomovimiento_id' => 'required',
			'tipodocumento_id' => 'required',
			'sucursal_id' => 'required',
        ];
    }
}
