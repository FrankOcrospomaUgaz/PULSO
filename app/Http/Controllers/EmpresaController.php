<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\Operacione;
use App\Http\Requests\EmpresaRequest;
use App\Models\Facturador;
use App\Models\Parametro;
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
use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Class EmpresaController
 * @package App\Http\Controllers
 */
class EmpresaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $query = Empresa::query();

        // Capturar término de búsqueda
        $search = request()->input('search');
        $perPage = request()->input('perPage', 10); // Default 20 si no envían nada

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('id', 'ILIKE', "%{$search}%")
                    ->orWhere('razon_social', 'ILIKE', "%{$search}%")
                    ->orWhere('direccion', 'ILIKE', "%{$search}%")
                    ->orWhere('ruc', 'ILIKE', "%{$search}%")
                    ->orWhere('email', 'ILIKE', "%{$search}%")
                    ->orWhere('telefono_fijo', 'ILIKE', "%{$search}%")
                    ->orWhere('telefono_movil', 'ILIKE', "%{$search}%")
                    ->orWhere('logo', 'ILIKE', "%{$search}%");
            });
        }

        // Aplicar perPage dinámico
        $empresas = $query->orderBy('id', 'desc')->paginate($perPage);

        $operaciones = Operacione::where('tabla_id', 18)
            ->where('estado', true)
            ->get();

        return view('empresa.index', compact('empresas', 'operaciones'))
            ->with('i', (request()->input('page', 1) - 1) * $empresas->perPage());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $empresa = new Empresa();

        return view('empresa.create', compact('empresa'));
    }

    public function miempresa()
    {
        // Obtener el usuario autenticado
        $user = auth()->user();

        // Obtener la sucursal asociada a la persona
        $persona = Persona::find($user->persona_id);

        // Obtener la empresa asociada a la sucursal
        $sucursal = Sucursal::find($persona->sucursal_id);

        $empresa = Empresa::find($sucursal->empresa_id);


        return view('empresa.miempresa', compact('empresa'));
    }



    /**
     * Store a newly created resource in storage.
     */
    public function store(EmpresaRequest $request)
    {
        DB::beginTransaction(); // Iniciar transacción

        try {
            // Crear la empresa
            $empresa = Empresa::create($request->validated());

            // Crear una sucursal asociada con la empresa recién creada
            $sucursalData = [
                'razonsocial' => $empresa->razon_social,
                'nombrecomercial' => $empresa->razon_social,
                'direccion' => $empresa->direccion,
                'ruc' => $empresa->ruc,
                'email' => $empresa->email,
                'telefonofijo' => $empresa->telefono_fijo,
                'telefonomovil' => $empresa->telefono_movil,
                'logo' => $empresa->logo,
                'empresa_id' => $empresa->id,
                'ubigeo_id' => 151,
                'facturacion' => 'N',
                'situacion' => 'A',
                'tipo' => $request->input('tipo') ?? 'Venta',
                'venta_mayorista' => 'I',
                'business_intelligence_url' => $request->input('business_intelligence_url') ?? '',
            ];

            // Crear la sucursal
            $sucursal = Sucursal::create($sucursalData);

            // Obtener la sucursal base (sucursal_id = 1)
            $sucursalBaseId = 1;

            // 1. Insertar en `rel_tablasucursal`
            $tablasBase = RelTablasucursal::where('sucursal_id', $sucursalBaseId)->get();
            foreach ($tablasBase as $tabla) {
                RelTablasucursal::create([
                    'tabla_id' => $tabla->tabla_id,
                    'sucursal_id' => $sucursal->id
                ]);
            }
            // 2. Insertar en `perfilsucursal`
            $perfilesBase = Perfilsucursal::where('sucursal_id', $sucursalBaseId)->get();
            foreach ($perfilesBase as $perfil) {
                Perfilsucursal::create([
                    'perfil_id' => $perfil->perfil_id,
                    'sucursal_id' => $sucursal->id
                ]);
            }

            // 3. Insertar en `permisousuario`
            $permisosBase = Permisousuario::where('sucursal_id', $sucursalBaseId)->get();
            foreach ($permisosBase as $permiso) {
                Permisousuario::create([
                    'menuopcion_id' => $permiso->menuopcion_id,
                    'perfil_id' =>  $permiso->perfil_id,
                    'situacion' => $permiso->situacion,
                    'sucursal_id' => $sucursal->id // Asigna la nueva sucursal creada
                ]);
            }

            // 4. Insertar en `rel_seccionsucursalperfil`
            $seccionPerfilBase = RelSeccionsucursalperfil::where('sucursal_id', $sucursalBaseId)->get();
            foreach ($seccionPerfilBase as $seccionPerfil) {
                RelSeccionsucursalperfil::create([
                    'perfil_id' => $seccionPerfil->perfil_id,
                    'sucursal_id' => $sucursal->id,
                    'seccion_id' => $seccionPerfil->seccion_id,
                    'menuopcionesdefecto_id' => $seccionPerfil->menuopcionesdefecto_id
                ]);
            }

            // 5. Insertar en `rel_seccionsucursal`
            $seccionesBase = RelSeccionsucursal::where('sucursal_id', $sucursalBaseId)->get();
            foreach ($seccionesBase as $seccion) {
                RelSeccionsucursal::create([
                    'seccion_id' => $seccion->seccion_id,
                    'sucursal_id' => $sucursal->id
                ]);
            }

            // 6. Insertar en `rel_parametrosucursal`
            $parametrosBase = RelParametrosucursal::where('sucursal_id', $sucursalBaseId)->get();
            foreach ($parametrosBase as $parametro) {
                RelParametrosucursal::create([
                    'valor' => $parametro->valor,
                    'parametro_id' => $parametro->parametro_id,
                    'sucursal_id' => $sucursal->id
                ]);
            }

            // 7. Insertar en `rel_operacionsucursalperfil`
            $operacionPerfilBase = RelOperacionsucursalperfil::where('sucursal_id', $sucursalBaseId)->get();
            foreach ($operacionPerfilBase as $operacionPerfil) {
                RelOperacionsucursalperfil::create([
                    'operacion_id' => $operacionPerfil->operacion_id,
                    'tabla_id' => $operacionPerfil->tabla_id,
                    'perfil_id' => $operacionPerfil->perfil_id,
                    'sucursal_id' => $sucursal->id
                ]);
            }

            // 8. Insertar en `rel_operacionsucursal`
            $operacionesBase = RelOperacionsucursal::where('sucursal_id', $sucursalBaseId)->get();
            foreach ($operacionesBase as $operacion) {
                RelOperacionsucursal::create([
                    'orden' => $operacion->orden,
                    'operacion_id' => $operacion->operacion_id,
                    'tabla_id' => $operacion->tabla_id,
                    'sucursal_id' => $sucursal->id
                ]);
            }

            // 9. Insertar en `rel_camposucursal`
            $camposBase = RelCamposucursal::where('sucursal_id', $sucursalBaseId)->get();
            foreach ($camposBase as $campo) {
                RelCamposucursal::create([
                    'orden' => $campo->orden,
                    'campo_id' => $campo->campo_id,
                    'tabla_id' => $campo->tabla_id,
                    'sucursal_id' => $sucursal->id
                ]);
            }

            // 10. Crear el registro en 'personamaestro' con los datos de la empresa
            $personamaestro = Personamaestro::create([
                'tipopersona' => 'Juridico', // Asumimos que es una persona jurídica
                'nombres' => $empresa->razon_social,
                'apellidos' => '', // En el caso de empresas, no hay apellidos
                'nrodoc' => $empresa->ruc,
                'sexo' => 'N',
            ]);

            // 11. Crear el registro en 'persona' utilizando los datos de la empresa
            $persona = Persona::create([
                'nombres' => $empresa->razon_social,
                'apellidos' => '', // No hay apellidos para empresas
                'direccion' => $empresa->direccion,
                'email' => $empresa->email,
                'telefonofijo' => $empresa->telefono_fijo,
                'telefonomovil' => $empresa->telefono_movil,
                'imagen' => '-',  // Imagen predeterminada
                'compartido' => 'S',  // Valor predeterminado
                'personamaestro_id' => $personamaestro->id,  // Relación con personamaestro
                'sucursal_id' => $sucursal->id,  // ID de la nueva sucursal creada
                'distrito_id' => 151, // Asumiendo un valor por defecto
            ]);

            // 12. Crear el registro en 'user' con el RUC como nombre de usuario y contraseña
            $user = User::create([
                'name' => $empresa->ruc,  // Usar el RUC como nombre de usuario
                'email' => $empresa->email, // Email de la empresa
                'password' => bcrypt($empresa->ruc), // Usar el RUC como contraseña cifrada
                'persona_id' => $persona->id,  // Relación con la persona creada
                'perfil_id' => 10,
            ]);


            // Crear el registro en 'rolpersona' asignando el rol_id = 1
            Rolpersona::create([
                'persona_id' => $persona->id, // Relacionar con la persona creada
                'rol_id' => 1,  // Asignar el rol con ID 1
                'sucursal_id' => $sucursal->id // Relacionar con la nueva sucursal
            ]);
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


            // Obtener los parámetros de tipo "Empresa"
            $parametrosEmpresa = Parametro::where('tipo', 'EMPRESA')->where('estado', true)->get();

            foreach ($parametrosEmpresa as $parametro) {
                RelParametroempresa::create([
                    'valor' => $parametro->valor, // Usar el valor predeterminado del parámetro
                    'parametro_id' => $parametro->id, // Relación con el parámetro
                    'empresa_id' => $empresa->id, // Asignar la empresa creada
                ]);
            }

            // Confirmar la transacción
            DB::commit();

            // Retornar la respuesta exitosa
            return response()->json([
                'success' => true,
                'message' => 'Empresa y sucursal creadas exitosamente junto con las relaciones.',
                'empresa' => $empresa,
                'sucursal' => $sucursal,
                'redirect_url' => route('empresas.index', ['page' => $request->input('pagina_actual', 1)])
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
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $empresa = Empresa::find($id);
        return view('empresa.edit', compact('empresa'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(EmpresaRequest $request, Empresa $empresa)
    {
        try {
            $empresa->update($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Empresa actualizada exitosamente.',
                'empresa' => $empresa,
                'redirect_url' => route('empresas.index')
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
            Empresa::find($id)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Empresa eliminada correctamente.',
                'redirect_url' => route('empresas.index')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    public function verSucursal(Request $request, $id)
    {
        // Obtenemos los parámetros de búsqueda y cantidad de registros por página
        $search = $request->input('search', ''); // por defecto vacío
        $perPage = $request->input('perPage', 10); // por defecto 10

        // Consulta con búsqueda por varios campos
        $sucursales = Sucursal::where('empresa_id', $id)
            ->where(function ($query) use ($search) {
                if (!empty($search)) {
                    $query->where('razonsocial', 'like', "%{$search}%")
                        ->orWhere('nombrecomercial', 'like', "%{$search}%")
                        ->orWhere('direccion', 'like', "%{$search}%")
                        ->orWhere('ruc', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('telefonofijo', 'like', "%{$search}%")
                        ->orWhere('telefonomovil', 'like', "%{$search}%");
                }
            })
            ->orderBy('id', 'asc')
            ->paginate($perPage)
            ->appends([
                'search' => $search,
                'perPage' => $perPage,
            ]);

        $operaciones = Operacione::where('tabla_id', 22)
            ->where('estado', true)
            ->get();

        $empresa_id = $id;

        return view('sucursal.index', compact('sucursales', 'operaciones', 'empresa_id', 'search', 'perPage'))
            ->with('i', ($request->input('page', 1) - 1) * $sucursales->perPage());
    }

    public function verMisSucursales($id)
    {
        // $empresas = Empresa::where('', 18)->where('estado', true)->get();
        $sucursales = Sucursal::where('empresa_id', $id)->paginate();
        $operaciones = Operacione::where('tabla_id', 22)->where('estado', true)->get();
        $empresa_id = $id;

        return view('sucursal.missucursales', compact('sucursales', 'operaciones', 'empresa_id'))
            ->with('i', (request()->input('page', 1) - 1) * $sucursales->perPage());
    }

    public function verParametrosEmpresa($id)
    {
        $relParametroempresas = RelParametroempresa::where('empresa_id', $id)->paginate();
        $operaciones = Operacione::where('tabla_id', 47)->where('estado', true)->get(); // Cambia '40' por el ID correcto de la tabla
        $empresa_id = $id;

        return view('rel-parametroempresa.index', compact('relParametroempresas', 'operaciones', 'id', 'empresa_id'))
            ->with('i', (request()->input('page', 1) - 1) * $relParametroempresas->perPage());
    }
}
