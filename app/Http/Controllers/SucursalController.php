<?php

namespace App\Http\Controllers;

use App\Models\Sucursal;
use App\Http\Requests\SucursalRequest;
use App\Models\Operacione;
use App\Models\Parametro;
use App\Models\Perfile;
use App\Models\Perfilsucursal;
use App\Models\Permisousuario;
use App\Models\Persona;
use App\Models\Personamaestro;
use App\Models\RelCamposucursal;
use App\Models\RelOperacionsucursal;
use App\Models\RelOperacionsucursalperfil;
use App\Models\RelParametroempresa;
use App\Models\RelParametrosucursal;
use App\Models\RelSeccionsucursal;
use App\Models\RelSeccionsucursalperfil;
use App\Models\RelTablasucursal;
use App\Models\Rolpersona;
use App\Models\Ubigeo;
use App\Models\User;
use App\Models\Producto;
use App\Models\Categorium;
use App\Models\Listaunidad;
use App\Models\RelCategoriasucursal;
use App\Models\RelListaunidadsucursal;
use App\Models\RelProductosucursal;
use App\Models\Stockproducto;
use Illuminate\Support\Facades\Auth;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ProductosImport;
use App\Models\Facturador;

/**
 * Class SucursalController
 * @package App\Http\Controllers
 */
class SucursalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, $id)
    {
        $search = $request->input('search');
        $perPage = $request->input('perPage', 10); // valor por defecto 10

        $query = Sucursal::where('empresa_id', $id);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('razonsocial', 'ILIKE', "%{$search}%")
                    ->orWhere('nombrecomercial', 'ILIKE', "%{$search}%")
                    ->orWhere('direccion', 'ILIKE', "%{$search}%")
                    ->orWhere('ruc', 'ILIKE', "%{$search}%")
                    ->orWhere('email', 'ILIKE', "%{$search}%")
                    ->orWhere('telefonofijo', 'ILIKE', "%{$search}%")
                    ->orWhere('telefonomovil', 'ILIKE', "%{$search}%");
            });
        }

        $sucursales = $query->paginate($perPage)->appends([
            'search' => $search,
            'perPage' => $perPage,
        ]);

        $operaciones = Operacione::where('tabla_id', 22)
            ->where('estado', true)
            ->get();

        $empresa_id = $id;

        return view('sucursal.index', compact('sucursales', 'operaciones', 'empresa_id', 'search', 'perPage'))
            ->with('i', (request()->input('page', 1) - 1) * $sucursales->perPage());
    }



    /**
     * Show the form for creating a new resource.
     */
    public function create($empresa_id)
    {
        $sucursal = new Sucursal();
        $departamentos = Ubigeo::where('tipo', '2')->get();
        // Consultar las provincias relacionadas con el departamento seleccionado
        $provincias = Ubigeo::where('tipo', '3') // Asegúrate de que '3' es el tipo de provincia
            ->where('ubigeo_id', 15) // parent_id debe ser el ID del departamento
            ->get();
        $distritos = Ubigeo::where('tipo', '4') // Asegúrate de que '3' es el tipo de provincia
            ->where('ubigeo_id', 151) // parent_id debe ser el ID del departamento
            ->get();
        return view('sucursal.create', compact('sucursal', 'departamentos', 'provincias', 'distritos', 'empresa_id'));
    }


    public function store(SucursalRequest $request)
    {
        DB::beginTransaction(); // Iniciar transacción

        try {
            // Ajustar los valores de 'facturacion' y 'venta_mayorista' antes de guardar
            $data = $request->validated();
            $empresa_id = $request->input('empresa_id');

            // Si los checkboxes están marcados, se guarda como 'A', de lo contrario como 'I'
            $data['facturacion'] = $request->has('facturacion') ? 'A' : 'I';
            $data['venta_mayorista'] = $request->has('venta_mayorista') ? 'A' : 'I';
            // Verificar si se ha subido un logo
            if ($request->hasFile('logo') && $request->file('logo')->isValid()) {
                $logo = $request->file('logo');
                $logoName = time() . '_' . $logo->getClientOriginalName(); // Generar un nombre único
                $logoPath = storage_path('app/public/logos_sucursales');
                $logo->move($logoPath, $logoName);
                $data['logo'] = 'storage/logos_sucursales/' . $logoName;
            }


            // Crear la nueva sucursal con los datos ajustados
            $sucursal = Sucursal::create($data);

            // Obtener el ID de la sucursal base desde RelParametroempresa
            $parametroSucursalBase = RelParametroempresa::where('empresa_id', $empresa_id)->first();

            if (!$parametroSucursalBase) {
                throw new \Exception("No se pudo encontrar la sucursal base para replicar los datos.");
            }

            // Verificar que el valor sea numérico y convertirlo a entero
            if (!is_numeric($parametroSucursalBase->valor)) {
                throw new \Exception($parametroSucursalBase->valor);
            }

            $sucursalBaseId = (int) $parametroSucursalBase->valor;  // Convertir a entero

            // 1. Insertar en `rel_tablasucursal`
            $tablasBase = RelTablasucursal::where('sucursal_id', 1)->get();
            foreach ($tablasBase as $tabla) {
                RelTablasucursal::create([
                    'tabla_id' => $tabla->tabla_id,
                    'sucursal_id' => $sucursal->id
                ]);
            }

            // 2. Insertar en `perfilsucursal`
            $perfilesBase = Perfilsucursal::where('sucursal_id', 1)->get();
            foreach ($perfilesBase as $perfil) {
                Perfilsucursal::create([
                    'perfil_id' => $perfil->perfil_id,
                    'sucursal_id' => $sucursal->id
                ]);
            }

            // 3. Insertar en `permisousuario`
            $permisosBase = Permisousuario::where('sucursal_id', 1)->get();
            foreach ($permisosBase as $permiso) {
                Permisousuario::create([
                    'menuopcion_id' => $permiso->menuopcion_id,
                    'perfil_id' => $permiso->perfil_id,
                    'sucursal_id' => $sucursal->id
                ]);
            }

            // 4. Insertar en `rel_seccionsucursalperfil`
            $seccionPerfilBase = RelSeccionsucursalperfil::where('sucursal_id', 1)->get();
            foreach ($seccionPerfilBase as $seccionPerfil) {
                RelSeccionsucursalperfil::create([
                    'perfil_id' => $seccionPerfil->perfil_id,
                    'sucursal_id' => $sucursal->id,
                    'seccion_id' => $seccionPerfil->seccion_id,
                    'menuopcionesdefecto_id' => $seccionPerfil->menuopcionesdefecto_id
                ]);
            }

            // 5. Insertar en `rel_seccionsucursal`
            $seccionesBase = RelSeccionsucursal::where('sucursal_id', 1)->get();
            foreach ($seccionesBase as $seccion) {
                RelSeccionsucursal::create([
                    'seccion_id' => $seccion->seccion_id,
                    'sucursal_id' => $sucursal->id
                ]);
            }

            // 6. Insertar en `rel_parametrosucursal`
            $parametrosBase = RelParametrosucursal::where('sucursal_id', 1)->get();
            foreach ($parametrosBase as $parametro) {
                RelParametrosucursal::create([
                    'valor' => $parametro->valor,
                    'parametro_id' => $parametro->parametro_id,
                    'sucursal_id' => $sucursal->id
                ]);
            }

            // 7. Insertar en `rel_operacionsucursalperfil`
            $operacionPerfilBase = RelOperacionsucursalperfil::where('sucursal_id', 1)->get();
            foreach ($operacionPerfilBase as $operacionPerfil) {
                RelOperacionsucursalperfil::create([
                    'operacion_id' => $operacionPerfil->operacion_id,
                    'tabla_id' => $operacionPerfil->tabla_id,
                    'perfil_id' => $operacionPerfil->perfil_id,
                    'sucursal_id' => $sucursal->id
                ]);
            }

            // 8. Insertar en `rel_operacionsucursal`
            $operacionesBase = RelOperacionsucursal::where('sucursal_id', 1)->get();
            foreach ($operacionesBase as $operacion) {
                RelOperacionsucursal::create([
                    'orden' => $operacion->orden,
                    'operacion_id' => $operacion->operacion_id,
                    'tabla_id' => $operacion->tabla_id,
                    'sucursal_id' => $sucursal->id
                ]);
            }

            // 9. Insertar en `rel_camposucursal`
            $camposBase = RelCamposucursal::where('sucursal_id', 1)->get();
            foreach ($camposBase as $campo) {
                RelCamposucursal::create([
                    'orden' => $campo->orden,
                    'campo_id' => $campo->campo_id,
                    'tabla_id' => $campo->tabla_id,
                    'sucursal_id' => $sucursal->id
                ]);
            }
            // Crear el registro en 'personamaestro' con los datos de la sucursal
            $personamaestro = Personamaestro::create([
                'tipopersona' => 'J', // Persona jurídica
                'nombres' => $sucursal->nombrecomercial,
                'apellidos' => '', // No aplica para empresas
                'nrodoc' => $sucursal->ruc,
                'sexo' => 'N', // No aplica para empresas
            ]);

            // Crear el registro en 'persona' utilizando los datos de la sucursal
            $persona = Persona::create([
                'nombres' => $sucursal->nombrecomercial,
                'apellidos' => '', // No aplica para empresas
                'direccion' => $sucursal->direccion,
                'email' => $sucursal->email,
                'telefonofijo' => $sucursal->telefonofijo,
                'telefonomovil' => $sucursal->telefonomovil,
                'imagen' => '-',  // Imagen predeterminada
                'compartido' => 'S',  // Valor predeterminado
                'personamaestro_id' => $personamaestro->id,  // Relación con personamaestro
                'sucursal_id' => $sucursal->id,  // ID de la nueva sucursal creada
                'distrito_id' => 151, // Valor por defecto
            ]);

            // Crear el registro en 'user' con el RUC como nombre de usuario y contraseña
            $user = User::create([
                'name' => $sucursal->ruc,  // Usar el RUC como nombre de usuario
                'email' => $sucursal->email, // Email de la sucursal
                'password' => bcrypt($sucursal->ruc), // Usar el RUC como contraseña cifrada
                'persona_id' => $persona->id,  // Relación con la persona creada
                'perfil_id' => 10,

            ]);

            // Crear el registro en 'rolpersona' asignando el rol_id = 1
            Rolpersona::create([
                'persona_id' => $persona->id, // Relacionar con la persona creada
                'rol_id' => 1,  // Asignar el rol con ID 1
                'sucursal_id' => $sucursal->id // Relacionar con la nueva sucursal
            ]);
            if ($request->input('facturacion') == 'S') {

                // Obtener el facturador asociado a la sucursal base
                $facturadorBase = Facturador::where('sucursal_id', 39)->first();

                if ($facturadorBase) {
                    // Crear el registro en 'facturador' para la nueva sucursal
                    Facturador::create([
                        'empresa_facturador_id' => $sucursal->empresa_id,  // Relacionar con la empresa recién creada
                        'porcentaje_igv' => $facturadorBase->porcentaje_igv,  // Copiar el porcentaje de IGV de la base
                        'username_wsdl' => $facturadorBase->username_wsdl,  // Copiar el username_wsdl de la base
                        'password_wsdl' => $facturadorBase->password_wsdl,  // Copiar el password_wsdl de la base
                        'modo_autenticacion' => $facturadorBase->modo_autenticacion,  // Copiar el modo de autenticación
                        'username_sunat' => $facturadorBase->username_sunat,  // Copiar el username_sunat de la base
                        'password_sunat' => $facturadorBase->password_sunat,  // Copiar el password_sunat de la base
                        'situacion' => $facturadorBase->situacion,  // Copiar la situación del facturador de la base
                        'sucursal_id' => $sucursal->id,  // Asignar la nueva sucursal creada
                    ]);
                }
            }

            // Confirmar la transacción
            DB::commit();

            // Retornar la respuesta exitosa
            return response()->json([
                'success' => true,
                'message' => 'Sucursal creada exitosamente junto con las relaciones replicadas.',
                'sucursal' => $sucursal,
                'redirect_url' => route('verSucursal', $empresa_id)
            ]);
        } catch (\Exception $e) {
            // Si ocurre un error, revertir la transacción
            DB::rollBack();

            // Retornar el error
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $sucursal = Sucursal::find($id);

        return view('sucursal.show', compact('sucursal'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $sucursal = Sucursal::find($id);
        //        dd($sucursal);

        $departamentos = Ubigeo::where('tipo', '2')->get();
        $provincias = Ubigeo::where('tipo', '3')
            ->where('ubigeo_id', $sucursal->ubigeo->ubigeo->ubigeo_id)
            ->get();
        $distritos = Ubigeo::where('tipo', '4')
            ->where('ubigeo_id', $sucursal->ubigeo->ubigeo_id)
            ->get();
        $empresa_id = $sucursal->empresa_id;
        return view('sucursal.edit', compact('sucursal', 'departamentos', 'provincias', 'distritos', 'empresa_id'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SucursalRequest $request, Sucursal $sucursal)
    {
        try {
            $data = $request->validated();
            $empresa_id = $request->input('empresa_id');

            // Si los checkboxes están marcados, se guarda como 'S', de lo contrario como 'N'
            $data['facturacion'] = $request->has('facturacion') ? 'S' : 'N';
            $data['venta_mayorista'] = $request->has('venta_mayorista') ? 'S' : 'N';

            // Verificar si se ha subido un logo
            if ($request->hasFile('logo') && $request->file('logo')->isValid()) {
                $logo = $request->file('logo');
                $logoName = time() . '_' . $logo->getClientOriginalName(); // Generar un nombre único
                $logoPath = storage_path('app/public/logos_sucursales');
                $logo->move($logoPath, $logoName);
                $data['logo'] = 'storage/logos_sucursales/' . $logoName;
            }

            // Actualizar la sucursal con los nuevos datos
            $sucursal->update($data);

            // Insertar relaciones sin duplicados
            // 1. Insertar en `rel_tablasucursal`
            $tablasBase = RelTablasucursal::where('sucursal_id', 1)->get();
            foreach ($tablasBase as $tabla) {
                if (!RelTablasucursal::where('sucursal_id', $sucursal->id)->where('tabla_id', $tabla->tabla_id)->exists()) {
                    RelTablasucursal::create([
                        'tabla_id' => $tabla->tabla_id,
                        'sucursal_id' => $sucursal->id
                    ]);
                }
            }

            // 2. Insertar en `perfilsucursal`
            $perfilesBase = Perfilsucursal::where('sucursal_id', 1)->get();
            foreach ($perfilesBase as $perfil) {
                if (!Perfilsucursal::where('sucursal_id', $sucursal->id)->where('perfil_id', $perfil->perfil_id)->exists()) {
                    Perfilsucursal::create([
                        'perfil_id' => $perfil->perfil_id,
                        'sucursal_id' => $sucursal->id
                    ]);
                }
            }

            // 3. Insertar en `permisousuario`
            $permisosBase = Permisousuario::where('sucursal_id', 1)->get();
            foreach ($permisosBase as $permiso) {
                if (!Permisousuario::where('sucursal_id', $sucursal->id)->where('menuopcion_id', $permiso->menuopcion_id)->exists()) {
                    Permisousuario::create([
                        'menuopcion_id' => $permiso->menuopcion_id,
                        'perfil_id' => $permiso->perfil_id,
                        'sucursal_id' => $sucursal->id
                    ]);
                }
            }

            // 4. Insertar en `rel_seccionsucursalperfil`
            $seccionPerfilBase = RelSeccionsucursalperfil::where('sucursal_id', 1)->get();

            foreach ($seccionPerfilBase as $seccionPerfil) {
                // Verificamos si este perfil ya existe en la sucursal actual
                $exists = RelSeccionsucursalperfil::where('sucursal_id', $sucursal->id)
                    ->where('perfil_id', $seccionPerfil->perfil_id)
                    ->exists();

                // Si no existe, lo insertamos
                if (!$exists) {
                    RelSeccionsucursalperfil::create([
                        'perfil_id' => $seccionPerfil->perfil_id,
                        'sucursal_id' => $sucursal->id,
                        'seccion_id' => $seccionPerfil->seccion_id,
                        'menuopcionesdefecto_id' => $seccionPerfil->menuopcionesdefecto_id
                    ]);
                }
            }


            // 5. Insertar en `rel_seccionsucursal`
            $seccionesBase = RelSeccionsucursal::where('sucursal_id', 1)->get();
            foreach ($seccionesBase as $seccion) {
                if (!RelSeccionsucursal::where('sucursal_id', $sucursal->id)->where('seccion_id', $seccion->seccion_id)->exists()) {
                    RelSeccionsucursal::create([
                        'seccion_id' => $seccion->seccion_id,
                        'sucursal_id' => $sucursal->id
                    ]);
                }
            }

            // 6. Insertar en `rel_parametrosucursal`
            $parametrosBase = RelParametrosucursal::where('sucursal_id', 1)->get();
            foreach ($parametrosBase as $parametro) {
                if (!RelParametrosucursal::where('sucursal_id', $sucursal->id)->where('parametro_id', $parametro->parametro_id)->exists()) {
                    RelParametrosucursal::create([
                        'valor' => $parametro->valor,
                        'parametro_id' => $parametro->parametro_id,
                        'sucursal_id' => $sucursal->id
                    ]);
                }
            }

            // 7. Insertar en `rel_operacionsucursalperfil`
            $operacionPerfilBase = RelOperacionsucursalperfil::where('sucursal_id', 1)->get();
            foreach ($operacionPerfilBase as $operacionPerfil) {
                if (!RelOperacionsucursalperfil::where('sucursal_id', $sucursal->id)->where('operacion_id', $operacionPerfil->operacion_id)->exists()) {
                    RelOperacionsucursalperfil::create([
                        'operacion_id' => $operacionPerfil->operacion_id,
                        'tabla_id' => $operacionPerfil->tabla_id,
                        'perfil_id' => $operacionPerfil->perfil_id,
                        'sucursal_id' => $sucursal->id
                    ]);
                }
            }

            // 8. Insertar en `rel_operacionsucursal`
            $operacionesBase = RelOperacionsucursal::where('sucursal_id', 1)->get();
            foreach ($operacionesBase as $operacion) {
                if (!RelOperacionsucursal::where('sucursal_id', $sucursal->id)->where('operacion_id', $operacion->operacion_id)->exists()) {
                    RelOperacionsucursal::create([
                        'orden' => $operacion->orden,
                        'operacion_id' => $operacion->operacion_id,
                        'tabla_id' => $operacion->tabla_id,
                        'sucursal_id' => $sucursal->id
                    ]);
                }
            }

            // 9. Insertar en `rel_camposucursal`
            $camposBase = RelCamposucursal::where('sucursal_id', 1)->get();
            foreach ($camposBase as $campo) {
                if (!RelCamposucursal::where('sucursal_id', $sucursal->id)->where('campo_id', $campo->campo_id)->exists()) {
                    RelCamposucursal::create([
                        'orden' => $campo->orden,
                        'campo_id' => $campo->campo_id,
                        'tabla_id' => $campo->tabla_id,
                        'sucursal_id' => $sucursal->id
                    ]);
                }
            }

            // Confirmar la transacción
            DB::commit();

            // Retornar la respuesta exitosa
            return response()->json([
                'success' => true,
                'message' => 'Sucursal actualizada correctamente.',
                'redirect_url' => route('verSucursal', ['empresa' => $empresa_id])
            ]);
        } catch (\Exception $e) {
            // Si ocurre un error, revertir la transacción
            DB::rollBack();

            // Retornar el error
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    public function activar($id)
    {
        try {
            $sucursal = Sucursal::find($id);
            $sucursal->update(['situacion' => 'A']);

            // Llamar a la función index para reutilizar la lógica de cargar la vista
            return response()->json([
                'success' => true,
                'message' => 'Sucursal activada exitosamente.',
                'sucursal' => $sucursal,
                'redirect_url' => route('verSucursal', $sucursal->empresa_id)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    public function desactivar($id)
    {
        try {
            $sucursal = Sucursal::find($id);
            $sucursal->update(['situacion' => 'D']);

            return response()->json([
                'success' => true,
                'message' => 'Sucursal desactivada exitosamente.',
                'sucursal' => $sucursal,
                'redirect_url' => route('verSucursal', $sucursal->empresa_id)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    public function importProductos($sucursal_id)
    {

        return view('sucursal.importarProductos', compact('sucursal_id'));
    }


    public function importarConfiguracionStock(Request $request, $id)
    {
        $request->validate([
            'productos' => 'required|mimes:xlsx,xls'
        ]);

        DB::beginTransaction();
        $sucursal = Sucursal::find($id);
        try {
            // Importar productos usando una clase de importación personalizada
            Excel::import(new ProductosImport($id), $request->file('productos'));
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Productos importados correctamente.',
                'redirect_url' => route('verSucursal', $sucursal->empresa_id)

            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'error' => 'Error al importar productos: ' . $e->getMessage()
            ], 500);
        }
    }

    public function transferirProductos($sucursal_id)
    {
        $sucursalDestino = Sucursal::with('empresa')->findOrFail($sucursal_id);

        $sucursalesOrigen = Sucursal::with('empresa')
            ->where('id', '!=', $sucursalDestino->id)
            ->orderBy('nombrecomercial')
            ->get()
            ->sortBy(function ($sucursal) {
                return ($sucursal->empresa->razon_social ?? '') . $sucursal->nombrecomercial;
            })
            ->values();

        return view('sucursal.transferirProductos', compact('sucursalDestino', 'sucursalesOrigen'));
    }

    public function productosParaTransferencia($sucursal_id)
    {
        $sucursal = Sucursal::findOrFail($sucursal_id);

        $productoIds = RelProductosucursal::where('sucursal_id', $sucursal->id)
            ->pluck('producto_id')
            ->unique()
            ->values();

        $productos = Producto::with('categorium')
            ->whereIn('id', $productoIds)
            ->orderBy('descripcion')
            ->get()
            ->map(function ($producto) {
                return [
                    'id' => $producto->id,
                    'codigo' => $producto->codigo,
                    'descripcion' => $producto->descripcion,
                    'categoria' => $producto->categorium->descripcion ?? '',
                ];
            });

        return response()->json($productos);
    }

    public function guardarTransferenciaProductos(Request $request, $sucursal_id)
    {
        $sucursalDestino = Sucursal::findOrFail($sucursal_id);

        $request->validate([
            'sucursal_origen_id' => 'required|integer|exists:sucursal,id|different:' . $sucursalDestino->id,
            'transferir_todos' => 'nullable|boolean',
            'mover_productos' => 'nullable|boolean',
            'productos' => 'required_without:transferir_todos|array',
            'productos.*' => 'integer|exists:producto,id',
        ]);

        $sucursalOrigen = Sucursal::findOrFail($request->input('sucursal_origen_id'));

        $productoIds = RelProductosucursal::where('sucursal_id', $sucursalOrigen->id)
            ->when(!$request->boolean('transferir_todos'), function ($query) use ($request) {
                $query->whereIn('producto_id', $request->input('productos', []));
            })
            ->pluck('producto_id')
            ->unique()
            ->values();

        if ($productoIds->isEmpty()) {
            return response()->json([
                'success' => false,
                'errors' => 'No hay productos seleccionados para importar.'
            ], 422);
        }

        $productosOrigen = Producto::whereIn('id', $productoIds)->get()->keyBy('id');
        $idsFaltantes = $productoIds->filter(fn ($id) => !$productosOrigen->has($id))->values();

        if ($idsFaltantes->isNotEmpty()) {
            return response()->json([
                'success' => false,
                'errors' => 'Algunos productos seleccionados ya no existen o fueron eliminados.'
            ], 422);
        }

        DB::beginTransaction();

        try {
            foreach ($productoIds as $productoId) {
                $productoOrigen = $productosOrigen->get($productoId);
                $this->transferirProductoEntreSucursales(
                    $productoOrigen,
                    $sucursalOrigen,
                    $sucursalDestino,
                    $request->boolean('mover_productos')
                );
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $request->boolean('mover_productos')
                    ? 'Productos importados y retirados del origen correctamente.'
                    : 'Productos importados correctamente.',
                'redirect_url' => route('verSucursal', $sucursalDestino->empresa_id)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'errors' => 'Error al importar productos: ' . $e->getMessage()
            ], 500);
        }
    }

    private function transferirProductoEntreSucursales(
        Producto $productoOrigen,
        Sucursal $sucursalOrigen,
        Sucursal $sucursalDestino,
        bool $moverProductos
    ): void {
        $productoDestino = $this->resolverProductoDestino($productoOrigen, $sucursalDestino);

        $relOrigen = RelProductosucursal::where('sucursal_id', $sucursalOrigen->id)
            ->where('producto_id', $productoOrigen->id)
            ->firstOrFail();

        $relDestino = $relOrigen->replicate();
        $relDestino->sucursal_id = $sucursalDestino->id;
        $relDestino->producto_id = $productoDestino->id;

        RelProductosucursal::updateOrCreate(
            [
                'sucursal_id' => $sucursalDestino->id,
                'producto_id' => $productoDestino->id,
            ],
            collect($relDestino->getAttributes())->only($relDestino->getFillable())->all()
        );

        if ($productoDestino->categoria_id) {
            $relCategoriaOrigen = RelCategoriasucursal::where('sucursal_id', $sucursalOrigen->id)
                ->where('categoria_id', $productoOrigen->categoria_id)
                ->first();

            RelCategoriasucursal::updateOrCreate(
                [
                    'sucursal_id' => $sucursalDestino->id,
                    'categoria_id' => $productoDestino->categoria_id,
                ],
                [
                    'tipocarta' => $relCategoriaOrigen->tipocarta ?? 'NINGUNA',
                    'situacion' => $relCategoriaOrigen->situacion ?? 'A',
                ]
            );
        }

        Stockproducto::where('sucursal_id', $sucursalOrigen->id)
            ->where('producto_id', $productoOrigen->id)
            ->get()
            ->each(function ($stockOrigen) use ($sucursalDestino, $productoDestino) {
                $stockDestino = $stockOrigen->replicate();
                $stockDestino->sucursal_id = $sucursalDestino->id;
                $stockDestino->producto_id = $productoDestino->id;

                Stockproducto::updateOrCreate(
                    [
                        'sucursal_id' => $sucursalDestino->id,
                        'producto_id' => $productoDestino->id,
                        'unidadbase_id' => $stockOrigen->unidadbase_id,
                    ],
                    collect($stockDestino->getAttributes())->only($stockDestino->getFillable())->all()
                );
            });

        Listaunidad::where('producto_id', $productoOrigen->id)
            ->where('empresa_id', $productoOrigen->empresa_id)
            ->get()
            ->each(function ($listaOrigen) use ($sucursalOrigen, $sucursalDestino, $productoDestino) {
                $listaDestino = Listaunidad::firstOrCreate(
                    [
                        'producto_id' => $productoDestino->id,
                        'unidad_id' => $listaOrigen->unidad_id,
                        'empresa_id' => $sucursalDestino->empresa_id,
                    ],
                    [
                        'formula' => $listaOrigen->formula,
                        'unidadbase_id' => $listaOrigen->unidadbase_id,
                    ]
                );

                $relListaOrigen = RelListaunidadsucursal::where('sucursal_id', $sucursalOrigen->id)
                    ->where('listaunidad_id', $listaOrigen->id)
                    ->first();

                if ($relListaOrigen) {
                    $relListaDestino = $relListaOrigen->replicate();
                    $relListaDestino->sucursal_id = $sucursalDestino->id;
                    $relListaDestino->listaunidad_id = $listaDestino->id;

                    RelListaunidadsucursal::updateOrCreate(
                        [
                            'sucursal_id' => $sucursalDestino->id,
                            'listaunidad_id' => $listaDestino->id,
                        ],
                        collect($relListaDestino->getAttributes())->only($relListaDestino->getFillable())->all()
                    );
                }
            });

        if ($moverProductos) {
            RelProductosucursal::where('sucursal_id', $sucursalOrigen->id)
                ->where('producto_id', $productoOrigen->id)
                ->delete();
            Stockproducto::where('sucursal_id', $sucursalOrigen->id)
                ->where('producto_id', $productoOrigen->id)
                ->delete();
            RelListaunidadsucursal::where('sucursal_id', $sucursalOrigen->id)
                ->whereHas('listaunidad', function ($query) use ($productoOrigen) {
                    $query->where('producto_id', $productoOrigen->id);
                })
                ->delete();
        }
    }

    private function resolverProductoDestino(Producto $productoOrigen, Sucursal $sucursalDestino): Producto
    {
        if ((int) $productoOrigen->empresa_id === (int) $sucursalDestino->empresa_id) {
            return $productoOrigen;
        }

        $productoExistente = Producto::where('empresa_id', $sucursalDestino->empresa_id)
            ->where('codigo', $productoOrigen->codigo)
            ->first();

        if ($productoExistente) {
            return $productoExistente;
        }

        $atributos = collect($productoOrigen->getAttributes())
            ->only($productoOrigen->getFillable())
            ->all();

        $atributos['empresa_id'] = $sucursalDestino->empresa_id;

        if ($productoOrigen->categoria_id) {
            $atributos['categoria_id'] = $this->resolverCategoriaDestino(
                $productoOrigen->categoria_id,
                $sucursalDestino->empresa_id
            );
        }

        return Producto::create($atributos);
    }

    private function resolverCategoriaDestino(int $categoriaId, int $empresaDestinoId): ?int
    {
        $categoriaOrigen = Categorium::find($categoriaId);

        if (!$categoriaOrigen) {
            return null;
        }

        $categoriaDestino = Categorium::where('empresa_id', $empresaDestinoId)
            ->where('descripcion', $categoriaOrigen->descripcion)
            ->first();

        if ($categoriaDestino) {
            return $categoriaDestino->id;
        }

        $atributos = collect($categoriaOrigen->getAttributes())
            ->only($categoriaOrigen->getFillable())
            ->all();

        $atributos['empresa_id'] = $empresaDestinoId;

        return Categorium::create($atributos)->id;
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id, $empresa_id)
    {
        try {
            $sucursal = Sucursal::find($id);
            if ($sucursal) {
                $sucursal->delete();
                if ($empresa_id != null) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Sucursal eliminada correctamente.',
                        'redirect_url' => route('verSucursal', $empresa_id)
                    ]);
                } else {
                    return response()->json([
                        'success' => true,
                        'message' => 'Sucursal eliminada correctamente.',
                        'redirect_url' => route('sucursal.index')
                    ]);
                }
            } else {
                throw new Exception("Sucursal no encontrada.");
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    public function getProvincias($departamento_id)
    {
        // Consultar las provincias relacionadas con el departamento seleccionado
        $provincias = Ubigeo::where('tipo', '3')
            ->where('ubigeo_id', $departamento_id)
            ->get();

        return response()->json($provincias);
    }
    public function getDistritos($provincia_id)
    {
        // Consultar las provincias relacionadas con el departamento seleccionado
        $distritos = Ubigeo::where('tipo', '4')
            ->where('ubigeo_id', $provincia_id)
            ->get();

        return response()->json($distritos);
    }

    public function verPerfilSucursal(Request $request, $id)
    {
        $perPage = $request->input('perPage', 10); // Cantidad de resultados por página
        $search = $request->input('search'); // Texto de búsqueda

        $query = Perfilsucursal::where('sucursal_id', $id);

        // 🔹 Búsqueda case-insensitive por descripción
        if ($search) {
            $search = mb_strtolower($search);

            $query->whereRaw('LOWER(descripcion) LIKE ?', ["%{$search}%"]);
        }

        $perfilsucursals = $query->paginate($perPage)->appends([
            'search' => $request->input('search'),
            'perPage' => $perPage
        ]);

        $operaciones = Operacione::where('tabla_id', 29)
            ->where('estado', true)
            ->get();

        $sucursal_id = $id;

        return view('perfilsucursal.index', compact('perfilsucursals', 'operaciones', 'sucursal_id'))
            ->with('i', (request()->input('page', 1) - 1) * $perfilsucursals->perPage());
    }

    public function verPersonalSucursal(Request $request, $id)
    {
        $perPage = $request->input('perPage', 10); // Registros por página
        $search = $request->input('search'); // Texto de búsqueda
        $no_grato = $request->input('no_grato');

        $query = Persona::with('personamaestro', 'rolpersonas')
            ->whereHas('rolpersonas', function ($q) {
                $q->where('rol_id', 1); // Rol de responsable
            })
            ->where('sucursal_id', $id);

        if ($no_grato !== null && $no_grato !== '' && $no_grato !== 'Todos') {
            if ($no_grato === '1' || $no_grato === 'SI' || $no_grato === 'true') {
                $query->where('no_grato', true);
            } elseif ($no_grato === '0' || $no_grato === 'NO' || $no_grato === 'false') {
                $query->where(function ($q) {
                    $q->where('no_grato', false)->orWhereNull('no_grato');
                });
            }
        }

        // 🔹 Búsqueda case-insensitive
        if ($search) {
            $search = mb_strtolower($search); // Convertimos a minúsculas en PHP

            $query->where(function ($q) use ($search) {
                // Campos de Persona
                $q->whereRaw('LOWER(nombres) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(apellidos) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(direccion) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(email) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(telefonofijo) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(telefonomovil) LIKE ?', ["%{$search}%"]);

                // Campos de Personamaestro relacionados
                $q->orWhereHas('personamaestro', function ($qm) use ($search) {
                    $qm->whereRaw('LOWER(nombres) LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(apellidos) LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(nrodoc) LIKE ?', ["%{$search}%"]);
                });
            });
        }

        $personas = $query->paginate($perPage)->appends([
            'search' => $request->input('search'),
            'no_grato' => $request->input('no_grato'),
            'perPage' => $perPage
        ]);

        $operaciones = Operacione::where('tabla_id', 32)
            ->where('estado', true)
            ->get();

        $sucursal_id = $id;

        if ($request->ajax()) {
            return view('persona.index', compact('personas', 'operaciones', 'sucursal_id'))->render();
        }

        return view('persona.index', compact('personas', 'operaciones', 'sucursal_id'))
            ->with('i', (request()->input('page', 1) - 1) * $personas->perPage());
    }



    public function verParametros($id)
    {
        $relParametrosucursals = RelParametrosucursal::where('sucursal_id', $id)->paginate();
        $operaciones = Operacione::where('tabla_id', 36)->where('estado', true)->get();
        $sucursal_id = $id;

        return view('rel-parametrosucursal.index', compact('relParametrosucursals', 'operaciones', 'sucursal_id'))
            ->with('i', (request()->input('page', 1) - 1) * $relParametrosucursals->perPage());
    }

    public function verTablas($id)
    {
        $relTablasucursals = RelTablasucursal::where('sucursal_id', $id)->paginate(10); // 10 resultados por página
        $operaciones = Operacione::where('tabla_id', 30)->where('estado', true)->get();
        $sucursal_id = $id;

        return view('rel-tablasucursal.index', compact('relTablasucursals', 'operaciones', 'sucursal_id'))
            ->with('i', (request()->input('page', 1) - 1) * $relTablasucursals->perPage());
    }

    public function verSecciones(Request $request, $id)
    {
        $perPage = $request->input('perPage', 10); // Cantidad de resultados por página (default 10)
        $search = $request->input('search'); // Texto a buscar

        $query = RelSeccionsucursal::with('seccione')
            ->where('sucursal_id', $id);

        // 🔹 Búsqueda sin distinguir mayúsculas/minúsculas
        if ($search) {
            $search = mb_strtolower($search); // Convertimos el término a minúsculas en PHP

            $query->whereHas('seccione', function ($q) use ($search) {
                $q->whereRaw("LOWER(descripcion) LIKE ?", ["%{$search}%"])
                    ->orWhereRaw("LOWER(abreviatura) LIKE ?", ["%{$search}%"]);
            });
        }

        $relSeccionsucursals = $query->paginate($perPage)->appends([
            'search' => $request->input('search'),
            'perPage' => $perPage
        ]);

        $operaciones = Operacione::where('tabla_id', 31)
            ->where('estado', true)
            ->get();

        $sucursal_id = $id;

        return view('rel-seccionsucursal.index', compact('relSeccionsucursals', 'operaciones', 'sucursal_id'))
            ->with('i', (request()->input('page', 1) - 1) * $relSeccionsucursals->perPage());
    }



    public function verFacturacion($id)
    {
        $operaciones = Operacione::where('tabla_id', 67)
            ->where('estado', true)
            ->get();
        $facturadores = Facturador::where('sucursal_id', $id)->paginate(10);
        $sucursal_id = $id;

        return view('facturador.index', compact('facturadores', 'operaciones', 'sucursal_id'))
            ->with('i', (request()->input('page', 1) - 1) * $facturadores->perPage());
    }

    public function business_intelligence()
    {
        $user = Auth::user();
        $persona = Persona::find($user->persona_id);
        $sucursal = Sucursal::find($persona->sucursal_id);
        $sucursal_id = $sucursal->id;
        return view('powerbi.bi', compact('sucursal'));
    }
    public function limpiar($id)
    {
        DB::beginTransaction();

        try {
            // Borra kardex
            DB::table('kardex')->where('sucursal_id', $id)->delete();

            // Borra detalles de movimientos de venta
            DB::table('det_movventa')->where('sucursal_id', $id)->delete();

            // Borra movimientos de venta
            DB::table('movventa')->where('sucursal_id', $id)->delete();

            // Borra movimientos de caja
            DB::table('movcaja')->where('sucursal_id', $id)->delete();

            // Borra movimientos
            DB::table('movimiento')->where('sucursal_id', $id)->delete();

            // Actualiza caja: pone situacion = 'C' para esa sucursal
            DB::table('caja')->where('sucursal_id', $id)->update(['situacion' => 'C']);

            DB::commit();

            return response()->json([
                'success' => true,
                'redirect_url' => route('sucursal.index'),
                'message' => 'Datos de la sucursal limpiados correctamente.',
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error al limpiar la sucursal: ' . $e->getMessage(),
            ], 500);
        }
    }
}
