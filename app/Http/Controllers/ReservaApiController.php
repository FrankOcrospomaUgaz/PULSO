<?php

namespace App\Http\Controllers;

use App\Models\Banco;
use App\Models\Billeteradigital;
use App\Models\DetMovcaja;
use App\Models\Igv;
use App\Models\Movcaja;
use App\Models\Movreserva;
use App\Http\Requests\MovreservaRequest;
use App\Models\Caja;
use App\Models\Movventum;
use App\Models\Operacione;
use App\Models\Pasarelapago;
use App\Models\Persona;
use App\Models\Habitacion;
use App\Models\HistorialHabitacion;
use App\Models\Huesped;
use App\Models\Mediopago;
use App\Models\Motivoreserva;
use App\Models\Movhabitacion;
use App\Models\Movimiento;
use App\Models\MovreservaHabitacion;
use App\Models\Origenreserva;
use App\Models\Personamaestro;
use App\Models\MovhabitacionHabitacion;

use App\Models\Piso;
use App\Models\Producto;
use App\Models\RelParametrosucursal;
use App\Models\RelTurnocaja;
use App\Models\Sucursal;
use App\Models\Tarjetum;
use App\Models\Tipodocumento;
use App\Services\CorrelativoService;
use App\Models\Turno;
use App\Models\Ubigeo;
use App\Models\User;
use Barryvdh\Snappy\Facades\SnappyPdf as Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Class MovReservaController
 * @package App\Http\Controllers
 */
class ReservaApiController  extends Controller
{
    /**
     * Display a listing of the resource.
     */

public function store(Request $request)
{
    try {
        // Validación del correo electrónico
        $correo = $request->persona['email'];
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            return response()->json([
                'success' => false,
                'message' => 'Por favor ingrese un correo electrónico válido.'
            ], 400); // Código de estado 400 para una mala solicitud
        }

        DB::beginTransaction();

        // Obtener datos generales
        $sucursal = Sucursal::findOrFail($request->sucursal_id);

        // Obtener el primer responsable registrado para esa sucursal
        $responsable = Persona::where('sucursal_id', $sucursal->id)->orderBy('id')->first();

        if (!$responsable) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró un responsable para esta sucursal.'
            ], 404);
        }

        $usuario = User::where('persona_id', $responsable->id)->first();
        if (!$usuario) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró un usuario para el responsable.'
            ], 404);
        }

        // Registrar o recuperar persona (la que viene en $request->persona)
        $personaData = $request->persona;

        // Buscar la persona por el nrodoc en Personamaestro
        $personamaestro = Personamaestro::where('nrodoc', $personaData['nrodoc'])->first();

        if ($personamaestro) {
            $persona = Persona::where('personamaestro_id', $personamaestro->id)->first();

            if ($persona) {
                // Actualizar persona
                $persona->update([
                    'nombres' => $personaData['nombres'],
                    'apellidos' => $personaData['apellidos'],
                    'telefonomovil' => $personaData['telefono'],
                    'email' => $personaData['email'],
                    'direccion' => $personaData['direccion'] ?? '',
                    'imagen' => '-',
                    'compartido' => 'N',
                    'no_grato' => false,
                    'distrito_id' => $sucursal->ubigeo_id,
                ]);
            } else {
                // Crear persona porque no existe
                $persona = Persona::create([
                    'personamaestro_id' => $personamaestro->id,
                    'sucursal_id' => $sucursal->id,
                    'nombres' => $personaData['nombres'],
                    'apellidos' => $personaData['apellidos'],
                    'direccion' => $personaData['direccion'] ?? '',
                    'email' => $personaData['email'],
                    'telefonofijo' => $personaData['telefonofijo'] ?? '-',
                    'telefonomovil' => $personaData['telefono'] ?? '-',
                    'imagen' => '-',
                    'compartido' => 'N',
                    'no_grato' => false,
                    'distrito_id' => $sucursal->ubigeo_id,
                ]);
            }
        } else {
            // Si no existe un personamaestro, crearlo
            $personamaestro = Personamaestro::create([
                'nrodoc' => $personaData['nrodoc'],
                'nombres' => $personaData['nombres'],
                'apellidos' => $personaData['apellidos'],
                'tipopersona' => 'Natural',
                'nrodoc' =>  $personaData['nrodoc'],
            ]);

            // Crear una nueva persona asociada al personamaestro
            $persona = Persona::create([
                'personamaestro_id' => $personamaestro->id,
                'sucursal_id' => $sucursal->id,
                'nombres' => $personaData['nombres'],
                'apellidos' => $personaData['apellidos'],
                'direccion' => $personaData['direccion'] ?? '',
                'email' => $personaData['email'],
                'telefonofijo' => $personaData['telefonofijo'] ?? '-',
                'telefonomovil' => $personaData['telefono'] ?? '-',
                'imagen' => '-',
                'compartido' => 'N',
                'no_grato' => false,
                'distrito_id' => $sucursal->ubigeo_id,
            ]);
        }

        // -- NUEVA PARTE: Registro opcional de persona para FACTURACIÓN --
        // Si viene persona_facturacion, crear o actualizar y usar para movimiento
        $personaFacturacion = null;
        if ($request->has('persona_facturacion') && is_array($request->persona_facturacion)) {
            $pf = $request->persona_facturacion;

            $pmFacturacion = Personamaestro::firstOrCreate(
                ['nrodoc' => $pf['nrodoc']],
                [
                    'nombres' => $pf['nombres'],
                    'apellidos' => $pf['apellidos'],
                    'tipopersona' => 'Natural',
                ]
            );

            $personaFacturacion = Persona::updateOrCreate(
                ['personamaestro_id' => $pmFacturacion->id],
                [
                    'sucursal_id' => $sucursal->id,
                    'nombres' => $pf['nombres'],
                    'apellidos' => $pf['apellidos'],
                    'direccion' => $pf['direccion'] ?? '',
                    'email' => $pf['email'] ?? '',
                    'telefonofijo' => $pf['telefonofijo'] ?? '-',
                    'telefonomovil' => $pf['telefono'] ?? '-',
                    'imagen' => '-',
                    'compartido' => 'N',
                    'no_grato' => false,
                    'distrito_id' => $sucursal->ubigeo_id,
                ]
            );
        }

        // Construir json acompañantes
        $acompanantes = $request->acompanantes ?? [];
        // Si hay persona para facturación nueva, agregar el responsable anterior como acompañante
        if ($personaFacturacion && $personaFacturacion->id !== $responsable->id) {
            $acompanantes[] = $persona;
        }

        // Crear la habitación como arreglo para json_habitaciones
        $habitacion = Habitacion::findOrFail($request->habitacion_id);
        $jsonHabitaciones = [[
            'id' => $habitacion->id,
            'numero' => $habitacion->numero,
            'precio' => $request->preciohabitacion
        ]];
        $habitacionPrincipal = $habitacion->id;

        // Fechas
        $fechaInicio = new Carbon($request->fechavencimiento);
        $fechaFin = (clone $fechaInicio)->addDays($request->noches);

        // Validar conflicto de reserva
        $reservasCandidatas = Movreserva::where(function ($q) use ($habitacionPrincipal) {
            $q->where('habitacionprincipal_id', $habitacionPrincipal)
                ->orWhereHas('movreserva_habitacion', function ($qh) use ($habitacionPrincipal) {
                    $qh->where('habitacion_id', $habitacionPrincipal);
                });
        })
            ->whereHas('movimiento', fn($q) => $q->where('situacion', 'A'))
            ->where('estado', '!=', 'ANULADO')
            ->with(['movreserva_habitacion' => function ($q) use ($habitacionPrincipal) {
                $q->where('habitacion_id', $habitacionPrincipal);
            }])
            ->get();

        foreach ($reservasCandidatas as $conflicto) {
            $detalleHab = $conflicto->movreserva_habitacion->first();
            $esFinalizado = ($conflicto->estado === 'FINALIZADO') || ($detalleHab && $detalleHab->situacion === 'FINALIZADO');

            $inicioRaw = $detalleHab?->fechacheckin ?? $conflicto->fechainicio ?? $conflicto->fechavencimiento;
            if (!$inicioRaw) {
                continue;
            }
            $inicioReserva = Carbon::parse($inicioRaw)->startOfDay();

            if ($esFinalizado) {
                $finRaw = $detalleHab?->fechacheckout ?? $conflicto->fechasalida;
                if ($finRaw) {
                    $fechaFinConflicto = Carbon::parse($finRaw);
                } else {
                    $dias = (int) ($detalleHab?->dias ?? $conflicto->dias ?? 1);
                    $fechaFinConflicto = (new Carbon($inicioRaw))->addDays(max($dias, 1));
                }
            } else {
                $dias = (int) ($detalleHab?->dias ?? $conflicto->dias ?? 1);
                $finProgramado = (new Carbon($conflicto->fechavencimiento))->addDays(max($dias, 1));
                $finRaw = $conflicto->fechasalida ?? $detalleHab?->fechacheckout;
                $fechaFinConflicto = $finRaw ? Carbon::parse($finRaw) : $finProgramado;
            }

            if ($fechaInicio->gte($fechaFinConflicto) || $fechaInicio->isSameDay($fechaFinConflicto)) {
                continue;
            }

            if ($fechaFin->lte($inicioReserva) || $fechaFin->isSameDay($inicioReserva)) {
                continue;
            }

            return response()->json([
                'success' => false,
                'message' => 'Conflicto de reserva para habitación',
                'conflicto' => $conflicto
            ], 422);
        }

        // Crear Movimiento
        $tipodocumento = Tipodocumento::findOrFail(9);
        $movimiento = Movimiento::create([
            'numero' => $this->generateNumero($tipodocumento->id, $responsable),
            'fecha' => now(),
            'usuario_id' => $usuario->id,
            'usuario' => $usuario->name,
            'responsable_id' => $responsable->id,
            'responsable' => $responsable->nombres . ' ' . $responsable->apellidos,
            'persona_id' => $personaFacturacion ? $personaFacturacion->id : $persona->id,
            'persona' => $personaFacturacion
                ? $personaFacturacion->nombres . ' ' . $personaFacturacion->apellidos
                : $persona->nombres . ' ' . $persona->apellidos,
            'comentario' => $request->comentario,
            'situacion' => 'A',
            'tipomovimiento_id' => $tipodocumento->tipomovimiento_id,
            'tipodocumento_id' => $tipodocumento->id,
            'sucursal_id' => $sucursal->id,
        ]);

        // Crear MovReserva
        $movReserva = Movreserva::create([
            'movimiento_id' => $movimiento->id,
            'fechavencimiento' => $request->fechavencimiento,
            'dias' => $request->noches,
            'moneda' => 'PEN',
            'tipocambio' => 3.75,
            'total' => $request->total,
            'adelanto' => $request->adelanto,
            'habitacionprincipal_id' => $habitacionPrincipal,
            'habitacion_principal' => $habitacion->numero,
            'json_habitaciones' => json_encode($jsonHabitaciones),
            'estado' => 'PENDIENTE',
            'motivo_id' => 3,
            'origen_id' => 5,
            'mediopagoadelanto_id' => $request->medio_pago_adelanto_id,
            'sucursal_id' => $sucursal->id,
            'celular' => $persona->telefonomovil,
            'email' => $persona->email,
            'nropersonas' => $request->nropersonas,
            'observacion' => $request->comentario,
            'preciohabitacion' => $request->preciohabitacion,
            'modo' => 'U',
            'json_acompañantes' => json_encode($acompanantes, JSON_UNESCAPED_UNICODE),
        ]);

        // Detalle de habitación
        MovreservaHabitacion::create([
            'movreserva_id' => $movReserva->id,
            'habitacion_id' => $habitacion->id,
            'principal' => true,
            'tarifa' => $request->preciohabitacion,
            'fechacheckin' => $request->fechavencimiento,
            'dias' => $request->noches,
            'total' => $request->preciohabitacion * $request->noches,
            'situacion' => 'PENDIENTE',
            'json_habitacion' => json_encode($jsonHabitaciones[0]),
            'early_checkin' => 0,
            'late_checkout' => 0,
        ]);

        // Guardar acompañantes como huéspedes si los hay
        foreach ($acompanantes as $acompanante) {
            $huesped = new Huesped();
            $huesped->persona_id = $acompanante->id;
            $huesped->movreserva_id = $movReserva->id;
            $huesped->habitacion_id = $request->habitacion_id; // Asigna la habitación correspondiente
            $huesped->save();
        }

        // Enviar correo con PDF
        $pdf = app(MovreservaController::class)->generarPDF($movReserva->id);
        $pdf = mb_convert_encoding($pdf, 'UTF-8', 'ISO-8859-1');
        $fields = [
            'token' => config('services.comprobantes_mail.token'),
            "to" => [$persona->email],
            "bcc" => [],
            "cc" => [],
            "subject" => "📅 HotelHub | Confirmación de Reserva – Código de Consulta",
            "body" => "
    <p>Estimado(a) {$persona->nombres},</p>
    
    <p>Nos complace informarle que su reserva ha sido generada con éxito en <strong>HotelHub</strong>.</p>
    
    <p>Para su comodidad, le proporcionamos el siguiente código de reserva, el cual podrá utilizar para consultar el estado de su reserva en cualquier momento:</p>
    
    <p><strong>Código de Reserva: {$movReserva->id}</strong></p>
    
    <p>Haga clic en el siguiente botón para consultar el estado de su reserva:</p>
    
    <p style='text-align: center;'>
        <a href='https://reservaciones-trujillo.vercel.app/reservacion/{$movReserva->id}' target='_blank' style='
            background-color: #00897b ;
            color: white;
            padding: 14px 20px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
        '>Consultar Estado de Reserva</a>
    </p>

    <p>Además, como parte de nuestra gestión de reservas, hemos adjuntado un archivo PDF con la constancia de su reserva.
     Este archivo contiene los detalles de su estancia, incluyendo la habitación asignada y los servicios disponibles.</p>
    
    <p>Le agradecemos por elegirnos y le deseamos una excelente estancia. Si tiene alguna pregunta o requiere asistencia, 
    no dude en contactarnos.</p>
    
    <p>Atentamente,</p>
    <p><strong>El Equipo de HotelHub</strong></p>
",
            "attachments" => [[
                "name_file" => "reserva_{$movReserva->id}.pdf",
                "content_file" => $pdf,
                "content_type" => "application/pdf",
            ]],
        ];

        $client = new \GuzzleHttp\Client();
        $response = $client->post("https://facturae-garzasoft.com/facturacion/enviaEmail/envia_correo_json.php", [
            'json' => $fields,
        ]);

        // Verifica si la respuesta del correo fue exitosa
        if ($response->getStatusCode() != 200) {
            DB::rollBack();
            $errorMessage = $response->getBody()->getContents(); // Captura el mensaje de error
            return response()->json([
                'success' => false,
                'message' => 'Error al enviar el correo, la reserva no fue registrada.',
                'error_details' => $errorMessage, // Devuelve el error del servidor de correo
            ], 500);
        }

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Reserva registrada con éxito.',
            'codigo_reserva' => $movReserva->id,
            'email_sent_to' => $persona->email, // Devuelve el correo al que se envió el mensaje
        ]);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
}




    public function show($id)
    {
        try {
            $reserva = Movreserva::with([
                'movimiento',
                'habitacion.tipohabitacion',
                'motivoreserva',
                'origen',
                'mediopago',
                'movreserva_habitacion.habitacion.tipohabitacion',
                'sucursal',
                'habitacion',  // Relación definida en el modelo Movreserva
            ])->findOrFail($id);

            // Obtener todos los detalles de la habitación
            $habitaciones = $reserva->movreserva_habitacion->map(function ($movHab) {
                return [
                    'habitacion_id' => $movHab->habitacion->id,
                    'numero' => $movHab->habitacion->numero,
                    'situacion' => $movHab->habitacion->situacion,
                    'descripcion' => $movHab->habitacion->descripcion,
                    'piso' => $movHab->habitacion->piso->nombre ?? null,  // Nombre del piso asociado
                    'tipohabitacion' => [
                        'nombre' => $movHab->habitacion->tipohabitacion->nombre,
                        'capacidad' => $movHab->habitacion->tipohabitacion->capacidad,
                        'precio' => $movHab->habitacion->tipohabitacion->precio,
                    ],
                    'precio' => $movHab->habitacion->precio ?? null, // Precio de la habitación
                ];
            });

            return response()->json([
                'success' => true,
                'reserva' => [
                    'id' => $reserva->id,
                    'fechareserva' => $reserva->fechavencimiento,
                    'dias' => $reserva->dias,
                    'estado' => $reserva->estado,
                    'modo' => $reserva->modo == 'U' ? 'Único' : 'Grupal',
                    'habitacion' => $habitaciones, // Detalles completos de la habitación
                    'total' => $reserva->total,
                    'adelanto' => $reserva->adelanto,
                    'celular' => $reserva->celular,
                    'email' => $reserva->email,
                    'comentario' => $reserva->observacion,
                    'nropersonas' => $reserva->nropersonas,
                    'sucursal' => $reserva->sucursal->nombre ?? '',
                    'motivo' => $reserva->motivoreserva->nombre ?? null,
                    'origen' => $reserva->origen->nombre ?? null,
                    'mediopago' => $reserva->mediopago->descripcion ?? null,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Reserva no encontrada',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    private function generateNumero($tipoDocumentoId, $persona, $reservar = true)
    {
        // Obtener los tipos de documento relacionados con `tipo_movimiento_id = 2`
        $tiposDocumento = Tipodocumento::where('tipomovimiento_id', 2)->get();

        // Buscar el tipo de documento seleccionado por el ID
        $tipoDocumentoPredeterminado = $tiposDocumento->where('id', $tipoDocumentoId)->first();

        if (!$tipoDocumentoPredeterminado) {
            return response()->json(['success' => false, 'message' => 'Tipo de documento no encontrado']);
        }

        $serieKey = CorrelativoService::SIN_SERIE;

        if ($reservar) {
            return CorrelativoService::nextNumeroVenta($persona->sucursal_id, $tipoDocumentoId, $serieKey);
        }

        return CorrelativoService::peekNumeroVenta($persona->sucursal_id, $tipoDocumentoId, $serieKey);
    }
    /**
     * Display the specified resource.
     */
}
