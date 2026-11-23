<div class="tipohabitacion-form">

    <div class="row" style="margin-bottom: 0;">
        <div class="input-field col s12 m6 l3">
            <input type="text" name="nombre" id="nombre" class="validate @error('nombre') invalid @enderror"
                value="{{ old('nombre', $tipohabitacion?->nombre) }}">
            <label for="nombre" class="{{ $tipohabitacion?->nombre ? 'active' : '' }}">{{ __('Nombre') }}</label>
            {!! $errors->first('nombre', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
        </div>

        <div class="input-field col s12 m6 l3">
            <input type="text" name="descripcion" id="descripcion" class="validate @error('descripcion') invalid @enderror"
                value="{{ old('descripcion', $tipohabitacion?->descripcion) }}">
            <label for="descripcion"
                class="{{ $tipohabitacion?->descripcion ? 'active' : '' }}">{{ __('Descripcion') }}</label>
            {!! $errors->first('descripcion', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
        </div>

        <div class="input-field col s12 m6 l3">
            <input type="number" name="capacidad" id="capacidad" class="validate @error('capacidad') invalid @enderror"
                value="{{ old('capacidad', $tipohabitacion?->capacidad) }}">
            <label for="capacidad" class="{{ $tipohabitacion?->capacidad ? 'active' : '' }}">{{ __('Capacidad') }}</label>
            {!! $errors->first('capacidad', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
        </div>

        <div class="input-field col s12 m6 l3">
            <input type="number" name="precio" id="precio" class="validate @error('precio') invalid @enderror"
                value="{{ old('precio', $tipohabitacion?->precio) }}">
            <label for="precio" class="{{ $tipohabitacion?->precio ? 'active' : '' }}">{{ __('Precio por día') }}</label>
            {!! $errors->first('precio', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
        </div>

        <input type="hidden" name="sucursal_id" value="{{ $sucursal_id }}">
    </div>

    <div class="row">
        <div class="col s12 l6">
            <div class="card-panel grey lighten-5 z-depth-0 tipohabitacion-panel">
                <h6 class="grey-text text-darken-2" style="margin-top: 0;">{{ __('Precios por hora') }}</h6>

                <div class="row" style="margin-bottom: 0;">
                    <div class="input-field col s12 m4">
                        <input type="number" id="hora" name="hora" min="1" placeholder="Ej. 1, 2, 3..." class="validate">
                        <label class="active" for="hora">{{ __('Horas') }}</label>
                    </div>

                    <div class="input-field col s12 m4">
                        <input type="number" id="precio_hora" class="validate">
                        <label class="active" for="precio_hora">{{ __('Precio por hora') }}</label>
                    </div>

                    <div class="col s12 m4 tipohabitacion-btn-wrap">
                        <button type="button" class="btn waves-effect waves-light btn-agregar-inline" onclick="agregarPrecio()">{{ __('Agregar') }}</button>
                    </div>
                </div>

                <input type="hidden" name="json_precio_horas" id="json_precio_horas"
                    value="{{ isset($tipohabitacion) ? json_encode(json_decode($tipohabitacion->json_precio_horas, true)) : '[]' }}">

                <div class="table-scroll">
                    <table id="preciosPorHoras" class="highlight striped centered tipohabitacion-tabla">
                        <thead class="thead-custom">
                            <tr>
                                <th>{{ __('Horas') }}</th>
                                <th>{{ __('Precio') }}</th>
                                <th>{{ __('Acciones') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(isset($tipohabitacion) && !empty($tipohabitacion->json_precio_horas))
                                @php
                                    $preciosHoras = json_decode($tipohabitacion->json_precio_horas, true);
                                @endphp

                                @if(is_array($preciosHoras))
                                    @foreach($preciosHoras as $precio)
                                        <tr>
                                            <td>
                                                <input class="hora" value="{{ $precio['hora'] }}" min="1" readonly>
                                            </td>
                                            <td>
                                                <input type="number" class="precio" value="{{ $precio['precio'] }}">
                                            </td>
                                            <td>
                                                <button type="button" class="btn red btn-small waves-effect waves-light"
                                                    onclick="eliminarPrecio(this)">{{ __('Eliminar') }}</button>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col s12 l6">
            <div class="card-panel grey lighten-5 z-depth-0 tipohabitacion-panel">
                <h6 class="grey-text text-darken-2" style="margin-top: 0;">{{ __('Amenidades') }}</h6>

                <div class="row" style="margin-bottom: 0;">
                    <div class="input-field col s12 m4">
                        <input type="text" id="amenity_nombre" placeholder="Nombre de la amenidad" class="validate">
                        <label for="amenity_nombre" class="active">{{ __('Nombre de la Amenidad') }}</label>
                    </div>
                    <div class="input-field col s12 m4">
                        <input type="text" id="amenity_icon" placeholder="Icono (opcional)" class="validate">
                        <label for="amenity_icon" class="active">{{ __('Icono') }}</label>
                    </div>
                    <div class="col s12 m4 tipohabitacion-btn-wrap">
                        <button type="button" class="btn blue darken-2 btn-agregar-inline" onclick="agregarAmenidad()">{{ __('Agregar') }}</button>
                    </div>
                </div>

                <div class="table-scroll">
                    <table id="amenities-table" class="highlight striped centered tipohabitacion-tabla">
                        <thead class="thead-custom">
                            <tr>
                                <th>{{ __('Nombre') }}</th>
                                <th>{{ __('Icono') }}</th>
                                <th>{{ __('Acciones') }}</th>
                            </tr>
                        </thead>
                        <tbody id="amenities-container">
                            @if(isset($tipohabitacion))
                                @foreach ($tipohabitacion->amenities as $amenity)
                                    <tr data-id="{{ $amenity->id }}">
                                        <td>
                                            <input type="text" name="amenities[{{ $amenity->id }}][nombre]" value="{{ $amenity->nombre }}"
                                                class="validate" placeholder="Nombre de la amenidad">
                                        </td>
                                        <td>
                                            <input type="text" name="amenities[{{ $amenity->id }}][icon]" value="{{ $amenity->icon }}"
                                                class="validate" placeholder="Icono (opcional)" id="amenity-icon-{{ $amenity->id }}">
                                        </td>
                                        <td>
                                            <button type="button" class="btn red btn-small" onclick="removeAmenity(this)">{{ __('Eliminar') }}</button>
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col s12">
            <div class="modal-footer tipohabitacion-footer">
                <button type="submit" class="btn waves-effect waves-light"><i
                        class="material-icons left">send</i>{{ __('Guardar') }}</button>
                <a href="#!" class="modal-close waves-effect waves-green btn-flat">{{ __('Cerrar') }}</a>
            </div>
        </div>
    </div>

</div>
