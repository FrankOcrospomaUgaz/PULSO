@section('template_title')
    {{ __('Crear') }} Operaciones
@endsection

<div class="main-body">
    <div class="row">
        <div class="col s12">
             <div class="card-content">
                <p class="grey-text text-darken-3 without-tb center card-title" id="modal-title">
<i class="fa-solid fa-plus left"></i>
Operaciones | Nuevo 
@if(!empty($tablas?->descripcion))
    para la tabla {{ $tablas->descripcion }}
@endif
                </p>
            </div>

            <!-- Mostrar errores de la sesión -->
            @if (session('error'))
                <div class="card-panel red lighten-4 red-text text-darken-4">
                    <strong>{{ session('error') }}</strong>
                </div>
            @endif

            <!-- Mostrar mensajes de validación de errores -->
            @if ($errors->any())
                <div class="card-panel red lighten-4 red-text text-darken-4">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="card-body bg-white">
                <!-- Formulario para crear operación -->
                <form id="form" enctype="multipart/form-data">
                    @csrf
                    @isset($id)
                        <!-- URL de acción dinámica para editar o crear -->
                        <input type="hidden" id="form-action-url" value="{{ route('tablas.operaciones.store', $id) }}">
                    @else
                        <input type="hidden" id="form-action-url" value="{{ route('operaciones.store') }}">
                    @endisset

                    <!-- Incluir el formulario de la operación -->
                    @include('operacione.form')

                </form>
            </div>
        </div>
    </div>
</div>
