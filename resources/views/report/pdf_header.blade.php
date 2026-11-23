<!DOCTYPE html>
<html>
<head>
    <style>
        * {
            font-family: Verdana, sans-serif;
            font-size: 9px;
        }


        .header-left {
            float: left;
        }

        .header-right {
            float: right;
        }
    </style>
</head>
<body>

<header>
    <span class="header-left">
        Empresa: {{ $sucursal->empresa->razon_social }}
        | Sucursal: {{ $sucursal->nombrecomercial }}
    </span>

    <span class="header-right">
        Fecha: {{ \Carbon\Carbon::now()->format('d/m/Y h:i:s A') }}
    </span>
</header>

</body>
</html>
