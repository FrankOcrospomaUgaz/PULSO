<?php
use Illuminate\Support\Facades\Auth;
use App\Models\RelSeccionsucursalperfil;
use App\Models\Seccione;
use App\Models\Persona;
use App\Models\RelSeccionsucursal;
use App\Models\Menuopcione;
use App\Models\Permisousuario;
use App\Models\RelSeccionopcionmenu;
use Illuminate\Support\Facades\Route;

// Obtener el usuario autenticado
$user = Auth::user();
$persona = Persona::find($user->persona_id);

// Obtener la sucursal y perfil directamente del usuario
$sucursalId = $persona->sucursal_id;
$perfilId = $user->perfil_id; // Ahora obtenemos perfil_id directamente desde el usuario
$opcionesPermitidasIds = Permisousuario::where('sucursal_id', $sucursalId)
    ->where('perfil_id', $perfilId)
    ->where('situacion', true)
    ->pluck('menuopcion_id')
    ->all();

// Obtener las secciones activas y ordenarlas por 'orden'
$secciones = Seccione::where('situacion', 'A')->orderBy('orden')->get();

// Inicializar un array para almacenar las acciones de menú y descripciones
$seccionesConAcciones = [];

// Obtener todas las relaciones activas de secciones con la sucursal
$relSeccionesSucursal = RelSeccionsucursal::where('situacion', 'A')->where('sucursal_id', $sucursalId)->get()->keyBy('seccion_id'); // Usamos keyBy para indexarlas por 'seccion_id' y facilitar la búsqueda

// Recorrer las secciones ordenadas para mantener el orden original
foreach ($secciones as $seccione) {
    // Verificar si la sección está relacionada con la sucursal
    if (!$relSeccionesSucursal->has($seccione->id)) {
        continue; // Si la sección no tiene relación, se omite
    }

    $menuAccion = null;
    $menuDescripcion = null;
    $menuIcono = null;

    if ($perfilId) {
        // Obtener la relación de perfil y sección para la sucursal activa
        $relSeccionPerfil = RelSeccionsucursalperfil::where('sucursal_id', $sucursalId)->where('perfil_id', $perfilId)->where('seccion_id', $seccione->id)->first();

        if ($relSeccionPerfil) {
            // Si la relación existe, obtener las opciones del menú y la descripción
            $menuOpcion = $relSeccionPerfil->menuopcione;
            $menuAccion = $menuOpcion ? $menuOpcion->accion : null;
            $menuDescripcion = $menuOpcion ? $menuOpcion->descripcion : null;
            $menuIcono = $menuOpcion ? $menuOpcion->icono : null;
        }
    }

    // Si el perfil no tiene una opción predeterminada, entrar por la primera
    // opción de este módulo que sí tenga permiso y una ruta registrada.
    $menuUrl = null;
    if ($menuAccion && Route::has($menuAccion . '.index')) {
        $menuUrl = route($menuAccion . '.index');
    } else {
        $opcionEntrada = RelSeccionopcionmenu::where('seccion_id', $seccione->id)
            ->whereIn('opcionmenu_id', $opcionesPermitidasIds)
            ->with('menuopcione')
            ->get()
            ->pluck('menuopcione')
            ->filter(fn ($opcion) => $opcion && $opcion->accion && Route::has($opcion->accion . '.index'))
            ->sortBy('orden')
            ->first();

        if ($opcionEntrada) {
            $menuAccion = $opcionEntrada->accion;
            $menuDescripcion = $opcionEntrada->descripcion;
            $menuIcono = $opcionEntrada->icono;
            $menuUrl = route($menuAccion . '.index');
        }
    }

    // Agregar la sección junto con su acción de menú y descripción
    $seccionesConAcciones[] = [
        'seccion' => $seccione,
        'accion' => $menuAccion,
        'descripcion' => $menuDescripcion,
        'icono' => $menuIcono,
        'url' => $menuUrl,
    ];
}
?>


<x-app-layout>
    <x-slot name="header">
        <div class="nav row">
            <div class="col s12 m12 l6" id="cabecera-titulo-container">
                <h5 class="header">
                    <i id="icono-seccion" class="fa-solid fa-home left"></i>
                    <span id="titulo-seccion">Inicio</span>
                </h5>
            </div>
            <div class="col s12 m12 l6 right-align without-tb valign-wrapper" id="cabecera-ruta-container">
                <div id="breadcrumb-container">
                    <a href="/" style="" class="breadcrumb">Inicio</a>
                </div>
            </div>
        </div>


        <div class="main-body" id="main-body" style="margin: -2% 1.6%;">
            <div class="section">
                <!-- Título e Introducción -->
                <div class="row" style="margin-top: -15px">
                    <div class="col s12 mb20">
                        <br>
                        <p>Administra en un solo lugar la atención, las citas y la operación de tu organización.</p>
                    </div>
                </div>

                <!-- Tarjetas -->
                <div class="row" style="margin-bottom: 25px;">
                    @foreach ($seccionesConAcciones as $seccionConAccion)
                        @php
                            $seccione = $seccionConAccion['seccion'];
                            $menuAccion = $seccionConAccion['accion'];
                            $menuDescripcion = $seccionConAccion['descripcion'];
                            $menuIcono = $seccionConAccion['icono'];

                        @endphp
                        <div class="col s12 m6 l4">
                            <div class="card minicard ">
                                <div class="card-image">
                                    <span class="icon-container activator right"
                                        style="position: absolute; margin-top: 10px; margin-right: 15px;">
                                        <a class="btn-floating btn-large pulse tooltipped" data-tooltip="Más información"> {!! $seccione->icono !!} </a>
                                    </span>
                                </div>

                                <div class="card-content" style="height: 18em; position: relative;">
                                    <span class="card-title activator truncate" style="font-weight: bold;">
    {{ $seccione->descripcion }}
</span>

                                    <p style="margin-top: 15px;" class="truncate">
                                        {{ $seccione->subtitulo }}
                                    </p>
                                </div>

                                <div class="card-reveal">
                                    <span class="card-title truncate">{{ $seccione->descripcion }}
                                        <i class="material-icons right">close</i>
                                    </span>
                                    <font class="bold">{{ $seccione->subtitulo }}</font><br>
                                    <p>{{ $seccione->comentario }}</p>
                                </div>
                                <div class="card-action right-align transparent"
                                    style="padding-top: 0!important; border-top: none;">
                                    <a href="{{ $seccionConAccion['url'] ?? '#' }}"
                                        class="btn waves-effect waves-light teal lighten-1 btn-acceder-modulo
                                    btn-cargar-crud"
                                        data-icono="{{ $menuIcono }}" data-modulo="{{ $seccione->descripcion }}"
                                        data-titulo="{{ $menuDescripcion ?? 'Sin título' }}"
                                        data-seccion-id="{{ $seccione->id }}">
                                        Acceder<i class="fa-solid fa-caret-right right"></i>
                                    </a>
                                </div>


                            </div>
                        </div>
                    @endforeach

                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    {{ __("You're logged in!") }}
                </div>
            </div>
        </div>
    </div>
  
</x-app-layout>

