<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Huéspedes</title>
    <link rel="stylesheet" href="{{ public_path('css/pdf.css') }}">
</head>
<body>
    <header class="left xx-small">
        Empresa: {{$sucursal->empresa->razon_social ?? ''}} | Sucursal: {{$sucursal->nombrecomercial ?? ''}}
    </header>
    <header class="right xx-small">
        {{ \Carbon\Carbon::now()->format('Y-m-d H:i:s') }}
    </header>
    <footer class="left xx-small"></footer>
    <footer class="xx-small">
        Copyright&copy; {{ date('Y') }}. Garzasoft. Todos los derechos reservados.
    </footer>
    <footer class="right xx-small"></footer>
    <h5 style="text-align: center">Reporte de Huéspedes</h5>
    <p style="text-align: center; font-size: 10px; margin-top: -20px">
        Filtros: Tipo: {{ $tipopersona }} | Género: {{ $genero }} | Buscar: {{ $search }}
    </p>
    <table class="table-full-soft-bordered xx-small" width="100%">
        <thead class="bg-primary white-text">
            <tr>
                <th>Distrito</th>
                <th>Tipo</th>
                <th>Nro. documento</th>
                <th>Nombres</th>
                <th>N° alojamientos</th>
                <th>Fecha Nac.</th>
                <th>Género</th>
                <th>Dirección</th>
                <th>Email</th>
                <th>Teléfono Fijo</th>
                <th>Teléfono Móvil</th>
              
            </tr>
        </thead>
        <tbody>
            @foreach ($huespedes as $persona)
                <tr>
                    <td>{{ $persona->ubigeo->descripcion ?? '' }}</td>
                    <td>{{ strtoupper(
                        $persona->personamaestro->tipopersona === 'Natural'
                        ? 'DNI'
                        : ($persona->personamaestro->tipopersona === 'Juridico'
                            ? 'RUC'
                            : $persona->personamaestro->tipopersona),
                    ) }}</td>
                    <td>{{ $persona->personamaestro->nrodoc }}</td>
                    <td>{{ $persona->apellidos }} {{ $persona->nombres }}</td>
                    <td>{{ $persona->huespedes->whereNotNull('movhabitacion_id')->count() }}</td>
                    <td>{{ $persona->personamaestro->fechanac }}</td>
                    <td>{{ $persona->personamaestro->sexo }}</td>
                    <td>{{ $persona->direccion }}</td>
                    <td>{{ $persona->email }}</td>
                    <td>{{ $persona->telefonofijo }}</td>
                    <td>{{ $persona->telefonomovil }}</td>
                    
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
