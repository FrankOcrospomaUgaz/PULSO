
<div class="main-body">
    <div class="row" style="margin-top: 0px;">
        <div class="col s12">
            <div class="card main-content">
                <div class="card-content" style="margin-top: -20px">
                    <div class="row valign-wrapper" style="margin-bottom: 10px;">
                        <!-- Título del Tipo de Habitación -->
                        <div class="col s12">
                            <h4>Imágenes de: {{ $tipohabitacion->nombre }}</h4>
                        </div>
                    </div>

                    <div class="row">
                        @if ($imagenes->count() > 0)
                            @foreach ($imagenes as $imagen)
                                <div class="col s12 m4">
                                    <div class="card">
                                        <div class="card-image">
                                            <img src="{{ asset('storage/'.$imagen->url) }}" alt="Imagen de {{ $tipohabitacion->nombre }}">
                                        </div>
                                        <div class="card-content">
                                            <p>{{ $imagen->descripcion }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="col s12">
                                <p>No hay imágenes disponibles para este tipo de habitación.</p>
                            </div>
                        @endif
                    </div>

                    <!-- Paginación -->
                    <div class="row">
                        <div class="col s12">
                            {{ $imagenes->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
