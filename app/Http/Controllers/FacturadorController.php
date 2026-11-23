<?php

namespace App\Http\Controllers;

use App\Models\Facturador;
use App\Http\Requests\FacturadorRequest;
use App\Models\Igv;
use App\Models\Movhabitacion;
use App\Models\Operacione;
use App\Models\Persona;
use App\Models\Producto;
use App\Models\RelParametrosucursal;
use App\Models\Solicitud;
use App\Models\Sucursal;
use App\Models\Unidad;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use nusoap_client;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class FacturadorController extends Controller
{
    /**
     * Muestra una lista de los facturadores.
     */
    private $boleta_id = 4;

    public function index()
    {
        $operaciones = Operacione::where('tabla_id', 67)
            ->where('estado', true)
            ->get();
        $facturadores = Facturador::paginate();

        return view('facturador.index', compact('facturadores', 'operaciones'))
            ->with('i', (request()->input('page', 1) - 1) * $facturadores->perPage());
    }

    /**
     * Muestra el formulario para crear un nuevo facturador.
     */
    public function create($sucursal_id)
    {
        $facturador = new Facturador();
        return view('facturador.create', compact('facturador', 'sucursal_id'));
    }

    /**
     * Almacena un nuevo facturador en la base de datos.
     */
    public function store(FacturadorRequest $request)
    {
        try {
            Facturador::create($request->validated());
            $sucursal_id = $request->input('sucursal_id');

            return response()->json([
                'success' => true,
                'message' => 'Facturador creado exitosamente.',
                'redirect_url' => route('sucursal.facturacion', ['id' => $sucursal_id])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Muestra los detalles del facturador especificado.
     */
    public function show($id)
    {
        $facturador = Facturador::find($id);

        return view('facturador.show', compact('facturador'));
    }

    /**
     * Muestra el formulario para editar el facturador especificado.
     */
    public function edit($id, $sucursal_id)
    {
        $facturador = Facturador::find($id);

        return view('facturador.edit', compact('facturador', 'sucursal_id'));
    }

    /**
     * Actualiza el facturador especificado en la base de datos.
     */
    public function update(FacturadorRequest $request, Facturador $facturador)
    {
        try {
            // Actualiza el facturador con los datos validados
            $facturador->update($request->validated());

            // Obtiene el sucursal_id del request
            $sucursal_id = $request->input('sucursal_id');

            // Retorna una respuesta JSON con la URL actualizada
            return response()->json([
                'success' => true,
                'message' => 'Facturador actualizado exitosamente.',
                'redirect_url' => route('sucursal.facturacion', ['id' => $sucursal_id])
            ]);
        } catch (\Exception $e) {
            // Manejo de errores
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Elimina el facturador especificado de la base de datos.
     */
    public function destroy($id)
    {
        try {
            Facturador::find($id)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Facturador eliminado exitosamente.',
                'redirect_url' => route('facturador.index')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Activa un facturador.
     */
    public function activar($id)
    {
        try {
            $facturador = Facturador::find($id);
            $facturador->update(['estado' => true]);

            return response()->json([
                'success' => true,
                'message' => 'Facturador activado correctamente.',
                'redirect_url' => route('facturador.index')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Desactiva un facturador.
     */
    public function desactivar($id)
    {
        try {
            $facturador = Facturador::find($id);
            $facturador->update(['estado' => false]);

            return response()->json([
                'success' => true,
                'message' => 'Facturador desactivado correctamente.',
                'redirect_url' => route('facturador.index')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }


    public function enviarFacturacionElectronica($movventa, $detalles_MovVenta, $glosas_MovVenta, $persona, $sucursal, $facturador, $movcaja, $detalles_MovCaja, $reenvio, $sol = null)
    {
        // Tipo de documento: factura o boleta
        $tipo_documento = ($movventa->movimiento->tipodocumento_id == $this->boleta_id) ? 'B' : 'F';
        $tipo_nombre = ($tipo_documento == 'B') ? 'boleta' : 'factura';
        $tipo_nro_doc = ($tipo_documento == 'B') ? 'dni' : 'ruc';
        $tipo_numero = 'numero' . $tipo_nombre;
        $accion = ($tipo_documento == 'B') ? 'enviarBoleta' : 'enviarFactura';

        $solicitud = Solicitud::where('movimiento_id', $movventa->movimiento_id)->first();

        // Obtener detalles directamente desde el modelo para obtener relación igv
        $detalles = \App\Models\DetMovventum::with('igv')
            ->where('movventa_id', $movventa->id)
            ->get();

        $porcentaje_igv = null;

        if ($detalles->isNotEmpty()) {
            // Tomamos el porcentaje_igv del primer detalle que tenga relación con igv
            $primerDetalle = $detalles->first();

            if ($primerDetalle->igv && isset($primerDetalle->igv->porcentaje_igv)) {
                $porcentaje_igv = $primerDetalle->igv->porcentaje_igv;
            }
        } else {
            // Si no hay detalles, fallback al porcentaje de facturador
            $porcentaje_igv = $facturador->porcentaje_igv ?? null;
        }

        // Selección de URL según tipo documento y porcentaje IGV
        if ($tipo_documento == 'B') {
         
                $url = "http://157.245.85.164/facturacion/wsdl/wsdl_boleta_rc.php";
            
        } else {
          
                $url = "http://157.245.85.164/facturacion/wsdl/wsdl_factura_rc.php";
            
        }


        // Datos generales del documento
        $serieSinPrimeraLetra = substr($movventa->serie, 1);
        $numerocomprobante = $serieSinPrimeraLetra . '-' . $movventa->movimiento->numero;
        $fechaReferencia = $solicitud
            ? $solicitud->fecha
            : ($movventa->movimiento->fecha     ?? $movventa->created_at);
        $fechaemision = Carbon::parse($fechaReferencia)->toDateString();
        $horaemision = Carbon::parse($fechaReferencia)->toTimeString();
        $moneda = $movventa->moneda;
        $descuentototal = '0';
        $percepcion = '';
        $aplicacionpercepcion = '';
        $documentosanexos = [];

        // Datos de la persona
        $nro_doc = $persona->personamaestro->nrodoc;
        $cod_ubigeo = '0000';
        $tipo_doc = $persona->personamaestro->tipopersona == 'Juridico'
            ? '6'
            : ($persona->personamaestro->tipopersona == 'Carnet de Extranjería'
                ? '4'
                : ($persona->personamaestro->tipopersona == 'Pasaporte'
                    ? '7'
                    : '1'));
        $nombre = trim($persona->apellidos . ' ' . $persona->nombres);
        $direccion = $persona->direccion;

        // Generar detalles de comprobante
        $detalles = [];
        if ($movventa->consumo == 'S') {
            $igv_codigo = '10';
            if ($detalles_MovVenta && $movventa->tipo_detalle == 'DETALLADO') {
                $detalle = $detalles_MovVenta->first();
                if ($detalle) $igv_codigo = $detalle->igv_codigo;
            } else if ($glosas_MovVenta && $movventa->tipo_detalle == 'GLOSA') {
                $glosa = $glosas_MovVenta->first();
                if ($glosa) $igv_codigo = $glosa->igv_codigo;
            }

            $detalles[] = [
                'tipodetalle' => 'V',
                'codigo' => '-',
                'unidadmedida' => 'NIU',
                'cantidad' => 1,
                'descripcion' => 'POR CONSUMO',
                'precioventaunitarioxitem' => $movventa->total,
                'descuentoxitem' => '0',
                'tipoigv' => $igv_codigo,
                'tasaisc' => '0',
                'aplicacionisc' => '',
                'precioventasugeridoxitem' => ''
            ];
        } else {
            // Detalles según el tipo de venta
            if ($movventa->tipo_detalle == 'DETALLADO') {
                $detalles_MovVenta = $detalles_MovVenta->filter(function ($detalle) {
                    return ($detalle['total'] ?? $detalle['monto']) > 0;
                })->map(function ($detalle) {
                    if (isset($detalle['producto_id'])) {
                        $producto = Producto::find($detalle['producto_id']); // Busca el producto
                        if ($producto && $producto->relProductosucursal) {
                            $igv = Igv::find($producto->relProductosucursal->igv_id); // Obtiene el IGV desde relProductosucursal

                            if ($igv) {
                                $codigoIgv = $igv->codigo; // Extrae el código del IGV
                            } else {
                                $codigoIgv = null; // Manejo si no se encuentra el IGV
                            }
                        }
                    } else {
                        $codigoIgv = null; // Manejo si no existe el producto o la relación
                    }
                    $usuario = Auth::user();
                    $responsable = $usuario->persona;
                    $personasucursal = Persona::find($usuario->persona_id);
                    $sucursal = Sucursal::find($personasucursal->sucursal_id);
                    // Si $codigoIgv es null o vacío, asignar el código de IGV predeterminado
                    if (empty($codigoIgv)) {
                        $igvPredeterminado = RelParametrosucursal::where('sucursal_id', $sucursal->id)
                            ->where('parametro_id', 8) // ID del parámetro para IGV
                            ->value('valor');

                        $igvObjeto = Igv::find($igvPredeterminado);

                        $codigoIgv = $igvObjeto ? $igvObjeto->codigo : null; // Asigna el código del IGV predeterminado si existe
                    }

                    //dd($producto->relProductosucursal);

                    return [
                        'tipodetalle' => 'V',
                        'codigo' => $detalle['codigo'],
                        'unidadmedida' => 'NIU',
                        'cantidad' => $detalle['cantidad'],
                        'descripcion' => $detalle['descripcion'],
                        'precioventaunitarioxitem' => isset($detalle['monto']) ? $detalle['monto'] : $detalle['precio_unitario'],
                        'descuentoxitem' => '0',
                        'tipoigv' => $codigoIgv,
                        'tasaisc' => '0',
                        'aplicacionisc' => '',
                        'precioventasugeridoxitem' => ''
                    ];
                });

                $detalles_MovVenta = $detalles_MovVenta->groupBy(function ($detalle) {
                    return $detalle['codigo'] . '-' . $detalle['unidadmedida'] . '-' . $detalle['descripcion'] . '-' . $detalle['precioventaunitarioxitem'] . '-' . $detalle['tipoigv'];
                });
                $detalles = $detalles_MovVenta->map(function ($detalle) {
                    return [
                        'tipodetalle' => 'V',
                        'codigo' => $detalle->first()['codigo'],
                        'unidadmedida' => $detalle->first()['unidadmedida'],
                        'cantidad' => $detalle->sum('cantidad'),
                        'descripcion' => $detalle->first()['descripcion'],
                        'precioventaunitarioxitem' => $detalle->first()['precioventaunitarioxitem'],
                        'descuentoxitem' => '0',
                        'tipoigv' => $detalle->first()['tipoigv'],
                        'tasaisc' => '0',
                        'aplicacionisc' => '',
                        'precioventasugeridoxitem' => ''
                    ];
                })->values()->all();
            } else {
                //dd($glosas_MovVenta);
                // Glosas de venta
                $igvPredeterminado = RelParametrosucursal::where('sucursal_id', $sucursal->id)
                    ->where('parametro_id', 8) // ID del parámetro para IGV
                    ->value('valor');

                $igvObjeto = Igv::find($igvPredeterminado);
                $codigoIgv = $igvObjeto ? $igvObjeto->codigo : null; // Código del IGV predeterminado

                $detalles = collect($glosas_MovVenta)->filter(function ($detalle) {
                    return $detalle['total'] > 0;
                })->map(function ($detalle) use ($codigoIgv) {
                    return [
                        'tipodetalle' => 'V',
                        'codigo' => $detalle['codigo'],
                        'unidadmedida' => $detalle['unidad_abreviatura'] ?? 'NIU',
                        'cantidad' => $detalle['cantidad'],
                        'descripcion' => $detalle['descripcion'],
                        'precioventaunitarioxitem' => $detalle['precio_unitario'],
                        'descuentoxitem' => '0',
                        'tipoigv' => $detalle['igv_codigo'] ?? $codigoIgv,
                        'tasaisc' => '0',
                        'aplicacionisc' => '',
                        'precioventasugeridoxitem' => ''
                    ];
                })->values()->all();
            }
        }
        // dd($movventa->tipo_pago);
        // Obtener cuotas en caso de ser pago a crédito
        $formapago = $this->resolverFormaPagoSunat($movventa, $detalles_MovCaja);
        $cuotas = [];
        if ($formapago == 'D') {
            $cuotas = collect($detalles_MovCaja)->filter(function ($detalle) {
                return strcasecmp((string) $this->valorDetalleCaja($detalle, 'tipo'), 'Deuda') === 0;
            })->map(function ($detalle) {
                $fechaVencimiento = $this->valorDetalleCaja($detalle, 'fechavencimiento');
                return [
                    'monto' => floatval($this->valorDetalleCaja($detalle, 'monto')),
                    'fecha' => $fechaVencimiento
                        ? Carbon::createFromTimestamp(strtotime($fechaVencimiento))->toDateString()
                        : today()->toDateString()
                ];
            })->values()->all();

            $porcentajeDetraccion = floatval($movventa->porcentaje_detraccion ?? 0);
            $tieneDetraccion = ($movventa->detraccion == 'S' || $porcentajeDetraccion > 0);

            if ($tieneDetraccion && $porcentajeDetraccion > 0) {
                $montoDetraccion = round(($movventa->total * $porcentajeDetraccion) / 100, 2);
                if (empty($cuotas)) {
                    $fechaEmisionDefault = $fechaemision ?? today()->toDateString();
                    $cuotas = [
                        [
                            'monto' => floatval($movventa->total),
                            'fecha' => $fechaEmisionDefault,
                        ]
                    ];
                }
                $cuotas = $this->aplicarDescuentoDetraccionACuotas($cuotas, $montoDetraccion, floatval($movventa->total));
            }
        }

        // Datos del comprobante para la solicitud
        $comprobante = [
            $tipo_numero => $numerocomprobante,
            'fechaemision' => $fechaemision,
            'horaemision' => $horaemision,
            'usuario' => $nombre,
            'codubigeo' => $cod_ubigeo,
            'tipodoc' => $tipo_doc,
            $tipo_nro_doc => $nro_doc == 0 ? '' : $nro_doc,
            'moneda' => $moneda,
            'descuentototal' => $descuentototal,
            'percepcion' => $percepcion,
            'aplicacionpercepcion' => $aplicacionpercepcion,
            'documentosanexos' => $documentosanexos,
            'detalles' => $detalles,
            'formapago' => $formapago,
            'cuotas' => $cuotas,
            'porcentajeigv' => ( $porcentaje_igv),
            'detraccion' => floatval($movventa->porcentaje_detraccion ?? 0),
            'codbiendetraccion' => (string) ($movventa->codigo_bien_detraccion ?? ''),
            'cuentadetraccion' => (string) ($movventa->cuenta_detraccion ?? ''),
        ];

        // En un reenvío, solicitud.data es la fuente original del comprobante.
        // Esto permite reenviar documentos históricos aunque sus filas de detalle
        // ya no estén disponibles en det_movventa.
        if ($reenvio && $sol) {
            $comprobanteGuardado = $this->comprobanteGuardado($sol);
            if ($comprobanteGuardado !== null) {
                $comprobante = $comprobanteGuardado;
            }
        }

        if (empty($comprobante['detalles'])) {
            return [
                'success' => false,
                'message' => 'No se puede enviar el comprobante porque no tiene detalles.',
                'details' => 'El campo "detalles" está vacío.'
            ];
        }

        if ($reenvio == false) {

            // Inserción en la base de datos
            $solicitud = Solicitud::create([
                'tipo_solicitud' => 'sendBill',
                'data' => json_encode($comprobante),
                'fecha' => now(),
                'estado' => 'E',
                'moneda' => $movventa->moneda,
                'total' => $movventa->total,
                'tipo_documento' => $tipo_documento,
                'serie' => substr($movventa->serie, 1),
                'correlativo' => intval($movventa->movimiento->numero),
                'persona_id' => $movventa->movimiento->persona_id,
                'json_persona' => json_encode($persona),
                'sucursal_id' => $sucursal->id,
                'json_sucursal' => json_encode($sucursal),
                'facturador_id' => $facturador->id,
                'json_facturador' => json_encode($facturador),
                'movimiento_id' => $movventa->movimiento_id,
            ]);


            // Preparar datos para el WebService
            $token = ''; 
            $user_wsdl = $facturador->username_wsdl;
            $pass_wsdl = $facturador->password_wsdl;

            $datos_enviar = [
                'token' => $token,
                'serie' . $tipo_nombre => $solicitud->serie,
                'correlativo' . $tipo_nombre => intval($solicitud->correlativo),
                'doc' => ($tipo_nombre == 'boleta') ? $comprobante['dni'] : $comprobante['ruc'],
                'nombre' => $comprobante['usuario'],
                'direccion' => $direccion,
                'total' => $solicitud->total,
                'comprobante' => json_encode($comprobante)
            ];
            $datos_enviar = json_encode($datos_enviar);

            // Llamada al WebService
            $datos = [
                'ruc' => $user_wsdl,
                'password' => $pass_wsdl,
                'json' => $datos_enviar
            ];

            // Actualizar solicitud a "Enviado"
            $solicitud->estado = 'R';

            // Crear el cliente del WebService
            $client = new nusoap_client($url);
            $result = $client->call($accion, $datos);

            if ($client->fault) {
                return [
                    'success' => false,
                    'message' => 'Fault in WebService',
                    'details' => $result
                ];
            } else if ($client->getError()) {
                return [
                    'success' => false,
                    'message' => 'SOAP Error',
                    'details' => $client->getError()
                ];
            } else {
                $result = json_decode($result);
                if ($result->code == '0') {
                    $file_ZIP_BASE64 = $result->fileZIPBASE64;
                    $nombre_documento = $result->nombre_documento;
                    $file_ZIP = base64_decode($file_ZIP_BASE64);
                    $filename_zip = 'ficheros/sucursal_' . $sucursal->id . '/' . $nombre_documento . '.zip';
                    Storage::put($filename_zip, $file_ZIP);
                    DB::table('solicitud')->where('id', $solicitud->id)->update([
                        'estado' => 'R',
                        'solicitud_facturador_id' => $result->id_solicitud,
                        'nombre' => $result->nombre_documento,
                        'data' => json_encode($comprobante),

                    ]);
                    return [
                        'success' => true,
                        'message' => $result->mensaje, // Toma el mensaje exacto del servidor
                        'details' => $result,
                        'url' => $url,
                    ];
                } else {
                    return [
                        'success' => false,
                        'message' => $result->mensaje, // Toma el mensaje exacto del servidor
                        'details' => $result
                    ];
                }
            }
        } else {

            // Preparar datos para el WebService
            $token = ''; // Aquí puedes agregar el token si es necesario
            $user_wsdl = $facturador->username_wsdl;
            $pass_wsdl = $facturador->password_wsdl;

            $datos_enviar = [
                'token' => $token,
                'serie' . $tipo_nombre => $sol->serie,
                'correlativo' . $tipo_nombre => intval($sol->correlativo),
                'doc' => ($tipo_nombre == 'boleta') ? $comprobante['dni'] : $comprobante['ruc'],
                'nombre' => $comprobante['usuario'],
                'direccion' => $direccion,
                'total' => $sol->total,
                'comprobante' => json_encode($comprobante)
            ];
            $datos_enviar = json_encode($datos_enviar);

            // Llamada al WebService
            $datos = [
                'ruc' => $user_wsdl,
                'password' => $pass_wsdl,
                'json' => $datos_enviar
            ];

            // Actualizar solicitud a "E"
            $sol->estado = 'R';

            // Crear el cliente del WebService
            $client = new nusoap_client($url);
            $result = $client->call($accion, $datos);





            if ($client->fault) {
                return [
                    'success' => false,
                    'message' => 'Fault in WebService',
                    'details' => $result
                ];
            } else if ($client->getError()) {
                return [
                    'success' => false,
                    'message' => 'SOAP Error',
                    'details' => $client->getError()
                ];
            } else {
                $result = json_decode($result);
                if ($result->code == '0') {
                    $file_ZIP_BASE64 = $result->fileZIPBASE64;
                    $nombre_documento = $result->nombre_documento;
                    $file_ZIP = base64_decode($file_ZIP_BASE64);
                    $filename_zip = 'ficheros/sucursal_' . $sucursal->id . '/' . $nombre_documento . '.zip';
                    Storage::put($filename_zip, $file_ZIP);
                    //dd($sol);
                    DB::table('solicitud')->where('id', $sol->id)->update([
                        'estado' => 'R',
                        'solicitud_facturador_id' => $result->id_solicitud,
                        'nombre' => $result->nombre_documento,
                        'data' => json_encode($comprobante),

                    ]);
                    return [
                        'success' => true,
                        'message' => $result->mensaje, // Toma el mensaje exacto del servidor
                        'details' => $result,
                        'url' => $url,
                    ];
                } else {
                    return [
                        'success' => false,
                        'message' => $result->mensaje, // Toma el mensaje exacto del servidor
                        'details' => $result
                    ];
                }
            }
        }
    }

    private function comprobanteGuardado(Solicitud $solicitud): ?array
    {
        $comprobante = json_decode((string) $solicitud->data, true);

        return is_array($comprobante) && $comprobante !== []
            ? $comprobante
            : null;
    }

    private function resolverFormaPagoSunat($movventa, $detallesMovCaja): string
    {
        $tieneDeuda = collect($detallesMovCaja)->contains(function ($detalle) {
            return strcasecmp((string) $this->valorDetalleCaja($detalle, 'tipo'), 'Deuda') === 0;
        });

        if ($tieneDeuda) {
            return 'D';
        }

        return strcasecmp((string) ($movventa->tipo_pago ?? ''), 'CREDITO') === 0 ? 'D' : 'C';
    }

    private function valorDetalleCaja($detalle, string $campo)
    {
        if (is_array($detalle)) {
            return $detalle[$campo] ?? null;
        }

        return $detalle->{$campo} ?? null;
    }

    private function aplicarDescuentoDetraccionACuotas(array $cuotas, float $montoDetraccion, float $totalVenta): array
    {
        if ($montoDetraccion <= 0 || empty($cuotas)) {
            return $cuotas;
        }

        $sumCuotas = array_sum(array_map(fn($c) => floatval($c['monto'] ?? 0), $cuotas));
        if ($sumCuotas <= 0) {
            return $cuotas;
        }

        $montoNetoObjetivo = max(0, round($sumCuotas - $montoDetraccion, 2));
        $cuotasAjustadas = [];
        $acumulado = 0.0;
        $count = count($cuotas);

        foreach ($cuotas as $index => $cuota) {
            $montoOriginal = floatval($cuota['monto'] ?? 0);
            if ($index === $count - 1) {
                $nuevoMonto = max(0, round($montoNetoObjetivo - $acumulado, 2));
            } else {
                $proporcion = $montoOriginal / $sumCuotas;
                $nuevoMonto = max(0, round($montoOriginal - ($montoDetraccion * $proporcion), 2));
                $acumulado += $nuevoMonto;
            }

            $cuotaAjustada = $cuota;
            $cuotaAjustada['monto'] = $nuevoMonto;
            $cuotasAjustadas[] = $cuotaAjustada;
        }

        return $cuotasAjustadas;
    }
}
