<?php

namespace App\Http\Controllers;

use App\Models\DetEncuestum;
use App\Models\DetRespuestum;
use Illuminate\Http\Request;
use App\Models\Encuestum;
use App\Models\Movhabitacion;
use App\Models\Movimiento;
use App\Models\Persona;
use App\Models\Personamaestro;
use App\Models\Respuestum;
use App\Models\ValordetRespuestum;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SurveyController extends Controller
{
    public function getCustomerData(Request $request, $id)
    {
        // Obtener el UUID de la encuesta desde los encabezados
        $uuid = $request->header('Authorization');
    
        // Verificar si el UUID y el token de autorización son válidos
        $encuesta = Encuestum::where('uuid', $uuid)->first();
    
        if (!$encuesta) {
            return response()->json([
                'status' => 401,
                'message' => 'Unauthorized'
            ], 401);
        }
    
        // Buscar en la base de datos
        $personamaestro = Personamaestro::where('nrodoc', $id)->first();
    
        // Si no se encuentra en la BD, buscar en RENIEC
        if (!$personamaestro) {
            $personaData = $this->buscarEnReniec($id);
    
            if ($personaData) {
                return response()->json([
                    'status' => 200,
                    'data' => $personaData
                ], 200);
            }
    
            return response()->json([
                'status' => 404,
                'message' => 'Customer not found in database or RENIEC'
            ], 404);
        }
    
        // Si se encontró en la BD, buscar en la tabla "Persona"
        $persona = Persona::where('personamaestro_id', $personamaestro->id)->first();
    
        // Si no se encuentra en Persona, devolver la información de RENIEC si está disponible
        if (!$persona) {
            $personaData = $this->buscarEnReniec($id);
            if ($personaData) {
                return response()->json([
                    'status' => 200,
                    'data' => $personaData
                ], 200);
            }
            
            return response()->json([
                'status' => 404,
                'message' => 'Person data not found'
            ], 404);
        }
    
        // Construir la respuesta con los datos de la BD
        $customerData = [
            'id' => $id,
            'type' => $personamaestro->tipopersona,
            'first_name' => $personamaestro->nombres,
            'last_name' => $personamaestro->apellidos,
            'gender' => $personamaestro->sexo,
            'birthday' => $personamaestro->fechanac,
            'landline_phone' => $persona->telefonofijo,
            'cellphone_number' => $persona->telefonomovil,
            'email_address' => $persona->email
        ];
    
        return response()->json([
            'status' => 200,
            'data' => $customerData
        ], 200);
    }
    
    /**
     * Función para buscar en RENIEC por DNI o RUC
     */
    private function buscarEnReniec($numeroDocumento)
    {
        // Determinar si es DNI (8 dígitos) o RUC (11 dígitos)
        if (strlen($numeroDocumento) == 8) {
            $url = "https://facturae-garzasoft.com/facturacion/buscaCliente/BuscaCliente2.php";
            $params = [
                'dni' => $numeroDocumento,
                'fe' => 'N',
                'token' => config('services.comprobantes_mail.token')
            ];
        } elseif (strlen($numeroDocumento) == 11) {
            $url = "https://comprobante-e.com/facturacion/buscaCliente/BuscaClienteRuc.php";
            $params = [
                'ruc' => $numeroDocumento,
                'fe' => 'N',
                'token' => config('services.comprobantes_mail.token')
            ];
        } else {
            return null; // Número de documento inválido
        }
    
        // Hacer la consulta HTTP
        $response = Http::get($url, $params);
    
        // Verificar si la consulta fue exitosa
        if ($response->successful()) {
            $data = $response->json();
    
            if (!$data) {
                return null; // No encontró información
            }
    
            // Construir el array de datos basado en la estructura de RENIEC
            if (strlen($numeroDocumento) == 8) { // DNI
                return [
                    'id' => $numeroDocumento,
                    'type' => 'Natural',
                    'first_name' => $data['nombres'] ?? '',
                    'last_name' => trim(($data['apepat'] ?? '') . ' ' . ($data['apemat'] ?? '')),
                    'gender' => null,
                    'birthday' => $data['fecnac'] ?? null,
                    'landline_phone' => null,
                    'cellphone_number' => null,
                    'email_address' => null
                ];
            } elseif (strlen($numeroDocumento) == 11) { // RUC
                return [
                    'id' => $numeroDocumento,
                    'type' => 'Jurídica',
                    'first_name' => $data['RazonSocial'] ?? '',
                    'last_name' => '',
                    'gender' => null,
                    'birthday' => null,
                    'landline_phone' => null,
                    'cellphone_number' => null,
                    'email_address' => null
                ];
            }
        }
    
        return null; // Si falla la consulta
    }
    
    public function getRoomData(Request $request, $id)
{
    // Obtener el UUID de la encuesta desde los encabezados
    $uuid = $request->header('Authorization');
    
    // Verificar si el UUID y el token de autorización son válidos
    $encuesta = Encuestum::where('uuid', $uuid)->first();
    
    if (!$encuesta) {
        return response()->json([
            'status' => 401,
            'message' => 'Unauthorized'
        ], 401);
    }

    // Buscar el movimiento de habitación por el id
    $movHabitacion = Movhabitacion::find($id);

    if (!$movHabitacion) {
        return response()->json([
            'status' => 404,
            'message' => 'Room data not found'
        ], 404);
    }

    // Obtener los datos de la habitación asociada
    $roomData = [
        'id' => $movHabitacion->id,
        'room_number' => $movHabitacion->habitacion ? $movHabitacion->habitacion->numero : null, // Número de la habitación
        'room_type' => $movHabitacion->habitacion ? $movHabitacion->habitacion->tipohabitacion->descripcion : null, // Tipo de habitación
        'check_in_date' => $movHabitacion->fechaingreso,
        'check_out_date' => $movHabitacion->fechasalida,
        'total_amount' => $movHabitacion->total,
        // 'status' => $movHabitacion->situacion, // Estado del movimiento de la habitación
        'comments' => $movHabitacion->comentario,
        'currency' => $movHabitacion->moneda,
        'early_checkin' => $movHabitacion->early_checkin,
        'late_checkout' => $movHabitacion->late_checkout,
        'day_use' => $movHabitacion->dias ? $movHabitacion->dias . ' dias' : null,
        'time_use' => $movHabitacion->horas ? $movHabitacion->horas . ' horas' : null
    ];

    // Devolver la respuesta en el formato solicitado
    return response()->json([
        'status' => 200,
        'data' => $roomData
    ], 200);
}

public function getSurveyData(Request $request)
{
    // Obtener el UUID de la encuesta desde los encabezados
    $uuid = $request->header('Authorization');
    
    // Verificar si el UUID y el token de autorización son válidos
    $encuesta = Encuestum::where('uuid', $uuid)->first();
    
    if (!$encuesta) {
        return response()->json([
            'status' => 401,
            'message' => 'Unauthorized'
        ], 401);
    }

    // Obtener las preguntas asociadas a la encuesta
    $questions = DetEncuestum::where('encuesta_id', $encuesta->id)
                            ->with(['tipopregunta', 'categoriapregunta']) // Relacionar las tablas necesarias
                            ->get();

    // Formatear las preguntas
    $formattedQuestions = $questions->map(function ($question) {
        return [
            'id' => $question->id,
            'statement' => $question->enunciado,
            'mandatory' => $question->obligatorio == 'S', // Convertir 'S'/'N' a true/false
            'options' => json_decode($question->opciones), // Opciones almacenadas como JSON
            'type_id' => $question->tipopregunta_id // El ID del tipo de pregunta
        ];
    });

    // Construir la respuesta
    $surveyData = [
        'survey' => [
            'description' => $encuesta->descripcion,
            'final_message' => $encuesta->mensaje_finalizacion,
            'validate_customer' => $encuesta->validacion == 'S' ? true : false, 
        ],
        'questions' => $formattedQuestions
    ];

    // Devolver la respuesta en el formato solicitado
    return response()->json([
        'status' => 200,
        'data' => $surveyData
    ], 200);
}

public function storeSurveyData(Request $request)
{
    $uuid = $request->header('Authorization');

    // Verificar si el UUID es válido
    $encuesta = Encuestum::where('uuid', $uuid)->first();

    if (!$encuesta) {
        return response()->json([
            'status' => 401,
            'message' => 'Unauthorized'
        ], 401);
    }

    $customerData = $request->input('customer');
    $personamaestro = null;

    // Verificar si la encuesta requiere validación con documento
    if ($encuesta->validacion === 'S') {
        if (!$customerData || !isset($customerData['id'])) {
            return response()->json([
                'status' => 400,
                'message' => 'Document ID is required for this survey'
            ], 400);
        }

        $customerId = $customerData['id'];

        // Buscar en la base de datos
        $personamaestro = Personamaestro::where('nrodoc', $customerId)->first();

        // Si no existe, buscar en RENIEC
        if (!$personamaestro) {
            $personaData = $this->buscarEnReniec($customerId);

            if ($personaData) {
                $personamaestro = new Personamaestro();
                $personamaestro->nrodoc = $personaData['id'];
                $personamaestro->tipopersona = $personaData['type'];
                $personamaestro->nombres = $personaData['first_name'];
                $personamaestro->apellidos = $personaData['last_name'];
                $personamaestro->sexo = ($personaData['gender'] ?? '') === 'Femenino' ? 'F' : 'M';
                $personamaestro->fechanac = $personaData['birthday'];
                $personamaestro->save();
            } else {
                return response()->json([
                    'status' => 404,
                    'message' => 'Customer not found in database or RENIEC'
                ], 404);
            }
        }
        
        // Verificar movimiento de habitación
        $movHabitacion = Movhabitacion::find($request->input('row_code'));

        $movimiento = Movimiento::find($movHabitacion->movimiento_id);

        // Crear respuesta
        $respuesta = new Respuestum();
        $respuesta->codigo = Str::random(10);
        $respuesta->fecha = now();
        $respuesta->persona_id = $personamaestro->id;
        $respuesta->persona = $personamaestro->nombres . ' ' . $personamaestro->apellidos;
        $respuesta->encuesta_id = $encuesta->id;
        $respuesta->sucursal_id = $encuesta->sucursal_id;
        $respuesta->movimiento_id = $movimiento->id;
        $respuesta->movimiento = $movimiento ? json_encode($movimiento) : null;
        $respuesta->save();
    
        // Guardar detalles de respuestas
        foreach ($request->input('responses') as $response) {
            $detEncuesta = DetEncuestum::find($response['question_id']);
    
            if ($detEncuesta) {
                $detRespuesta = new DetRespuestum();
                $detRespuesta->det_encuesta_id = $detEncuesta->id;
                $detRespuesta->respuesta_id = $respuesta->id;
                $detRespuesta->sucursal_id = 1;
                $detRespuesta->save();
    
                foreach ($response['values'] as $value) {
                    $detRespuestaValue = new ValordetRespuestum();
                    $detRespuestaValue->valor = $value;
                    $detRespuestaValue->det_respuesta_id = $detRespuesta->id;
                    $detRespuestaValue->sucursal_id = 1;
                    $detRespuestaValue->save();
                }
            }
        }

    } else {
        // Si la encuesta NO requiere validación, crear persona solo con nombres
        $firstName = $request->input('first_name');
        $lastName = $request->input('last_name');

        if (!$firstName || !$lastName) {
            return response()->json([
                'status' => 400,
                'message' => 'First name and last name are required'
            ], 400);
        }
        // Verificar movimiento de habitación
        $movHabitacion = Movhabitacion::find($request->input('row_code'));
        $movimiento = Movimiento::find($movHabitacion->movimiento_id);

        // Crear respuesta
        $respuesta = new Respuestum();
        $respuesta->codigo = Str::random(10);
        $respuesta->fecha = now();
        $respuesta->persona_id = null;
        $respuesta->persona = $firstName . ' ' . $lastName;
        $respuesta->encuesta_id = $encuesta->id;
        $respuesta->sucursal_id = $encuesta->sucursal_id;
        $respuesta->movimiento_id = $movimiento->id;
        $respuesta->movimiento = $movimiento ? json_encode($movimiento) : null;
        $respuesta->save();
    
        // Guardar detalles de respuestas
        foreach ($request->input('responses') as $response) {
            $detEncuesta = DetEncuestum::find($response['question_id']);
    
            if ($detEncuesta) {
                $detRespuesta = new DetRespuestum();
                $detRespuesta->det_encuesta_id = $detEncuesta->id;
                $detRespuesta->respuesta_id = $respuesta->id;
                $detRespuesta->sucursal_id = 1;
                $detRespuesta->save();
    
                foreach ($response['values'] as $value) {
                    $detRespuestaValue = new ValordetRespuestum();
                    $detRespuestaValue->valor = $value;
                    $detRespuestaValue->det_respuesta_id = $detRespuesta->id;
                    $detRespuestaValue->sucursal_id = 1;
                    $detRespuestaValue->save();
                }
            }
        }
    }

    if (!$movHabitacion) {
        return response()->json([
            'status' => 404,
            'message' => 'Movement not found'
        ], 404);
    }



    return response()->json([
        'status' => 200,
        'data' => [
            'code' => $respuesta->codigo
        ]
    ], 200);
}



}
