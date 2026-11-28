<?php

namespace App\Http\Controllers;

use App\Models\Persona;
use App\Http\Requests\PersonaRequest;
use App\Models\Menuopcione;
use App\Models\Operacione;
use App\Models\Perfile;
use App\Models\Permisousuario;
use App\Models\Personamaestro;
use App\Models\PacienteClinico;
use App\Models\RelParametrosucursal;
use App\Models\Rol;
use App\Models\Rolpersona;
use App\Models\Sucursal;
use App\Models\Ubigeo;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash; // Asegúrate de incluir esta clase
use Illuminate\Support\Facades\Schema;
/**
 * Class PersonaController
 * @package App\Http\Controllers
 */
class PersonaController extends Controller
{
    /**
     * Display a listing of the resource.
     */


public function index(Request $request)
{
    $search = $request->input('search');
    $no_grato = $request->input('no_grato');
    $sucursal_id = Auth::user()->persona->sucursal_id;
    $perPage = request()->input('perPage', 10);
    $query = Persona::with('personamaestro')
        ->where('sucursal_id', $sucursal_id);

    if ($no_grato !== null && $no_grato !== '' && $no_grato !== 'Todos') {
        if ($no_grato === '1' || $no_grato === 'SI' || $no_grato === 'true') {
            $query->where('no_grato', true);
        } elseif ($no_grato === '0' || $no_grato === 'NO' || $no_grato === 'false') {
            $query->where(function ($q) {
                $q->where('no_grato', false)->orWhereNull('no_grato');
            });
        }
    }

    if ($search) {
        // Búsqueda tanto en tabla persona como en personamaestro
        $query->where(function ($q) use ($search) {
            $q->whereHas('personamaestro', function ($subq) use ($search) {
                $subq->whereRaw('LOWER(nombres) LIKE ?', ['%' . strtolower($search) . '%'])
                     ->orWhereRaw('LOWER(apellidos) LIKE ?', ['%' . strtolower($search) . '%'])
                     ->orWhereRaw('LOWER(nrodoc) LIKE ?', ['%' . strtolower($search) . '%']);
            })
            ->orWhereRaw('LOWER(email) LIKE ?', ['%' . strtolower($search) . '%']); // ejemplo de columna propia
        });
    }

    $personas = $query->orderBy('id', 'desc')->paginate($perPage);

    $operaciones = Operacione::where('tabla_id', 32)->where('estado', true)->get();

    if ($request->ajax()) {
        return view('persona.clientes', compact('personas', 'operaciones', 'sucursal_id'))->render();
    }

    return view('persona.clientes', compact('personas', 'operaciones', 'sucursal_id'))
        ->with('i', (request()->input('page', 1) - 1) * $personas->perPage());
}

    /**
     * Show the form for creating a new resource.
     */
    public function create($sucursal_id, $accion, Request $request)
    {
        // Verificar si se está agregando un acompañante
        $esAcompanante = $request->query('es_acompanante', 'false') === 'true';
        $esPrincipal = $request->query('es_principal', 'false') === 'true';


        // Guardar en sesión que es un acompañante
        session(['es_acompanante' => $esAcompanante]);
        session(['es_principal' => $esPrincipal]);

        $opcion = $request->query('opcion', 'documento'); // Por defecto, 'documento'


        // Obtener los IDs de los acompañantes si están presentes
        $acompanantesIds = $request->query('acompanantes', []);
        $principalId = $request->query('principal');

        $persona = new Persona();
        $sucursal = Sucursal::find($sucursal_id);
        // Buscar las personas que son acompañantes
        $acompanantes = Persona::whereIn('id', $acompanantesIds)->get();
        $principal = Persona::find($principalId);

        $personamaestros = Personamaestro::get();
        $departamentos = Ubigeo::where('tipo', '2')->get();
        $provincias = Ubigeo::where('tipo', '3')->where('ubigeo_id', $sucursal->ubigeo->ubigeo->ubigeo_id)->get();
        $distritos = Ubigeo::where('tipo', '4')->where('ubigeo_id', $sucursal->ubigeo->ubigeo_id)->get();
        $perfiles = Perfile::where('id', '!=', 9)->get();
        $opciones = Menuopcione::where('estado', true)->get();
        $roles = Rol::get();

        // Pasar los acompañantes a la vista
        return view('persona.create', compact(
            'persona',
            'personamaestros',
            'distritos',
            'provincias',
            'departamentos',
            'sucursal_id',
            'perfiles',
            'opciones',
            'roles',
            'opcion',
            'accion',
            'acompanantes',
            'principal',
            'sucursal'
        ));
    }
    public function clientecreate($sucursal_id, $accion, Request $request)
    {
        // Verificar si se está agregando un acompañante
        $esAcompanante = $request->query('es_acompanante', 'false') === 'true';
        $esPrincipal = $request->query('es_principal', 'false') === 'true';
        $tipo = $request->query('tipo', 'CLIENTE');
        

        // Guardar en sesión que es un acompañante
        session(['es_acompanante' => $esAcompanante]);
        session(['es_principal' => $esPrincipal]);

        $opcion = $request->query('opcion', 'documento'); // Por defecto, 'documento'
        $movventa_id = $request->query('movventa_id');

        $ubigeoconfiguracion = RelParametrosucursal::where('sucursal_id', $sucursal_id)
        ->whereHas('parametro', function ($query) {
            $query->where('descripcion', 'Ubigeo');
        })
        ->value('valor');
    

        // Obtener los IDs de los acompañantes si están presentes
        $acompanantesIds = $request->query('acompanantes', []);
        $principalId = $request->query('principal');

        $persona = new Persona();

        // Buscar las personas que son acompañantes
        $acompanantes = Persona::whereIn('id', $acompanantesIds)->get();
        $principal = Persona::find($principalId);
        $sucursal = Sucursal::find($sucursal_id);
        $personamaestros = Personamaestro::get();
        $departamentos = Ubigeo::where('tipo', '2')->get();
        $provincias = Ubigeo::where('tipo', '3')->where('ubigeo_id', $sucursal->ubigeo->ubigeo->ubigeo_id)->get();
        $distritos = Ubigeo::where('tipo', '4')->where('ubigeo_id', $sucursal->ubigeo->ubigeo_id)->get();
        $perfiles = Perfile::where('id', '!=', 9)->get();
        $opciones = Menuopcione::where('estado', true)->get();
        $roles = Rol::get();

        // Pasar los acompañantes a la vista
        return view('persona.formcliente', compact(
            'persona',
            'personamaestros',
            'distritos',
            'provincias',
            'departamentos',
            'sucursal_id',
            'perfiles',
            'opciones',
            'roles',
            'opcion',
            'accion',
            'acompanantes',
            'principal',
            'sucursal',
            'ubigeoconfiguracion',
            'tipo',
            'movventa_id'
        ));
    }


    public function restablecerPassword($persona_id, $sucursal_id)
    {


        return view('persona.contraseña', compact('sucursal_id', 'persona_id'));
    }

    public function restablecerPasswordUsuarioLogueado()
    {


        return view('persona.contraseñausuario');
    }



    public function verUsuario($persona_id, $sucursal_id)
    {
        $permisousu = Permisousuario::join('persona', 'persona.sucursal_id', '=', 'permisousuario.sucursal_id')
            ->where('permisousuario.sucursal_id', $sucursal_id)
            ->where('persona.id', $persona_id)
            ->select('permisousuario.*')
            ->first();
        $usuario = User::where('persona_id', $persona_id)->first();
        $perfiles = Perfile::where('id', '!=', 9)->get();
        return view('persona.usuario', compact('permisousu', 'usuario', 'perfiles', 'sucursal_id', 'persona_id'));
    }

    public function actualizarUsuarioPerfil(Request $request, $persona_id, $sucursal_id)
    {
        $request->validate([
            'perfil_id' => 'required|exists:perfiles,id',
        ]);

        DB::beginTransaction();

        try {
            $usuario = User::where('persona_id', $persona_id)->first();
            if (!$usuario) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no encontrado.'
                ], 404);
            }

            $usuario->perfil_id = $request->input('perfil_id');
            $usuario->save();

            $permisousu = Permisousuario::where('sucursal_id', $sucursal_id)->first();
            if ($permisousu) {
                $permisousu->update([
                    'perfil_id' => $request->input('perfil_id'),
                    'situacion' => true,
                ]);
            } elseif ($request->filled('menuopcion_id_actual')) {
                Permisousuario::create([
                    'sucursal_id' => $sucursal_id,
                    'perfil_id' => $request->input('perfil_id'),
                    'menuopcion_id' => $request->input('menuopcion_id_actual'),
                    'situacion' => true,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Perfil actualizado.',
                'redirect_url' => route('persona.verUsuario', [$persona_id, $sucursal_id])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PersonaRequest $request)
    {
        $clinicalData = null;
        if ($request->boolean('registro_clinico')) {
            $clinicalData = $request->validate([
                'tutor_nombres' => 'nullable|string|max:180',
                'tutor_documento' => 'nullable|string|max:30',
                'tutor_telefono' => 'nullable|string|max:40',
                'tutor_email' => 'nullable|email|max:150',
                'acepta_notificaciones' => 'nullable|boolean',
                'observaciones' => 'nullable|string|max:2000',
            ]);
            if ($request->filled('fechanac') && \Illuminate\Support\Carbon::parse($request->input('fechanac'))->age < 18 && !$request->filled('tutor_nombres')) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'tutor_nombres' => 'Registra al padre, madre o tutor para pacientes menores de edad.',
                ]);
            }
        }

        DB::beginTransaction();

        try {
            $tipopersona = $request->input('tipopersona', 'Natural');
            $sexo = $request->input('sexo', 'N');
            $nrodoc = $request->input('nrodoc', '0');
            $apellidos = $request->input('apellidos') ?? ' ';
            $accion = $request->input('accion', 'CRUD');
            $sucursal_id = $request->input('sucursal_id');

            // Buscar si existe un Personamaestro con el mismo nrodoc
            $personamaestro = Personamaestro::where('nrodoc', $nrodoc)->first();

            if ($personamaestro and $personamaestro->nrodoc !== '0') {
                // Buscar si la persona ya existe en la misma sucursal
                $personaExistente = Persona::where('personamaestro_id', $personamaestro->id)
                    ->where('sucursal_id', $sucursal_id)
                    ->first();

                if ($personaExistente) {
                    // Si ya existe en la misma sucursal, usar el mismo registro
                    $persona = $personaExistente;
                    if ($accion === 'CLINICA') {
                        $personamaestro->update([
                            'tipopersona' => $tipopersona,
                            'nombres' => $request->input('nombres'),
                            'apellidos' => $apellidos,
                            'sexo' => $sexo,
                            'fechanac' => $request->input('fechanac'),
                        ]);
                        $persona->update([
                            'nombres' => $request->input('nombres'),
                            'apellidos' => $apellidos,
                            'direccion' => $request->input('direccion', '-') ?: '-',
                            'email' => $request->input('email') ?: null,
                            'telefonofijo' => $request->input('telefonofijo') ?: '-',
                            'telefonomovil' => $request->input('telefonomovil') ?: '-',
                            'no_grato' => $request->has('no_grato'),
                            'distrito_id' => $request->input('distrito_id'),
                        ]);
                    }
                } else {
                    // Si existe en otra sucursal, solo registrar en Persona
                    $persona = Persona::create([
                        'nombres'           => $personamaestro->nombres,
                        'apellidos'         => $personamaestro->apellidos,
                        'direccion'         => $request->input('direccion', '-') ?: '-',
                        'email'             => $request->input('email') ?: null,
                        'telefonofijo'      => $request->input('telefonofijo') ?: '-',
                        'telefonomovil'     => $request->input('telefonomovil') ?: '-',
                        'imagen'            => '-',
                        'compartido'        => $request->input('compartido', 'N'),
                        'no_grato'          => $request->has('no_grato') ? true : false,
                        'personamaestro_id' => $personamaestro->id,
                        'sucursal_id'       => $sucursal_id,
                        'distrito_id'       => $request->input('distrito_id'),
                    ]);
                }
            } else {
                // Si no existe en Personamaestro, crearlo
                $personamaestro = Personamaestro::create([
                    'tipopersona' => $tipopersona,
                    'nombres'     => $request->input('nombres'),
                    'apellidos'   => $apellidos,
                    'nrodoc'      => $nrodoc,
                    'sexo'        => $sexo,
                    'fechanac'    => $request->input('fechanac'),
                ]);

                if (!$personamaestro) {
                    throw new \Exception("Error al crear Personamaestro");
                }

                // Crear persona asociada
                $persona = Persona::create([
                    'nombres'           => $request->input('nombres'),
                    'apellidos'         => $apellidos,
                    'direccion'         => $request->input('direccion', '-') ?: '-',
                    'email'             => $request->input('email') ?: null,
                    'telefonofijo'      => $request->input('telefonofijo') ?: '-',
                    'telefonomovil'     => $request->input('telefonomovil') ?: '-',
                    'imagen'            => '-',
                    'compartido'        => $request->input('compartido', 'N'),
                    'personamaestro_id' => $personamaestro->id,
                    'no_grato'          => $request->has('no_grato') ? true : false,

                    'sucursal_id'       => $sucursal_id,
                    'distrito_id'       => $request->input('distrito_id'),
                ]);
            }

            // Si se proporcionan credenciales, crear usuario
            if ($request->filled(['username', 'password'])) {
                $user = User::updateOrCreate(
                    ['persona_id' => $persona->id],
                    [
                        'name'       => $request->input('username'),
                        'email'      => $request->input('email'),
                        'password'   => bcrypt($request->input('password')),
                        'perfil_id'  => $request->input('perfil_id'),
                    ]
                );

                if (!$user) {
                    throw new \Exception("Error al crear User");
                }
            }

            // Registrar roles seleccionados
            if ($request->has('rol_id')) {
                foreach ($request->input('rol_id') as $rolId) {
                    Rolpersona::updateOrCreate(
                        ['persona_id' => $persona->id, 'rol_id' => $rolId, 'sucursal_id' => $sucursal_id]
                    );
                }
            }
            if ($accion == 'CRUD') {
                 // Registrar siempre el rol de Empleado
            Rolpersona::updateOrCreate(
                ['persona_id' => $persona->id, 'rol_id' => 1, 'sucursal_id' => $sucursal_id]
            );
            }

            if ($clinicalData !== null) {
                $tipoDocumento = match ($tipopersona) {
                    'Juridico' => 'RUC',
                    'Carnet de Extranjería' => 'CE',
                    'Pasaporte' => 'PASAPORTE',
                    default => 'DNI',
                };
                $aceptaNotificaciones = $request->boolean('acepta_notificaciones');
                PacienteClinico::updateOrCreate(
                    ['persona_id' => $persona->id],
                    [
                        'sucursal_id' => $sucursal_id,
                        'tipo_documento' => $tipoDocumento,
                        'numero_documento' => $nrodoc !== '0' ? $nrodoc : null,
                        'nombres' => $request->input('nombres'),
                        'apellidos' => $apellidos,
                        'fecha_nacimiento' => $request->input('fechanac'),
                        'sexo' => $sexo,
                        'telefono' => $request->input('telefonomovil') ?: $request->input('telefonofijo'),
                        'email' => $request->input('email'),
                        'direccion' => $request->input('direccion'),
                        'tutor_nombres' => $clinicalData['tutor_nombres'] ?? null,
                        'tutor_documento' => $clinicalData['tutor_documento'] ?? null,
                        'tutor_telefono' => $clinicalData['tutor_telefono'] ?? null,
                        'tutor_email' => $clinicalData['tutor_email'] ?? null,
                        'observaciones' => $clinicalData['observaciones'] ?? null,
                        'acepta_notificaciones' => $aceptaNotificaciones,
                        'acepta_notificaciones_at' => $aceptaNotificaciones ? now() : null,
                    ]
                );
            }
           

            // Manejo de acompañantes y principal
            if (session('es_acompanante', false)) {
                $acompanantes = session('acompanantes', []);
                $acompanantes[] = $persona->id;
                session(['acompanantes' => $acompanantes]);
            }

            if (session('es_principal', false)) {
                session(['principal' => $persona->id]);
            }

            DB::commit();

            // Redireccionar según acción
            if ($accion == 'CONTRATO') {
                return response()->json([
                    'success' => true,
                    'message' => 'Persona creada exitosamente.',
                    'redirect_url' => route('contrato.create', [
                        'personaCreadaId' => $persona->id
                    ]),
                    'persona' => $persona,
                    'personamaestro' => $personamaestro,
                    'tipo' => $request->input('tipo', 'CLIENTE')
                ]);
            } elseif ($accion == 'VENTA') {
                return response()->json([
                    'success' => true,
                    'message' => 'Persona creada exitosamente.',
                    'redirect_url' => route('movventa.create', [
                        'personaCreadaId' => $persona->id
                    ]),
                    'persona' => $persona,
                    'personamaestro' => $personamaestro
                ]);
            } elseif ($accion == 'CONVERTIR') {
                return response()->json([
                    'success' => true,
                    'message' => 'Persona creada exitosamente.',
                    'redirect_url' => route('movventa.convertirAComprobante', [
                        'id' => $request->input('movventa_id'),
                        'personaCreadaId' => $persona->id,
                    ]),
                    'persona' => $persona,
                    'personamaestro' => $personamaestro
                ]);
            } elseif ($accion == 'CLINICA') {
                return response()->json([
                    'success' => true,
                    'message' => 'Paciente registrado correctamente.',
                    'redirect_url' => route('pacientes.index'),
                ]);
            } elseif ($accion == 'CRUD') {
                return response()->json([
                    'success' => true,
                    'message' => 'Persona creada exitosamente.',
                    'redirect_url' => route('persona.index', [
                        'personaCreadaId' => $persona->id
                    ])
                ]);
            } else {
                return response()->json([
                    'success' => true,
                    'message' => 'Persona creada exitosamente.',
                    'persona' => $persona,
                    'redirect_url' => route('sucursal.verPersonal', [
                        'id' => $sucursal_id,
                        'page' => $request->input('pagina_actual', 1),
                    ]),
                ]);
            }
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }



    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $persona = Persona::find($id);
        return view('persona.show', compact('persona'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id, $accion)
    {
        $persona = Persona::find($id);
        $sucursal_id = $persona->sucursal_id;
        $sucursal = Sucursal::find($sucursal_id);

        $personamaestros =  Personamaestro::get();
        $departamentos = Ubigeo::where('tipo', '2')->get();
        $provincias = Ubigeo::where('tipo', '3')
        ->where('ubigeo_id', $persona?->ubigeo?->ubigeo?->ubigeo_id)
        ->get();
    
        $distritos = Ubigeo::where('tipo', '4')
            ->where('ubigeo_id', $persona?->ubigeo?->ubigeo_id)
            ->get();
    
        $perfiles =  Perfile::get();
        $opciones =  Menuopcione::get();
        $opcion =  'documento'; // Por defecto, 'documento' si no se pasa el parámetro
        $roles = Rol::get();

        return view('persona.edit', compact('persona', 'personamaestros', 'distritos', 'provincias', 'departamentos', 'sucursal_id', 'perfiles', 'opciones', 'opcion', 'roles', 'accion', 'sucursal'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PersonaRequest $request, Persona $persona)
    {
        $clinicalData = null;
        if ($request->boolean('registro_clinico')) {
            $clinicalData = $request->validate([
                'tutor_nombres' => 'nullable|string|max:180',
                'tutor_documento' => 'nullable|string|max:30',
                'tutor_telefono' => 'nullable|string|max:40',
                'tutor_email' => 'nullable|email|max:150',
                'acepta_notificaciones' => 'nullable|boolean',
                'observaciones' => 'nullable|string|max:2000',
            ]);
            if ($request->filled('fechanac') && \Illuminate\Support\Carbon::parse($request->input('fechanac'))->age < 18 && !$request->filled('tutor_nombres')) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'tutor_nombres' => 'Registra al padre, madre o tutor para pacientes menores de edad.',
                ]);
            }
        }

        DB::beginTransaction();
        
        try {
            $tipopersona = $request->input('tipopersona', 'Natural');
            $sexo = $request->input('sexo', 'N');
            $nrodoc = $request->input('nrodoc', '0');
            $apellidos = $request->input('apellidos') ?? ' ';
            $accion = $request->input('accion', 'CRUD');
            $sucursal_id = $request->input('sucursal_id');
    
            // Buscar o actualizar Personamaestro
            $personamaestro = Personamaestro::where('nrodoc', $nrodoc)->first();
    
            if ($personamaestro) {
                $personamaestro->update([
                    'tipopersona' => $tipopersona,
                    'nombres'     => $request->input('nombres'),
                    'apellidos'   => $apellidos,
                    'sexo'        => $sexo,
                    'fechanac'    => $request->input('fechanac'),
                ]);
            } else {
                $personamaestro = Personamaestro::create([
                    'tipopersona' => $tipopersona,
                    'nombres'     => $request->input('nombres'),
                    'apellidos'   => $apellidos,
                    'nrodoc'      => $nrodoc,
                    'sexo'        => $sexo,
                    'fechanac'    => $request->input('fechanac'),
                ]);
            }
    
            // Actualizar Persona
            $persona->update([
                'nombres'           => $request->input('nombres'),
                'apellidos'         => $apellidos,
                'direccion'         => $request->input('direccion', '-') ?: '-',
                'email'             => $request->input('email') ?: null,
                'telefonofijo'      => $request->input('telefonofijo') ?: '-',
                'telefonomovil'     => $request->input('telefonomovil') ?: '-',
                'compartido'        => $request->input('compartido', 'N'),
                'no_grato'          => $request->has('no_grato') ? true : false,
                'sucursal_id'       => $sucursal_id,
                'distrito_id'       => $request->input('distrito_id'),
                'personamaestro_id' => $personamaestro->id,
            ]);
    
        
            // Registrar roles seleccionados
            if ($request->has('rol_id')) {
                foreach ($request->input('rol_id') as $rolId) {
                    Rolpersona::updateOrCreate(
                        ['persona_id' => $persona->id, 'rol_id' => $rolId, 'sucursal_id' => $sucursal_id]
                    );
                }
            }
    
            if ($accion == 'CRUD') {
                Rolpersona::updateOrCreate(
                    ['persona_id' => $persona->id, 'rol_id' => 1, 'sucursal_id' => $sucursal_id]
                );
            }

            if ($clinicalData !== null) {
                $tipoDocumento = match ($tipopersona) {
                    'Juridico' => 'RUC',
                    'Carnet de Extranjería' => 'CE',
                    'Pasaporte' => 'PASAPORTE',
                    default => 'DNI',
                };
                $aceptaNotificaciones = $request->boolean('acepta_notificaciones');
                $paciente = PacienteClinico::where('persona_id', $persona->id)->first();
                PacienteClinico::updateOrCreate(
                    ['persona_id' => $persona->id],
                    [
                        'sucursal_id' => $sucursal_id,
                        'tipo_documento' => $tipoDocumento,
                        'numero_documento' => $nrodoc !== '0' ? $nrodoc : null,
                        'nombres' => $request->input('nombres'),
                        'apellidos' => $apellidos,
                        'fecha_nacimiento' => $request->input('fechanac'),
                        'sexo' => $sexo,
                        'telefono' => $request->input('telefonomovil') ?: $request->input('telefonofijo'),
                        'email' => $request->input('email'),
                        'direccion' => $request->input('direccion'),
                        'tutor_nombres' => $clinicalData['tutor_nombres'] ?? null,
                        'tutor_documento' => $clinicalData['tutor_documento'] ?? null,
                        'tutor_telefono' => $clinicalData['tutor_telefono'] ?? null,
                        'tutor_email' => $clinicalData['tutor_email'] ?? null,
                        'observaciones' => $clinicalData['observaciones'] ?? null,
                        'acepta_notificaciones' => $aceptaNotificaciones,
                        'acepta_notificaciones_at' => $aceptaNotificaciones
                            ? ($paciente?->acepta_notificaciones_at ?? now())
                            : null,
                    ]
                );
            }
    
            DB::commit();
            if ($accion == 'CLINICA') {
                return response()->json([
                    'success' => true,
                    'message' => 'Paciente actualizado correctamente.',
                    'redirect_url' => route('pacientes.index'),
                ]);
            }
            if ($accion == 'CRUD') {
                return response()->json([
                    'success' => true,
                    'message' => 'Persona creada exitosamente.',
                    'redirect_url' => route('persona.index', [
                        'personaCreadaId' => $persona->id
                    ])
                ]);
            } else {

                return response()->json([
                    'success' => true,
                    'message' => 'Persona actualizada exitosamente.',
                    'persona' => $persona,
                     'redirect_url' => route('persona.index', [
                        'personaCreadaId' => $persona->id
                    ])
                ]);
            }
        } catch (\Exception $e) {
            DB::rollBack();
    
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }
    public function nuevacontraseña(Request $request, $persona_id)
    {
        // Validar las entradas del formulario
        $request->validate([
            'nuevacontraseña' => 'required|string|confirmed', // Usar "confirmed" para verificar que ambas contraseñas coinciden
        ]);

        DB::beginTransaction();

        try {


            // Encontrar el usuario relacionado con la persona
            $user = User::where('persona_id', $persona_id)->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no encontrado.'
                ], 404);
            }

            // Actualizar la contraseña con el valor cifrado
            $user->password = bcrypt($request->input('nuevacontraseña'));
            $user->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Contraseña reestablecida exitosamente.',
                'redirect_url' => route('sucursal.verPersonal', ['id' => $request->input('sucursal_id')])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }


    public function nuevacontraseñaUsuarioLogueado(Request $request)
    {
        // Validar las entradas del formulario, incluyendo la contraseña antigua
        $request->validate([
            'antiguacontraseña' => 'required|string',
            'nuevacontraseña' => 'required|string|confirmed', // "confirmed" para verificar que ambas contraseñas coinciden
        ]);

        DB::beginTransaction();

        try {
            // Obtener la persona asociada al usuario autenticado
            $persona = Persona::find(auth()->user()->persona_id);
            if (!$persona) {
                return response()->json([
                    'success' => false,
                    'message' => 'Persona no encontrada.'
                ], 404);
            }

            // Obtener el usuario relacionado con la persona
            $user = User::where('persona_id', $persona->id)->first();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no encontrado.'
                ], 404);
            }

            // Verificar si la contraseña antigua proporcionada coincide con la actual
            if (!Hash::check($request->input('antiguacontraseña'), $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'La contraseña actual no coincide.'
                ], 400); // Código 400 para error de solicitud incorrecta
            }

            // Si la contraseña antigua es correcta, actualizamos la contraseña
            $user->password = bcrypt($request->input('nuevacontraseña'));
            $user->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Contraseña reestablecida exitosamente.',
                'redirect_url' => route('sucursal.verPersonal', ['id' => $persona->sucursal_id])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }





    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            Persona::find($id)->delete();

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Persona eliminada correctamente.',
                'redirect_url' => route('persona.index')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }
}
