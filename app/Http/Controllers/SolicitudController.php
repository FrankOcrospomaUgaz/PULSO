<?php

namespace App\Http\Controllers;

use App\Helpers\NumeroTexto;
use App\Models\Solicitud;
use App\Http\Requests\SolicitudRequest;
use App\Models\DetMovcaja;
use App\Models\Caja;
use App\Models\Movventum;
use App\Models\Operacione;
use App\Models\Persona;
use Barryvdh\Snappy\Facades\SnappyPdf as Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use nusoap_client;
use App\Models\DetMovventum;
use App\Models\Facturador;
use App\Models\Movcaja;
use App\Models\Movimiento;
use App\Models\Sucursal;
use App\Models\Tipodocumento;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SolicitudExport;
use App\Services\CorrelativoService;
use Illuminate\Support\Facades\Config;
use App\Services\UuidService;
use GuzzleHttp\Client;
use ZipArchive;

/**
 * Class SolicitudController
 * @package App\Http\Controllers
 */
class SolicitudController extends Controller
{
    public function downloadXmlById($id)
    {
        $branchId = (int) Auth::user()->persona?->sucursal_id;
        abort_unless($branchId, 403);
        $comprobante = Solicitud::where('sucursal_id', $branchId)->findOrFail($id);

        $this->asegurarPublicNameComprobante($comprobante);

        return $this->descargarXmlDesdeSolicitud($comprobante, true);
    }

    public function downloadCdrById($id)
    {
        $branchId = (int) Auth::user()->persona?->sucursal_id;
        abort_unless($branchId, 403);
        $comprobante = Solicitud::where('sucursal_id', $branchId)->findOrFail($id);

        return $this->descargarCdrDesdeSolicitud($comprobante, true);
    }

    public function downloadBill(string $publicName, string $format, bool $asAttachment = false)
    {
        $comprobante = Solicitud::where('public_name', $publicName)->firstOrFail();

        if ($format === 'xml') {
            return $this->descargarXmlDesdeSolicitud($comprobante, $asAttachment);
        }

        if ($format === 'cdr') {
            return $this->descargarCdrDesdeSolicitud($comprobante, $asAttachment);
        }

        abort(404);
    }

    private function asegurarPublicNameComprobante(Solicitud $comprobante): void
    {
        if ($comprobante->public_name) {
            return;
        }

        $comprobante->public_name = UuidService::uuidv4()->toString();
        $comprobante->save();
    }

    private function rutaZipComprobante(Solicitud $comprobante): string
    {
        return 'ficheros/sucursal_' . $comprobante->sucursal_id . '/' . $comprobante->nombre . '.zip';
    }

    private function buscarEntradaXmlEnZip(ZipArchive $zip, bool $esCdr): ?string
    {
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entryName = $zip->getNameIndex($i);
            if (!$entryName || strcasecmp(pathinfo($entryName, PATHINFO_EXTENSION), 'xml') !== 0) {
                continue;
            }

            $esEntradaCdr = stripos(basename($entryName), 'R-') === 0;

            if ($esCdr && $esEntradaCdr) {
                return $entryName;
            }

            if (!$esCdr && !$esEntradaCdr) {
                return $entryName;
            }
        }

        return null;
    }

    private function respuestaDescargaXml(string $xmlContent, string $nombreArchivo, bool $asAttachment = false)
    {
        $charset = 'UTF-8';
        if (preg_match('/<\?xml[^>]+encoding=["\']([^"\']+)["\']/i', $xmlContent, $matches)) {
            $charset = strtoupper($matches[1]);
        }

        $disposition = $asAttachment ? 'attachment' : 'inline';

        return response($xmlContent, 200, [
            'Content-Type' => 'application/xml; charset=' . $charset,
            'Content-Disposition' => $disposition . '; filename="' . $nombreArchivo . '"',
        ]);
    }

    private function descargarXmlDesdeSolicitud(Solicitud $comprobante, bool $asAttachment = false)
    {
        if (!$comprobante->nombre) {
            abort(404, 'XML no disponible para esta solicitud');
        }

        $zipPath = $this->rutaZipComprobante($comprobante);

        if (!Storage::exists($zipPath)) {
            abort(404, 'Archivo ZIP no encontrado');
        }

        $zip = new ZipArchive();
        $opened = $zip->open(Storage::path($zipPath));

        if ($opened !== true) {
            abort(500, 'No se pudo abrir el archivo ZIP');
        }

        $xmlFileName = $this->buscarEntradaXmlEnZip($zip, false);

        if (!$xmlFileName) {
            $zip->close();
            abort(404, 'XML no encontrado dentro del ZIP');
        }

        $xmlContent = $zip->getFromName($xmlFileName);
        $zip->close();

        if ($xmlContent === false) {
            abort(500, 'No se pudo leer el XML');
        }

        $nombreArchivo = rtrim((string) $comprobante->nombre, '.') . '.xml';

        return $this->respuestaDescargaXml($xmlContent, $nombreArchivo, $asAttachment);
    }

    private function descargarCdrDesdeSolicitud(Solicitud $comprobante, bool $asAttachment = false)
    {
        if (!$comprobante->nombre) {
            abort(404, 'CDR no disponible para esta solicitud');
        }

        $nombreBase = rtrim((string) $comprobante->nombre, '.');
        $nombreArchivo = 'R-' . $nombreBase . '.xml';
        $xmlContent = null;

        $zipPath = $this->rutaZipComprobante($comprobante);
        if (Storage::exists($zipPath)) {
            $zip = new ZipArchive();
            if ($zip->open(Storage::path($zipPath)) === true) {
                $xmlFileName = $this->buscarEntradaXmlEnZip($zip, true);
                if ($xmlFileName) {
                    $xmlContent = $zip->getFromName($xmlFileName);
                    $nombreArchivo = basename($xmlFileName);
                }
                $zip->close();
            }
        }

        if ($xmlContent === null || $xmlContent === false) {
            $cdr = $this->obtenerCdrDesdeFacturador($comprobante);
            $xmlContent = $cdr['content'];
            $nombreArchivo = $cdr['filename'];
        }

        return $this->respuestaDescargaXml($xmlContent, $nombreArchivo, $asAttachment);
    }

    private function obtenerCdrDesdeFacturador(Solicitud $comprobante): array
    {
        if (!$comprobante->solicitud_facturador_id) {
            abort(404, 'CDR no disponible: el comprobante no tiene identificador del facturador');
        }

        $facturador = json_decode($comprobante->json_facturador);
        if (!$facturador || empty($facturador->username_wsdl) || empty($facturador->password_wsdl)) {
            abort(404, 'Facturador no configurado para descargar el CDR');
        }

        $client = new nusoap_client(config('services.facturacion.comprobantes_wsdl'));
        $result = $client->call('descargarCDR', [
            'ruc' => $facturador->username_wsdl,
            'password' => $facturador->password_wsdl,
            'json' => json_encode([
                'id_solicitud' => $comprobante->solicitud_facturador_id,
            ]),
        ]);

        if ($client->getError()) {
            abort(500, 'Error al consultar el CDR: ' . $client->getError());
        }

        $decoded = json_decode($result);
        if (!$decoded || (string) ($decoded->code ?? '') !== '0') {
            abort(404, $decoded->mensaje ?? 'CDR no disponible para este comprobante');
        }

        if (empty($decoded->file_base64)) {
            abort(404, 'CDR no disponible para este comprobante');
        }

        $zipBin = base64_decode($decoded->file_base64);
        if ($zipBin === false) {
            abort(500, 'No se pudo decodificar el CDR recibido');
        }

        $tempZip = tempnam(sys_get_temp_dir(), 'cdr_');
        file_put_contents($tempZip, $zipBin);

        $zip = new ZipArchive();
        $opened = $zip->open($tempZip);

        if ($opened !== true) {
            @unlink($tempZip);
            abort(500, 'No se pudo abrir el CDR recibido');
        }

        $xmlFileName = $this->buscarEntradaXmlEnZip($zip, true);
        if (!$xmlFileName) {
            $zip->close();
            @unlink($tempZip);
            abort(404, 'CDR no encontrado dentro del archivo recibido');
        }

        $xmlContent = $zip->getFromName($xmlFileName);
        $zip->close();
        @unlink($tempZip);

        if ($xmlContent === false) {
            abort(500, 'No se pudo leer el CDR');
        }

        return [
            'content' => $xmlContent,
            'filename' => basename($xmlFileName),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Operaciones relacionadas a la tabla 'solicitud'
        $operaciones = Operacione::where('tabla_id', 70)
            ->where('estado', true)
            ->get();

        // Usuario logueado
        $user = Auth::user();
        $persona = Persona::find($user->persona_id);

        // Parámetros de búsqueda y paginación
        $search = $request->input('search');
        $perPage = $request->input('perPage', 10);

        // Query base
        $query = Solicitud::where('sucursal_id', $persona->sucursal_id)
            ->whereNotIn('tipo_documento', ['E', 'A']); // excluir documentos de baja

        // Búsqueda
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'ILIKE', "%{$search}%")
                    ->orWhere('serie', 'ILIKE', "%{$search}%")
                    ->orWhere('correlativo', 'ILIKE', "%{$search}%")
                    ->orWhere('moneda', 'ILIKE', "%{$search}%")
                    ->orWhere('estado', 'ILIKE', "%{$search}%")
                    ->orWhere('total', 'ILIKE', "%{$search}%");
            });
        }

        // Orden y paginación
        $solicituds = $query->orderBy('fecha', 'DESC')
            ->paginate($perPage)
            ->appends([
                'search' => $search,
                'perPage' => $perPage,
            ]);

        return view('solicitud.index', compact('solicituds', 'operaciones', 'search', 'perPage'))
            ->with('i', (request()->input('page', 1) - 1) * $solicituds->perPage());
    }


    public function anulacion_boletas()
    {
        // Obtener las operaciones relacionadas con la tabla 'solicitud'
        $operaciones = Operacione::where('tabla_id', 5)->where('estado', true)->get();
        // Obtener el usuario logueado
        $user = Auth::user();

        // Obtener la persona relacionada al usuario
        $persona = Persona::find($user->persona_id);

        // Paginación de los registros de Solicitud
        $solicituds = Solicitud::where('sucursal_id', $persona->sucursal_id)->orderBy('fecha', 'DESC')->where('tipo_documento', '=', 'E')->paginate();
        $estados = [
            'Aún no declarado',
            'No registrado',
            'Declarado y activo',
            'Declarado y anulado',
            'Declarado e incorrecto',
            'Declarado sin comprobar'
        ];

        return view('solicitud.boletasAnuladas', compact('solicituds', 'operaciones', 'estados'))
            ->with('i', (request()->input('page', 1) - 1) * $solicituds->perPage());
    }
    public function anulacion_boleta_detalle($id)
    {
        try {
            $sol_anulacion = Solicitud::findOrFail($id);

            // Decodificar el JSON de la solicitud de anulación
            $data = json_decode($sol_anulacion->data, true);

            // Extraer los IDs de las solicitudes afectadas
            $detalle_ids = collect($data['detalles'])->pluck('id')->toArray();

            // Obtener las solicitudes originales anuladas
            $solicituds = Solicitud::whereIn('id', $detalle_ids)->paginate(10);

            return view('solicitud.detallesAnulacionBoletas', compact('solicituds', 'sol_anulacion'))
                ->with('i', (request()->input('page', 1) - 1) * $solicituds->perPage());
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    public function anulacion_facturas()
    {
        $operaciones = Operacione::where('tabla_id', 5)->where('estado', true)->get();
        $user = Auth::user();
        $persona = Persona::find($user->persona_id);

        $solicituds = Solicitud::where('sucursal_id', $persona->sucursal_id)
            ->where('tipo_documento', 'A') // A = anulación de factura
            ->orderBy('fecha', 'DESC')
            ->paginate();

        $estados = [
            'Aún no declarado',
            'No registrado',
            'Declarado y activo',
            'Declarado y anulado',
            'Declarado e incorrecto',
            'Declarado sin comprobar'
        ];

        return view('solicitud.facturasAnuladas', compact('solicituds', 'operaciones', 'estados'))
            ->with('i', (request()->input('page', 1) - 1) * $solicituds->perPage());
    }


    public function anulacion_factura_detalle($id)
    {
        try {
            $sol_anulacion = Solicitud::findOrFail($id);

            // Decodificar el JSON de la solicitud de anulación
            $data = json_decode($sol_anulacion->data, true);

            // Extraer los IDs de las solicitudes afectadas
            $detalle_ids = collect($data['detalles'])->pluck('id')->toArray();

            // Obtener las solicitudes originales anuladas (sendBill tipo 'F')
            $solicituds = Solicitud::whereIn('id', $detalle_ids)->paginate(10);

            return view('solicitud.detallesAnulacionBoletas', compact('solicituds', 'sol_anulacion'))
                ->with('i', (request()->input('page', 1) - 1) * $solicituds->perPage());
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }


    public function reporte()
    {
        // Obtener el usuario logueado
        $user = Auth::user();

        // Obtener la persona relacionada al usuario
        $persona = Persona::find($user->persona_id);
        $tipodocumentos = Tipodocumento::where('tipomovimiento_id', 2)->get();

        // Obtener el mes y año actual
        $inicioMes = now()->startOfMonth(); // Primer día del mes
        $finMes = now()->endOfMonth(); // Último día del mes

        // Filtrar las solicitudes por la sucursal del usuario y el mes actual
        $solicituds = Solicitud::where('sucursal_id', $persona->sucursal_id)
            ->whereNotIn('tipo_documento', ['E', 'A', 'N']) // Excluir boletas anuladas, facturas anuladas y notas de crédito
            ->orderBy('fecha', 'DESC')
            ->paginate();

        $estados = [
            'Aún no declarado',
            'No registrado',
            'Declarado y activo',
            'Declarado y anulado',
            'Declarado e incorrecto',
            'Declarado sin comprobar'
        ];

        return view('solicitud.reporte', compact('solicituds', 'tipodocumentos', 'estados'))
            ->with('i', (request()->input('page', 1) - 1) * $solicituds->perPage());
    }
    public function generarPDF($tipo, $situacion, $fecha_inicio, $fecha_fin)
    {
        set_time_limit(600);
        Config::set('timeout', 600);
        ini_set('memory_limit', '2048M');
        // Mapeo de los estados a sus letras
        $estado_map = [
            'Aún no declarado' => 'R',
            'No registrado' => 'E',
            'Declarado y activo' => 'M',
            'Declarado y anulado' => 'B',
            'Declarado e incorrecto' => 'I',
            'Declarado sin comprobar' => 'S',
            'No tributable' => 'T',
        ];

        // Obtener la letra correspondiente al estado recibido, si es 0 no se filtra por estado
        $situacion_letra = $situacion != 0 ? ($estado_map[$situacion] ?? null) : null;

        if ($situacion != 0 && !$situacion_letra) {
            return response()->json(['error' => 'Estado no válido'], 400);
        }

        // Determinar el tipo de comprobante, si es 0 no se filtra por tipo
        $comprobante = null;
        if ($tipo == 4) {
            $comprobante = 'B';
        } elseif ($tipo == 10) {
            $comprobante = 'F';
        } elseif ($tipo != 0) {
            return response()->json(['error' => 'Tipo de comprobante no válido'], 400);
        }

        $user = Auth::user();
        $persona = Persona::find($user->persona_id);
        $sucursal = Sucursal::find($persona->sucursal_id);

        // Construir la consulta dinámicamente
        $fecha_inicio = Carbon::parse($fecha_inicio)->startOfDay(); // Empieza a las 00:00:00
        $fecha_fin = Carbon::parse($fecha_fin)->endOfDay(); // Termina a las 23:59:59

        // Filtrar las solicitudes dentro del rango de fechas
        $solicitudes = Solicitud::where('sucursal_id', $persona->sucursal_id)
            ->whereNotIn('tipo_documento', ['E', 'A', 'N'])
            ->whereBetween('fecha', [$fecha_inicio, $fecha_fin])
            ->orderBy('fecha', 'DESC');

        // Filtrar por tipo de comprobante, si es necesario
        if ($comprobante) {
            $solicitudes->where('tipo_documento', $comprobante);
        }

        // Filtrar por estado si es necesario
        if ($situacion_letra) {
            $solicitudes->where('estado', $situacion_letra);
        }

        // Obtener las solicitudes filtradas
        $solicitudes = $solicitudes->get();



        $pdf = Pdf::loadView('solicitud.comprobante', compact('solicitudes', 'sucursal', 'fecha_inicio', 'fecha_fin'))
            ->setPaper('a4', 'landscape');

        return $pdf->stream("REPORTE.pdf");
    }


    public function generarExcel($tipo, $situacion, $fecha_inicio, $fecha_fin)
    {
        // Obtener solicitudes filtradas (mismo código que antes)
        $estado_map = [
            'Aún no declarado' => 'R',
            'No registrado' => 'E',
            'Declarado y activo' => 'M',
            'Declarado y anulado' => 'B',
            'Declarado e incorrecto' => 'I',
            'Declarado sin comprobar' => 'S',
            'No tributable' => 'T',
        ];

        $situacion_letra = $situacion != 0 ? ($estado_map[$situacion] ?? null) : null;

        if ($situacion != 0 && !$situacion_letra) {
            return response()->json(['error' => 'Estado no válido'], 400);
        }

        $comprobante = $tipo == 4 ? 'B' : ($tipo == 10 ? 'F' : null);
        if ($tipo != 0 && !$comprobante) {
            return response()->json(['error' => 'Tipo de comprobante no válido'], 400);
        }

        $user = Auth::user();
        $persona = Persona::find($user->persona_id);
        $sucursal = Sucursal::find($persona->sucursal_id);

        $solicitudes = Solicitud::where('sucursal_id', $persona->sucursal_id)
            ->whereNotIn('tipo_documento', ['E', 'A', 'N']) // Excluir boletas anuladas, facturas anuladas y notas de crédito
            ->whereBetween('fecha', [$fecha_inicio, $fecha_fin]) // <- Aquí el filtro por fechas
            ->orderBy('fecha', 'DESC');
        if ($comprobante) {
            $solicitudes->where('tipo_documento', $comprobante);
        }

        if ($situacion_letra) {
            $solicitudes->where('estado', $situacion_letra);
        }

        $solicitudes = $solicitudes->get();

        return Excel::download(new SolicitudExport($solicitudes, $fecha_inicio, $fecha_fin), 'REPORTE.xlsx');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $solicitud = new Solicitud();
        return view('solicitud.create', compact('solicitud'));
    }
    public function enviarCorreo(Request $request)
    {
        $request->validate([
            'correo' => 'required|email', // Validar correo electrónico
            'venta_id' => 'required|numeric', // Validar que el ID de venta esté presente
        ]);

        $correo = $request->input('correo');
        $venta_id = $request->input('venta_id');
        $subject = 'Comprobante de Venta';
        $body = 'Evidencia comprobante electrónico';




        // Generar el PDF directamente en este controlador
        $pdf = app(MovventumController::class)->generarPDF($venta_id, true);
        $pdf = mb_convert_encoding($pdf, 'UTF-8', 'ISO-8859-1');
        // Preparar el archivo adjunto
        $fileName = "comprobante-{$venta_id}.pdf";

        $fields = [
            'token' => config('services.comprobantes_mail.token'),
            "to" => [$correo],
            "bcc" => [],
            "cc" => [],
            "subject" => $subject,
            "body" => $body,
            "attachments" => [
                [
                    "name_file" => $fileName,
                    "content_file" => $pdf,
                    "content_type" => "application/pdf",
                ],
            ],
        ];
        // dd($fields);
        // Enviar el correo con los datos preparados
        $client = new \GuzzleHttp\Client();
        try {
            $response = $client->post("https://facturae-garzasoft.com/facturacion/enviaEmail/envia_correo_json.php", [
                'json' => $fields,
            ]);
            // dd($response->getBody());

            if ($response->getStatusCode() !== 200) {
                return response()->json(['message' => 'Error al enviar correo'], $response->getStatusCode());
            }

            $datos = json_decode($response->getBody());
            if ($datos->code != 0) {
                return response()->json(['message' => 'Error al enviar correo'], 500);
            }

            // Retornar respuesta de éxito
            return response()->json([
                'success' => true,
                'message' => 'Correo enviado exitosamente.',
                'redirect_url' => route('solicitud.index'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error inesperado: ' . $e->getMessage(),
            ], 500);
        }
    }


    public function enviarCorreoModal($id)
    {
        $solicitud = Solicitud::find($id);
        return view('solicitud.modalcorreo', compact('solicitud'));
    }
    public function crearNotaCredito(int $id)
    {
        $branchId = (int) Auth::user()->persona?->sucursal_id;
        abort_unless($branchId, 403);
        $comprobante = Solicitud::where('sucursal_id', $branchId)
            ->whereIn('tipo_documento', ['B', 'F'])->whereIn('estado', ['M', 'T'])->findOrFail($id);
        abort_unless($comprobante->movimiento && $comprobante->movimiento->getRawOriginal('situacion') === 'A', 422, 'La venta asociada está anulada.');
        abort_unless(!$this->tieneNotaCreditoCompleta($comprobante), 409, 'Este comprobante ya tiene una nota de crédito total registrada.');

        $tiposNota = [
            '01' => 'Anulación de la operación',
            '06' => 'Devolución total',
        ];

        return view('solicitud.nota-credito', compact('comprobante', 'tiposNota'));
    }

    public function guardarNotaCredito(Request $request, int $id)
    {
        $data = $request->validate([
            'tipo_nota' => 'required|in:01,06',
            'motivo' => 'required|string|min:5|max:500',
        ]);
        $branchId = (int) Auth::user()->persona?->sucursal_id;
        abort_unless($branchId, 403);

        DB::beginTransaction();
        try {
            $comprobante = Solicitud::where('sucursal_id', $branchId)->whereIn('tipo_documento', ['B', 'F'])
                ->whereIn('estado', ['M', 'T'])->lockForUpdate()->findOrFail($id);
            if (!$comprobante->movimiento || $comprobante->movimiento->getRawOriginal('situacion') !== 'A') {
                DB::rollBack();
                return back()->withErrors(['comprobante' => 'La venta asociada está anulada.']);
            }
            if ($this->tieneNotaCreditoCompleta($comprobante)) {
                DB::rollBack();
                return back()->withErrors(['comprobante' => 'Este comprobante ya tiene una nota de crédito total registrada.']);
            }

            $facturador = json_decode((string) $comprobante->json_facturador, true) ?: [];
            if (empty($facturador['username_wsdl']) || empty($facturador['password_wsdl'])) {
                DB::rollBack();
                return back()->withErrors(['facturador' => 'El facturador de este comprobante no tiene credenciales WSDL.']);
            }
            $datosOriginales = json_decode((string) $comprobante->data, true);
            if (!is_array($datosOriginales) || empty($datosOriginales['detalles'])) {
                DB::rollBack();
                return back()->withErrors(['comprobante' => 'No se encontraron los datos y detalles originales para emitir la nota.']);
            }

            // Serializa el correlativo de notas por facturador y conserva la serie del documento afectado.
            $facturadorDb = Facturador::whereKey($comprobante->facturador_id)->where('sucursal_id', $branchId)->lockForUpdate()->first();
            if (!$facturadorDb) {
                DB::rollBack();
                return back()->withErrors(['facturador' => 'No se encontró el facturador de la sucursal.']);
            }
            $correlativo = ((int) Solicitud::where('sucursal_id', $branchId)->where('tipo_documento', 'N')
                ->where('serie', $comprobante->serie)->max('correlativo')) + 1;
            $serieNotaCompleta = $comprobante->tipo_documento . $comprobante->serie;
            $numeroNota = $serieNotaCompleta . '-' . str_pad((string) $correlativo, 8, '0', STR_PAD_LEFT);
            $fechaEmision = now();
            $tipoSunatAfectado = $comprobante->tipo_documento === 'F' ? '01' : '03';

            $payload = $datosOriginales;
            unset($payload['numerofactura'], $payload['numeroboleta']);
            $payload['numeronotacredito'] = $numeroNota;
            $payload['fechaemision'] = $fechaEmision->toDateString();
            $payload['horaemision'] = $fechaEmision->format('H:i:s');
            $payload['tipo_nota'] = $data['tipo_nota'];
            $payload['motivo'] = trim($data['motivo']);
            $payload['documento_afectado'] = [
                'solicitud_id' => $comprobante->id,
                'tipo_documento' => $tipoSunatAfectado,
                'tipo_documento_pulso' => $comprobante->tipo_documento,
                'serie' => $comprobante->serie,
                'correlativo' => (string) $comprobante->correlativo,
                'numero' => $comprobante->serie . '-' . str_pad((string) $comprobante->correlativo, 8, '0', STR_PAD_LEFT),
            ];
            $payload['total'] = (float) $comprobante->total;

            $personaJson = json_decode((string) $comprobante->json_persona, true) ?: [];
            $requestData = [
                'token' => '',
                'serienotacredito' => $comprobante->serie,
                'serienotacredito_completa' => $serieNotaCompleta,
                'correlativonotacredito' => $correlativo,
                'doc' => $datosOriginales['ruc'] ?? $datosOriginales['dni'] ?? '',
                'nombre' => $datosOriginales['usuario'] ?? '',
                'direccion' => $personaJson['direccion'] ?? '',
                'total' => (float) $comprobante->total,
                'comprobante' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ];

            $nota = Solicitud::create([
                'tipo_solicitud' => 'sendBill',
                'data' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'fecha' => $fechaEmision,
                'estado' => 'E',
                'total' => $comprobante->total,
                'moneda' => $comprobante->moneda,
                'tipo_documento' => 'N',
                'serie' => $comprobante->serie,
                'correlativo' => $correlativo,
                'persona_id' => $comprobante->persona_id,
                'json_persona' => $comprobante->json_persona,
                'sucursal_id' => $branchId,
                'json_sucursal' => $comprobante->json_sucursal,
                'facturador_id' => $comprobante->facturador_id,
                'json_facturador' => $comprobante->json_facturador,
                'movimiento_id' => $comprobante->movimiento_id,
            ]);

            $client = new nusoap_client(config('services.facturacion.nota_credito_wsdl'));
            $result = $client->call('enviarNotaCredito', [
                'ruc' => $facturador['username_wsdl'],
                'password' => $facturador['password_wsdl'],
                'json' => json_encode($requestData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ]);
            if (!$client->fault && $client->getError()) {
                // Un error de transporte no confirma si el proveedor recibió el documento.
                // Se conserva como pendiente para que no se emita otro crédito duplicado.
                $nota->update(['estado' => 'R', 'error_text' => mb_substr('No se pudo confirmar la respuesta del facturador. Verifica y sincroniza antes de volver a emitir. '.$client->getError(), 0, 10000)]);
                DB::commit();
                return back()->withErrors(['facturador' => 'El facturador no confirmó la recepción. La solicitud quedó pendiente; sincroniza o consulta al proveedor antes de volver a emitir.']);
            }
            $decoded = is_string($result) ? json_decode($result, true) : null;

            if ($client->fault || $client->getError() || !is_array($decoded) || (string) ($decoded['code'] ?? '') !== '0') {
                $error = $client->fault
                    ? 'El servicio del facturador rechazó la solicitud SOAP.'
                    : ($client->getError() ?: ($decoded['mensaje'] ?? 'Respuesta no reconocida del servicio de notas de crédito.'));
                $nota->update(['estado' => 'E', 'error_text' => mb_substr((string) $error, 0, 10000)]);
                DB::commit();
                return back()->withErrors(['facturador' => $error])->withInput();
            }

            if (!empty($decoded['fileZIPBASE64']) && !empty($decoded['nombre_documento'])) {
                $zip = base64_decode($decoded['fileZIPBASE64'], true);
                if ($zip !== false) {
                    Storage::put('ficheros/sucursal_' . $branchId . '/' . $decoded['nombre_documento'] . '.zip', $zip);
                }
            }
            $nota->update([
                'estado' => 'R',
                'fechaenvio' => now(),
                'solicitud_facturador_id' => $decoded['id_solicitud'] ?? null,
                'nombre' => $decoded['nombre_documento'] ?? null,
                'error_text' => null,
            ]);
            DB::commit();

            return redirect()->route('solicitud.index')->with('success', $decoded['mensaje'] ?? 'Nota de crédito enviada al facturador.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error al emitir nota de crédito', ['solicitud_id' => $id, 'error' => $e->getMessage()]);
            return back()->withErrors(['facturador' => 'No se pudo emitir la nota de crédito: ' . $e->getMessage()])->withInput();
        }
    }

    private function tieneNotaCreditoCompleta(Solicitud $comprobante): bool
    {
        return Solicitud::where('sucursal_id', $comprobante->sucursal_id)
            ->where('tipo_documento', 'N')
            ->whereRaw("data->'documento_afectado'->>'solicitud_id' = ?", [(string) $comprobante->id])
            ->whereIn('estado', ['R', 'M', 'T'])
            ->exists();
    }

    public function modalcancell_boletas()
    {
        $sucursal_id = Auth::user()->persona->sucursal_id;

        $solicitudes = Solicitud::where('sucursal_id', $sucursal_id)
            ->where('tipo_documento', 'B')
            ->whereIn('estado', ['M', 'T']) // Boletas en estado M o T
            ->get();

        return view('solicitud.modalanularboletas');
    }


    public function modalcancell_facturas()
    {
        $sucursal_id = Auth::user()->persona->sucursal_id;

        $solicitudes = Solicitud::where('sucursal_id', $sucursal_id)
            ->where('tipo_documento', 'F')
            ->whereIn('estado', ['M', 'T']) // Facturas en estado M o T
            ->get();

        return view('solicitud.modalanularfacturas');
    }

    public function cancell_facturas(Request $request)
    {
        try {
            $sucursal_id = Auth::user()->persona->sucursal_id;
            $ids = $request->input('solicitud_id', []);
            $motivos = $request->input('motivos', []);

            if (empty($ids)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se seleccionó ninguna factura para anular.',
                ], 400);
            }

            $solicitudes = DB::table('solicitud')
                ->where('sucursal_id', $sucursal_id)
                ->where('tipo_documento', 'F') // Facturas a anular
                ->whereIn('id', $ids)
                ->get()
                ->groupBy(function ($s) {
                    return (new Carbon($s->fecha))->toDateString() . '-' . $s->moneda;
                });

            $resumenes = [];

            foreach ($solicitudes as $grupo) {
                $fecha_referencia = (new Carbon($grupo->first()->fecha))->toDateString();
                $fecha_emision = today()->toDateString();
                $serie = today()->format('Ymd');
                $correlativo = str_pad(
                    DB::table('solicitud')
                        ->where('sucursal_id', $sucursal_id)
                        ->where('serie', $serie)
                        ->where('tipo_documento', 'A')
                        ->count() + 1,
                    5,
                    '0',
                    STR_PAD_LEFT
                );

                $numerobaja = $serie . '-' . $correlativo;

                $detalles = $grupo->map(function ($detalle) use ($motivos) {
                    return [
                        'id' => $detalle->id,
                        'idservidor' => $detalle->solicitud_facturador_id,
                        'tipo' => '01', // Código SUNAT para factura
                        'numero' => substr($detalle->serie, 1),
                        'correlativo' => $detalle->correlativo,
                        'motivo' => $motivos[$detalle->id] ?? 'Error en comprobante',
                    ];
                })->values()->all();

                $resumen = [
                    'numerobaja' => $numerobaja,
                    'fechaemision' => $fecha_emision,
                    'fechareferencia' => $fecha_referencia,
                    'detalles' => $detalles,
                ];

                // Guardar resumen en base de datos y obtener ID
                $id = DB::table('solicitud')->insertGetId([
                    'tipo_solicitud' => 'sendSummary',
                    'data' => json_encode($resumen),
                    'fecha' => now(),
                    'estado' => '',
                    'moneda' => $grupo->first()->moneda,
                    'total' => null,
                    'tipo_documento' => 'A', // Anulación factura
                    'serie' => $serie,
                    'correlativo' => $correlativo,
                    'persona_id' => null,
                    'json_persona' => null,
                    'sucursal_id' => $sucursal_id,
                    'json_sucursal' => $grupo->first()->json_sucursal,
                    'facturador_id' => $grupo->first()->facturador_id,
                    'json_facturador' => $grupo->first()->json_facturador,
                ]);

                $solicitud = DB::table('solicitud')->find($id);
                $facturador = json_decode($grupo->first()->json_facturador);
                $user_wsdl = $facturador->username_wsdl;
                $pass_wsdl = $facturador->password_wsdl;

                $token = ''; // Asigna el token si aplica

                $datos_enviar = [
                    'token' => $token,
                    'seriebaja' => $serie,
                    'correlativobaja' => $correlativo,
                    'comprobante' => json_encode($resumen),
                ];

                $json_enviar = json_encode($datos_enviar);

                $datos = [
                    'ruc' => $user_wsdl,
                    'password' => $pass_wsdl,
                    'json' => $json_enviar,
                ];

                $url = config('services.facturacion.bajas_wsdl');

                // Enviar petición POST al WebService
                $client = new \GuzzleHttp\Client();
                $response = $client->post($url, [
                    'form_params' => $datos,
                ]);

                if ($response->getStatusCode() == 200) {
                    // Actualizar estado solicitud a enviado
                    DB::table('solicitud')->where('id', $id)->update(['estado' => 'E']);
                } else {
                    // Actualizar estado con error
                    DB::table('solicitud')->where('id', $id)->update(['estado' => 'X']);
                }

                $resumenes[] = [
                    'solicitud_id' => $id,
                    'numero_baja' => $numerobaja,
                    'estado_envio' => $response->getStatusCode() == 200 ? 'Enviado' : 'Error',
                ];
            }

            return response()->json([
                'success' => true,
                'message' => 'Resumen de baja de facturas generado y enviado correctamente.',
                'data' => $resumenes,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }



    public function enviarWhatsAppModal($id)
    {

        $solicitud =  Solicitud::find($id);


        return view('solicitud.modalwhatsapp', compact('solicitud'));
    }
    public function enviarWhatsApp(Request $request)
    {
        $request->validate([
            'celular' => 'required|numeric',
            'sucursal_id' => 'required|numeric',
            'id' => 'required|numeric',
        ]);

        $sucursal_id = $request->input('sucursal_id');
        $id = $request->input('id');
        $telefono = $request->input('celular');

        $comprobante = Solicitud::with('movimiento.persona', 'movimiento.movventa')
            ->where('sucursal_id', $sucursal_id)->find($id);

        if (!$comprobante) {
            return response()->json(['success' => false, 'errors' => 'Comprobante no encontrado'], 404);
        }

        $this->asegurarPublicNameComprobante($comprobante);

        $facturador = json_decode($comprobante->json_facturador);
        $sucursal = json_decode($comprobante->json_sucursal);
        $persona = $comprobante->movimiento->Persona;

        $razonsocial = $facturador->username_wsdl . ' - ' . $sucursal->razonsocial;
        $cliente = $persona->nrodoc . ' - ' . ' ' . $persona->nombres . $persona->apellidos;
        $numero = $comprobante->tipo_documento . $comprobante->serie . '-' . str_pad($comprobante->correlativo, 8, '0', STR_PAD_LEFT);
        $archivo = $comprobante->nombre;
        $nombrecomercial = $sucursal->nombrecomercial;
        $firma = 'PULSO+';
        $downloadBillUrl = $comprobante->public_name . '/xml';

        // ✅ Generar el PDF como binario (sin codificar)
        $ventaId = $comprobante->movimiento->movventa->id;
        $pdfContent = app(\App\Http\Controllers\MovventumController::class)->generarPDF($ventaId, true);

        // Crear stream binario
        $stream = fopen('php://temp', 'r+');
        if (!$stream) {
            return response()->json(['success' => false, 'errors' => 'No se pudo crear el archivo temporal'], 500);
        }
        fwrite($stream, $pdfContent);
        rewind($stream);

        $auth_token = env('META_AUTH_TOKEN');
        $from = "202127742990342";
        $apiVersion = "v19.0";

        try {
            $client = new Client();

            // Subir archivo
            $uploadResponse = $client->request('POST', "https://graph.facebook.com/$apiVersion/$from/media", [
                'headers' => [
                    'Authorization' => "Bearer $auth_token"
                ],
                'multipart' => [
                    [
                        'name' => 'file',
                        'contents' => $stream,
                        'filename' => $archivo . '.pdf',
                        'headers' => [
                            'Content-Type' => 'application/pdf'
                        ]
                    ],
                    [
                        'name' => 'messaging_product',
                        'contents' => 'whatsapp'
                    ]
                ]
            ]);

            if (is_resource($stream)) {
                fclose($stream);
            }

            $mediaId = json_decode($uploadResponse->getBody(), true)['id'];

            $fields = [
                'messaging_product' => "whatsapp",
                'to' => $telefono,
                'type' => 'template',
                'template' => [
                    'name' => 'pulso_pdf_invoice',
                    'language' => ['code' => 'es'],
                    'components' => [
                        ['type' => 'header', 'parameters' => [
                            ['type' => 'document', 'document' => [
                                'id' => $mediaId,
                                'filename' => $archivo . '.pdf'
                            ]]
                        ]],
                        ['type' => 'body', 'parameters' => [
                            ['type' => 'text', 'text' => $cliente],
                            ['type' => 'text', 'text' => $numero],
                            ['type' => 'text', 'text' => $razonsocial],
                            ['type' => 'text', 'text' => $nombrecomercial],
                            ['type' => 'text', 'text' => $firma],
                        ]],
                        ['type' => 'button', 'sub_type' => 'url', 'index' => 0, 'parameters' => [
                            ['type' => 'text', 'text' => $downloadBillUrl]
                        ]]
                    ],
                ],
            ];

            $sendResponse = $client->request('POST', "https://graph.facebook.com/$apiVersion/$from/messages", [
                'headers' => [
                    'Authorization' => "Bearer $auth_token",
                    'Content-Type' => 'application/json',
                ],
                'json' => $fields,
            ]);

            $data = json_decode($sendResponse->getBody(), true);
            if (empty($data['messages']) || ($data['messages'][0]['message_status'] ?? '') !== 'accepted') {
                return response()->json(['success' => false, 'errors' => 'Mensaje no aceptado por WhatsApp'], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Mensaje enviado exitosamente por WhatsApp.',
                'redirect_url' => route('solicitud.index'),
            ]);
        } catch (\Exception $e) {
            if (is_resource($stream)) {
                fclose($stream);
            }

            return response()->json([
                'success' => false,
                'errors' => 'Error al enviar por WhatsApp: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SolicitudRequest $request)
    {
        try {
            // Crear una nueva Solicitud
            $solicitud = Solicitud::create($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Solicitud creada exitosamente.',
                'solicitud' => $solicitud,
                'redirect_url' => route('solicituds.index')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $solicitud = Solicitud::find($id);
        return view('solicitud.edit', compact('solicitud'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SolicitudRequest $request, Solicitud $solicitud)
    {
        try {
            // Actualizar el registro de Solicitud
            $solicitud->update($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Solicitud actualizada exitosamente.',
                'solicitud' => $solicitud,
                'redirect_url' => route('solicituds.index')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            Solicitud::find($id)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Solicitud eliminada correctamente.',
                'redirect_url' => route('solicituds.index')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Activar una solicitud.
     */
    public function activar($id)
    {
        try {
            $solicitud = Solicitud::find($id);
            $solicitud->update(['situacion' => true]);

            // Reutilizar la lógica del index
            return redirect()->route('solicitud.index')
                ->with('success', 'La solicitud fue activada correctamente.');
        } catch (\Exception $e) {
            return redirect()->route('solicitud.index')
                ->withErrors(['error' => 'Ocurrió un error: ' . $e->getMessage()]);
        }
    }


    public function sync()
    {
        try {
            $sucursal_id = Auth::user()->persona->sucursal_id;

            // Inicializar las variables
            $fecha_inicio = $fecha_inicio ?? '';
            $fecha_fin = $fecha_fin ?? '';

            $fecha_inicio = ($fecha_inicio != '') ? $fecha_inicio : Carbon::now()->startOfMonth();
            $fecha_fin = ($fecha_fin != '') ? $fecha_fin : Carbon::today();

            $tieneComprobantes = DB::table('solicitud')->where('sucursal_id', $sucursal_id)->count() > 0;
            $respuestasServidor = []; // Aquí almacenaremos las respuestas del servidor

            if ($tieneComprobantes) {
                $facturadores = DB::table('solicitud')
                    ->where('sucursal_id', $sucursal_id)
                    ->selectRaw('facturador_id, json_facturador::text')
                    ->groupByRaw('facturador_id, json_facturador::text')
                    ->get();

                $facturadores->each(function ($facturador) use ($fecha_inicio, $fecha_fin, $sucursal_id, &$respuestasServidor) {
                    $facturador = json_decode($facturador->json_facturador);
                    $datos_sincronizacion = null;

                    $user_wsdl = $facturador->username_wsdl;
                    $pass_wsdl = $facturador->password_wsdl;
                    $datos_enviar = [
                        'fechainicio' => $fecha_inicio,
                        'fechafinal' => $fecha_fin,
                    ];
                    $datos_enviar = json_encode($datos_enviar);

                    $datos_sincronizacion = [
                        'ruc' => $user_wsdl,
                        'password' => $pass_wsdl,
                        'json' => $datos_enviar,
                    ];

                    $comprobantes = [];
                    if ($datos_sincronizacion) {
                        $url = config('services.facturacion.comprobantes_wsdl');
                        $accion = 'descargar';

                        $client = new nusoap_client($url);
                        $result = $client->call($accion, $datos_sincronizacion);

                        if ($client->fault) {
                            abort(500, json_encode($result));
                        } else if ($client->getError()) {
                            abort(500, $client->getError());
                        } else {
                            $result = json_decode($result);
                            $respuestasServidor[] = $result; // Guardar respuesta del servidor
                            $comprobantes = $result->comprobantes;
                        }
                    }

                    $facturador_id = $facturador->id;
                    foreach ($comprobantes as $comprobante) {
                        $solicitud = DB::table('solicitud')->where('sucursal_id', $sucursal_id)->where('solicitud_facturador_id', $comprobante->id_solicitud)->first();
                        $estado_local = 'E';

                        // Identificador de solicitud en el facturador (servidor)
                        if ($solicitud) $solicitud_facturador_id = $solicitud->solicitud_facturador_id;
                        else {
                            // Buscar por json de datos
                            $data = $comprobante->data_solicitud;
                            $solicitud_por_data = DB::table('solicitud')->where('sucursal_id', $sucursal_id)->where(function ($query) use ($data) {
                                $query->whereRaw("data::text = ?", [$data])->orWhereRaw("data::jsonb - 'horaemision'::text = ?", [$data]);
                            })->where('facturador_id', $facturador_id)->where(function ($query) {
                                $query->where('estado', 'E')->orWhere('estado', 'R');
                            })->first();

                            // Si no existe el comprobante en el sistema, omitir
                            if (!$solicitud_por_data) continue;

                            // Asignar identificador registrado en sistema
                            $solicitud_facturador_id = $solicitud_por_data->solicitud_facturador_id;

                            // Si no tiene identificador, asignar el enviado por servidor
                            if (!$solicitud_facturador_id) {
                                $solicitud = DB::table('solicitud')->where('id', $solicitud_por_data->id)
                                    ->update([
                                        'solicitud_facturador_id' => $comprobante->id_solicitud,
                                    ]);
                                $solicitud = DB::table('solicitud')->find($solicitud_por_data->id);
                                $solicitud_facturador_id = $solicitud->solicitud_facturador_id;
                            }
                        }

                        // Nuevo estado de solicitud
                        switch ($comprobante->estado_solicitud) {
                            case 'C':
                                $estado_local = "T";
                                if ($comprobante->tipo_documento == "B") $estado_local = "M";
                                else if ($comprobante->tipo_documento == "E" || $comprobante->tipo_documento == "A") $estado_local = "U";
                                break;
                            case 'I':
                                $estado_local = "I";
                                if ($comprobante->tipo_documento == "E" || $comprobante->tipo_documento == "A") $estado_local = "V";
                                break;
                            default:
                                $estado_local = $comprobante->estado_solicitud;
                                break;
                        }

                        // Actualizar solicitud
                        if ($solicitud || $solicitud_por_data) {
                            if ($comprobante->estado_solicitud == "C") { // Correcto
                                $nombre = $comprobante->nombre_solicitud != '' ? $comprobante->nombre_solicitud : null;
                                $fechaenvio = $comprobante->fechahora_enviosunat != '' ? $comprobante->fechahora_enviosunat : null;
                                $fecharespuesta = $comprobante->fechahora_respuestasunat != '' ? $comprobante->fechahora_respuestasunat : null;
                                $error_text = $comprobante->error_text != '' ? $comprobante->error_text : null;

                                $solicitud = DB::table('solicitud')->where(array(
                                    'solicitud_facturador_id' => $solicitud_facturador_id
                                ))->update(array(
                                    'estado' => $estado_local,
                                    'tipo_documento' => $comprobante->tipo_documento,
                                    'nombre' => $nombre,
                                    'fechaenvio' => $fechaenvio,
                                    'fecharespuesta' => $fecharespuesta,
                                    'error_text' => $error_text
                                ));
                            } elseif ($comprobante->estado_solicitud == "B") { // Baja
                                $nombre = $comprobante->nombre_solicitud != '' ? $comprobante->nombre_solicitud : null;

                                $solicitud = DB::table('solicitud')->where(array(
                                    'solicitud_facturador_id' => $solicitud_facturador_id
                                ))->update(array(
                                    'estado' => $estado_local,
                                    'tipo_documento' => $comprobante->tipo_documento,
                                    'nombre' => $nombre,
                                ));
                            } elseif ($comprobante->estado_solicitud == "I") { // Incorrecto
                                $nombre = $comprobante->nombre_solicitud != '' ? $comprobante->nombre_solicitud : null;
                                $fechaenvio = $comprobante->fechahora_enviosunat != '' ? $comprobante->fechahora_enviosunat : null;
                                $fecharespuesta = $comprobante->fechahora_respuestasunat != '' ? $comprobante->fechahora_respuestasunat : null;
                                $error_text = $comprobante->error_text != '' ? $comprobante->error_text : null;

                                $solicitud = DB::table('solicitud')->where(array(
                                    'solicitud_facturador_id' => $solicitud_facturador_id
                                ))->update(array(
                                    'estado' => $estado_local,
                                    'tipo_documento' => $comprobante->tipo_documento,
                                    'nombre' => $nombre,
                                    'fechaenvio' => $fechaenvio,
                                    'fecharespuesta' => $fecharespuesta,
                                    'error_text' => $error_text,
                                    'error_code' => $comprobante->error_code
                                ));
                            } elseif ($comprobante->estado_solicitud == "T") { // Ticket
                                $nombre = $comprobante->nombre_solicitud != '' ? $comprobante->nombre_solicitud : null;

                                $solicitud = DB::table('solicitud')->where(array(
                                    'solicitud_facturador_id' => $solicitud_facturador_id
                                ))->update(array(
                                    'estado' => $estado_local,
                                    'tipo_documento' => $comprobante->tipo_documento,
                                    'nombre' => $nombre,
                                    'ticket_solicitud' => $comprobante->ticket_solicitud
                                ));
                            } elseif ($comprobante->estado_solicitud == 'E') { // Problema con comprobante
                                $estado_local = 'R';
                                $nombre = $comprobante->nombre_solicitud != '' ? $comprobante->nombre_solicitud : null;
                                $fechaenvio = $comprobante->fechahora_enviosunat != '' ? $comprobante->fechahora_enviosunat : null;
                                $fecharespuesta = $comprobante->fechahora_respuestasunat != '' ? $comprobante->fechahora_respuestasunat : null;
                                $error_text = $comprobante->error_text != '' ? $comprobante->error_text : null;

                                $solicitud = DB::table('solicitud')->where(array(
                                    'solicitud_facturador_id' => $solicitud_facturador_id
                                ))->update(array(
                                    'estado' => $estado_local,
                                    'tipo_documento' => $comprobante->tipo_documento,
                                    'nombre' => $nombre,
                                    'fechaenvio' => $fechaenvio,
                                    'fecharespuesta' => $fecharespuesta,
                                    'error_text' => $error_text
                                ));
                            } else { // Otro estado
                                $nombre = $comprobante->nombre_solicitud != '' ? $comprobante->nombre_solicitud : null;
                                $fechaenvio = $comprobante->fechahora_enviosunat != '' ? $comprobante->fechahora_enviosunat : null;
                                $fecharespuesta = $comprobante->fechahora_respuestasunat != '' ? $comprobante->fechahora_respuestasunat : null;
                                $error_text = $comprobante->error_text != '' ? $comprobante->error_text : null;

                                $solicitud = DB::table('solicitud')->where(array(
                                    'solicitud_facturador_id' => $solicitud_facturador_id
                                ))->update(array(
                                    'estado' => $estado_local,
                                    'tipo_documento' => $comprobante->tipo_documento,
                                    'nombre' => $nombre,
                                    'fechaenvio' => $fechaenvio,
                                    'fecharespuesta' => $fecharespuesta,
                                    'error_text' => $error_text
                                ));
                            }
                        }
                    }
                });
            }

            //Reutilizar la lógica del index
            return response()->json([
                'success' => true,
                'message' => 'Sincronización completada con éxito.',
                'data' => $respuestasServidor,
                'redirect_url' => route('solicitud.index')

            ]);
            // return $this->index();
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage(),
            ], 500);
        }
    }


    public function send_pending_boletas()
    {
        try {
            $facturadorController = new FacturadorController();
            $sucursal_id = Auth::user()->persona->sucursal_id;

            $solicitudes = Solicitud::where('sucursal_id', $sucursal_id)
                ->where('estado', 'E')
                ->whereHas('movimiento', function ($query) {
                    $query->where('tipodocumento_id', 4);
                })
                ->get();

            $respuestasServidor = []; // Almacena todas las respuestas del servidor

            foreach ($solicitudes as $sol) {
                $movventa = $sol->movimiento->movventa;
                $sol = $this->actualizarBoletaPendienteConSerieCajaActiva($sol, $movventa, $sucursal_id);
                $movventa->refresh();
                $movventa->load('movimiento');

                $detalles = DetMovventum::where('movventa_id', $movventa->id)->get();
                $detalles = json_decode($detalles, true);
                $persona = Persona::find($movventa->movimiento->persona_id);
                $movcaja = Movcaja::find($movventa->movcaja->id);
                $detallesMovCaja = DetMovcaja::where('movcaja_id', $movcaja->id)->get();
                // **Enviar la solicitud al facturador**
                $respuesta = $facturadorController->enviarFacturacionElectronica(
                    $movventa,
                    collect($detalles),
                    [],
                    $persona,
                    Sucursal::find($sucursal_id),
                    Facturador::where('sucursal_id', $sucursal_id)->first(),
                    $movcaja,
                    collect($detallesMovCaja),
                    true,
                    $sol,

                );

                // **Almacenar la respuesta**
                $respuestasServidor[] = [
                    'solicitud_id' => $sol->id,
                    'respuesta' => $respuesta
                ];
            }
            // $respuestasServidor = [];
            // // Intentar reenviar a facturador
            // $resendResponse = $this->resend_to_facturador();

            // // Verificar si el reenvío fue exitoso
            // if (!$resendResponse['success']) {
            //     // Si hubo un error, reflejarlo en la respuesta
            //     return response()->json([
            //         'success' => false,
            //         'message' => 'Error al reenviar al facturador: ' . $resendResponse['error'],
            //         'data' => $respuestasServidor, // Mantener las respuestas previas
            //     ], 500);
            // }

            // Si todo fue exitoso, devolver el mensaje
            return response()->json([
                'success' => true,
                'message' => 'Boletas reenviadas con éxito.',
                'data' => $respuestasServidor, // Enviar todas las respuestas del servidor
                'redirect_url' => route('solicitud.index')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->__toString(),
            ], 500);
        }
    }

    private function actualizarBoletaPendienteConSerieCajaActiva(Solicitud $solicitud, Movventum $movventa, int $sucursalId): Solicitud
    {
        if ($solicitud->estado !== 'E' || $solicitud->tipo_documento !== 'B') {
            return $solicitud;
        }

        $cajaActiva = Caja::where('situacion', 'A')
            ->where('sucursal_id', $sucursalId)
            ->first();

        if (!$cajaActiva) {
            throw new \Exception('No hay caja activa para validar la serie de las boletas pendientes.');
        }

        $serieCaja = str_pad((string) $cajaActiva->serie, 3, '0', STR_PAD_LEFT);
        $serieCompleta = 'B' . $serieCaja;

        if ($movventa->serie === $serieCompleta && $solicitud->serie === $serieCaja) {
            return $solicitud;
        }

        return DB::transaction(function () use ($solicitud, $movventa, $sucursalId, $serieCaja, $serieCompleta) {
            $nuevoNumero = CorrelativoService::nextNumeroVenta($sucursalId, 4, $serieCompleta);
            $nuevoCorrelativo = (int) $nuevoNumero;

            $movimiento = $movventa->movimiento;
            $movimiento->numero = $nuevoNumero;
            $movimiento->save();

            $movventa->serie = $serieCompleta;
            $movventa->save();

            $data = json_decode($solicitud->data, true) ?: [];
            $data['numeroboleta'] = $serieCaja . '-' . $nuevoNumero;

            $solicitud->serie = $serieCaja;
            $solicitud->correlativo = $nuevoCorrelativo;
            $solicitud->data = json_encode($data);
            $solicitud->save();

            return $solicitud->fresh(['movimiento.movventa']);
        });
    }

    public function resend_to_facturador()
    {
        try {
            // Obtener todas las solicitudes con tipo_solicitud 'sendSummary' y que no hayan sido enviadas
            $solicitudes = DB::table('solicitud')
                ->where('tipo_solicitud', 'sendSummary')
                ->whereIn('estado', ['E', 'P'])
                ->get();

            $resumenes = [];

            foreach ($solicitudes as $solicitud) {
                $data = json_decode($solicitud->data, true);

                // Dependiendo del tipo de documento, enviamos al WebService correspondiente
                $tipo_documento = $data['numeroboleta'] ? 'B' : 'F'; // Verificar si es boleta o factura
                $url = $tipo_documento == 'B'
                    ? config('services.facturacion.resumen_boletas_wsdl')
                    : config('services.facturacion.bajas_wsdl');

                // Datos de facturador y solicitud
                $facturador = json_decode($solicitud->json_facturador);
                $user_wsdl = $facturador->username_wsdl;
                $pass_wsdl = $facturador->password_wsdl;
                $token = ''; // Asigna el token si aplica

                // En función del tipo de documento, establecemos el campo adecuado (numeroboleta para boleta y numerobaja para factura)
                $numero_documento = $tipo_documento == 'B' ? $data['numeroboleta'] : $data['numerobaja'];

                // Crear los datos para enviar al WebService
                $datos_enviar = [
                    'token' => $token,
                    'comprobante' => json_encode($data)
                ];

                $json_enviar = json_encode($datos_enviar);

                $datos = [
                    'ruc' => $user_wsdl,
                    'password' => $pass_wsdl,
                    'json' => $json_enviar,
                ];

                // Realizar la petición HTTP POST al WebService
                $client = new \GuzzleHttp\Client();
                $response = $client->post($url, [
                    'form_params' => $datos,
                ]);

                // Verificamos la respuesta
                if ($response->getStatusCode() == 200) {
                    // **Actualizar estado de la solicitud dependiendo de la respuesta**
                    $solicitud_facturador_id = json_decode($solicitud->json_facturador)->id_solicitud;

                    // Verificar la respuesta del servidor
                    $result = json_decode($response->getBody()->getContents());
                    $estado_solicitud = $result->estado_solicitud ?? 'E'; // Establecer estado por defecto como 'E' si no se especifica

                    // Actualizamos el estado de la solicitud en la base de datos
                    DB::table('solicitud')->where('id', $solicitud->id)->update([
                        'estado' => $estado_solicitud, // Actualiza con el estado devuelto
                        'respuesta_servidor' => json_encode($result), // Guardar respuesta del servidor
                        'fecha_respuesta' => Carbon::now(),
                    ]);

                    $resumenes[] = [
                        'solicitud_id' => $solicitud->id,
                        'estado_envio' => 'Enviado',
                        'numero_documento' => $numero_documento // Incluir el número de documento
                    ];
                } else {
                    // En caso de error en la respuesta
                    DB::table('solicitud')->where('id', $solicitud->id)->update(['estado' => 'X']);
                    $resumenes[] = [
                        'solicitud_id' => $solicitud->id,
                        'estado_envio' => 'Error',
                        'numero_documento' => $numero_documento // Incluir el número de documento en caso de error
                    ];
                }
            }

            return [
                'success' => true,
                'message' => 'Solicitudes reenviadas correctamente.',
                'data' => $resumenes,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }


    public function send_pending_facturas()
    {
        $sucursalId = Auth::user()->persona->sucursal_id;
        $sucursal = Sucursal::find($sucursalId);
        $facturadorController = new FacturadorController();

        // La pantalla identifica las facturas mediante tipo_documento. Usar el mismo
        // criterio evita omitir solicitudes por inconsistencias en el movimiento.
        $solicitudes = Solicitud::query()
            ->where('sucursal_id', $sucursalId)
            ->where('tipo_documento', 'F')
            ->where('estado', 'E')
            ->with(['movimiento.movventa', 'facturador'])
            ->orderBy('fecha')
            ->get();

        $reenviadas = [];
        $noReenviadas = [];

        foreach ($solicitudes as $solicitud) {
            $numero = $this->numeroComprobante($solicitud);

            try {
                $movimiento = $solicitud->movimiento;
                $movventa = $movimiento?->movventa;

                if (!$movimiento || !$movventa) {
                    throw new \RuntimeException('No se encontró la venta relacionada.');
                }

                if ((int) $movimiento->tipodocumento_id !== 10) {
                    throw new \RuntimeException('El movimiento relacionado no está configurado como factura.');
                }

                $persona = Persona::find($movimiento->persona_id);
                if (!$persona) {
                    throw new \RuntimeException('No se encontró el cliente relacionado.');
                }

                $movcaja = $movventa->movcaja;
                if (!$movcaja) {
                    throw new \RuntimeException('No se encontró el movimiento de caja relacionado.');
                }

                $facturador = $solicitud->facturador;
                if (!$facturador) {
                    throw new \RuntimeException('No se encontró la configuración del facturador.');
                }

                if (!$sucursal) {
                    throw new \RuntimeException('No se encontró la sucursal relacionada.');
                }

                $detalles = DetMovventum::where('movventa_id', $movventa->id)->get();
                $detallesMovCaja = DetMovcaja::where('movcaja_id', $movcaja->id)->get();

                // FacturadorController usa el primer conjunto para DETALLADO y el
                // segundo para GLOSA. Los datos persistidos sirven para ambos casos.
                $respuesta = $facturadorController->enviarFacturacionElectronica(
                    $movventa,
                    $detalles,
                    $detalles,
                    $persona,
                    $sucursal,
                    $facturador,
                    $movcaja,
                    $detallesMovCaja,
                    true,
                    $solicitud,
                );

                if (!is_array($respuesta) || !($respuesta['success'] ?? false)) {
                    throw new \RuntimeException($this->mensajeRespuestaFacturador($respuesta, $facturador));
                }

                $reenviadas[] = [
                    'id' => $solicitud->id,
                    'numero' => $numero,
                    'mensaje' => $this->ocultarCredencialesFacturador(
                        (string) ($respuesta['message'] ?? 'Enviada correctamente.'),
                        $facturador,
                    ),
                ];
            } catch (\Throwable $e) {
                Log::warning('No se pudo reenviar una factura pendiente.', [
                    'solicitud_id' => $solicitud->id,
                    'numero' => $numero,
                    'sucursal_id' => $sucursalId,
                    'error' => $e->getMessage(),
                ]);

                $noReenviadas[] = [
                    'id' => $solicitud->id,
                    'numero' => $numero,
                    'mensaje' => $e->getMessage(),
                ];
            }
        }

        $cantidadReenviadas = count($reenviadas);
        $cantidadNoReenviadas = count($noReenviadas);

        return response()->json([
            'success' => $cantidadNoReenviadas === 0,
            'batch_result' => true,
            'status' => $this->estadoResultadoReenvio(
                $solicitudes->count(),
                $cantidadReenviadas,
                $cantidadNoReenviadas,
            ),
            'message' => $this->mensajeResultadoReenvio(
                $solicitudes->count(),
                $cantidadReenviadas,
                $cantidadNoReenviadas,
            ),
            'summary' => [
                'total' => $solicitudes->count(),
                'reenviadas' => $cantidadReenviadas,
                'no_reenviadas' => $cantidadNoReenviadas,
            ],
            'reenviadas' => $reenviadas,
            'no_reenviadas' => $noReenviadas,
            'redirect_url' => route('solicitud.index'),
        ]);
    }

    private function numeroComprobante(Solicitud $solicitud): string
    {
        return $solicitud->tipo_documento . $solicitud->serie . '-' . $solicitud->correlativo;
    }

    private function mensajeRespuestaFacturador($respuesta, Facturador $facturador): string
    {
        if (!is_array($respuesta)) {
            return 'El facturador devolvió una respuesta no válida.';
        }

        $mensaje = (string) ($respuesta['message'] ?? 'El facturador rechazó la solicitud sin indicar el motivo.');
        $detalle = $respuesta['details'] ?? null;

        if (is_object($detalle) && isset($detalle->mensaje)) {
            $detalle = $detalle->mensaje;
        } elseif (is_array($detalle) && isset($detalle['mensaje'])) {
            $detalle = $detalle['mensaje'];
        }

        if (is_string($detalle) && trim(strip_tags($detalle)) !== '') {
            $detalle = trim(strip_tags($detalle));
            if (!str_contains($mensaje, $detalle)) {
                $mensaje .= ': ' . $detalle;
            }
        }

        return $this->ocultarCredencialesFacturador($mensaje, $facturador);
    }

    private function ocultarCredencialesFacturador(string $mensaje, Facturador $facturador): string
    {
        $credenciales = array_filter([
            (string) $facturador->username_wsdl,
            (string) $facturador->password_wsdl,
        ], fn($valor) => $valor !== '');

        return str_replace($credenciales, '***', $mensaje);
    }

    private function estadoResultadoReenvio(int $total, int $reenviadas, int $noReenviadas): string
    {
        if ($total === 0) {
            return 'empty';
        }

        if ($noReenviadas === 0) {
            return 'success';
        }

        return $reenviadas > 0 ? 'partial' : 'error';
    }

    private function mensajeResultadoReenvio(int $total, int $reenviadas, int $noReenviadas): string
    {
        if ($total === 0) {
            return 'No hay facturas pendientes para reenviar.';
        }

        if ($noReenviadas === 0) {
            return $reenviadas === 1
                ? 'Se reenvió 1 factura correctamente.'
                : "Se reenviaron {$reenviadas} facturas correctamente.";
        }

        if ($reenviadas === 0) {
            return $noReenviadas === 1
                ? 'No se pudo reenviar la factura pendiente.'
                : "No se pudieron reenviar las {$noReenviadas} facturas pendientes.";
        }

        $resultadoFallido = $noReenviadas === 1
            ? '1 no pudo enviarse'
            : "{$noReenviadas} no pudieron enviarse";

        return "Se reenviaron {$reenviadas} de {$total} facturas; {$resultadoFallido}.";
    }


    /**
     * Desactivar una solicitud.
     */
public function desactivar($id)
{
    try {
        $solicitud = Solicitud::findOrFail($id);

        // Actualizar estado a "false"
        $solicitud->update(['estado' => false]);

        return redirect()->route('solicitud.index')
            ->with('success', 'La solicitud fue desactivada correctamente.');
    } catch (\Exception $e) {
        return redirect()->route('solicitud.index')
            ->withErrors(['error' => 'Ocurrió un error: ' . $e->getMessage()]);
    }
}


    public function cancell_boletas(Request $request)
    {
        try {
            $sucursal_id = Auth::user()->persona->sucursal_id;

            // IDs y motivos recibidos desde el frontend
            $ids = $request->input('solicitud_id', []);
            $motivos = $request->input('motivos', []);

            if (empty($ids)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se seleccionó ninguna boleta para anular.',
                ], 400);
            }

            // Obtener las solicitudes tipo boleta seleccionadas y agrupar por fecha+moneda
            $solicitudes = DB::table('solicitud')
                ->where('sucursal_id', $sucursal_id)
                ->where('tipo_documento', 'B')
                ->whereIn('id', $ids)
                ->get()
                ->groupBy(function ($s) {
                    return (new Carbon($s->fecha))->toDateString() . '-' . $s->moneda;
                });

            $resumenes = [];

            foreach ($solicitudes as $grupo_ventas) {
                $tipo_documento = 'B';
                $serie = today()->format('Ymd');
                $fecha_emision = today()->toDateString();
                $fecha_referencia = (new Carbon($grupo_ventas->first()->fecha))->toDateString();
                $moneda = $grupo_ventas->first()->moneda;
                $tipo_resumen = 'E';
                $codigo_tipo = '03'; // Código SUNAT para boleta
                $accion = 'enviarResumenBoletas';
                $url = config('services.facturacion.resumen_boletas_wsdl');

                // Agrupar por facturador (username_wsdl + sucursal)
                $resumenes_group = $grupo_ventas->groupBy(function ($s) {
                    $fact = json_decode($s->json_facturador);
                    return $fact->username_wsdl . '-' . $s->sucursal_id;
                });

                foreach ($resumenes_group as $anulacion) {
                    $correlativo = DB::table('solicitud')
                        ->where('sucursal_id', $sucursal_id)
                        ->where('serie', $serie)
                        ->where('tipo_documento', $tipo_resumen)
                        ->count();

                    $correlativo = str_pad((string) ($correlativo + 1), 5, '0', STR_PAD_LEFT);
                    $numero_solicitud = $serie . '-' . $correlativo;

                    $detalles = $anulacion->map(function ($detalle) use ($codigo_tipo, $motivos) {
                        $persona = json_decode($detalle->json_persona);
                        $nrodoc = $persona->personamaestro->nrodoc ?? null;
                        $motivo = $motivos[$detalle->id] ?? 'Error de comprobante';

                        return [
                            'id' => $detalle->id,
                            'idservidor' => $detalle->solicitud_facturador_id,
                            'tipo' => $codigo_tipo,
                            'numero' => substr($detalle->serie, 1),
                            'correlativo' => $detalle->correlativo,
                            'dni' => $nrodoc == '0' ? null : $nrodoc,
                            'total' => floatval($detalle->total),
                            'numeroReferencia' => '',
                            'tipoReferencia' => '',
                            'motivo' => $motivo
                        ];
                    })->values()->all();

                    $resumen = [
                        'numeroboleta' => $numero_solicitud,
                        'tiporesumen' => intval($codigo_tipo),
                        'fechaemision' => $fecha_emision,
                        'fechareferencia' => $fecha_referencia,
                        'moneda' => $moneda,
                        'detalles' => $detalles
                    ];

                    // Guardar resumen en DB
                    $id = DB::table('solicitud')->insertGetId([
                        'tipo_solicitud' => 'sendSummary',
                        'data' => json_encode($resumen),
                        'fecha' => now(),
                        'estado' => '',
                        'moneda' => $moneda,
                        'total' => null,
                        'tipo_documento' => $tipo_resumen,
                        'serie' => $serie,
                        'correlativo' => $correlativo,
                        'persona_id' => null,
                        'json_persona' => null,
                        'sucursal_id' => $anulacion->first()->sucursal_id,
                        'json_sucursal' => $anulacion->first()->json_sucursal,
                        'facturador_id' => $anulacion->first()->facturador_id,
                        'json_facturador' => $anulacion->first()->json_facturador,
                    ]);

                    // Preparar datos para enviar al WS
                    $solicitud = DB::table('solicitud')->find($id);
                    $facturador = json_decode($anulacion->first()->json_facturador);
                    $user_wsdl = $facturador->username_wsdl;
                    $pass_wsdl = $facturador->password_wsdl;

                    $token = ''; // Obtener token si aplica

                    $datos_enviar = [
                        'token' => $token,
                        'serieboleta' => $serie,
                        'correlativoboleta' => $correlativo,
                        'comprobante' => json_encode($resumen),
                    ];

                    $json_enviar = json_encode($datos_enviar);

                    $datos = [
                        'ruc' => $user_wsdl,
                        'password' => $pass_wsdl,
                        'json' => $json_enviar,
                    ];

                    // Enviar petición HTTP POST a WS
                    $client = new \GuzzleHttp\Client();
                    $response = $client->post($url, [
                        'form_params' => $datos
                    ]);

                    if ($response->getStatusCode() == 200) {
                        // Actualizar estado solicitud a enviado
                        DB::table('solicitud')->where('id', $id)->update(['estado' => 'E']);
                    } else {
                        // Actualizar estado con error
                        DB::table('solicitud')->where('id', $id)->update(['estado' => 'X']);
                    }

                    $resumenes[] = [
                        'solicitud_id' => $id,
                        'tipo_documento' => $tipo_documento,
                        'numero_solicitud' => $numero_solicitud,
                        'estado_envio' => $response->getStatusCode() == 200 ? 'Enviado' : 'Error',
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Resumen de anulaciones generado y enviado correctamente.',
                'data' => $resumenes,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function cambiarEstado($id)
    {
        try {
            // Encontramos la solicitud por su ID
            $solicitud = Solicitud::findOrFail($id);

            // Verificamos si el estado es 'R' (Aún no declarado)
            if ($solicitud->estado == 'R') {
                // Actualizamos el estado de la solicitud a 'E' (No registrado)
                $solicitud->estado = 'E';
                $solicitud->save();

                // Retornamos una respuesta exitosa
                return response()->json([
                    'success' => true,
                    'message' => 'Estado actualizado exitosamente.',
                    'solicitud' => $solicitud,
                    'redirect_url' => route('solicitud.index') // Redirigir a la página de solicitudes
                ]);
            } else {
                // Si el estado no es 'R', lanzamos un error
                throw new \Exception('El estado no se puede cambiar porque no está en "Aún no declarado".');
            }
        } catch (\Exception $e) {
            // Capturamos cualquier error y retornamos la respuesta de error
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }
}
