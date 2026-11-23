@extends('layouts.app')

@section('content')
    <style>
        .card-habitacion {
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            /* Sombra más suave */
            transition: transform 0.2s ease-in-out;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 10px;

            color: #000000;
            /* Texto en blanco */

        }

        /* Asegura que todas las tarjetas de las habitaciones tengan la misma altura */
        .habitacion-column {
            display: block;
            /* quitar flex */
        }

        .habitacion-item {
            display: block;
            /* quitar flex */
            min-height: 350px;
            /* mantener altura uniforme */
        }

        @media (min-width: 300px) {
            .habitacion-item {
                flex: 1 1 100%;
                /* 2 tarjetas por fila */
                max-width: 100%;
            }

            .card-item-body small,
            .card-item-footer small {
                font-size: 1em;
            }

            .operaciones-container .btn-floating {
                width: 40px;
                height: 40px;
            }

            .numerohabitacion {

                font-size: 35px;
            }



            .tr-habitacion {
                font-size: 1.5em;
            }



            .card-item-icon {
                font-size: 1.2em;
            }

        }



        @media (min-width: 500px) {
            .habitacion-item {
                flex: 1 1 50%;
                /* 2 tarjetas por fila */
                max-width: 50%;
            }

            .operaciones-container .btn-floating {
                width: 30px;
                height: 30px;
            }

            .btn-floating i {
                font-size: 1.2em;
            }

            .tr-habitacion {
                font-size: 0.9em;
            }

            .card-item-body small,
            .card-item-footer small {
                font-size: 0.9em;
            }

            .estadopago{
                font-size: 0.9em !important;
            }

        }

        @media (min-width: 600px) {
            .habitacion-item {
                flex: 1 1 48%;
                /* 2 tarjetas por fila */
                max-width: 48%;
            }

            .operaciones-container .btn-floating {
                width: 23px;
                height: 23px;
                font-size: 0.7em;
            }

            .tr-habitacion {
                font-size: 0.8em;
            }

            .card-item-body small,
            .card-item-footer small {
                font-size: 0.55em;
            }

            .estadopago {
                font-size: 0.7em !important;
            }

            .card-item-icon {
                font-size: 0.8em;
            }

            .numerohabitacion {
                font-size: 30px;
            }

        }



        @media (min-width: 800px) {
            .habitacion-item {
                flex: 1 1 33.333%;
                /* 3 tarjetas por fila */
                max-width: 33.333%;
            }

            .row .col.l3 {
                width: 100%;
                margin-left: auto;
                left: auto;
                right: auto;
            }

            .operaciones-container .btn-floating {
                width: 35px;
                height: 35px;
                font-size: 0.9em;
            }

            .card-item-icon {
                font-size: 1em;
            }

            .tr-habitacion {
                font-size: 1.2em;
            }

            .estadopago {
                font-size: 0.9em !important;
            }

            .card-item-body small,
            .card-item-footer small {
                font-size: 0.8em;
            }




        }

        @media (min-width: 900px) {
            .habitacion-item {
                flex: 1 1 22%;
                /* 4 tarjetas por fila solo si hay mucho espacio */
                max-width: 25%;
            }

            .tr-habitacion {
                font-size: 1em;
            }

            .card-item-body small,
            .card-item-footer small {
                font-size: 0.7em;
            }

            .operaciones-container .btn-floating {
                width: 30px;
                height: 30px;
                font-size: 0.9em;
            }

            .estadopago {
                font-size: 0.9em !important;
            }
              .card-item-icon {
                font-size: 0.8em;
            }

        }
@media (min-width: 1200px) {
            .habitacion-item {
                flex: 1 1 20%;
                max-width: 20%;
            }

            /* Reducir el tamaño del texto dentro de las tarjetas */
            .card-item-title {
                font-size: 0.9em;
            }

            .numerohabitacion {
                font-size: 2em;
            }

            .card-item-body small,
            .card-item-footer small {
                font-size: 0.8em;
            }

            .operaciones-container .btn-floating {
                width: 32px;
                height: 32px;
                font-size: 0.9em;
            }

            .card-item-icon {
                font-size: 1.1em;
            }

            /* Ajustar tamaño del contador de tiempo */
            .tiempo-restante-container,
            .tiempo-restante {
                font-size: 0.75em;
                /* ajusta según se vea bien */
            }

            /* Ajustar tamaño del número dentro del timer */
            .tiempo-restante span {
                font-size: 1.10em;
                /* o menos si quieres más pequeño */
            }

            .tr-habitacion {
                font-size: 1.2em;
            }

            .estadopago {
                font-size: 0.75em !important;
            }
        }
        @media (min-width: 1400px) {
            .habitacion-item {
                flex: 1 1 16.66%;
                max-width: 16.66%;
            }

            /* Reducir el tamaño del texto dentro de las tarjetas */
            .card-item-title {
                font-size: 0.9em;
            }

            .numerohabitacion {
                font-size: 2em;
            }

            .card-item-body small,
            .card-item-footer small {
                font-size: 0.75em;
            }

            .operaciones-container .btn-floating {
                width: 32px;
                height: 32px;
                font-size: 0.9em;
            }

            .card-item-icon {
                font-size: 1.1em;
            }

            /* Ajustar tamaño del contador de tiempo */
            .tiempo-restante-container,
            .tiempo-restante {
                font-size: 0.75em;
                /* ajusta según se vea bien */
            }

            /* Ajustar tamaño del número dentro del timer */
            .tiempo-restante span {
                font-size: 1.10em;
                /* o menos si quieres más pequeño */
            }

            .tr-habitacion {
                font-size: 1.2em;
            }

            .estadopago {
                font-size: 0.75em !important;
            }
        }

        @media (min-width: 1600px) {
                .habitacion-item {
                flex: 1 1 14.28%;
                max-width: 14.28%;
            }

            /* Reducir el tamaño del texto dentro de las tarjetas */
            .card-item-title {
                font-size: 0.9em;
            }

            .numerohabitacion {
                font-size: 2em;
            }


            .operaciones-container .btn-floating {
                width: 32px;
                height: 32px;
                font-size: 0.9em;
            }

            .card-item-icon {
                font-size: 1.1em;
            }

            /* Ajustar tamaño del contador de tiempo */
            .tiempo-restante-container,
            .tiempo-restante {
                font-size: 0.75em;
                /* ajusta según se vea bien */
            }

            /* Ajustar tamaño del número dentro del timer */
            .tiempo-restante span {
                font-size: 1.10em;
                /* o menos si quieres más pequeño */
            }

            .tr-habitacion {
                font-size: 1.2em;
            }

            .estadopago {
                font-size: 0.75em !important;
            }
        }

          @media (min-width: 1900px) {
                .habitacion-item {
                flex: 1 1 12.28%;
                max-width: 12.28%;
            }

            /* Reducir el tamaño del texto dentro de las tarjetas */
            .card-item-title {
                font-size: 0.9em;
            }

            .numerohabitacion {
                font-size: 2em;
            }

            .card-item-body small,
            .card-item-footer small {
                font-size: 0.75em;
            }

            .operaciones-container .btn-floating {
                width: 32px;
                height: 32px;
                font-size: 0.9em;
            }

            .card-item-icon {
                font-size: 1.1em;
            }

            /* Ajustar tamaño del contador de tiempo */
            .tiempo-restante-container,
            .tiempo-restante {
                font-size: 0.75em;
                /* ajusta según se vea bien */
            }

            /* Ajustar tamaño del número dentro del timer */
            .tiempo-restante span {
                font-size: 1.10em;
                /* o menos si quieres más pequeño */
            }

            .tr-habitacion {
                font-size: 1.2em;
            }

            .estadopago {
                font-size: 0.75em !important;
            }
        }


         
        /* 🔧 Ajuste para vista móvil */
        @media (max-width: 950px) {
            .control-habitaciones-bar {
                flex-direction: column !important;
                align-items: stretch !important;
                justify-content: center !important;
                text-align: center !important;
                gap: 10px;
                /* separa visualmente los bloques */
            }

            .status-icons {
                justify-content: center !important;
                flex-wrap: wrap;
            }

            .pisos-row {
                justify-content: center !important;
                flex-wrap: wrap;
            }

            .total {
                text-align: center !important;
                margin-top: 8px;
            }

            /* Ajuste de círculos y botones para que no queden grandes */
            .circle {
                width: 34px;
                height: 34px;
            }

            .piso-boton {
                width: 42px !important;
                height: 42px !important;
                margin: 3px !important;
            }

            /* Tamaño de texto del total más legible en móvil */
            .total {
                font-size: 1em;
            }
        }

        .control-habitaciones-tools {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 0;
        }

        .control-total-tools {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 8px;
            white-space: nowrap;
        }

        .control-total-tools .total {
            margin: 0;
        }

        .control-habitaciones-filtros {
            margin: 0 0 18px;
            padding: 0;
        }

        .control-filtro-busqueda {
            margin: 0 !important;
            min-width: 0;
        }

        .control-filtro-limpiar {
            height: 40px;
            min-width: 44px;
        }

        .control-filtro-clear {
            padding-top: 18px !important;
        }

        @media (max-width: 1100px) {
            .control-filtro-clear {
                text-align: right;
            }
        }

        @media (max-width: 600px) {
            .control-habitaciones-tools {
                margin: 0;
            }

            .control-total-tools {
                justify-content: center;
            }

            .control-habitaciones-filtros {
                padding: 0 0 8px;
            }

            .control-filtro-limpiar {
                width: 100%;
            }
        }


        .custom-card {
            display: flex;
            flex-direction: column;
            height: 100%;
            /* max-width: 200px; */
            /* Hace que la tarjeta ocupe toda la altura disponible */
            justify-content: space-between;
            /* Asegura que el contenido se distribuya bien */
        }



        .card-item-body small {
            display: block;
            font-size: 0.9em;
            color: #000000;
            padding-top: 8px;

            /* Ajustar el color del texto secundario */
        }

        .card-habitacion:hover {
            transform: scale(1.05);
            /* Aumentar el efecto de hover */
        }

        .card-item-title {
            font-weight: bold;
            font-size: 1.1em;
            color: #333;
            background-color: white;
            border-radius: 20px;
            justify-content: center;
            text-align: center;
        }

        .numerohabitacion {
            font-weight: bold;
            color: #333;
            background-color: white;
            border-radius: 20px;
            padding: 5px;
            display: inline-block;
            /* Ajusta el ancho al contenido */
        }


        .card-item-icon {

            display: flex;
            justify-content: flex-end;
            margin-top: 10px;
        }

        .card-item-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: white;
            border-radius: 20px;
            padding: 5px;
            margin-top: 10px;
        }

        .card-item-footer small {

            color: #000000;
            /* Color verde para el precio */
            font-weight: bold;
        }




        .card-item-footer .bed-icon.maintenance {
            color: orange;

        }


        .text-warning {
            color: #fdc16a !important;
        }

        .text-green {
            color: #7FFFCC !important;
        }

        .card-body {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .d-flex {
            display: flex;
            justify-content: space-between;
            /* Alinea los elementos a los extremos */
            align-items: center;
            /* Alinea los elementos verticalmente al centro */
        }

        .habitaciones-details {
            display: flex;
            flex-direction: column;
            /* Los detalles se muestran de arriba a abajo */
            gap: 5px;
            /* Espaciado entre los detalles */
            font-size: 100%;
            color: #555;
        }

        .habitaciones-details small {
            display: inline-block;
            /* Asegura que los detalles estén en línea, uno debajo del otro */
            margin-left: 10px;
            /* Espacio entre el nombre del piso y los detalles */
        }

        .item.active .custom-card {
            background-color: #e0f7fa;
            /* Celeste claro */
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            /* Sombra más suave */
            transition: background-color 0.3s ease;
            /* Efecto suave al cambiar el fondo */
        }

        .card-item-body {
            display: flex;
            flex-direction: column;
            /* Distribuir en columnas */
            gap: 10px;
            /* Espaciado entre elementos */
        }


        .tr-habitacion-container {
            border-radius: 20px;
            padding: 3px;
        }

        .tr-habitacion {
            font-weight: bold;

            color: #ffffff;
            /* Color del cronómetro, puedes cambiarlo si lo deseas */
        }

        .palpitar {
            animation: palpitar 1s infinite;
        }

        @keyframes palpitar {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }
        }

        .habitacion-botones-cronometro {
            display: flex;
            flex-direction: column;
        }

        .tr-habitacion-container {

            text-align: center;
        }

        .operaciones-container {
            display: flex;
            gap: 2px;
            /* Espaciado horizontal entre botones */
            flex-wrap: wrap;
            /* Permite que los botones pasen a una nueva línea si es necesario */
            justify-content: flex-end;
            /* Alinea los botones al inicio */
        }

        .btn-floating {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s;
        }

        .btn-floating:hover {
            transform: scale(1.1);
            /* Efecto al pasar el mouse */
        }

        .estado-circle {
            display: inline-block;
            width: 12px;
            /* Tamaño del círculo */
            height: 12px;
            border-radius: 50%;
            margin-right: 10px;
            /* Espaciado con el texto */
        }

        /* Contenedor de la cinta "Comentado" */
        .ribbon-wrapper.comentario {
            height: 80px;
            overflow: hidden;
            position: absolute;
            left: -1px;
            /* Posición izquierda */
            top: -1px;
            width: 80px;
            z-index: 10;
        }

        /* Estilo de la cinta "Comentado" */
        .ribbon.comentario {
            background-color: blue;
            /* Azul para Comentado */
            color: white;
            font-weight: bold;
            box-shadow: 0 0 3px rgba(0, 0, 0, .3);
            font-size: 0.6rem;
            line-height: 100%;
            padding: 5px 0;
            position: relative;
            left: -25px;
            /* Posición izquierda */
            text-align: center;
            text-shadow: 0 -1px 0 rgba(0, 0, 0, .4);
            text-transform: uppercase;
            top: 20px;
            transform: rotate(-45deg);
            width: 110px;
            /* Más ancho para mostrar todo el texto */
        }

        /* Contenedor de la cinta "Reservada" */
        .ribbon-wrapper.reservada {
            height: 80px;
            overflow: hidden;
            position: absolute;
            right: -1px;
            /* Posición derecha */
            top: -1px;
            width: 80px;
            z-index: 10;
        }

        /* Estilo de la cinta "Reservada" */
        .ribbon.reservada {
            background-color: red;
            /* Rojo para Reservada */
            color: white;
            font-weight: bold;
            box-shadow: 0 0 3px rgba(0, 0, 0, .3);
            font-size: 0.6rem;
            line-height: 100%;
            padding: 5px 0;
            position: relative;
            right: -1px;
            /* Posición derecha */
            text-align: center;
            text-shadow: 0 -1px 0 rgba(0, 0, 0, .4);
            text-transform: uppercase;
            top: 20px;
            transform: rotate(45deg);
            width: 110px;
            /* Más ancho para mostrar todo el texto */
        }

        /* Contenedor */
        .control-habitaciones-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0px 12px 10px 12px;
            background: #fff;
            font-family: Arial, sans-serif;
        }

        /* Icons */
        .status-icons {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        /* Bola exterior */
        .circle {
            position: relative;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow:
                inset -6px -6px 12px rgba(0, 0, 0, 0.25),
                inset 6px 6px 10px rgba(255, 255, 255, 0.25),
                0 3px 6px rgba(0, 0, 0, 0.25);
            border: 2px solid rgba(255, 255, 255, 0.85);
            overflow: visible;
        }

        /* Bola interior blanca (el disco) */
        .circle .inner {
            position: absolute;
            width: 56%;
            /* tamaño del disco blanco */
            height: 56%;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            background: #fff;
            border-radius: 50%;
            box-shadow:
                inset 0 -2px 4px rgba(0, 0, 0, 0.12),
                0 1px 3px rgba(0, 0, 0, 0.15);
            border: 1px solid rgba(0, 0, 0, 0.08);
        }

        /* Número centrado (sobre la circunferencia blanca) */
        .circle .num {
            position: absolute;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            font-weight: 700;
            font-size: 14px;
            pointer-events: none;
            z-index: 2;
        }

        /* Efecto de luz (pequeño brillo en la bola) */
        .circle::before {
            content: "";
            position: absolute;
            left: 10%;
            top: 8%;
            width: 26%;
            height: 26%;
            border-radius: 50%;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.9), rgba(255, 255, 255, 0.15));
            transform: rotate(-20deg);
            opacity: 0.9;
            filter: blur(0.6px);
            z-index: 3;
        }

        /* Colores tipo bola con degradado */
        .ocupada {
            background: radial-gradient(circle at 30% 28%, #ff8383, #ff8383 70%);
        }

        .disponible {
            background: radial-gradient(circle at 30% 28%, #69f0ae, #69f0ae 70%);
        }

        .limpieza {
            background: radial-gradient(circle at 30% 28%, #FFA726, #FFA726 70%);
        }

        .mantenimiento {
            background: radial-gradient(circle at 30% 28%, #805008, #805008 70%);
        }

        /* Ajustes de color del número según la bola (opcional, para mejorar contraste) */
        .limpieza .num {
            color: #111;
        }

        /* el amarillo usa número oscuro */
        .disponible .num {
            color: #004d3a;
        }

        /* opción para buen contraste */
        .ocupada .num,
        .mantenimiento .num {
            color: #000000;
        }

        /* Texto y total */
        .title {
            font-weight: 700;
            text-align: center;
            flex-grow: 1;
            margin: 0 12px;
        }

        .total {
            font-weight: bold;
        }


        /* Responsive: achica las bolas en pantallas pequeñas */
        @media (max-width:480px) {
            .circle {
                width: 32px;
                height: 32px;
            }

            .circle .inner {
                width: 58%;
                height: 58%;
            }

            .circle .num {
                font-size: 12px;
            }
        }

        .pisos-row {
            display: flex;
            flex-wrap: wrap;
            /* permite que bajen a la siguiente línea */
            gap: 8px;
            /* separación horizontal/vertical entre botones */
            align-items: center;
        }

        /* Mantener tamaño y estilo actual de los botones, sólo eliminar márgenes por defecto */
        .piso-boton {
            margin: 0;
            /* evitamos márgenes extras que los separen */
            display: inline-flex;
            /* ya lo tienes, pero lo reafirmamos para centrar contenido */
            align-items: center;
            justify-content: center;
        }
        /* Modal opciones de habitacion ocupada */
        #modalOcupada.modal-ocupada {
            --mh-ink: #1f2937;
            --mh-muted: #6b7280;
            --mh-accent: #0f766e;
            --mh-card-border: #e4e8ee;
            --mh-danger: #c62828;
            border-radius: 18px;
            max-width: 980px;
            width: 94%;
        }

        #modalOcupada.modal-ocupada .modal-content {
            padding: 26px 28px 18px;
            background: linear-gradient(180deg, #f6f8fb 0%, #ffffff 42%);
        }

        #modalOcupada.modal-ocupada .modal-ocupada-title {
            margin: 0 0 12px;
            font-weight: 700;
            letter-spacing: 0.2px;
            color: var(--mh-ink);
            text-align: left;
        }

        #modalOcupada.modal-ocupada .comentario-panel {
            display: flex;
            gap: 12px;
            align-items: flex-start;
            padding: 12px 14px;
            border-radius: 12px;
            background: #eef7f5;
            border: 1px solid rgba(15, 118, 110, 0.2);
        }

        #modalOcupada.modal-ocupada .comentario-icon {
            font-size: 1.4rem;
            color: var(--mh-accent);
            margin-top: 2px;
        }

        #modalOcupada.modal-ocupada .comentario-label {
            font-size: 0.7rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--mh-muted);
        }

        #modalOcupada.modal-ocupada .comentario-habitacion {
            font-weight: 600;
            color: var(--mh-accent);
            margin-top: 2px;
            line-height: 1.4;
            word-break: break-word;
        }

        #modalOcupada.modal-ocupada .comentario-habitacion:empty::before {
            content: "Sin comentario";
            color: var(--mh-muted);
            font-weight: 500;
        }

        #modalOcupada.modal-ocupada .modal-ocupada-actions {
            margin-top: 18px;
        }

        #modalOcupada.modal-ocupada .action-card {
            border-radius: 14px;
            border: 1px solid var(--mh-card-border);
            background: #ffffff;
            box-shadow: 0 6px 16px rgba(15, 23, 42, 0.08);
            transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
            text-decoration: none;
            color: inherit;
            display: block;
            cursor: pointer;
        }

        #modalOcupada.modal-ocupada .action-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 14px 30px rgba(15, 23, 42, 0.14);
            border-color: rgba(15, 118, 110, 0.45);
        }

        #modalOcupada.modal-ocupada .action-card .card-content {
            padding: 18px 12px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
        }

        #modalOcupada.modal-ocupada .action-card h6 {
            margin: 0;
            font-weight: 600;
            font-size: 0.95rem;
            color: var(--mh-ink);
        }

        #modalOcupada.modal-ocupada .action-card i.large {
            font-size: 2.4rem;
            line-height: 1;
        }

        #modalOcupada.modal-ocupada .action-card.action-danger {
            border-color: rgba(198, 40, 40, 0.35);
            background: linear-gradient(180deg, rgba(198, 40, 40, 0.08), #ffffff 55%);
        }

        #modalOcupada.modal-ocupada .action-card.action-danger:hover {
            border-color: var(--mh-danger);
        }

        #modalOcupada.modal-ocupada .action-form {
            width: 100%;
            margin: 0;
        }

        #modalOcupada.modal-ocupada .action-form button.action-card {
            width: 100%;
            padding: 0;
        }

        #modalOcupada.modal-ocupada .modal-footer {
            background: #f3f6fa;
            border-top: 1px solid #e5e7eb;
            padding: 12px 24px;
        }

        #modalOcupada.modal-ocupada .modal-ocupada-close {
            border: 1px solid #d1d5db;
            border-radius: 10px;
            color: #334155;
            font-weight: 600;
        }

        #modalOcupada.modal-ocupada .modal-ocupada-close:hover {
            background: #ffffff;
        }

        /* Modales de estado (disponible / limpieza / mantenimiento) */
        .modal-estado {
            --mh-ink: #1f2937;
            --mh-muted: #6b7280;
            --mh-accent: #0f766e;
            --mh-soft: rgba(15, 118, 110, 0.12);
            --mh-card-border: #e4e8ee;
            border-radius: 18px;
            max-width: 860px;
            width: 94%;
        }

        .modal-estado.modal-disponible {
            --mh-accent: #16a34a;
            --mh-soft: rgba(22, 163, 74, 0.12);
        }

        .modal-estado.modal-limpieza {
            --mh-accent: #d97706;
            --mh-soft: rgba(217, 119, 6, 0.12);
        }

        .modal-estado.modal-mantenimiento {
            --mh-accent: #6b4e16;
            --mh-soft: rgba(107, 78, 22, 0.12);
        }

        .modal-estado .modal-content {
            padding: 24px 26px 16px;
            background: linear-gradient(180deg, #f7f8fb 0%, #ffffff 50%);
        }

        .modal-estado-title {
            margin: 0;
            font-weight: 700;
            letter-spacing: 0.2px;
            color: var(--mh-ink);
            text-align: left;
        }

        .modal-estado-subtitle {
            margin: 6px 0 12px;
            font-size: 0.85rem;
            color: var(--mh-muted);
        }

        .modal-estado-actions {
            margin-top: 6px;
        }

        .modal-estado .action-card {
            border-radius: 14px;
            border: 1px solid var(--mh-card-border);
            background: #ffffff;
            box-shadow: 0 6px 16px rgba(15, 23, 42, 0.08);
            transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
            text-decoration: none;
            color: inherit;
            display: block;
            cursor: pointer;
        }

        .modal-estado .action-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 14px 30px rgba(15, 23, 42, 0.14);
            border-color: var(--mh-accent);
        }

        .modal-estado .action-card .card-content {
            padding: 18px 12px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
        }

        .modal-estado .action-card h6 {
            margin: 0;
            font-weight: 600;
            font-size: 0.95rem;
            color: var(--mh-ink);
        }

        .modal-estado .action-card i.large {
            font-size: 2.4rem;
            line-height: 1;
        }

        .modal-estado .modal-footer {
            background: #f3f6fa;
            border-top: 1px solid #e5e7eb;
            padding: 12px 24px;
        }

        .modal-estado-close {
            border: 1px solid #d1d5db;
            border-radius: 10px;
            color: #334155;
            font-weight: 600;
        }

        .modal-estado-close:hover {
            background: #ffffff;
        }

        /* Dos columnas únicamente en móvil; el layout original de PC queda intacto. */
        @media (max-width: 699px) {
            .habitacion-column {
                display: grid !important;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 14px;
                margin: 0 !important;
            }

            .habitacion-column > .habitacion-item {
                width: auto !important;
                max-width: none !important;
                min-height: 0 !important;
                float: none !important;
                flex: none !important;
                margin: 0 !important;
                padding: 0 !important;
                box-sizing: border-box;
            }

            .habitacion-column .card-habitacion {
                width: 100%;
                min-width: 0;
                min-height: 0 !important;
                height: auto !important;
                align-self: start;
                margin: 0 !important;
                padding: 9px !important;
                box-sizing: border-box;
                transform: none;
            }

            .habitacion-column .card-habitacion .card-body {
                height: auto !important;
                flex: 0 0 auto;
                gap: 7px;
                padding: 0 !important;
            }

            .habitacion-column .card-item-body,
            .habitacion-column .habitacion-detalles {
                gap: 5px;
            }

            .habitacion-column .card-item-title {
                min-width: 0;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
                font-size: clamp(0.72rem, 2.2vw, 1rem);
                padding: 5px 7px;
            }

            .habitacion-column .numerohabitacion {
                margin: 8px 0;
                padding: 4px 8px;
                font-size: clamp(1.55rem, 7vw, 2.25rem);
                line-height: 1.1;
            }

            .habitacion-column .card-item-footer {
                min-width: 0;
                gap: 4px;
                margin-top: 4px;
                padding: 5px 7px;
                overflow: hidden;
            }

            .habitacion-column .card-item-footer small,
            .habitacion-column .cliente-principal {
                min-width: 0;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }

            .habitacion-column .operaciones-container {
                gap: 4px;
            }

            .habitacion-column .operaciones-container .btn-floating {
                width: 30px;
                height: 30px;
                line-height: 30px;
            }

            .habitacion-column .operaciones-container .btn-floating i {
                font-size: 0.9rem;
            }

            .habitacion-column .tr-habitacion {
                font-size: clamp(0.72rem, 2.7vw, 1rem);
                white-space: nowrap;
            }
        }

        /* En escritorio, compactar el panel de filtros y acercar las tarjetas. */
        @media (min-width: 700px) {
            .control-habitaciones-bar {
                padding: 8px 16px 10px;
                margin-bottom: 0;
            }

            .control-habitaciones-filtros {
                margin: 0 0 12px !important;
                padding-bottom: 0 !important;
            }

            .main-content > .row {
                margin-bottom: 0 !important;
            }

            .main-content > .control-habitaciones-filtros:not([style*="none"]) {
                margin: 0 0 12px !important;
            }

            .main-content > .control-habitaciones-filtros:not([style*="none"]) + .row {
                margin-top: 0 !important;
            }

            .main-content > .control-habitaciones-filtros:not([style*="none"]) + .row .control-habitaciones-bar {
                transform: translateY(-12px);
            }

            .main-content .habitacion-column {
                margin-top: 12px !important;
            }

        }
    </style>
        @php
        $user = Auth::user();
        $persona = App\Models\Persona::find($user->persona_id);
        $sucursal = App\Models\Sucursal::findOrFail($persona->sucursal_id);
        $contraseñaanulacion = App\Models\RelParametrosucursal::where('sucursal_id', $sucursal->id)
            ->whereHas('parametro', function ($query) {
                $query->where('descripcion', 'Contraseña para anulación de alojamiento');
            })
            ->value('valor');

            
        $telefonoadmin = App\Models\RelParametrosucursal::where('sucursal_id', $sucursal->id)
            ->whereHas('parametro', function ($query) {
                $query->where('descripcion', 'Celular de administración');
            })
            ->value('valor');
    @endphp
        <input type="hidden" id="contraseñaanulacion" name="contraseñaanulacion" value={{ $contraseñaanulacion }}>
    <input type="hidden" name="telefonoadmin" id="telefonoadmin" value='@json($telefonoadmin)'>

    <div class="main-body">
        <div class="row">
            <div class="col s12">
                <div class="card main-content">
                    @include('movhabitacion.partials.filtros-control')

                    <div class="row" style="margin-bottom: -20px;">
                        <div class="control-habitaciones-bar">
                            <div class="status-icons">
                                
    {{-- OCUPADAS --}}
    <a href="{{ route('movhabitacion.filtrarEstado', ['estado' => 'ocupada']) }}"
       class="btn waves-effect waves-dark ocupada lighten-1 white-text text-darken-3 {{ ($estado ?? 'todos') == 'ocupada' ? 'active' : '' }}  btn-cargar-crud tooltipped"
       data-tooltip="Habitaciones ocupadas" style="font-size: 1.2rem; font-weight: 600">
        <span>{{ $habitacionesOcupadasGeneral }}</span>
        <i class="fa-solid fa-bed left"></i>
    </a>

    {{-- DISPONIBLES --}}
    <a href="{{ route('movhabitacion.filtrarEstado', ['estado' => 'disponible']) }}"
       class="btn waves-effect waves-dark disponible accent-4 white-text ml5 {{ ($estado ?? 'todos') == 'disponible' ? 'active' : '' }}  btn-cargar-crud tooltipped"
        data-tooltip="Habitaciones disponibles" style="font-size: 1.2rem; font-weight: 600">
        <span>{{ $habitacionesDisponiblesGeneral }}</span>
        <i class="fa-solid fa-door-open left"></i>
    </a>

    {{-- LIMPIEZA --}}
    <a href="{{ route('movhabitacion.filtrarEstado', ['estado' => 'limpieza']) }}"
       class="btn waves-effect waves-dark limpieza lighten-1 white-text ml5 {{ ($estado ?? 'todos') == 'limpieza' ? 'active' : '' }}  btn-cargar-crud tooltipped"
        data-tooltip="Habitaciones en limpieza"style="font-size: 1.2rem; font-weight: 600">
        <span>{{ $habitacionesLimpiezaGeneral }}</span>
        <i class="fa-solid fa-broom left"></i>
    </a>

    {{-- MANTENIMIENTO --}}
    <a href="{{ route('movhabitacion.filtrarEstado', ['estado' => 'mantenimiento']) }}"
       class="btn waves-effect waves-dark mantenimiento darken-1 white-text ml5 {{ ($estado ?? 'todos') == 'mantenimiento' ? 'active' : '' }}  btn-cargar-crud tooltipped"
       data-tooltip="Habitaciones en mantenimiento" style="font-size: 1.2rem; font-weight: 600">
        <span>{{ $habitacionesMantenimientoGeneral }}</span>
        <i class="fa-solid fa-screwdriver-wrench left"></i>
    </a>

                       
                            </div>

                            <div class="pisos-row">
                                {{-- Botón para todos los pisos --}}
                                <a href="{{ route('movhabitacion.filtrarPiso', ['pisoId' => 0]) }}"
                                    class="btn btn-floating {{ ($pisoId ?? '0') == '0' ? 'bg-primary' : 'bg-secondary' }} white-text tooltipped piso-boton btn-cargar-crud"
                                    data-tooltip="Todos"
                                    style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                                    T
                                </a>

                                {{-- Botones por piso --}}
                              @foreach ($Pisos as $piso)
                                <a href="{{ route('movhabitacion.filtrarPiso', ['pisoId' => $piso->id]) }}"
                                    class="btn btn-floating {{ ($pisoId ?? 'todos') == $piso->id ? 'bg-primary' : 'bg-secondary' }} white-text tooltipped piso-boton btn-cargar-crud"
                                    data-tooltip="{{ $piso->nombre }}"
                                    style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                                    
                                    {{ strtoupper(mb_substr($piso->nombre,0,1)) . substr(preg_replace('/[^0-9]/','',$piso->nombre),-1) }}
                                    
                                </a>
                            @endforeach
                            </div>

                            <div class="control-total-tools">
                                <div class="control-habitaciones-tools">
                                    <button class="btn-flat btn-grid-options tooltipped"
                                        id="btn-filtro-control-habitaciones" type="button"
                                        onclick="mostrarFiltrosControlHabitaciones(this)" data-position="top"
                                        data-tooltip="Filtros" aria-expanded="false"
                                        aria-controls="filtros-control-habitaciones">
                                        <i class="fa-solid fa-magnifying-glass-plus" style="color:#343a40;"></i>
                                    </button>
                                </div>
                                <h5 class="total">S/ {{ $totales['ventas'] }}</h5>
                            </div>
                        </div>

                        <!-- Habitaciones -->
                        <div class="col s12 m12">
                            <div class="row habitacion-column">
                                <!-- Habitaciones -->
                                @php
                                    $tarifaPorHabitacion = collect($movimientosDetalle)
                                        ->pluck('tarifa', 'habitacion_id')
                                        ->map(fn($tarifa) => $tarifa ?: ' ');
                                @endphp
                                @foreach ($Habitaciones as $habitacion)
                                    @php
                                        $reservada = $movreservahoy->flatMap->movreserva_habitacion
                                            ->filter(function ($mrh) {
                                                return ($mrh->situacion ?? '') === 'PENDIENTE';
                                            })
                                            ->pluck('habitacion_id')
                                            ->contains($habitacion->id);
                                        $movhabitacion = $movimientosDetalle->firstWhere(
                                            'habitacion_id',
                                            $habitacion->id,
                                        );

                                        // Misma apariencia verde que habitación disponible con reserva pendiente (evita fondo blanco si BD = Reservada)
                                        $claseTarjetaSituacion = strtolower(trim((string) $habitacion->situacion));
                                        $clasesValidas = ['ocupada', 'disponible', 'limpieza', 'mantenimiento'];
                                        if (! in_array($claseTarjetaSituacion, $clasesValidas, true)) {
                                            $claseTarjetaSituacion = 'disponible';
                                        }
                                        if (
                                            $reservada
                                            && $habitacion->situacion !== 'Ocupada'
                                            && $habitacion->situacion !== 'Limpieza'
                                            && $habitacion->situacion !== 'Mantenimiento'
                                        ) {
                                            $claseTarjetaSituacion = 'disponible';
                                        }

                                    @endphp

                                    <div class="col s12 m4 l3 habitacion-item"
                                        data-numero="{{ $habitacion->numero }}"
                                        data-estado="{{ $reservada ? 'reservada' : strtolower(trim($habitacion->situacion)) }}"
                                        data-piso="{{ $habitacion->piso_id }}"
                                        data-tipo="{{ $habitacion->tipohabitacion_id }}"
                                        data-busqueda="{{ $habitacion->numero }} {{ $habitacion->tipohabitacion->nombre }} {{ $clientesPorHabitacion[$habitacion->id] ?? '' }}">
                                        <div class="card custom-card card-habitacion {{ $claseTarjetaSituacion }} 
                                                    {{ $reservada ? 'habitacion-reservada' : '' }}"
                                            data-habitacion-id="{{ $habitacion->id }}"
                                            data-caja-abierta="{{ $cajaAbierta ? '1' : '0' }}"
                                            onclick="{{ !($reservada && $habitacion->situacion !== 'Ocupada') ? "abrirModal('$habitacion->situacion', $habitacion->id)" : '' }}">

                                            <div class="card-body">
                                                <div class="card-item-title">
                                                    <span>{{ $habitacion->tipohabitacion->nombre }}</span>
                                                </div>

                                                <div class="card-item-body">

                                                    <div class="habitacion-detalles d-flex flex-column">
                                                        <h5 class="font-weight-bold text-dark text-light numerohabitacion">
                                                            {{ $habitacion->numero }}
                                                        </h5>

                                                        <!-- Mostrar un aviso si está reservada -->
                                                        @if ($reservada)
                                                            <div class="ribbon-wrapper reservada">
                                                                <div class="ribbon reservada">
                                                                    <i class="fas fa-lock"></i> Reservada
                                                                </div>
                                                            </div>
                                                        @endif

                                                        <!-- Mostrar un aviso si está comentada -->
                                                        @if ($movhabitacion && $movhabitacion['comentario'])
                                                            <div class="ribbon-wrapper comentario"
                                                                data-comentario="{{ $movhabitacion['comentario'] }}">
                                                                <div class="ribbon comentario">
                                                                    <i class="fas fa-comment"></i> Comentado
                                                                </div>
                                                            </div>
                                                        @endif




                                                        <div class="habitacion-botones-cronometro">
                                                            @if ($habitacion->situacion == 'Ocupada')
                                                                <div
                                                                    class="tiempo-restante-container tr-habitacion-container">
                                                                    @foreach ($horasPorHabitacion as $horas)
                                                                        @if ($horas['habitacion_id'] == $habitacion->id)
                                                                            <div id="tiempo-restante-{{ $habitacion->id }}"
                                                                                class="tiempo-restante tr-habitacion"
                                                                                data-habitacion-id="{{ $habitacion->id }}"
                                                                                data-hora-fin="{{ \Carbon\Carbon::parse($horas['hora_fin'])->format('Y-m-d H:i:s') }}">
                                                                                <span
                                                                                    id="timer-{{ $habitacion->id }}"></span>
                                                                            </div>
                                                                        @endif
                                                                    @endforeach
                                                                </div>
                                                            @endif

                                                            <div class="operaciones-container">
                                                                @foreach ($operaciones as $operacion)
                                                                    @php
                                                                        // Verificar si es una operación que NO debe ser deshabilitada aunque esté reservada
                                                                        $esOperacionLimpieza = in_array(
                                                                            $operacion->accion,
                                                                            [
                                                                                'habitacion.limpiar',
                                                                                'habitacion.dejarlimpiar',
                                                                            ],
                                                                        );

                                                                        // Solo deshabilitar si está reservada y NO es de limpieza
                                                                        $deshabilitado =
                                                                            $reservada &&
                                                                            !$esOperacionLimpieza &&
                                                                            $habitacion->situacion != 'Ocupada'
                                                                                ? 'disabled opacity-50'
                                                                                : '';
                                                                    @endphp

                                                                    @if (
                                                                        ($operacion->accion == 'movhabitacion.checkout' ||
                                                                            $operacion->accion == 'movhabitacion.productos' ||
                                                                            $operacion->accion == 'movhabitacion.servicios') &&
                                                                            $operacion->tipo === 'Registro' &&
                                                                            $habitacion->situacion == 'Ocupada')
                                                                        <a href="{{ route($operacion->accion, $habitacion->id) }}"
                                                                            class="btn-floating {{ $operacion->color }} darken-1 tooltipped waves-light btn-cargar-crud {{ $deshabilitado }}"
                                                                            data-icono="{{ $operacion->icon_class }}"
                                                                            data-titulo="{{ $operacion->descripcion }}"
                                                                            data-tooltip="{{ $operacion->descripcion }}">
                                                                            {!! $operacion->imagen !!}
                                                                        </a>
                                                                    @elseif(
                                                                        ($operacion->accion === 'movhabitacion.checkin' ||
                                                                            $operacion->accion === 'habitacion.limpiar' ||
                                                                            $operacion->accion === 'habitacion.mantenimiento') &&
                                                                            in_array($habitacion->situacion, ['Disponible', 'Reservada'], true))
                                                                        <a href="{{ route($operacion->accion, $habitacion->id) }}"
                                                                            class="btn-floating {{ $operacion->color }} darken-1 tooltipped waves-light btn-cargar-crud {{ $deshabilitado }}"
                                                                            data-icono="{{ $operacion->icon_class }}"
                                                                            data-titulo="{{ $operacion->descripcion }}"
                                                                            data-tooltip="{{ $operacion->descripcion }}">
                                                                            {!! $operacion->imagen !!}
                                                                        </a>
                                                                    @elseif(
                                                                        ($operacion->accion === 'habitacion.mantenimiento' || $operacion->accion === 'habitacion.dejarlimpiar') &&
                                                                            ($habitacion->situacion == 'Limpieza' or $habitacion->situacion == 'Mantenimiento'))
                                                                        <a href="{{ route($operacion->accion, $habitacion->id) }}"
                                                                            class="btn-floating {{ $operacion->color }} darken-1 tooltipped waves-light btn-cargar-crud {{ $deshabilitado }}"
                                                                            data-icono="{{ $operacion->imagen }}"
                                                                            data-titulo="{{ $operacion->descripcion }}"
                                                                            data-tooltip="{{ $operacion->descripcion }}">
                                                                            {!! $operacion->imagen !!}
                                                                        </a>
                                                                    @endif
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="card-item-footer">
                                                    @if ($habitacion->situacion == 'Ocupada')
                                                        <small>{{ $tarifaPorHabitacion->get($habitacion->id, ' ') }}</small>
                                                    @else
                                                        <small>
                                                            S/
                                                            @if (isset($modalidadtarifario) &&
                                                                    $modalidadtarifario === 'Horas' &&
                                                                    $habitacion->tipohabitacion &&
                                                                    $habitacion->tipohabitacion->json_precio_horas)
                                                                @php
                                                                    $preciosHoras = json_decode(
                                                                        $habitacion->tipohabitacion->json_precio_horas,
                                                                        true,
                                                                    );
                                                                    $primerPrecio =
                                                                        $preciosHoras[0]['precio'] ??
                                                                        $habitacion->precio;
                                                                @endphp
                                                                {{ number_format($primerPrecio, 2) }}
                                                            @else
                                                                {{ number_format($habitacion->precio, 2) }}
                                                            @endif
                                                        </small>
                                                    @endif
                                                    @php
                                                        // Buscar si la habitación tiene un movimiento de ocupación
                                                        $movimiento = $movimientosDetalle->firstWhere(
                                                            'habitacion_id',
                                                            $habitacion->id,
                                                        );
                                                    @endphp
                                                    @if ($movimiento && $movimiento['situacion'] == 'P')
                                                        <i class="fas fa-check-circle text-success tooltipped estadopago"
                                                            data-tooltip="Pagado"></i>
                                                    @elseif ($movimiento && $movimiento['situacion'] == 'A')
                                                        <i class="fas fa-exclamation-circle text-warning tooltipped estadopago"
                                                            data-tooltip="Pendiente de pago"></i>
                                                    @endif

                                                    </small>

                                                    @if ($habitacion->situacion == 'Ocupada' && isset($clientesPorHabitacion[$habitacion->id]))
                                                        @php
                                                            $nombreCompleto = $clientesPorHabitacion[$habitacion->id];
                                                        @endphp
                                                        <small class="cliente-principal">
                                                            <span class="nombre-abreviado tooltipped"
                                                                data-tooltip="{{ $nombreCompleto }}"
                                                                data-position="bottom">
                                                                {{-- puedes usar bottom, left o right --}}
                                                                {{ Str::limit($nombreCompleto, 16, '..') }}
                                                            </span>
                                                        </small>
                                                    @endif

                                                    <div class="card-item-icon bed-icon"
                                                        style="margin-top: 0px; margin-right: 5px">
                                                        @if ($habitacion->situacion == 'Limpieza')
                                                            <i class="fas fa-broom maintenance tooltipped" style="color: rgb(0, 0, 0);"
                                                                data-tooltip="En Limpieza"></i>
                                                        @elseif ($habitacion->situacion == 'Mantenimiento')
                                                            <i class="fas fa-tools maintenance tooltipped"
                                                                data-tooltip="En Mantenimiento"></i>
                                                        @elseif($habitacion->situacion == 'Ocupada')
                                                        @else
                                                            <i class="fa fa-bed text-success tooltipped" data-tooltip="Disponible"></i>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>


        <!-- Modal para vista general -->
        <div id="modalVistaGeneral" class="modal">
            <div class="modal-content">
                <h5 class="center-align">Vista General de Habitaciones</h5>
                <table class="striped centered">
                    <thead class="bg-primary">
                        <tr>
                            <th>Habitación</th>
                            <th>Tipo</th>
                            <th>Piso</th>
                            <th>Precio</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tablaHabitaciones">
                        <!-- Aquí se cargarán las habitaciones dinámicamente -->
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <a href="#" class="modal-close waves-effect waves-green btn-flat">Cerrar<i
                        class="material-icons right">clear</i></a>
            </div>
        </div>

        <!-- Modal para habitación en Limpieza -->
        <div id="modalLimpieza" class="modal z-depth-4 modal-estado modal-limpieza">
            <div class="modal-content">
                <h5 class="modal-estado-title">Opciones de la Habitación - En Limpieza</h5>
                <p class="modal-estado-subtitle">Selecciona una acción para la habitación.</p>
                <div class="row modal-estado-actions">
                    <div class="col s12 m4">
                        <div href="#" class="card hoverable center-align btn-cargar-crud action-card"
                            data-icono="fas fa-times-circle" data-titulo="Dejar de Limpiar"
                            data-url-base="{{ route('habitacion.dejarlimpiar', '') }}">
                            <div class="card-content">
                                <i class="fas fa-broom large text-green"></i>
                                <h6>Habilitar Habitación</h6>
                            </div>
                        </div>
                    </div>
                    <div class="col s12 m4">
                        <div href="#" class="card hoverable center-align btn-cargar-crud action-card"
                            data-icono="fas fa-times-circle" data-titulo="Mantenimiento"
                            data-url-base="{{ route('habitacion.mantenimiento', '') }}">
                            <div class="card-content">
                                <i class="fas fa-wrench large maintenance"></i>
                                <h6>Mantenimiento</h6>
                            </div>
                        </div>
                    </div>
                    {{-- <div class="col s12 m4">
                                <div class="card hoverable center-align" style="border-radius: 10px;">
                                    <div class="card-content">
                                        <i class="material-icons large blue-text">date_range</i>
                                        <h6>Reservas</h6>
                                    </div>
                                </div>
                            </div> --}}
                </div>
            </div>
            <div class="modal-footer">
                <a href="#!" class="modal-close waves-effect waves-green btn-flat modal-estado-close">Cerrar</a>
            </div>
        </div>

        <!-- Modal para habitación en Mantenimiento -->
        <div id="modalMantenimiento" class="modal z-depth-4 modal-estado modal-mantenimiento">
            <div class="modal-content">
                <h5 class="modal-estado-title">Opciones de la Habitación - En Mantenimiento</h5>
                <p class="modal-estado-subtitle">Selecciona una acción para la habitación.</p>
                <div class="row modal-estado-actions">
                    <div class="col s12 m4">
                        <div href="#" class="card hoverable center-align btn-cargar-crud action-card"
                            data-icono="fas fa-times-circle" data-titulo="Dejar de Limpiar"
                            data-url-base="{{ route('habitacion.dejarlimpiar', '') }}">
                            <div class="card-content">
                                <i class="fas fa-broom large text-green"></i>
                                <h6>Habilitar Habitación</h6>
                            </div>
                        </div>
                    </div>
                    <div class="col s12 m4">
                        <div href="#" class="card hoverable center-align btn-cargar-crud action-card"
                            data-icono="fas fa-times-circle" data-titulo="Limpiar"
                            data-url-base="{{ route('habitacion.limpiar', '') }}">
                            <div class="card-content">
                                <i class="fas fa-broom large text-warning"></i>
                                <h6>Limpieza</h6>
                            </div>
                        </div>
                    </div>
                    {{-- <div class="col s12 m4">
                                <div class="card hoverable center-align" style="border-radius: 10px;">
                                    <div class="card-content">
                                        <i class="material-icons large blue-text">date_range</i>
                                        <h6>Reservas</h6>
                                    </div>
                                </div>
                            </div> --}}
                </div>
            </div>
            <div class="modal-footer">
                <a href="#!" class="modal-close waves-effect waves-green btn-flat modal-estado-close">Cerrar</a>
            </div>
        </div>

        <!-- Modal para habitación Disponible -->
        <div id="modalDisponible" class="modal z-depth-4 modal-estado modal-disponible">
            <div class="modal-content">
                <h5 class="modal-estado-title">Opciones de la Habitación - Disponible</h5>
                <p class="modal-estado-subtitle">Selecciona una acción para la habitación.</p>
                <div class="row modal-estado-actions">
                    <div class="col s12 m3">
                        <div href="#"class="card hoverable center-align btn-cargar-crud action-card"
                            data-icono="fas fa-check-circle" data-titulo="Check-In de Habitación"
                            data-url-base="{{ route('movhabitacion.checkin', '') }}">
                            <div class="card-content">
                                <i class="fas fa-check-circle large green-text"></i>
                                <h6>Check-In</h6>
                            </div>
                        </div>
                    </div>
                    <div class="col s12 m3">
                        <div href="#" class="card hoverable center-align btn-cargar-crud action-card"
                            data-icono="fas fa-times-circle" data-titulo="Limpiar"
                            data-url-base="{{ route('habitacion.limpiar', '') }}">
                            <div class="card-content">
                                <i class="fas fa-broom large text-warning"></i>
                                <h6>Limpieza</h6>
                            </div>
                        </div>
                    </div>
                    <div class="col s12 m3">
                        <div href="#" class="card hoverable center-align btn-cargar-crud action-card"
                            data-icono="fas fa-times-circle" data-titulo="Mantenimiento"
                            data-url-base="{{ route('habitacion.mantenimiento', '') }}">
                            <div class="card-content">
                                <i class="fas fa-wrench large maintenance"></i>
                                <h6>Mantenimiento</h6>
                            </div>
                        </div>
                    </div>
                    {{-- <div class="col s12 m3">
                                <div class="card hoverable center-align" style="border-radius: 10px;">
                                    <div class="card-content">
                                        <i class="material-icons large blue-text">date_range</i>
                                        <h6>Reservas</h6>
                                    </div>
                                </div>
                            </div> --}}
                </div>
            </div>
            <div class="modal-footer">
                <a href="#!" class="modal-close waves-effect waves-green btn-flat modal-estado-close">Cerrar</a>
            </div>
        </div>
        <!-- Modal para habitación Ocupada -->
        <div id="modalOcupada" class="modal z-depth-4 modal-ocupada">
            <div class="modal-content">
                <h5 class="modal-header-titulo modal-ocupada-title">
                    <!-- Este es el lugar donde se mostrará el número y tipo de habitación -->
                    Opciones de la Habitación - Ocupada
                </h5> <!-- Espacio para mostrar el comentario -->
                <div class="row">
                    <div class="col s12">
                        <div class="comentario-panel">
                            <i class="material-icons comentario-icon">chat_bubble_outline</i>
                            <div class="comentario-body">
                                <div class="comentario-label">Comentario</div>
                                <div id="comentario-habitacion" class="comentario-habitacion"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row modal-ocupada-actions">
                    <div class="col s12 m4">
                        <a href="#" class="card hoverable center-align btn-cargar-crud action-card action-primary"
                            data-icono="fas fa-check-circle" data-titulo="Check-Out de Habitación"
                            data-url-base="{{ route('movhabitacion.checkout', '') }}">
                            <div class="card-content">
                                <i class="fas fa-check-circle large red-text"></i>
                                <h6>Check-Out</h6>
                            </div>
                        </a>
                    </div>
                    <div class="col s12 m4">
                        <a href="" class="card hoverable center-align btn-cargar-crud action-card" data-icono="fas fa-gifts"
                            data-titulo="Productos para habitación"
                            data-url-base="{{ route('movhabitacion.productos', '') }}">
                            <div class="card-content">
                                <i class="fas fa-gifts large blue-text"></i>
                                <h6>Productos</h6>
                            </div>
                        </a>
                    </div>
                    <div class="col s12 m4">
                        <a href="" class="card hoverable center-align btn-cargar-crud action-card" data-icono="fas fa-gifts"
                            data-titulo="Servicios para habitación"
                            data-url-base="{{ route('movhabitacion.servicios', '') }}">
                            <div class="card-content">
                                <i class="material-icons large teal-text">room_service</i>
                                <h6>Servicios</h6>
                            </div>
                        </a>
                    </div>
                    <div class="col s12 m4">
                        <a href="" class="card hoverable center-align btn-cargar-crud action-card" data-icono="fas fa-eye"
                            data-titulo="Detalles de Alojamiento" data-url-base="{{ route('movhabitacion.ver', '') }}">
                            <div class="card-content">
                                <i class="material-icons large purple-text">visibility</i>
                                <h6>Ver Detalles</h6>
                            </div>

                        </a>
                    </div>
                    <div class="col s12 m4">
                        <form class="anular-form action-form" method="POST">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" class="btn-actualizar-url"
                                data-url-base="{{ route('movhabitacion.anular', '') }}">
                            <button type="submit" class="card hoverable center-align action-card action-danger" >
                                <div class="card-content">
                                    <i class="material-icons large red-text text-darken-2">delete_forever</i>
                                    <h6 style="font-weight: 600; color: #e53935;">Anular</h6>
                                </div>
                            </button>
                        </form>
                    </div>

                    <!-- Nuevo botón agregado -->
                    <div class="col s12 m4">
                        <a href="" class="card hoverable center-align btn-cargar-crud action-card" data-icono="fas fa-edit"
                            data-titulo="Editar Alojamiento" data-url-base="{{ route('movhabitacion.editCheckin', '') }}">
                            <div class="card-content">
                                <i class="fas fa-edit large amber-text text-darken-1"></i>
                                <h6>Editar</h6>
                            </div>
                        </a>
                    </div>

                </div>
            </div>
            <div class="modal-footer">
                <a href="#!" class="modal-close waves-effect waves-green btn-flat modal-ocupada-close">Cerrar</a>
            </div>
        </div>

    </div>
        <div id="anular-venta-modal" class="modal modal-large" style="z-index: 99999999; display: none;">
        <div class="modal-content">
            <div class="row without-top">
                <p class="grey-text text-darken-3 without-tb center" id="modal-title"> <i
                        class="fa-solid fa-ban left"></i>Anular venta
                </p>
            </div>

            <div class="row without-tb" id="modal-form-anular">

                <div class="row">
                    <input type="text" autocomplete="username" tabindex="-1"
                        style="position:absolute; left:-9999px; width:1px; height:1px; opacity:0;">
                    <input type="password" autocomplete="current-password" tabindex="-1"
                        style="position:absolute; left:-9999px; width:1px; height:1px; opacity:0;">
                    <div class="input-field col s12 m6">
                        <input class="submit-form" id="motivo_anulacion" name="motivo_anulacion" type="text"
                            autocomplete="off" autocapitalize="off" autocorrect="off" spellcheck="false"
                            data-lpignore="true" required></input>
                        <label for="motivo_anulacion">Motivo de la anulación <span style="color:red;">*</span></label>
                        <span class="helper-text" id="motivo_anulacion_spn">Ingrese el motivo de anulación</span>
                    </div>

                    <div class="input-group-field" style="display: flex; align-items: center; gap: 0.5rem; ">
                        <div class="right-wrapper input-field" style="flex-grow: 1; margin: 0; margin-top: 16px;">
                            <input class="submit-form" id="codigo_aprobacion_anulacion" name="codigo_aprobacion_anulacion"
                                type="password" autocomplete="new-password" autocapitalize="off" autocorrect="off"
                                spellcheck="false" data-lpignore="true" required />
                            <label id="codigo_aprobacion_anulacion_lbl" for="codigo_aprobacion_anulacion" style="left: 0;">
                                Código de aprobación <span style="color:red;">*</span>
                            </label>
                            <span class="helper-text" id="codigo_aprobacion_movimiento_spn" data-error="" data-success="">
                                Ingrese el código o envíe uno temporal
                            </span>
                        </div>

                        <button class="btn tooltipped green" id="btn-codigo-aprobacion-movimiento-whatsapp"
                            data-tooltip="Solicitar código por WhatsApp" type="button"
                            onclick="sendWhatsAppCodeForAnulacionAlojamiento();" style="height: 36px;">
                            <i class="fa-brands fa-whatsapp"></i>
                        </button>

                        <button class="btn tooltipped" id="btn-codigo-aprobacion-movimiento-email"
                            data-tooltip="Solicitar código por e-mail" type="button" onclick="sendEmailCodeForAnulacionAlojamiento();"
                            style="height: 36px;">
                            <i class="fa-solid fa-paper-plane"></i>
                        </button>
                    </div>
                </div>

            </div>
        </div>

        <div class="modal-footer">
            <button id="btn-cancel-anular" type="button"
                class="btn-flat waves-effect waves-dark btn-cancelar btn-mantenimiento grey-text text-darken-3"
                onclick="cancelAnularVentaModal();">
                CANCELAR
            </button>
            <button id="btn-save-anular" type="button"
                class="btn-flat waves-effect waves-light teal-text semibold btn-aceptar btn-mantenimiento"
                onclick="aceptarAnularVentaModal()">
                ANULAR
            </button>
        </div>
    </div>

    <div id="modal-overlay-anular"
        style="display:none; position: fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1002;">
    </div>
@endsection
