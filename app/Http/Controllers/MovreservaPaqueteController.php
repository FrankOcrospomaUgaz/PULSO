<?php

namespace App\Http\Controllers;

use App\Models\MovreservaPaquete;
use App\Models\Movimiento;
use App\Models\Persona;
use App\Models\Personamaestro;
use App\Models\SalonPackage;
use App\Models\Sucursal;
use App\Models\User;
use App\Models\Tipodocumento;
use App\Services\CorrelativoService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Client;
use App\Models\Operacione;
use Barryvdh\Snappy\Facades\SnappyPdf as Pdf;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

/**
 * Class MovreservaPaqueteController
 * @package App\Http\Controllers
 */
class MovreservaPaqueteController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index(Request $request)
    {
        $search = $request->input('search');
        $estadoSeleccionado = $request->input('estado', 'PENDIENTE');

        $query = MovreservaPaquete::where('sucursal_id', Auth::user()->persona->sucursal_id);
        $perPage = $request->input('perPage', 10);
        $columns = Schema::getColumnListing('movreserva_paquetes');

        $textColumns = [];
        foreach ($columns as $column) {
            $columnType = DB::select("
                SELECT data_type
                FROM information_schema.columns
                WHERE table_name = 'movreserva_paquetes' AND column_name = ?
            ", [$column]);

            if (isset($columnType[0]) && in_array($columnType[0]->data_type, ['character varying', 'text'])) {
                $textColumns[] = $column;
            }
        }

        if ($search) {
            $query->where(function ($q) use ($textColumns, $search) {
                foreach ($textColumns as $column) {
                    $q->orWhereRaw("LOWER($column) LIKE ?", ['%' . strtolower($search) . '%']);
                }
            });

            $query->orWhereHas('movimiento', function ($q) use ($search) {
                $q->whereRaw("LOWER(numero) LIKE ?", ['%' . strtolower($search) . '%'])
                    ->orWhereRaw("LOWER(comentario) LIKE ?", ['%' . strtolower($search) . '%']);
            });

            $query->orWhereHas('movimiento.persona.personamaestro', function ($q) use ($search) {
                $q->whereRaw("LOWER(nrodoc) LIKE ?", ['%' . strtolower($search) . '%']);
            });
        }

        if ($estadoSeleccionado !== 'TODAS') {
            $query->where('estado', $estadoSeleccionado);
        }

        $reservas = $query->orderBy('id', 'desc')->paginate($perPage);

        $operaciones = Operacione::where('tabla_id', 81)->where('estado', true)->get();

        $estados = ['TODAS', 'PENDIENTE', 'CONFIRMADO', 'CANCELADO'];

        return view('movreserva-paquete.index', compact('reservas', 'operaciones', 'estados', 'estadoSeleccionado'))
            ->with('i', (request()->input('page', 1) - 1) * $reservas->perPage());
    }
    public function store(Request $request)
    {
        try {
            // Validación básica de email
            $correo = $request->persona['email'] ?? '';
            if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Por favor ingrese un correo electrónico válido.'
                ], 400);
            }

            DB::beginTransaction();

            $sucursal = Sucursal::findOrFail($request->sucursal_id);

            // Obtener responsable
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

            // Registrar o actualizar persona
            $personaData = $request->persona;
            $personamaestro = Personamaestro::where('nrodoc', $personaData['nrodoc'])->first();

            if ($personamaestro) {
                $persona = Persona::where('personamaestro_id', $personamaestro->id)->first();
                if ($persona) {
                    $persona->update([
                        'nombres' => $personaData['nombres'],
                        'apellidos' => $personaData['apellidos'],
                        'telefono' => $personaData['telefono'],
                        'email' => $personaData['email'],
                        'direccion' => $personaData['direccion'] ?? '',
                        'imagen' => '-',
                        'compartido' => 'N',
                        'no_grato' => false,
                        'distrito_id' => $sucursal->ubigeo_id,
                    ]);
                }
            } else {
                $personamaestro = Personamaestro::create([
                    'nrodoc' => $personaData['nrodoc'],
                    'nombres' => $personaData['nombres'],
                    'apellidos' => $personaData['apellidos'],
                    'tipopersona' => 'Natural',
                    'nrodoc' =>  $personaData['nrodoc'],

                ]);

                $persona = Persona::create([
                    'personamaestro_id' => $personamaestro->id,
                    'sucursal_id' => $sucursal->id,
                    'nombres' => $personaData['nombres'],
                    'apellidos' => $personaData['apellidos'],
                    'telefono' => $personaData['telefono'],
                    'direccion' => $personaData['direccion'] ?? '',
                    'email' => $personaData['email'],
                    'telefonofijo' => $personaData['telefonofijo'] ?? '-',
                    'telefonomovil' => $personaData['telefonomovil'] ?? '-',
                    'imagen' => '-',
                    'compartido' => 'N',
                    'no_grato' => false,
                    'distrito_id' => $sucursal->ubigeo_id,
                ]);
            }

            // Buscar paquete salón
            $paquete = SalonPackage::findOrFail($request->salon_package_id);

            // Validar conflicto reserva paquete si aplica (puedes agregar lógica)

            // Crear movimiento
            $tipodocumento = Tipodocumento::findOrFail(9);
            $movimiento = Movimiento::create([
                'numero' => $this->generateNumero($tipodocumento->id, $responsable),
                'fecha' => now(),
                'usuario_id' => $usuario->id,
                'usuario' => $usuario->name,
                'responsable_id' => $responsable->id,
                'responsable' => $responsable->nombres . ' ' . $responsable->apellidos,
                'persona_id' => $persona->id,
                'persona' => $persona->nombres . ' ' . $persona->apellidos,
                'comentario' => $request->comentario,
                'situacion' => 'A',
                'tipomovimiento_id' => $tipodocumento->tipomovimiento_id,
                'tipodocumento_id' => $tipodocumento->id,
                'sucursal_id' => $sucursal->id,
            ]);

            // Crear reserva paquete
            $movReservaPaquete = MovreservaPaquete::create([
                'movimiento_id' => $movimiento->id,
                'fechavencimiento' => $request->fechareserva,
                'precio_paquete' => $paquete->precio,
                'total' => $request->total,
                'salon_package_id' => $paquete->id,
                'salon_package_name' => $paquete->nombre,
                'json_paquetes' => json_encode($paquete),
                'estado' => 'PENDIENTE',
                'sucursal_id' => $sucursal->id,
                'celular' => $persona->telefono,
                'email' => $persona->email,
                'nropersonas' => $request->nropersonas,
                'observacion' => $request->comentario,
            ]);

            // Generar PDF (debes implementar el método generarPDF para paquetes)
            $pdf = $this->generarPDF($movReservaPaquete->id);
            $pdf = mb_convert_encoding($pdf, 'UTF-8', 'ISO-8859-1');

            $fields = [
                'token' => config('services.comprobantes_mail.token'),
                "to" => [$persona->email],
                "bcc" => [],
                "cc" => [],
                "subject" => "📅 HotelHub | Confirmación de Reserva de Paquete – Código de Consulta",
                "body" => "
                <p>Estimado(a) {$persona->nombres},</p>
                <p>Su reserva del paquete <strong>{$paquete->nombre}</strong> ha sido generada con éxito en <strong>HotelHub</strong>.</p>
                <p>Código de Reserva: <strong>{$movReservaPaquete->id}</strong></p>
                <p>Puede consultar el estado de su reserva en cualquier momento haciendo clic en el siguiente botón:</p>
                <p style='text-align: center;'>
                    <a href='https://recepcionestrujillo.com/consulta-estado-reserva?codigo={$movReservaPaquete->id}' target='_blank' style='
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
                <p>Adjuntamos el PDF con los detalles de su reserva.</p>
                <p>Gracias por elegirnos.</p>
                <p><strong>El Equipo de HotelHub</strong></p>
            ",
                "attachments" => [[
                    "name_file" => "reserva_paquete_{$movReservaPaquete->id}.pdf",
                    "content_file" => $pdf,
                    "content_type" => "application/pdf",
                ]],
            ];

            $client = new \GuzzleHttp\Client();
            $response = $client->post("https://facturae-garzasoft.com/facturacion/enviaEmail/envia_correo_json.php", [
                'json' => $fields,
            ]);

            if ($response->getStatusCode() != 200) {
                DB::rollBack();
                $errorMessage = $response->getBody()->getContents();
                return response()->json([
                    'success' => false,
                    'message' => 'Error al enviar el correo, la reserva no fue registrada.',
                    'error_details' => $errorMessage,
                ], 500);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Reserva paquete registrada con éxito.',
                'codigo_reserva' => $movReservaPaquete->id,
                'email_sent_to' => $persona->email,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Mostrar reserva paquete por ID
     */
    public function show($id)
    {
        try {
            $reservaPaquete = MovreservaPaquete::with([
                'movimiento',
                'salonPackage',
                'sucursal',
            ])->findOrFail($id);

            return response()->json([
                'success' => true,
                'reserva_paquete' => [
                    'id' => $reservaPaquete->id,
                    'fechareserva' => $reservaPaquete->fechavencimiento,
                    'estado' => $reservaPaquete->estado,
                    'precio_paquete' => $reservaPaquete->precio_paquete,
                    'total' => $reservaPaquete->total,
                    'celular' => $reservaPaquete->celular,
                    'email' => $reservaPaquete->email,
                    'comentario' => $reservaPaquete->observacion,
                    'nropersonas' => $reservaPaquete->nropersonas,
                    'sucursal_id' => $reservaPaquete->sucursal->id ?? '',
                    'sucursal' => $reservaPaquete->sucursal->razonsocial ?? '',
                    'paquete' => [
                        'id' => $reservaPaquete->salonPackage->id ?? null,
                        'nombre' => $reservaPaquete->salonPackage->nombre ?? null,
                        'precio' => $reservaPaquete->salonPackage->precio ?? null,
                        'content' => $reservaPaquete->salonPackage->content ?? null,
                        'imagen' => $reservaPaquete->salonPackage->imagen ?? null,
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Reserva paquete no encontrada',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generar número correlativo para movimientos (igual que en ReservaApiController)
     */
    private function generateNumero($tipoDocumentoId, $persona, $reservar = true)
    {
        $tiposDocumento = Tipodocumento::where('tipomovimiento_id', 2)->get();
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


    public function generarPDF($id)
    {
        // Buscar la reserva paquete con sus relaciones necesarias
        $reserva = MovreservaPaquete::with([
            'salonPackage',
            'movimiento.persona',
            'sucursal',
        ])->findOrFail($id);

        $paquete = $reserva->salonPackage;
        $cliente = $reserva->movimiento->persona;
        $sucursal = $reserva->sucursal;

        // Datos del paquete y reserva
        $reservaData = [
            'id' => $reserva->id,
            'fecha' => $reserva->fechavencimiento ? Carbon::parse($reserva->fechavencimiento)->format('d/m/Y') : null,
            'nombre_cliente' => ($cliente->nombres ?? '') . ' ' . ($cliente->apellidos ?? ''),
            'telefono' => $cliente->telefonofijo ?? '-',
            'movil' => $reserva->celular ?? '-',
            'email' => $reserva->email ?? '-',
            'direccion' => $cliente->direccion ?? 'N/A',
            'moneda' => 'PEN',
            'total' => $reserva->total,
            'precio_paquete' => $reserva->precio_paquete,
            'estado' => $reserva->estado,
            'observacion' => $reserva->observacion ?? 'N/A',
            'nropersonas' => $reserva->nropersonas,
            'paquete' => [
                'nombre' => $paquete->nombre ?? 'N/A',
                'precio' => $paquete->precio ?? 0,
                'contenido' => $paquete->content ?? '',
                'imagen' => $paquete->imagen ?? null,
            ],
        ];

        // Generar el PDF con la vista que diseñes para paquetes (ejemplo: 'movreserva_paquete.ficha')
        $pdf = Pdf::loadView('movreserva-paquete.ficha', compact('reservaData', 'sucursal'));

        // Retornar el stream del PDF para visualizarlo o descargarlo
        return $pdf->stream("ReservaPaquete_{$reserva->id}.pdf");
    }
}
