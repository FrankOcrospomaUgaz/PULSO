<div class="row padding-1 p-1">
    <div class="col s12">
        <!-- Campo de texto para 'codigo' -->
        <div class="input-field col s12 m2">
            <input type="text" name="codigo" id="codigo" class="validate @error('codigo') invalid @enderror"
                value="{{ old('codigo', $producto?->codigo ?? $ultimo_codigo) }}">
            <label for="codigo" class="active">{{ __('Código') }}</label>
            {!! $errors->first('codigo', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
        </div>

        <!-- Campo de texto para 'descripcion' -->

        <div class="col s12 m6 l4">
            <div class="input-group-field">
                <a class="btn bg-secondary right tooltipped" data-tooltip="Clonar" type="button"
                    onclick="cloneText()">
                    <i class="fa-solid fa-clone"></i></a>
                <div class="input-field col s12 m10">
                    <input type="text" name="descripcion" id="descripcion"
                        class="validate @error('descripcion') invalid @enderror"
                        value="{{ old('descripcion', $producto?->descripcion) }}">
                    <label for="descripcion"
                        class="{{ $producto?->descripcion ? 'active' : '' }}">{{ __('Descripción') }}</label>
                    {!! $errors->first(
                        'descripcion',
                        '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>',
                    ) !!}
                </div>
            </div>
        </div>


        <!-- Campo de texto para 'abreviatura' -->
        <div class="input-field col s12 m2">
            <input type="text" name="abreviatura" id="abreviatura"
                class="validate @error('abreviatura') invalid @enderror"
                value="{{ old('abreviatura', $producto?->abreviatura) }}">
            <label for="abreviatura"
                class="{{ $producto?->abreviatura ? 'active' : '' }}">{{ __('Abreviatura') }}</label>
            <span class="helper-text"><strong>Para tickets</strong></span>

            {!! $errors->first(
                'abreviatura',
                '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>',
            ) !!}
        </div>
        <!-- Select para 'clasificacion' -->
        <div class="input-field col s12 m2">
            <select name="clasificacion" id="clasificacion" class="validate @error('clasificacion') invalid @enderror">
                <option value="Bien"
                    {{ old('clasificacion', $producto?->clasificacion) == 'Bien' ? 'selected' : '' }}>Bien</option>
                <option value="Servicio"
                    {{ old('clasificacion', $producto?->clasificacion) == 'Servicio' ? 'selected' : '' }}>Servicio
                </option>
            </select>
            <label for="clasificacion">{{ __('Clasificación') }}</label>
            {!! $errors->first(
                'clasificacion',
                '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>',
            ) !!}
        </div>

        <!-- Select para 'tipo' -->
        <div class="input-field col s12 m2">
            <select name="tipo" id="tipo" class="validate @error('tipo') invalid @enderror">
                <option value="Producto final"
                    {{ old('tipo', $producto?->tipo) == 'Producto final' ? 'selected' : '' }}>Producto final</option>
                <option value="Componente" {{ old('tipo', $producto?->tipo) == 'Componente' ? 'selected' : '' }}>
                    Componente</option>
            </select>
            <label for="tipo">{{ __('Tipo') }}</label>
            {!! $errors->first('tipo', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
        </div>

        <!-- Campo de selección para 'categoria_id' -->
        <div class="input-field col s12 m4">
            <select name="categoria_id" id="categoria_id" class="validate @error('categoria_id') invalid @enderror">
                <option value="" disabled selected>Seleccionar categoría</option>
                @foreach ($categorias as $categoria)
                    <option value="{{ $categoria->id }}"
                        {{ old('categoria_id', $producto?->categoria_id) == $categoria->id ? 'selected' : '' }}>
                        {{ $categoria->descripcion }}
                    </option>
                @endforeach
            </select>
            <label for="categoria_id">{{ __('Categoría') }}</label>
            {!! $errors->first(
                'categoria_id',
                '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>',
            ) !!}
        </div>

        <div class="input-field col s12 m2">
            <label>
                <input type="checkbox" name="compuesto" id="compuesto"
                    {{ old('compuesto', $producto?->compuesto) == 'on' ? 'checked' : '' }} />
                <span>{{ __('Compuesto por componentes') }}</span>
            </label>
        </div>

        <!-- Checkbox para 'autoproduccion' (deshabilitado por defecto) -->
        <div class="input-field col s12 m2">
            <label>
                <input type="checkbox" name="autoproduccion" id="autoproduccion"
                    {{ old('autoproduccion', $producto?->autoproduccion) == 'on' ? 'checked' : '' }} disabled />
                <span>{{ __('Autoproducción') }}</span>
            </label>
        </div>




        <div class="input-field col s12 m4">
            <select name="complemento" id="complemento" class="validate @error('complemento') invalid @enderror">
                <option value="No permite complementos"
                    {{ old('complemento', $producto?->complemento) == 'No permite complementos' ? 'selected' : '' }}>No
                    permite complementos</option>
                <option value="Tiene complementos"
                    {{ old('complemento', $producto?->complemento) == 'Tiene complementos' ? 'selected' : '' }}>Tiene
                    complementos
                </option>
                <option value="Es complemento"
                    {{ old('complemento', $producto?->complemento) == 'Es complemento' ? 'selected' : '' }}>Es
                    complemento
                </option>
            </select>
            <label for="complemento">{{ __('Complemento') }}</label>
            {!! $errors->first(
                'complemento',
                '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>',
            ) !!}
            {{-- <span class="helper-text" role="alert">No tiene y no permite agregar complementos</span> --}}
        </div>

        <!-- Campo de selección para 'marca_id' -->
        <div class="input-field col s12 m4">
            <select name="marca_id" id="marca_id" class="validate @error('marca_id') invalid @enderror">
                <option value="" selected>Ninguna</option>
                @foreach ($marcas as $marca)
                    <option value="{{ $marca->id }}"
                        {{ old('marca_id', $producto?->marca_id) == $marca->id ? 'selected' : '' }}>
                        {{ $marca->nombre }}
                    </option>
                @endforeach
            </select>
            <label for="marca_id">{{ __('Marca') }}</label>
            {!! $errors->first(
                'marca_id',
                '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>',
            ) !!}
        </div>

        <input type="hidden" name="empresa_id" value="{{ $empresa_id }}">
        <input type="hidden" name="sucursal_id" value="{{ $sucursal_id }}">

        <!-- Campo de selección para 'unidadbase_id' -->
        <div class="input-field col s12 m4">
            <select name="unidadbase_id" id="unidadbase_id" class="validate @error('unidadbase_id') invalid @enderror">
                @foreach ($unidades_base as $unidad)
                    <option value="{{ $unidad->id }}"
                        {{ old('unidadbase_id', $producto?->unidadbase_id) == $unidad->id ? 'selected' : '' }}>
                        {{ $unidad->descripcion }}
                    </option>
                @endforeach
            </select>
            <label for="unidadbase_id">{{ __('Unidad Base') }}</label>
            {!! $errors->first(
                'unidadbase_id',
                '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>',
            ) !!}
        </div>

        <!-- Campo de texto para 'peso' -->
        <div class="input-field col s12 m2">
            <input type="number" name="peso" id="peso" class="validate @error('peso') invalid @enderror"
                value="{{ old('peso', $producto?->peso) ?? 0}}">
            <label for="peso" class="active">{{ __('Peso') }}</label>
            {!! $errors->first('peso', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
        </div>

        <!-- Campo de selección para 'unidad_id' -->
        <div class="input-field col s12 m2">
            <select name="unidad_id" id="unidad_id"
                class="validate @error('unidad_id') invalid @enderror">
                @foreach ($unidades as $unidad)
                    <option value="{{ $unidad->id }}"
                        {{ old('medidapeso_id', $producto?->medidapeso_id) == $unidad->id ? 'selected' : '' }}>
                        {{ $unidad->descripcion }}
                    </option>
                @endforeach
            </select>
            {!! $errors->first(
                'unidad_id',
                '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>',
            ) !!}
        </div>



        <!-- Campo de texto para 'caracteristicas' -->
        <div class="input-field col s12 m4">
            <input type="text" name="caracteristicas" id="caracteristicas"
                class="validate @error('caracteristicas') invalid @enderror"
                value="{{ old('caracteristicas', $producto?->caracteristicas) }}">
            <label for="caracteristicas"
                class="{{ $producto?->caracteristicas ? 'active' : '' }}">{{ __('Características') }}</label>
            {!! $errors->first(
                'caracteristicas',
                '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>',
            ) !!}
        </div>


        <!-- Campo de texto para 'comentario' -->
        <div class="input-field col s12 m4">
            <input type="text" name="comentario" id="comentario"
                class="validate @error('comentario') invalid @enderror"
                value="{{ old('comentario', $producto?->comentario) }}">
            <label for="comentario"
                class="{{ $producto?->comentario ? 'active' : '' }}">{{ __('Comentario') }}</label>
            {!! $errors->first(
                'comentario',
                '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>',
            ) !!}
        </div>

        <!-- Campo de texto para 'nro_parte' -->
        <div class="input-field col s12 m4">
            <input type="text" name="nro_parte" id="nro_parte"
                class="validate @error('nro_parte') invalid @enderror"
                value="{{ old('nro_parte', $producto?->nro_parte) }}">
            <label for="nro_parte"
                class="{{ $producto?->nro_parte ? 'active' : '' }}">{{ __('Número de parte') }}</label>
            {!! $errors->first(
                'nro_parte',
                '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>',
            ) !!}
        </div>



        <!-- Campo de selección para 'distribuidor_id' -->
        <div class="input-field col s12 m4">
            <select name="distribuidor_id" id="distribuidor_id"
                class="validate @error('distribuidor_id') invalid @enderror">
                <option value="" disabled selected>Seleccionar distribuidor</option>
                @foreach ($distribuidores as $distribuidor)
                    <option value="{{ $distribuidor->id }}"
                        {{ old('distribuidor_id', $producto?->distribuidor_id) == $distribuidor->id ? 'selected' : '' }}>
                        {{ $distribuidor->nombre }}
                    </option>
                @endforeach
            </select>
            <label for="distribuidor_id">{{ __('Distribuidor') }}</label>
            {!! $errors->first(
                'distribuidor_id',
                '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>',
            ) !!}
        </div>
        <!-- Campo para 'imagen' -->
        <div class="input-field col s12 m4">
            <div class="file-field input-field" style="display: flex; align-items: center;">
                <div class="btn left">
                    <span><i class="fa-solid fa-circle-up"></i></span>
                    <input id="imagen" name="imagen" type="file" accept=".png,.jpg">
                </div>
                <div class="file-path-wrapper" style="flex-grow: 1;">
                    <input class="file-path validate @error('imagen') invalid @enderror" type="text"
                        placeholder="No se ha seleccionado ningún archivo">
                </div>
            </div>
            {!! $errors->first('imagen', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
        </div>

        <!-- Campo para 'imagen2' -->
        <div class="input-field col s12 m4">
            <div class="file-field input-field" style="display: flex; align-items: center;">
                <div class="btn left">
                    <span><i class="fa-solid fa-circle-up"></i></span>
                    <input id="imagen2" name="imagen2" type="file" accept=".png,.jpg">
                </div>
                <div class="file-path-wrapper" style="flex-grow: 1;">
                    <input class="file-path validate @error('imagen2') invalid @enderror" type="text"
                        placeholder="No se ha seleccionado ningún archivo">
                </div>
            </div>
            {!! $errors->first(
                'imagen2',
                '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>',
            ) !!}
        </div>

        <!-- Campo para 'imagen3' -->
        <div class="input-field col s12 m4">
            <div class="file-field input-field" style="display: flex; align-items: center;">
                <div class="btn left">
                    <span><i class="fa-solid fa-circle-up"></i></span>
                    <input id="imagen3" name="imagen3" type="file" accept=".png,.jpg">
                </div>
                <div class="file-path-wrapper" style="flex-grow: 1;">
                    <input class="file-path validate @error('imagen3') invalid @enderror" type="text"
                        placeholder="No se ha seleccionado ningún archivo">
                </div>
            </div>
            {!! $errors->first(
                'imagen3',
                '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>',
            ) !!}
        </div>

        <div class="input-field col s12 m2">
            <label>
                <input type="checkbox" name="kardex" id="kardex"
                    {{ old('kardex', $producto?->kardex) == 'S' ? 'checked' : '' }} />
                <span>{{ __('Controla stock (kárdex)') }}</span>
            </label>
        </div>

        <!-- Campo de texto para 'stockminimo' -->
        <div class="input-field col s12 m2">
            <input type="number" name="stockminimo" id="stockminimo"
                class="validate @error('stockminimo') invalid @enderror"
                value="{{ old('stockminimo', $producto?->stockminimo) ?? 0 }}">
            <label for="stockminimo" class="active">{{ __('Stock mín.') }}</label>
            {!! $errors->first(
                'stockminimo',
                '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>',
            ) !!}
        </div>

        <!-- Campo de texto para 'stockmaximo' -->
        <div class="input-field col s12 m2">
            <input type="number" name="stockmaximo" id="stockmaximo"
                class="validate @error('stockmaximo') invalid @enderror"
                value="{{ old('stockmaximo', $producto?->stockmaximo) ?? 0 }}">
            <label for="stockmaximo" class="active">{{ __('Stock Máximo') }}</label>
            {!! $errors->first(
                'stockmaximo',
                '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>',
            ) !!}
        </div>

        <!-- Campo de texto para 'stockoptimo' -->
        <div class="input-field col s12 m2">
            <input type="number" name="stockoptimo" id="stockoptimo"
                class="validate @error('stockoptimo') invalid @enderror"
                value="{{ old('stockoptimo', $producto?->stockoptimo) ?? 0 }}">
            <label for="stockoptimo" class="active">{{ __('Stock Óptimo') }}</label>
            {!! $errors->first(
                'stockoptimo',
                '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>',
            ) !!}
        </div>

        <!-- Campo de texto para 'minimovender' -->
        <div class="input-field col s12 m2">
            <input type="number" name="minimovender" id="minimovender"
                class="validate @error('minimovender') invalid @enderror"
                value="{{ old('minimovender', $producto?->minimovender) ?? 0 }}">
            <label for="minimovender" class="active">{{ __('Mínimo para vender') }}</label>
            {!! $errors->first(
                'minimovender',
                '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>',
            ) !!}
        </div>

        <!-- Campo de texto para 'minimocomprar' -->
        <div class="input-field col s12 m2">
            <input type="number" name="minimocomprar" id="minimocomprar"
                class="validate @error('minimocomprar') invalid @enderror"
                value="{{ old('minimocomprar', $producto?->minimocomprar) ?? 0 }}">
            <label for="minimocomprar" class="active">{{ __('Mínimo para comprar') }}</label>
            {!! $errors->first(
                'minimocomprar',
                '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>',
            ) !!}
        </div>

    </div>
</div>
<div class="row padding-1 p-1">
    <div class="col s12">
        <div class="input-field col s12 m12">
            <h6 class="subtitle left-align"><i class="fa-solid fa-landmark left"></i> Datos por sucursal:</h6>
        </div>

        <!-- Campo de selección para 'proveedor_id' -->
        <div class="input-field col s12 m4">
            <select name="proveedor_id" id="proveedor_id" class="validate @error('proveedor_id') invalid @enderror">
                <option value="" disabled selected>Seleccionar proveedor</option>
                @foreach ($proveedores as $proveedor)
                    <option value="{{ $proveedor->id }}"
                        {{ old('proveedor_id', $producto?->proveedor_id) == $proveedor->id ? 'selected' : '' }}>
                        {{ $proveedor->nombre }}
                    </option>
                @endforeach
            </select>
            <label for="proveedor_id">{{ __('Proveedor') }}</label>
            {!! $errors->first(
                'proveedor_id',
                '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>',
            ) !!}
        </div>





        <!-- Campo de selección para 'ubicacion_id' -->
        <div class="input-field col s12 m4">
            <select name="ubicacion_id" id="ubicacion_id" class="validate @error('ubicacion_id') invalid @enderror">
                <option value="" disabled selected>Seleccionar ubicación</option>
                @foreach ($ubicaciones as $ubicacion)
                    <option value="{{ $ubicacion->id }}"
                        {{ old('ubicacion_id', $producto?->ubicacion_id) == $ubicacion->id ? 'selected' : '' }}>
                        {{ $ubicacion->nombre }}
                    </option>
                @endforeach
            </select>
            <label for="ubicacion_id">{{ __('Ubicación') }}</label>
            {!! $errors->first(
                'ubicacion_id',
                '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>',
            ) !!}
        </div>

        <!-- Campos de texto para 'fila' y 'columna' -->
        <div class="input-field col s12 m2">
            <input type="number" name="fila" id="fila" class="validate @error('fila') invalid @enderror"
                value="{{ old('fila', $producto?->fila) }}">
            <label for="fila" class="{{ $producto?->fila ? 'active' : '' }}">{{ __('Fila') }}</label>
            {!! $errors->first('fila', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
        </div>

        <div class="input-field col s12 m2">
            <input type="number" name="columna" id="columna"
                class="validate @error('columna') invalid @enderror"
                value="{{ old('columna', $producto?->columna) }}">
            <label for="columna" class="{{ $producto?->columna ? 'active' : '' }}">{{ __('Columna') }}</label>
            {!! $errors->first(
                'columna',
                '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>',
            ) !!}
        </div>


        <!-- Campo de selección para 'subalmacen_id' -->
        <div class="input-field col s12 m4">
            <select name="subalmacen_id" id="subalmacen_id"
                class="validate @error('subalmacen_id') invalid @enderror">
                <option value="" selected>Ninguno</option>
                @foreach ($subalmacenes as $subalmacen)
                    <option value="{{ $subalmacen->id }}"
                        {{ old('subalmacen_id', $producto?->subalmacen_id) == $subalmacen->id ? 'selected' : '' }}>
                        {{ $subalmacen->nombre }}
                    </option>
                @endforeach
            </select>
            <label for="subalmacen_id">{{ __('Subalmacén') }}</label>
            {!! $errors->first(
                'subalmacen_id',
                '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>',
            ) !!}
        </div>

<!-- Campo de selección para 'igv_id' -->
<div class="input-field col s12 m4">
    <select name="igv_id" id="igv_id" class="validate @error('igv_id') invalid @enderror">
        <option value="0" selected>Por defecto</option>
        @foreach ($igvs as $igv)
            <option value="{{ $igv->id }}"
                {{ old('igv_id', $producto?->relProductosucursal->igv_id ?? 0) == $igv->id ? 'selected' : '' }}>
                {{ $igv->descripcion }}
            </option>
        @endforeach
    </select>
    <label for="igv_id">{{ __('IGV predeterminado') }}</label>
    {!! $errors->first('igv_id', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
</div>


        <!-- Checkbox para 'venta_unitaria' -->
        <div class="input-field col s12 m2">
            <label>
                <input type="checkbox" name="venta_unitaria" id="venta_unitaria"
                    {{ old('venta_unitaria', $producto?->venta_unitaria) == 'S' ? 'checked' : '' }} />
                <span>{{ __('Venta unitaria') }}</span>
            </label>
        </div>




    </div>
</div>
<div class="row padding-1 p-1">
    <div class="col s12">
        <div class="input-field col s12 m12">
            <h6 class="subtitle left-align"><i class="fa fa-solid fa-money-bill left left"></i> Precios:</h6>
        </div>

<!-- Campo de texto para 'preciocompra' -->
<div class="input-field col s12 m4">
    <input type="number" name="preciocompra" id="preciocompra"
        class="validate @error('preciocompra') invalid @enderror"
        value="{{ old('preciocompra', optional($producto?->relListaUnidadSucursal)->preciocompra ?? 0) }}" step="0.000001">
    <label for="preciocompra" class="active">{{ __('Precio de compra') }}</label>
    {!! $errors->first(
        'preciocompra',
        '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>',
    ) !!}
</div>


        <!-- Campo de texto para 'preciomanoobra' -->
        <div class="input-field col s12 m4">
            <input type="number" name="preciomanoobra" id="preciomanoobra"
                class="validate @error('preciomanoobra') invalid @enderror"
                value="{{ old('preciomanoobra', $producto?->preciomanoobra) ?? 0 }}" step="0.000001">
            <label for="preciomanoobra" class="active">{{ __('Precio mano de obra') }}</label>
            {!! $errors->first(
                'preciomanoobra',
                '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>',
            ) !!}
        </div>

        <!-- Campo de texto para 'precioventa' -->
        <div class="input-field col s12 m4">
            <input type="number" name="precioventa" id="precioventa"
                class="validate @error('precioventa') invalid @enderror"
                value="{{ old('precioventa', $producto?->relListaUnidadSucursal->precioventa ?? 0) }}" step="0.000001">
            <label for="precioventa" class="active">{{ __('Precio de venta') }}</label>
            {!! $errors->first(
                'precioventa',
                '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>',
            ) !!}
        </div>
        
        <!-- Radio buttons para 'moneda' -->
        <div class="input-field col s12 m4">

            <label>
                <input type="radio" name="moneda" id="moneda_pen" value="PEN"
                    {{ old('moneda', $producto?->moneda ?? 'PEN') == 'PEN' ? 'checked' : '' }} />
                <span>S/.</span>
            </label>

            <label style="margin-left: 100px;">
                <input type="radio" name="moneda" id="moneda_usd" value="USD"
                    {{ old('moneda', $producto?->moneda) == 'USD' ? 'checked' : '' }} />
                <span>$</span>
            </label>
            {!! $errors->first('moneda', '<span class="helper-text red-text" role="alert"><strong>:message</strong></span>') !!}
        </div>

        <!-- Campo de texto para 'sucursales' -->
        <div class="input-field col s12 m4">
            <label>
                <input type="checkbox" name="sucursales" id="sucursales" />
                <span>{{ __('Activar en todas las sucursales') }}</span>
            </label>
        </div>

    </div>

</div>
<!-- Botones de Acción -->
<div class="col s12 mt20 mt-2">
    <div class="modal-footer">
        <button type="submit" class="btn waves-effect waves-light"><i
                class="material-icons left">send</i>{{ __('Guardar') }}</button>
        <a href="#!" class="modal-close waves-effect waves-green btn-flat">Cerrar</a>
    </div>
</div>
