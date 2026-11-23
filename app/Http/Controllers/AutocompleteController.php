<?php

namespace App\Http\Controllers;

use App\Models\Habitacion;
use App\Models\Menuopcione;
use App\Models\Mediopago;
use App\Models\Persona;
use App\Models\Producto;
use App\Models\RelMediopagopasarela;
use App\Models\Solicitud;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AutocompleteController extends Controller
{
    public function personas(Request $request)
    {
        $limit = $this->sanitizeLimit($request->input('limit'));
        $term = trim((string) $request->input('q', ''));
        $ids = $this->parseIds($request->input('ids'));
        $rolId = $request->input('rol_id');

        $sucursalId = optional(Auth::user()->persona)->sucursal_id;

        $query = Persona::with('personamaestro');

        if ($sucursalId) {
            $query->where('sucursal_id', $sucursalId);
        }

        if (!empty($rolId)) {
            $query->whereHas('rolpersonas', function ($q) use ($rolId) {
                $q->where('rol_id', $rolId);
            });
        }

        if (!empty($ids)) {
            $query->whereIn('id', $ids);
        } elseif ($term !== '') {
            $query->where(function ($q) use ($term) {
                $q->where('nombres', 'ILIKE', "%{$term}%")
                    ->orWhere('apellidos', 'ILIKE', "%{$term}%")
                    ->orWhereHas('personamaestro', function ($personamaestro) use ($term) {
                        $personamaestro->where('nrodoc', 'ILIKE', "%{$term}%");
                    });
            });
        } else {
            return response()->json([]);
        }

        $personas = $query
            ->orderBy('apellidos')
            ->orderBy('nombres')
            ->limit($limit)
            ->get();

        $items = $personas->map(function ($persona) {
            $nrodoc = optional($persona->personamaestro)->nrodoc;
            $label = $nrodoc
                ? "{$nrodoc} - {$persona->apellidos} {$persona->nombres}"
                : '0 - CLIENTES VARIOS';

            return [
                'id' => $persona->id,
                'label' => $label,
                'nrodoc' => $nrodoc,
                'nombres' => $persona->nombres,
                'apellidos' => $persona->apellidos,
            ];
        });

        return response()->json($items);
    }

    public function productos(Request $request)
    {
        $limit = $this->sanitizeLimit($request->input('limit'));
        $term = trim((string) $request->input('q', ''));
        $clasificacion = $request->input('clasificacion');

        if ($term === '') {
            return response()->json([]);
        }

        $sucursalId = optional(Auth::user()->persona)->sucursal_id;

        $query = Producto::with([
            'unidad',
            'relListaUnidadSucursal',
            'relProductosucursal' => function ($q) use ($sucursalId) {
                if ($sucursalId) {
                    $q->where('sucursal_id', $sucursalId);
                }
            },
            'relProductosucursal.igv',
        ]);

        if ($sucursalId) {
            $query->whereHas('relProductosucursal', function ($q) use ($sucursalId) {
                $q->where('sucursal_id', $sucursalId);
            });
        }

        if (!empty($clasificacion)) {
            $query->where('clasificacion', $clasificacion);
        }

        $query->where(function ($q) use ($term) {
            $q->where('codigo', 'ILIKE', "%{$term}%")
                ->orWhere('descripcion', 'ILIKE', "%{$term}%");
        });

        $productos = $query
            ->orderBy('descripcion')
            ->limit($limit)
            ->get();

        $items = $productos->map(function ($producto) {
            $listaUnidadSucursal = $producto->relListaUnidadSucursal;
            $ventaUnit = floatval($listaUnidadSucursal->precioventa ?? 0);
            $precioCompra = floatval($listaUnidadSucursal->preciocompra ?? 0);
            $igvModel = optional($producto->relProductosucursal)->igv;
            $igv = floatval($igvModel->porcentaje_igv ?? $igvModel->porcentaje ?? 0);
            $igvMultiplier = 1 + ($igv / 100);

            $unidad = $producto->unidad;
            $unidadId = $unidad ? $unidad->id : $producto->unidadbase_id;

            return [
                'id' => $producto->id,
                'label' => "{$producto->codigo} - {$producto->descripcion}",
                'codigo' => $producto->codigo,
                'descripcion' => $producto->descripcion,
                'precio_compra' => $precioCompra,
                'unidad' => $unidad->descripcion ?? 'Unidad',
                'unidad_id' => $unidadId,
                'igv' => $igv,
                'precio_unitario' => $ventaUnit,
                'precio_total' => $ventaUnit * $igvMultiplier,
                'cantidad' => 1,
            ];
        });

        return response()->json($items);
    }

    public function menuopciones(Request $request)
    {
        $limit = $this->sanitizeLimit($request->input('limit'));
        $term = trim((string) $request->input('q', ''));

        if ($term === '') {
            return response()->json([]);
        }

        $menuOpciones = Menuopcione::with('tipoopcione')
            ->where(function ($q) use ($term) {
                $q->where('descripcion', 'ILIKE', "%{$term}%")
                    ->orWhereHas('tipoopcione', function ($tipo) use ($term) {
                        $tipo->where('descripcion', 'ILIKE', "%{$term}%");
                    });
            })
            ->orderBy('descripcion')
            ->limit($limit)
            ->get();

        $items = $menuOpciones->map(function ($menu) {
            $tipoOpcion = optional($menu->tipoopcione)->descripcion ?? 'Sin tipo';

            return [
                'id' => $menu->id,
                'label' => "{$tipoOpcion} - {$menu->descripcion}",
                'descripcion' => $menu->descripcion,
                'tipo_opcion' => $tipoOpcion,
            ];
        });

        return response()->json($items);
    }

    public function solicitudes(Request $request)
    {
        $limit = $this->sanitizeLimit($request->input('limit'));
        $term = trim((string) $request->input('q', ''));
        $tipoDocumento = $request->input('tipo_documento');

        if ($term === '') {
            return response()->json([]);
        }

        $sucursalId = optional(Auth::user()->persona)->sucursal_id;

        $query = Solicitud::with('persona.personamaestro')
            ->whereIn('estado', ['M', 'T']);

        if ($sucursalId) {
            $query->where('sucursal_id', $sucursalId);
        }

        if (!empty($tipoDocumento)) {
            $query->where('tipo_documento', $tipoDocumento);
        }

        $query->where(function ($q) use ($term) {
            $q->where('serie', 'ILIKE', "%{$term}%")
                ->orWhereRaw("CAST(correlativo as TEXT) ILIKE ?", ["%{$term}%"])
                ->orWhereHas('persona', function ($persona) use ($term) {
                    $persona->where('nombres', 'ILIKE', "%{$term}%")
                        ->orWhere('apellidos', 'ILIKE', "%{$term}%")
                        ->orWhereHas('personamaestro', function ($personamaestro) use ($term) {
                            $personamaestro->where('nrodoc', 'ILIKE', "%{$term}%");
                        });
                });
        });

        $solicitudes = $query
            ->orderByDesc('fecha')
            ->limit($limit)
            ->get();

        $items = $solicitudes->map(function ($solicitud) {
            $persona = $solicitud->persona;
            $personaMaestro = optional($persona)->personamaestro;
            $nrodoc = optional($personaMaestro)->nrodoc;
            $nombres = $personaMaestro->nombres ?? ($persona->nombres ?? '');
            $apellidos = $personaMaestro->apellidos ?? ($persona->apellidos ?? '');
            $cliente = $nrodoc
                ? "{$nrodoc} - {$nombres} {$apellidos}"
                : '0 - CLIENTES VARIOS';

            $label = "{$solicitud->serie}-{$solicitud->correlativo} | {$cliente} | {$solicitud->moneda} " . number_format($solicitud->total, 2);

            return [
                'id' => $solicitud->id,
                'label' => $label,
            ];
        });

        return response()->json($items);
    }

    public function habitaciones(Request $request)
    {
        $limit = $this->sanitizeLimit($request->input('limit'));
        $term = trim((string) $request->input('q', ''));

        if ($term === '') {
            return response()->json([]);
        }

        $sucursalId = optional(Auth::user()->persona)->sucursal_id;

        $query = Habitacion::with('tipohabitacion', 'piso')
            ->select('id', 'numero', 'tipohabitacion_id', 'piso_id', 'precio')
            ->whereNull('deleted_at');

        if ($sucursalId) {
            $query->where('sucursal_id', $sucursalId);
        }

        $query->where(function ($q) use ($term) {
            $q->where('numero', 'ILIKE', "%{$term}%")
                ->orWhereHas('tipohabitacion', function ($tipo) use ($term) {
                    $tipo->where('nombre', 'ILIKE', "%{$term}%");
                })
                ->orWhereHas('piso', function ($piso) use ($term) {
                    $piso->where('nombre', 'ILIKE', "%{$term}%");
                });
        });

        $habitaciones = $query
            ->orderBy('numero')
            ->limit($limit)
            ->get();

        $items = $habitaciones->map(function ($habitacion) {
            $tipo = optional($habitacion->tipohabitacion)->nombre ?? 'Sin categoria';
            $piso = optional($habitacion->piso)->nombre ?? 'Sin piso';
            $precio = number_format($habitacion->precio ?? 0, 2);

            return [
                'id' => $habitacion->id,
                'label' => "Hab. {$habitacion->numero} - {$tipo} ({$piso}) - PEN {$precio}",
                'value' => $habitacion->numero,
            ];
        });

        return response()->json($items);
    }

    public function mediopagos(Request $request)
    {
        $limit = $this->sanitizeLimit($request->input('limit'));
        $term = trim((string) $request->input('q', ''));
        $pasarelaId = (int) $request->input('pasarela_id', 0);

        if ($term === '') {
            return response()->json([]);
        }

        $query = Mediopago::query()->where('situacion', true);

        if ($pasarelaId > 0) {
            $relacionados = RelMediopagopasarela::where('pasarela_id', $pasarelaId)
                ->pluck('medio_id');

            if ($relacionados->isNotEmpty()) {
                $query->whereNotIn('id', $relacionados);
            }
        }

        $medios = $query
            ->where('descripcion', 'ILIKE', "%{$term}%")
            ->orderBy('descripcion')
            ->limit($limit)
            ->get();

        $items = $medios->map(function ($medio) {
            return [
                'id' => $medio->id,
                'label' => $medio->descripcion,
                'descripcion' => $medio->descripcion,
            ];
        });

        return response()->json($items);
    }

    private function sanitizeLimit($limit, $default = 15)
    {
        $limit = is_numeric($limit) ? (int) $limit : $default;
        $limit = $limit <= 0 ? $default : $limit;
        return min($limit, 50);
    }

    private function parseIds($ids)
    {
        if (empty($ids)) {
            return [];
        }

        if (is_array($ids)) {
            return array_values(array_unique(array_filter(array_map('intval', $ids))));
        }

        return array_values(array_unique(array_filter(array_map('intval', explode(',', $ids)))));
    }
}
