<?php
use App\Models\Menuopcione;
use App\Models\Permisousuario;
use App\Models\Persona;
use App\Models\Perfile;
use App\Models\Caja;


// Obtenemos la información del usuario autenticado y su persona asociada
$user = Auth::user();
$persona = Persona::find($user->persona_id);

// Consultamos la sucursal del usuario y luego su perfil en Perfilsucursal
$sucursalId = $persona->sucursal_id;
$perfil = Perfile::find($user->perfil_id);
$cajas = Caja::where('sucursal_id', $sucursalId);
$cajaAperurada = Caja::where('sucursal_id', $sucursalId)->first();
$cajaSituacion = $cajaAperurada?->situacion;
$cajaAbierta = $cajaSituacion === 'A';
$cajaNumero = $cajaAperurada?->numero ?? 'Sin caja';
$cajaTooltip = $cajaAperurada ? ($cajaAbierta ? 'Aperturada' : 'Cerrada') : 'Sin caja asignada';
?>
<nav class="teal darken-1">
    <div class="nav-wrapper nav-content" 
           style="display: flex; ">
        <!-- Menú hamburguesa a la izquierda -->
        <a class="sidenav-trigger show-on-large" data-target="slide-out" href="#"
            style="flex: 0 0 auto; display: flex; align-items: center; height: 100%;">
            <i class="fa-solid fa-bars"></i>
        </a>

        <!-- Nombre de la sucursal -->
        <div
            style="flex: 1 1 auto; display: flex; align-items: center; overflow: hidden; white-space: nowrap; text-overflow: ellipsis; height: 100%; margin-left: 10px;">
            <a href="{{ route('dashboard') }}" class="brand-logo truncar-text" style="margin: 0;">
                {{ $persona->sucursal->nombrecomercial }}
            </a>
        </div>

        <!-- 🔹 Accesos rápidos y usuario en PC -->
        <ul class="right hide-on-med-and-down" id="accesoRapidoOpciones"
            style="flex: 0 0 auto; display: flex; align-items: center; height: 100%;">
            @guest
                <li><a href="{{ route('login') }}">{{ __('Login') }}</a></li>
                <li><a href="{{ route('register') }}">{{ __('Register') }}</a></li>
            @else
                <!-- Opciones de usuario en PC -->
                
             
                
                <li id="keypad-cashier"
                    class="tooltipped user-option "
                    data-tooltip="{{ $cajaTooltip }}">
                    
                    <a href="my_workshift" class="btn-crear"
                    style="display:flex; align-items:center; gap:6px;">
                    
                        {{ $cajaNumero }}

                        <i class="fa-solid right
                            {{ $cajaAbierta ? 'fa-circle-check green-text' : 'fa-circle red-text' }}">
                        </i>
                    </a>
                </li>
                <li class="tooltipped user-option " data-tooltip="Tutoriales">
                    <a href="https://www.youtube.com/playlist?list=PLTwle3OwQTDvNIH9Z0CDM_dVF4ftteIwW" target="_blank"
                        class="btnPerfil">
                        <i class="fa-solid fa-circle-question"></i>
                    </a>
                </li>
                <li class="tooltipped user-option" data-tooltip="Atrás">
                    <a href="#" onclick="back();">
                        <i class="fa-solid fa-reply"></i>
                    </a>
                </li>

                <li class="tooltipped user-option" data-tooltip="Mi perfil">
                    <a id="userProfileBtn" class="btnPerfil" href="#">
                        <i class="fas fa-user-circle user-icon"></i>
                        <span class="user-name">{{ strtoupper(Auth::user()->name) }}</span>
                    </a>
                </li>
            @endguest
        </ul>
    </div>
</nav>

<ul id="slide-out" class="sidenav">
    <li class="center-align">
        <img src="{{ asset('img/Logo_HotelHub_tr.png') }}" alt="Logo" style="max-width: 100%; height: auto;">
    </li>

    @guest
        <li><a href="{{ route('login') }}" class="white-text">{{ __('Login') }}</a></li>
        <li><a href="{{ route('register') }}" class="white-text">{{ __('Register') }}</a></li>
    @else
        <li id="sidenav-subheader">
            <a class="subheader">{{ $perfil->descripcion }}</a>
        </li>

        {{-- Opciones dinámicas de menú --}}
        <ul id="menuOpciones"></ul>

        {{-- 🔹 Accesos rápidos en mobile --}}
        <li class="divider hide-on-large-only"></li>
        <li id="sidenav-subheader" class="hide-on-large-only">
            <a class="subheader">Accesos rápidos</a>
        </li>
        <ul id="accesoRapidoMobile" class="hide-on-large-only"></ul>

        {{-- 🔹 Opciones de usuario en mobile --}}
        <li class="divider hide-on-large-only"></li>
        <li id="sidenav-subheader" class="hide-on-large-only">
            <a class="subheader">Opciones de usuario</a>
        </li>
        <ul id="usuarioMobile" class="hide-on-large-only"></ul>

    @endguest

    @php
        $esLimpiador = $persona->rolpersonas->contains(
            fn($rolPersona) => $rolPersona->rol->descripcion === 'Limpiador',
        );
        $logoutRoute = $esLimpiador ? route('housekeeping.logout', $persona->sucursal->ruc) : route('logout');
    @endphp
    <input type="hidden" id="logoutRoute" value="{{ $logoutRoute }}">
    <input type="hidden" id="csrfToken" value="{{ csrf_token() }}">

</ul>


<!-- Dropdown (si se usa en el futuro) -->
<ul id="dropdown1" class="dropdown-content"></ul>



<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        document.querySelectorAll(".btn-acceder-modulo").forEach(button => {
            button.addEventListener("click", function(event) {
                event.preventDefault();

                const seccionId = this.getAttribute("data-seccion-id");
                const tituloSeccion = this.getAttribute("data-modulo");
                const href = this.getAttribute("href");

                if (href === "#") {
                    mostrarMensajeError("Acceso restringido",
                        "Para acceder a este módulo comunícate con el equipo de soporte.");
                    return;
                }

                if (!seccionId) return;

                fetch(`/api/secciones/${seccionId}/opciones-menu`)
                    .then(response => response.json())
                    .then(data => {

                        if (data.success) {
                            if (!Array.isArray(data.opciones)) {
                                console.error("Error: 'opciones' no es un array", data
                                    .opciones);
                                return;
                            }

                            actualizarMenuDinamico(data.opciones);
                            actualizarAccesoRapido(data.accesoRapido ||
                        []); // Si no hay acceso rápido, pasa un array vacío
                            mostrarMensajeExito(`Bienvenido al módulo "${tituloSeccion}"`);

                        } else {
                            console.error("Error al cargar opciones de menú");
                        }
                    })
                    .catch(error => console.error("Error en la petición:", error));

            });
        });

        function actualizarMenuDinamico(opciones) {
            const menuContainer = document.getElementById("menuOpciones");

            if (!menuContainer) {
                console.warn("No se encontró el contenedor del menú.");
                return;
            }

            menuContainer.innerHTML = "";

            if (opciones.length === 0) {
                menuContainer.innerHTML = `<li><a href="#">No hay opciones disponibles</a></li>`;
                return;
            }

            let menuHTML = "";

            opciones.forEach(modulo => {
                menuHTML += `
                <li class="no-padding sidebar-item modulo-id-${modulo.modulo_id}">
                    <ul class="collapsible collapsible-accordion">
                        <li>
                            <a class="collapsible-header">${modulo.modulo_descripcion} ${modulo.modulo_icono}</a>
                            <div class="collapsible-body">
                                <ul>
            `;

                modulo.tipos.forEach(tipo => {
                    menuHTML += `
                    <li class="no-padding">
                        <ul class="collapsible collapsible-accordion">
                            <li>
                                <a class="collapsible-header">
                                    ${tipo.tipoopcion_descripcion}
                                    <i class="material-icons iconoBarra">arrow_drop_down</i>
                                </a>
                                <div class="collapsible-body">
                                    <ul>
                `;

                    tipo.opciones.forEach(opcion => {
                        menuHTML += `
                        <li>
                            <a href="${opcion.url}" class="btn-cargar-crud" data-icono="${opcion.icono}" data-titulo="${opcion.descripcion}">
                                <i class="${opcion.icono}"></i> ${opcion.descripcion}
                            </a>
                        </li>
                    `;
                    });

                    menuHTML += `
                                    </ul>
                                </div>
                            </li>
                        </ul>
                    </li>
                `;
                });

                menuHTML += `
                                </ul>
                            </div>
                        </li>
                    </ul>
                </li>
            `;
            });

            menuContainer.innerHTML = menuHTML;

            const elems = document.querySelectorAll(".collapsible");
            M.Collapsible.init(elems);
        }

        function actualizarAccesoRapido(opcionesAccesoRapido) {
            const accesoRapidoPC = document.getElementById("accesoRapidoOpciones");
            const accesoRapidoMobile = document.getElementById("accesoRapidoMobile");

            if (!accesoRapidoPC && !accesoRapidoMobile) return;
            // 🔹 Guardar en window para usar en el resize
            window.ultimoAccesoRapido = opcionesAccesoRapido;
            // Limpiar anteriores
            if (accesoRapidoPC) accesoRapidoPC.querySelectorAll(".accesrapid").forEach(el => el.remove());
            if (accesoRapidoMobile) accesoRapidoMobile.innerHTML = "";

            if (!opcionesAccesoRapido || opcionesAccesoRapido.length === 0) return;

            opcionesAccesoRapido.forEach(opcion => {
                // PC
                const listItemPC = document.createElement("li");
                listItemPC.className = "hide-on-med-and-down tooltipped accesrapid";
                listItemPC.setAttribute("data-tooltip", opcion.descripcion);
                listItemPC.innerHTML = `
                <a href="${opcion.url}" class="btn-cargar-crud" data-icono="${opcion.icono}" data-titulo="${opcion.descripcion}">
                    <i class="${opcion.icono}"></i>
                </a>`;

                // Móvil con estilo collapsible igual al original
                const listItemMobile = document.createElement("li");
                listItemMobile.className = "no-padding sidebar-item accesrapid-item";
                listItemMobile.innerHTML = `
                <ul class="collapsible collapsible-accordion">
                    <li>
                        <a href="${opcion.url}" class="collapsible-header btn-cargar-crud" data-icono="${opcion.icono}" data-titulo="${opcion.descripcion}">
                            <i class="${opcion.icono} iconoBarra"></i> ${opcion.descripcion}
                        </a>
                    </li>
                </ul>`;

                if (window.matchMedia("(min-width: 1141px)").matches) {
                    // 🔹 Insertar SIEMPRE antes del indicador de caja
                    const cajaBtn = accesoRapidoPC.querySelector('#keypad-cashier');

                    if (cajaBtn) {
                        accesoRapidoPC.insertBefore(listItemPC, cajaBtn);
                    } else {
                        accesoRapidoPC.appendChild(listItemPC);
                    }


                } else {
                    accesoRapidoMobile.appendChild(listItemMobile);
                }
            });

            if (accesoRapidoPC) M.Tooltip.init(document.querySelectorAll(".tooltipped"));
        }

        function actualizarOpcionesUsuario() {
            const usuarioMobile = document.getElementById("usuarioMobile");
            if (!usuarioMobile) return;

            usuarioMobile.innerHTML = "";

            if (window.matchMedia("(max-width: 1140px)").matches) {
                // 🔹 Definir manualmente las opciones de usuario para móvil
                const opcionesUsuario = [{
                        href: "https://www.youtube.com/playlist?list=PLTwle3OwQTDvNIH9Z0CDM_dVF4ftteIwW",
                        icon: "fa-solid fa-circle-question iconoBarra",
                        texto: "Tutoriales",
                        target: "_blank",
                        onclick: null,
                        id: null
                    },
                    {
                        href: "#",
                        icon: "fa-solid fa-reply iconoBarra",
                        texto: "Atrás",
                        target: null,
                        onclick: "back();",
                        id: null
                    },
                    {
                        href: "#",
                        icon: "fas fa-user-circle iconoBarra",
                        texto: document.querySelector("#userProfileBtn .user-name")?.textContent ||
                            "Usuario",
                        target: null,
                        onclick: null,
                        id: "userProfileBtnMovil"
                    }
                ];

                opcionesUsuario.forEach(opcion => {
                    const listItemMobile = document.createElement("li");
                    listItemMobile.className = "no-padding sidebar-item";
                    listItemMobile.innerHTML = `
        <ul class="collapsible collapsible-accordion">
            <li>
                <a ${opcion.id ? `id="${opcion.id}"` : ""} href="${opcion.href}" class="collapsible-header btnPerfil" ${opcion.target ? `target="${opcion.target}"` : ""} ${opcion.onclick ? `onclick="${opcion.onclick}"` : ""}>
                    <i class="${opcion.icon}"></i> ${opcion.texto}
                </a>
            </li>
        </ul>
    `;
                    usuarioMobile.appendChild(listItemMobile);
                });

                // 🔹 Agregar "Cerrar sesión"
                const logoutRoute = document.getElementById("logoutRoute").value;
                const csrfToken = document.getElementById("csrfToken").value;

                const logoutItem = document.createElement("li");
                logoutItem.className = "pb100";
                logoutItem.innerHTML = `
    <a href="${logoutRoute}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
        <i class="fa-solid fa-right-from-bracket left"></i> Cerrar sesión
    </a>
    <form id="logout-form" action="${logoutRoute}" method="POST" style="display: none;">
        <input type="hidden" name="_token" value="${csrfToken}">
    </form>
`;

                usuarioMobile.appendChild(logoutItem);

            }
        }


        window.addEventListener("resize", () => {
            actualizarAccesoRapido(window.ultimoAccesoRapido || []);
            actualizarOpcionesUsuario();
        });

        // Inicializar al cargar
        actualizarOpcionesUsuario();

        function mostrarMensajeExito(mensaje) {
            const swalInstance = Swal.fire({
                icon: 'success',
                title: 'Éxito',
                text: mensaje,
                position: 'bottom-left', // Ubicación en la esquina inferior izquierda
                showConfirmButton: false, // No mostrar botón de confirmación
                timer: 4000, // Duración de la alerta en milisegundos
                toast: true, // Activar modo de toast para que sea flotante
                timerProgressBar: true, // Mostrar la barra de progreso
                customClass: {
                    popup: 'rounded-popup', // Aplicar clase personalizada para el popup
                },
                didOpen: (swalInstance) => {
                    // Eliminar todos los select generados dentro del popup
                    const selects = swalInstance.querySelectorAll('select');
                    selects.forEach(select => {
                        select.remove(); // Elimina el select
                    });

                    // Pausar y reanudar el temporizador cuando el mouse esté encima o fuera de la alerta
                    swalInstance.onmouseenter = Swal.stopTimer;
                    swalInstance.onmouseleave = Swal.resumeTimer;
                }
            });
        }

        function mostrarMensajeError(titulo, mensaje) {
            let mensajeError = document.getElementById("mensaje-error");

            if (mensajeError) {
                mensajeError.classList.remove("hide");
                mensajeError.style.display = "flex";

                document.getElementById("mensaje-error-titulo").innerText = titulo;
                document.getElementById("mensaje-error-texto").innerText = mensaje;

                setTimeout(function() {
                    mensajeError.style.display = "none";
                }, 5000);
            }
        }
    });
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('#userProfileBtnMovil');
        if (!btn) return;

        e.preventDefault();
        e.stopPropagation(); // 🔹 Evita que el collapsible capture el click

        Swal.fire({
            icon: 'info',
            title: 'ADMIN',
            html: `
        <ul>
            <li><span style="font-weight: bold;">Documento:</span> {{ $persona->personamaestro?->nrodoc ?? 'No disponible' }}</li>
            <li><span style="font-weight: bold;">Nombres:</span> {{ $persona->nombres ?? 'No disponible' }} {{ $persona->apellidos ?? '' }}</li>
            <li><span style="font-weight: bold;">Dirección:</span> {{ $persona->direccion ?? 'No disponible' }}</li>
            <li><span style="font-weight: bold;">E-mail:</span> {{ $persona->email ?? 'No tiene email' }}</li>
        </ul>
    `,

            showCancelButton: true,
            cancelButtonText: 'Cerrar sesión', // Botón para cerrar sesión
            confirmButtonText: 'Mi perfil',
            denyButtonText: 'Cambiar contraseña',
            showDenyButton: true,
            customClass: {
                popup: 'custom-swal-popup',
                backdrop: 'custom-backdrop',
            },
            showCloseButton: true,
            closeButtonHtml: '&times;',
            backdrop: true,
        }).then((result) => {
            if (result.isConfirmed) {
                const personaId = "{{ Auth::user()->persona_id }}";
                const editUrl = `/personaedit/${personaId}/EDITARPERFIL`;
                const tempLink = document.createElement('a');
                tempLink.href = editUrl;
                tempLink.className = 'btn-crear';
                document.body.appendChild(tempLink);
                tempLink.click();
                document.body.removeChild(tempLink);
            } else if (result.isDenied) {
                const resetPasswordUrl = `/restablecerPasswordUsuarioLogueado`;
                const tempLink = document.createElement('a');
                tempLink.href = resetPasswordUrl;
                tempLink.className = 'btn-crear';
                document.body.appendChild(tempLink);
                tempLink.click();
                document.body.removeChild(tempLink);
            } else if (result.dismiss === Swal.DismissReason.cancel) {
                document.getElementById('logout-form').submit();
            }
        });
    });



    document.getElementById('userProfileBtn').addEventListener('click', function(event) {
        event.preventDefault(); // Evita que el enlace redirija
        Swal.fire({
            icon: 'info',
            title: 'ADMIN',
            html: `
        <ul>
            <li><span style="font-weight: bold;">Documento:</span> {{ $persona->personamaestro?->nrodoc ?? 'No disponible' }}</li>
            <li><span style="font-weight: bold;">Nombres:</span> {{ $persona->nombres ?? 'No disponible' }} {{ $persona->apellidos ?? '' }}</li>
            <li><span style="font-weight: bold;">Dirección:</span> {{ $persona->direccion ?? 'No disponible' }}</li>
            <li><span style="font-weight: bold;">E-mail:</span> {{ $persona->email ?? 'No tiene email' }}</li>
        </ul>
        `,
            position: 'top-end',
            showCancelButton: true,
            cancelButtonText: 'Cerrar sesión',
            confirmButtonText: 'Mi perfil',
            denyButtonText: 'Cambiar contraseña',
            showDenyButton: true,
            customClass: {
                popup: 'custom-swal-popup',
                backdrop: 'custom-backdrop',
            },
            showCloseButton: true,
            closeButtonHtml: '&times;',
            backdrop: true,
        }).then((result) => {
            if (result.isConfirmed) {
                const personaId = "{{ Auth::user()->persona_id }}";
                const editUrl = `/personaedit/${personaId}/EDITARPERFIL`;

                const tempLink = document.createElement('a');
                tempLink.href = editUrl;
                tempLink.className = 'btn-crear';
                document.body.appendChild(tempLink);
                tempLink.click();
                document.body.removeChild(tempLink);

            } else if (result.isDenied) {
                const resetPasswordUrl = `/restablecerPasswordUsuarioLogueado`;

                const tempLink = document.createElement('a');
                tempLink.href = resetPasswordUrl;
                tempLink.className = 'btn-crear';
                document.body.appendChild(tempLink);
                tempLink.click();
                document.body.removeChild(tempLink);

            } else if (result.dismiss === Swal.DismissReason.cancel) {
                // 🔹 Logout con fetch
                const logoutRoute = document.getElementById('logoutRoute').value;
                const csrfToken = document.getElementById('csrfToken').value;

                fetch(logoutRoute, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({}),
                    })
                    .then(response => {
                        if (response.ok) {
                            window.location.href = '/'; // redirigir al home o login
                        } else {
                            Swal.fire('Error', 'No se pudo cerrar sesión', 'error');
                        }
                    })
                    .catch(() => {
                        Swal.fire('Error', 'Ocurrió un problema al cerrar sesión', 'error');
                    });
            }
        });
    });

    // Asignar un solo evento para todos los enlaces con la clase 'btn-cargar-crud'
    $(document).on('click', '.remove-medio-pago', function(event) {
        event.preventDefault();
        const medioId = $(this).closest('tr').data('medio-id');

        // Eliminar el ID del arreglo
        medioPagoSeleccionados = medioPagoSeleccionados.filter(id => id !== medioId);
        actualizarCampoMedioPagoIds();
        // Eliminar la fila de la tabla
        $(this).closest('tr').remove();
    });

    document.addEventListener('DOMContentLoaded', function() {
        const compuestoCheckbox = document.getElementById('compuesto');
        const autoproduccionCheckbox = document.getElementById('autoproduccion');

        // Verificar si los elementos existen antes de agregar los listeners
        if (compuestoCheckbox && autoproduccionCheckbox) {
            // Escuchar cambios en el checkbox de 'compuesto'
            compuestoCheckbox.addEventListener('change', function() {
                if (compuestoCheckbox.checked) {
                    autoproduccionCheckbox.disabled = false;
                    console.log('Compuesto activado, autoproducción habilitada');
                } else {
                    autoproduccionCheckbox.disabled = true;
                    autoproduccionCheckbox.checked = false;
                    console.log('Compuesto desactivado, autoproducción deshabilitada');
                }
            });
        } else {}

    });
    document.addEventListener('DOMContentLoaded', function() {
        if ($('.select2').length) {
            $('.select2').select2();
        }

        if ($('.clientes-select2').length) {
            $('.clientes-select2').select2({
                theme: "classic",
                width: '100%',
            });
        }

        if ($('.habitacion-select2').length) {
            $('.habitacion-select2').select2({
                theme: "classic",
                width: '100%',
            });
        }
    });
</script>
