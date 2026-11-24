// Al loguearse, llamas esta función


let codigoGenerado = null;

// Filtro AJAX para Huesped
function filterHuesped() {
    const tipoPersona = document.getElementById('tipopersona').value;
    const genero = document.getElementById('genero').value;
    const noGrato = document.getElementById('no_grato') ? document.getElementById('no_grato').value : 'Todos';
    const searchQuery = document.getElementById('search-input').value.trim();
    const recordsPerPage = document.getElementById('records-per-page').value;

    mostrarPantallaDeCarga();

    // Usar la URL del index de huéspedes
    const url = `/huesped?tipopersona=${tipoPersona}&genero=${genero}&no_grato=${noGrato}&search=${encodeURIComponent(searchQuery)}&perPage=${recordsPerPage}`;

    fetch(url, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
        .then(response => {
            if (!response.ok) throw new Error('Error al obtener datos.');
            return response.text();
        })
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            document.querySelector('#tb-container').innerHTML = doc.querySelector('#tb-container').innerHTML;
            M.AutoInit();
            reinicializarDataTables();
            ocultarPantallaDeCarga();
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarMensajeError('Error al filtrar huéspedes');
            ocultarPantallaDeCarga();
        });
}
// Variable para almacenar el formulario que se desea eliminar
let formToAnular;
document.addEventListener('DOMContentLoaded', function () {
    localStorage.setItem('paginaActual', 1);

    // Inicializar Materialize
    M.AutoInit();

    inicializarEventosDeMovimiento();


    // Delegar el evento de clic para buscar DNI
    document.addEventListener('click', function (event) {

        // ♻️ Rehabilitar scroll al cerrar modales
        if (event.target.closest('.btn-flat, .modal-close, .modal-overlay')) {
            setTimeout(() => restaurarScrollPagina({ mantenerPosicion: true }), 50);
        }


        // 🔍 Buscar DNI
        const buscarDNIButton = event.target.closest('#buscarDNI');
        if (buscarDNIButton) {
            event.preventDefault(); // Evitar la acción predeterminada

            const dni = document.getElementById('nrodoc').value;
            if (dni) {
                buscarDNIButton.disabled = true;

                consultaDNI(dni).finally(() => {
                    buscarDNIButton.disabled = false;
                });
            } else {
                mostrarMensajeError('Por favor ingrese un DNI válido.');
            }
        }

        // 🔍 Buscar RUC
        const buscarRucButton = event.target.closest('#buscarRuc');
        if (buscarRucButton) {
            event.preventDefault(); // Evitar la acción predeterminada

            const ruc = document.getElementById('nrodoc').value;
            if (ruc) {
                buscarRucButton.disabled = true;

                consultaRUC(ruc).finally(() => {
                    buscarRucButton.disabled = false;
                });
            } else {
                mostrarMensajeError('Por favor ingrese un RUC válido.');
            }
        }

    });

    // Recalcular total de check-in mientras se escribe tarifa grupal
    document.addEventListener('input', function (event) {
        if (event.target && event.target.classList.contains('tarifa-input-grupal')) {
            calcularTotalCheckin();
        }
    });

    function consultaDNI(dni) {
        return fetch(`/api/document-lookup/dni?dni=${encodeURIComponent(dni)}`)
            .then(response => response.json())
            .then(data => {
                // Verifica si existe un código de error en la respuesta
                if (data?.mensaje && data?.code) {
                    M.toast({
                        html: data.mensaje,
                        classes: 'red darken-1 rounded'
                    });
                    return;
                }

                // Si los campos están presentes, los asigna
                const nombreCompleto = data.nombres || '';
                const apellidoCompleto = `${data.apepat || ''} ${data.apemat || ''}`.trim();
                const fechanac = data.fecnac || '';

                document.getElementById('nombres').value = nombreCompleto;
                document.getElementById('apellidos').value = apellidoCompleto;
                document.getElementById('fechanac').value = fechanac;

                M.updateTextFields();
            })
            .catch(error => {
                console.error("Error al obtener datos: ", error);
                mostrarMensajeError('Error al obtener datos del DNI');
            });
    }





    // Delegar el evento de cambio del select de perfil en el documento
    document.addEventListener('change', function (e) {
        if (e.target && e.target.id === 'perfil_id') {
            const perfilId = e.target.value;
            const sucursalId = e.target.getAttribute('data-sucursal-id'); // Obtener sucursal_id del atributo data
            const opcionMenuSelect = document.getElementById('opcionmenu_id');

            if (!opcionMenuSelect) {
                console.error('El select de opciones de menú no se encontró en el DOM.');
                return;
            }

            if (perfilId && sucursalId) {
                mostrarPantallaDeCarga();

                // Incluir sucursalId en la URL de la solicitud fetch
                fetch(`/get-opciones-menu/${perfilId}/${sucursalId}`)
                    .then(response => response.json())
                    .then(data => {
                        opcionMenuSelect.innerHTML = '<option>Seleccionar opción</option>';
                        if (Array.isArray(data)) {
                            data.forEach(opcion => {
                                const option = document.createElement('option');
                                option.value = opcion.id;

                                const tipoDescripcion = opcion.tipoopcion_descripcion || 'Sin Tipo';
                                option.textContent = `${tipoDescripcion} - ${opcion.descripcion}`;

                                opcionMenuSelect.appendChild(option);
                            });
                        } else {
                            console.error('La respuesta no es un array:', data);
                        }

                        M.FormSelect.init(opcionMenuSelect); // Inicializar nuevamente el select de Materialize
                    })
                    .catch(error => {
                        console.error('Error al obtener opciones de menú:', error);
                    })
                    .finally(() => {
                        ocultarPantallaDeCarga(); // Ocultar pantalla de carga
                    });
            }
        }
    });
    document.addEventListener('keydown', function (e) {
        const searchInput = document.getElementById('search-input');

        if (e.key === 'Enter') {
            e.preventDefault(); // Evita el comportamiento por defecto del Enter

            if (e.target.classList.contains('autocomplete')) {
                const autocompleteInstance = M.Autocomplete.getInstance(e.target);
                if (autocompleteInstance && autocompleteInstance.isOpen) {
                    // Si el menú de autocompletar está abierto, selecciona el primer elemento
                    const autocompleteDropdown = document.querySelector('.autocomplete-content');
                    if (autocompleteDropdown) {
                        const firstOption = autocompleteDropdown.querySelector('li');
                        if (firstOption) {
                            const selectedText = firstOption.textContent.trim(); // Obtiene el texto del primer elemento
                            e.target.value = selectedText; // Establece el valor en el input

                            // Ejecuta manualmente la función onAutocomplete
                            if (autocompleteInstance.options.onAutocomplete) {
                                autocompleteInstance.options.onAutocomplete(selectedText);
                            }

                            document.getElementById("producto_autocomplete").value = "";


                        }
                    }
                } else {
                    // Si no hay opciones abiertas, ejecutamos la búsqueda
                    selectFastAutocompleteOption(e.target);
                }
            }
        }
        if (e.target === searchInput && e.key === 'Enter') {
            e.preventDefault(); // Evita el comportamiento por defecto del Enter si es necesario
            realizarBusqueda();
        }
    });

    function selectFastAutocompleteOption($element) {
        const value = $element.value.toLowerCase();
        if (value === '') return false;

        const autocomplete = M.Autocomplete.getInstance($element);
        if (!autocomplete || !autocomplete.options.data) return false;

        const items = autocomplete.options.data;
        const matchingData = [];
        let count = 0;

        // Búsqueda de coincidencias
        for (const id in items) {
            if (Object.hasOwnProperty.call(items, id)) {
                const text = id.toLowerCase();
                const words = value.split(/\s/).filter(word => word !== '');
                let coincidence = words.length > 0;
                for (const word of words) {
                    if (text.indexOf(word) === -1) {
                        coincidence = false;
                        break;
                    }
                }

                if (coincidence) {
                    if (count >= autocomplete.options.limit) {
                        break;
                    }
                    matchingData.push(id);
                    count++;
                }
            }
        }

        if (autocomplete.options.sortFunction) {
            const sortFunctionBound = function (a, b) {
                return autocomplete.options.sortFunction(a.toLowerCase(), b.toLowerCase(), value);
            };
            matchingData.sort(sortFunctionBound);
        }

        // Si hay resultados de búsqueda
        if (matchingData.length > 0) {
            const id = matchingData[0]; // Tomamos el primer resultado
            $element.value = id; // Establecemos el valor del input
            autocomplete.close(); // Cerramos el autocompletado

            // Ejecuta manualmente la función onAutocomplete
            if (autocomplete.options.onAutocomplete) {
                autocomplete.options.onAutocomplete(id);
            }
            document.getElementById("producto_autocomplete").value = "";


            return false;
        }
    }



    // Delegar el evento de cambio del select en el documento
    document.addEventListener('change', function (e) {
        if (e.target && e.target.id === 'departamento') {
            const departamentoId = e.target.value;
            const provinciaSelect = document.getElementById('provincia');
            const distritoSelect = document.getElementById('distrito');

            if (!provinciaSelect || !distritoSelect) {
                console.error('El select de provincias o distritos no se encontró en el DOM.');
                return;
            }

            if (!departamentoId) {
                return;
            }

            mostrarPantallaDeCarga();

            fetch(`/get-provincias/${departamentoId}`)
                .then(response => response.json())
                .then(data => {
                    provinciaSelect.innerHTML = '';

                    data.forEach(provincia => {
                        const option = document.createElement('option');
                        option.value = provincia.id;
                        option.textContent = provincia.descripcion;
                        provinciaSelect.appendChild(option);
                    });

                    M.FormSelect.init(provinciaSelect);

                    if (provinciaSelect.options.length > 0) {
                        provinciaSelect.selectedIndex = 0;
                        const primeraProvinciaId = provinciaSelect.options[provinciaSelect.selectedIndex].value;
                        cargarDistritos(primeraProvinciaId);
                    }
                })
                .catch(error => {
                    console.error('Error al obtener provincias:', error);
                })
                .finally(() => {
                    ocultarPantallaDeCarga();
                });
        }

        if (e.target && e.target.id === 'provincia') {
            const provinciaId = e.target.value;
            cargarDistritos(provinciaId);
        }
    });

    // Función para cargar distritos dinámicamente
    function cargarDistritos(provinciaId) {
        const distritoSelect = document.getElementById('distrito');

        if (!distritoSelect) {
            console.error('El select de distrito no se encontró en el DOM.');
            return;
        }

        if (!provinciaId) {
            return;
        }

        mostrarPantallaDeCarga();

        fetch(`/get-distritos/${provinciaId}`)
            .then(response => response.json())
            .then(data => {
                distritoSelect.innerHTML = '';

                data.forEach(distrito => {
                    const option = document.createElement('option');
                    option.value = distrito.id;
                    option.textContent = distrito.descripcion;
                    distritoSelect.appendChild(option);
                });

                M.FormSelect.init(distritoSelect);
            })
            .catch(error => {
                console.error('Error al obtener distritos:', error);
            })
            .finally(() => {
                ocultarPantallaDeCarga();
            });
    }


    // Variable para almacenar el formulario que se desea eliminar
    let formToDelete;
    // Manejar el clic del botón de eliminación
    $(document).on('submit', '.delete-form', function (event) {
        event.preventDefault(); // Prevenir el envío automático

        // Obtener la URL de acción del formulario
        let formAction = $(this).attr('action');

        // Verificar si el formulario es de eliminación
        if ($(this).find('input[name="_method"]').val() === 'DELETE') {
            formToDelete = $(this); // Almacenar el formulario actual que se desea eliminar

            // Abrir el modal de confirmación
            var modal = M.Modal.getInstance(document.getElementById('modal-confirm-delete'));
            modal.open();
        } else {
            this.submit();
        }
    });

    $(document).on('submit', '.form-accion-contrato', function (event) {
        event.preventDefault();

        const form = $(this);
        const actionUrl = form.attr('action');
        const formData = form.serializeArray();

        // Leer mensajes desde atributos data-*
        const titulo = form.data('titulo') || '¿Confirmar acción?';
        const texto = form.data('texto') || '¿Deseas continuar?';
        const confirmar = form.data('confirmar') || 'Sí, continuar';
        const exito = form.data('exito') || '¡Éxito!';

        Swal.fire({
            title: titulo,
            text: texto,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#fe6055',
            cancelButtonColor: '#6e7881',
            confirmButtonText: confirmar,
            cancelButtonText: 'Cancelar',
            customClass: {
                popup: 'rounded-swal'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: actionUrl,
                    method: 'POST',
                    data: form.serialize(),
                    dataType: 'json',
                    success: function (response) {
                        if (response.success) {
                            Swal.fire({
                                title: exito,
                                text: response.message,
                                icon: 'success',
                                customClass: {
                                    popup: 'rounded-swal'
                                }
                            });
                            if (response.redirect_url) {
                                actualizarTabla(response.redirect_url);
                            }
                        } else {
                            Swal.fire({
                                title: 'Error',
                                text: response.message || 'Ocurrió un error.',
                                icon: 'error',
                                customClass: {
                                    popup: 'rounded-swal'
                                }
                            });
                        }
                    },
                    error: function (xhr) {
                        let errorMsg = 'Error al procesar la solicitud';
                        if (xhr.responseJSON?.message) {
                            errorMsg = xhr.responseJSON.message;
                        }
                        Swal.fire({
                            title: 'Error',
                            text: errorMsg,
                            icon: 'error',
                            customClass: {
                                popup: 'rounded-swal'
                            }
                        });
                    }
                });
            }
        });
    });

    let formToDeletePiso;

    // Manejar el clic del botón de eliminación
    $(document).on('submit', '.delete-form-piso', function (event) {
        event.preventDefault(); // Prevenir el envío automático

        // Obtener la URL de acción del formulario
        let formAction = $(this).attr('action');

        // Verificar si el formulario es de eliminación
        if ($(this).find('input[name="_method"]').val() === 'DELETE') {
            formToDeletePiso = $(this); // Almacenar el formulario actual que se desea eliminar

            // Abrir el modal de confirmación
            var modal = M.Modal.getInstance(document.getElementById('modal-confirm-delete-piso'));
            modal.open();
        } else {
            this.submit();
        }
    });



    // Manejar el clic del botón de eliminación
    $(document).on('submit', '.limpiar-form', function (event) {
        event.preventDefault(); // Prevenir el envío automático

        // Obtener la URL de acción del formulario
        let formAction = $(this).attr('action');

        // Verificar si el formulario es de eliminación
        if ($(this).find('input[name="_method"]').val() === 'DELETE') {
            formToDelete = $(this); // Almacenar el formulario actual que se desea eliminar

            // Abrir el modal de confirmación
            var modal = M.Modal.getInstance(document.getElementById('modal-confirm-delete'));
            modal.open();
        } else {
            this.submit();
        }
    });

    let formToFinalizar;

    // Manejar el clic del botón de eliminación
    $(document).on('submit', '.finalizar-form', function (event) {
        event.preventDefault(); // Prevenir el envío automático


        // Verificar si el formulario es de eliminación
        if ($(this).find('input[name="_method"]').val() === 'GET') {
            formToFinalizar = $(this); // Almacenar el formulario actual que se desea eliminar

            // Abrir el modal de confirmación
            var modal = M.Modal.getInstance(document.getElementById('modal-confirm-finalizar'));
            modal.open();
        } else {
            this.submit();
        }
    });


    // Manejar el clic del formulario de anulación
    $(document).on('submit', '.anular-form', function (event) {
        event.preventDefault(); // Prevenir el envío automático

        // Verificar si el formulario es de eliminación
        if ($(this).find('input[name="_method"]').val() === 'DELETE') {
            formToAnular = $(this); // Almacenar el formulario actual que se desea anular

            var modalEl = document.getElementById('modal-confirm-anular');
            if (modalEl) {
                var modal = M.Modal.getInstance(modalEl);
                if (!modal) {
                    modal = M.Modal.init(modalEl);
                }
                if (modal) {
                    modal.open();
                    return;
                }
            }
            openAnularVentaModal();
        } else {
            this.submit();
        }
    });



    // Confirmar eliminación cuando se presiona el botón de "Eliminar" en el modal
    $('#confirm-delete-btn-piso').on('click', function () {
        if (formToDeletePiso) {
            // Obtener la URL y el método del formulario almacenado
            let url = formToDeletePiso.attr('action');
            let method = formToDeletePiso.find('input[name="_method"]').val() ||
                'POST'; // Usar POST por defecto

            // Enviar la solicitud AJAX de eliminación
            $.ajax({
                url: url,
                type: method,
                data: formToDeletePiso.serialize(),
                success: function (response) {
                    if (response.success) {
                        // Mostrar un mensaje de éxito elegante
                        M.toast({
                            html: '<i class="material-icons left">check_circle</i> ' +
                                response.message,
                            classes: 'red darken-1 rounded',
                            displayLength: 4000 // Duración del toast en milisegundos
                        });
                        // Actualizar la tabla
                        actualizarTabla(response.redirect_url);
                    } else {
                        // Mostrar un mensaje de error
                        M.toast({
                            html: '<i class="material-icons left">error</i> Error al eliminar',
                            classes: 'red darken-1 rounded',
                            displayLength: 4000
                        });
                    }
                },
                error: function (xhr) {
                    // Mostrar un mensaje de error
                    M.toast({
                        html: '<i class="material-icons left">error</i> Error al eliminar',
                        classes: 'red darken-1 rounded',
                        displayLength: 4000
                    });
                }
            });
        } else {
            // Manejar el caso en el que formToDelete no esté definido
            M.toast({
                html: 'No se encontró el formulario para eliminar',
                classes: 'red darken-1'
            });
        }
    });

    // Confirmar eliminación cuando se presiona el botón de "Eliminar" en el modal
    $('#confirm-delete-btn').on('click', function () {
        if (formToDelete) {
            // Obtener la URL y el método del formulario almacenado
            let url = formToDelete.attr('action');
            let method = formToDelete.find('input[name="_method"]').val() ||
                'POST'; // Usar POST por defecto

            // Enviar la solicitud AJAX de eliminación
            $.ajax({
                url: url,
                type: method,
                data: formToDelete.serialize(),
                success: function (response) {
                    if (response.success) {
                        // Mostrar un mensaje de éxito elegante
                        M.toast({
                            html: '<i class="material-icons left">check_circle</i> ' +
                                response.message,
                            classes: 'red darken-1 rounded',
                            displayLength: 4000 // Duración del toast en milisegundos
                        });
                        // Actualizar la tabla
                        actualizarTabla(response.redirect_url);
                    } else {
                        // Mostrar un mensaje de error
                        M.toast({
                            html: '<i class="material-icons left">error</i> Error al eliminar',
                            classes: 'red darken-1 rounded',
                            displayLength: 4000
                        });
                    }
                },
                error: function (xhr) {
                    // Mostrar un mensaje de error
                    M.toast({
                        html: '<i class="material-icons left">error</i> Error al eliminar',
                        classes: 'red darken-1 rounded',
                        displayLength: 4000
                    });
                }
            });
        } else {
            // Manejar el caso en el que formToDelete no esté definido
            M.toast({
                html: 'No se encontró el formulario para eliminar',
                classes: 'red darken-1'
            });
        }
    });

    // Confirmar eliminación cuando se presiona el botón de "Anular" en el modal de confirmación
    $('#confirm-anular-btn').on('click', function (e) {
        e.preventDefault();

        if (!formToAnular) {
            mostrarMensajeError('No se encontró el formulario para anular');
            return;
        }

        // Cerrar correctamente la instancia del modal de confirmación
        var confirmModalEl = document.getElementById('modal-confirm-anular');
        if (confirmModalEl) {
            var confirmInst = M.Modal.getInstance(confirmModalEl);
            if (confirmInst) {
                confirmInst.close();
            }
        }

        // Cerrar todos los modales abiertos
        $('.modal.open').each(function () {
            var inst = M.Modal.getInstance(this);
            if (inst) {
                inst.close();
            } else {
                $(this).removeClass('open').css({
                    display: 'none',
                    opacity: 0,
                    top: '0',
                    transform: 'scaleX(0.7) scaleY(0.7)'
                });
            }
        });

        // Limpiar cualquier overlay residual de Materialize para evitar pantalla oscura bloqueada
        $('.modal-overlay').remove();

        // Abrir el modal de anulación
        openAnularVentaModal();
    });


    // Confirmar eliminación cuando se presiona el botón de "Eliminar" en el modal
    $('#confirm-finalizar-btn').on('click', function () {
        if (formToFinalizar) {
            // Obtener la URL y el método del formulario almacenado
            let url = formToFinalizar.attr('action');
            let method = formToFinalizar.find('input[name="_method"]').val() ||
                'POST'; // Usar POST por defecto

            // Enviar la solicitud AJAX de eliminación
            $.ajax({
                url: url,
                type: method,
                data: formToFinalizar.serialize(),
                success: function (response) {
                    if (response.success) {
                        // Mostrar un mensaje de éxito elegante
                        M.toast({
                            html: '<i class="material-icons left">check_circle</i> ' +
                                response.message,
                            classes: 'red darken-1 rounded',
                            displayLength: 4000 // Duración del toast en milisegundos
                        });
                        // Actualizar la tabla
                        actualizarTabla(response.redirect_url);
                    } else {
                        // Mostrar un mensaje de error
                        M.toast({
                            html: '<i class="material-icons left">error</i> Error al eliminar',
                            classes: 'red darken-1 rounded',
                            displayLength: 4000
                        });
                    }
                },
                error: function (xhr) {
                    // Mostrar un mensaje de error
                    M.toast({
                        html: '<i class="material-icons left">error</i> Error al eliminar',
                        classes: 'red darken-1 rounded',
                        displayLength: 4000
                    });
                }
            });
        } else {
            // Manejar el caso en el que formToDelete no esté definido
            M.toast({
                html: 'No se encontró el formulario para eliminar',
                classes: 'red darken-1'
            });
        }
    });



    // Inicializar los modales y eventos al cargar la página
    inicializarModal();

    // Inicializar la paginación AJAX al cargar la página
    handlePagination();
    // Llama a reinicializarDataTables después de cargar el contenido dinámico o al inicializar la página
    reinicializarDataTables();
    if (esUrlIndiceHabitacion(window.location.pathname)) {
        inicializarFiltrosHabitacion();
    }
    if (esUrlListadoHabitaciones(window.location.pathname)) {
        incializarTemporizador();
    }
    function handlePagination() {
        // Primero eliminar cualquier evento anterior asociado con la paginación
        $(document).off('click', '.pagination a'); // Esto asegura que no se dupliquen los eventos

        // Ahora registrar el evento de nuevo
        $(document).on('click', '.pagination a', function (event) {
            event.preventDefault();
            let url = $(this).attr('href');

            // Extraer el número de página de la URL
            let urlParams = new URLSearchParams(new URL(url).search);
            let paginaActual = urlParams.get('page');

            // Guardar el número de página en localStorage
            if (paginaActual) {
                localStorage.setItem('paginaActual', paginaActual);
            }

            // Mostrar pantalla de carga
            mostrarPantallaDeCarga();

            // Hacer la solicitud AJAX para la nueva página
            $.ajax({
                url: url,
                type: 'GET',
                success: function (response) {
                    // Reemplazar solo el contenedor de la tabla y la paginación
                    $('#tb-container').html($(response).find('#tb-container').html());

                    // Reinicializar componentes
                    reinicializarDataTables(); // Llamar a la función para reinicializar la tabla
                    ocultarPantallaDeCarga();
                },
                error: function (xhr) {
                    console.error('Error al cargar la nueva página:', xhr);
                    ocultarPantallaDeCarga();
                }
            });
        });
    }


    $(document).on('click', '.btn-cargar-crud', function (event) {
        event.preventDefault();

        // Las acciones de una habitación requieren una caja abierta.
        // Se valida aquí para cubrir también los botones internos de cada tarjeta.
        const habitacionCard = $(this).closest('.card-habitacion[data-caja-abierta]');
        if (habitacionCard.length && habitacionCard.attr('data-caja-abierta') === '0') {
            event.stopImmediatePropagation();
            mostrarAlertaCajaCerrada();
            return;
        }

        if ($(this).closest('#slide-out').length > 0) {
            cerrarMenuLateral();
        }

        let url = $(this).attr('href');
        let titulo = $(this).data('titulo');
        let icono = $(this).data('icono');

        // Verifica si la ruta está excluida
        if (esRutaExcluida(url)) {
            cargarContenidoDinamicamente(url);
            return;
        }

        let vieneDeMenuLateral = $(this).closest('#slide-out').length > 0;
        let vieneDeAccesoRapido = $(this).closest('.nav-wrapper').length > 0;

        // Si viene del menú lateral o de los accesos rápidos, reiniciar breadcrumb dejando solo "Inicio" y el nuevo título
        if (vieneDeAccesoRapido) {
            breadcrumbStack = [
                { title: 'Inicio', url: '/', icono: 'fa-solid fa-home left' },
                { title: titulo, url: url, icono: icono }
            ];
        }

        if (vieneDeMenuLateral) {
            breadcrumbStack = [{ title: 'Inicio', url: '/', icono: 'fa-solid fa-home left' }];
        }

        const existsInStack = breadcrumbStack.some(item => item.title === titulo);

        if (!existsInStack && titulo && icono) {
            let currentElement = $(this);
            const stackTemp = [];

            while (currentElement.length) {
                let parentHeader = currentElement.closest('.collapsible-body').prev('.collapsible-header');

                if (!parentHeader.length) {
                    parentHeader = currentElement.closest('.collapsible-accordion').find('> .collapsible-header');
                }

                if (parentHeader.length) {
                    const breadcrumbTitle = parentHeader.contents().filter(function () {
                        return this.nodeType === 3;
                    }).text().trim();

                    const breadcrumbIcon = parentHeader.find('i:first').attr('class') || '';

                    stackTemp.unshift({ title: breadcrumbTitle, url: '#', icono: breadcrumbIcon });
                }

                currentElement = parentHeader;
            }

            // Agregar el nuevo breadcrumb al stack
            breadcrumbStack = [...breadcrumbStack, ...stackTemp, { title: titulo, url: url, icono: icono }];

            // Si viene del menú lateral, asignar la URL del último a todos excepto al primero
            if (vieneDeMenuLateral) {
                const lastUrl = breadcrumbStack[breadcrumbStack.length - 1].url;
                breadcrumbStack = breadcrumbStack.map((item, index) => ({
                    ...item,
                    url: index === 0 ? '/' : lastUrl
                }));
            }
        }

        // Intentar cargar el contenido dinámicamente
        cargarContenidoDinamicamente(url)
            .then(() => {
                if (icono && titulo) {
                    actualizarCabecera(icono, titulo, url);
                }
            })
            .catch(error => {
                console.error('No se pudo cargar el contenido dinámicamente:', error);

                // Si es el caso en que el backend devolvió JSON con success:false
                if (error && error.type === 'server-json' && error.data) {
                    // Mostrar el mensaje que envió el servidor si existe
                    const mensaje = error.data.errors || error.data.message || 'Ocurrió un problema en el servidor';
                    mostrarMensajeError(mensaje);
                    return;
                }

                // Si vino un objeto con type server-json-success (JSON válido pero no HTML)
                if (error && error.type === 'server-json-success' && error.data) {
                    const mensaje = error.data.message || 'Respuesta del servidor';
                    // Maneja según lo necesites (redirigir, mostrar mensaje, etc.)
                    mostrarMensajeError(mensaje);
                    return;
                }

                // Si sigue siendo el caso clásico (login requerido)
                if (error && error.message === "Cannot read properties of null (reading 'innerHTML')") {
                    mostrarModalLogin();
                    return;
                }

                // Si es un objeto Response rechazado (por ejemplo error HTTP capturado)
                if (error instanceof Response) {
                    // Intentar obtener JSON para mensaje
                    error.json?.().then(ed => {
                        mostrarMensajeError(ed.errors || ed.message || 'Error en la petición');
                    }).catch(() => {
                        mostrarMensajeError('Ocurrió un problema inesperado. Consulta a soporte');
                    });
                    return;
                }

                // Fallback genérico
                mostrarMensajeError('Ocurrió un problema inesperado. Consulta a soporte');
            });


        actualizarBreadcrumb();

    });

    function decodeBase64Utf8(value) {
        if (!value) {
            return '';
        }

        try {
            return decodeURIComponent(Array.prototype.map.call(atob(value), function (char) {
                return '%' + ('00' + char.charCodeAt(0).toString(16)).slice(-2);
            }).join(''));
        } catch (error) {
            try {
                return atob(value);
            } catch (_) {
                return value;
            }
        }
    }

    $(document).on('click', '.btn-ver-respuesta-sunat', function (event) {
        event.preventDefault();

        const tipo = $(this).data('tipo') || '-';
        const numero = $(this).data('numero') || '-';
        const nombre = $(this).data('nombre') || '-';
        const respuestaEncoded = $(this).attr('data-respuesta') || '';
        const respuesta = decodeBase64Utf8(respuestaEncoded).trim() || 'Sin respuesta registrada.';

        $('#sunat-respuesta-tipo').text(tipo);
        $('#sunat-respuesta-numero').text(numero);
        $('#sunat-respuesta-nombre').text(nombre);
        $('#sunat-respuesta-texto').text(respuesta);

        const modalElement = document.getElementById('modal-respuesta-sunat');
        if (!modalElement) {
            return;
        }

        let instance = M.Modal.getInstance(modalElement);
        if (!instance) {
            instance = M.Modal.init(modalElement);
        }
        instance.open();
    });


    $(document).on('click', '.btn-retroceder', function (event) {
        event.preventDefault();

        // Lógica para el formulario de retroceso
        if (breadcrumbStack.length > 1) {
            breadcrumbStack.pop();
            const previousCrumb = breadcrumbStack[breadcrumbStack.length - 1];
            actualizarBreadcrumb();

            // Opcional: Cargar contenido dinámico del nivel anterior
            cargarContenidoDinamicamente(previousCrumb.url);
            actualizarCabecera(previousCrumb.icono, previousCrumb.title, previousCrumb.url);

        } else {
            mostrarMensajeError('No hay más niveles para retroceder.');
        }
    });


    function esRutaExcluida(url) {
        try {
            const parsedUrl = new URL(url, window.location.origin);
            const pathname = parsedUrl.pathname;
            // Patrón para /habitacion/dejar-limpiar/{id} y /habitacion/limpiar/{id}, donde {id} es un número
            const regex = /^\/habitacion\/(dejar-limpiar|limpiar)\/\d+$/;
            return regex.test(pathname);
        } catch (e) {
            console.error('URL inválida:', url);
            return false;
        }
    }

    $(document).on('click', '.btn-activar', function (event) {
        event.preventDefault();

        let url = $(this).attr('href'); // Obtener URL del botón
        let paginaActual = localStorage.getItem('paginaActual') || 1; // Página actual almacenada

        // Agregar parámetro de paginación si es necesario
        url += url.includes('?') ? `&page=${paginaActual}` : `?page=${paginaActual}`;

        activarContenido(url, paginaActual);
    });

    function activarContenido(url, paginaActual) {
        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(response => {
                if (!response.ok) throw response;
                return response.json();
            })
            .then(data => {
                if (data.batch_result) {
                    return mostrarResultadoReenvio(data).then(() => {
                        if (data.redirect_url) {
                            return cargarContenidoDinamicamente(`${data.redirect_url}?page=${paginaActual}`);
                        }
                    });
                }

                if (data.success) {
                    // Redirecciona y pasa el número de página
                    if (data.redirect_url) {
                        cargarContenidoDinamicamente(`${data.redirect_url}?page=${paginaActual}`);
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                mostrarMensajeError(error);
                forzarOcultarPantallaDeCarga();
            });
    }

    function mostrarResultadoReenvio(data) {
        const escaparHtml = (valor) => $('<div>').text(valor ?? '').html();
        const reenviadas = Array.isArray(data.reenviadas) ? data.reenviadas : [];
        const noReenviadas = Array.isArray(data.no_reenviadas) ? data.no_reenviadas : [];
        const resumen = data.summary || {};
        const iconos = {
            success: 'success',
            partial: 'warning',
            error: 'error',
            empty: 'info'
        };
        const titulos = {
            success: 'Reenvío completado',
            partial: 'Reenvío completado parcialmente',
            error: 'No se pudieron reenviar las facturas',
            empty: 'Sin facturas pendientes'
        };

        const construirLista = (titulo, elementos, color, textoVacio) => {
            const filas = elementos.length
                ? elementos.map(item => `
                    <div style="padding:9px 10px;border-bottom:1px solid #eceff1;text-align:left;">
                        <strong style="display:block;color:${color};">${escaparHtml(item.numero)}</strong>
                        <span style="font-size:13px;color:#546e7a;">${escaparHtml(item.mensaje)}</span>
                    </div>
                `).join('')
                : `<div style="padding:12px;color:#90a4ae;text-align:left;">${escaparHtml(textoVacio)}</div>`;

            return `
                <div style="margin-top:14px;border:1px solid #e0e0e0;border-radius:8px;overflow:hidden;">
                    <div style="padding:9px 10px;background:#f5f7f8;text-align:left;font-weight:700;color:${color};">
                        ${escaparHtml(titulo)} (${elementos.length})
                    </div>
                    <div style="max-height:190px;overflow-y:auto;">${filas}</div>
                </div>
            `;
        };

        const html = `
            <p style="margin:0 0 12px;color:#455a64;">${escaparHtml(data.message)}</p>
            <div style="display:flex;justify-content:center;gap:10px;flex-wrap:wrap;">
                <span style="padding:7px 12px;border-radius:16px;background:#e8f5e9;color:#2e7d32;">
                    Reenviadas: <strong>${Number(resumen.reenviadas || 0)}</strong>
                </span>
                <span style="padding:7px 12px;border-radius:16px;background:#ffebee;color:#c62828;">
                    No reenviadas: <strong>${Number(resumen.no_reenviadas || 0)}</strong>
                </span>
                <span style="padding:7px 12px;border-radius:16px;background:#eceff1;color:#455a64;">
                    Total: <strong>${Number(resumen.total || 0)}</strong>
                </span>
            </div>
            ${construirLista('Facturas reenviadas', reenviadas, '#2e7d32', 'No se reenvió ninguna factura.')}
            ${construirLista('Facturas no reenviadas', noReenviadas, '#c62828', 'No hubo errores.')}
        `;

        return Swal.fire({
            icon: iconos[data.status] || 'info',
            title: titulos[data.status] || 'Resultado del reenvío',
            html,
            width: 720,
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#009688',
            allowOutsideClick: false
        });
    }



});








function eliminarPersona(context) {
    const prefix = typeof context === 'string' && context.trim() ? context.trim() : null;
    const inputId = prefix ? `${prefix}-persona` : 'persona_autocomplete';
    const hiddenId = prefix ? `${prefix}-persona_id` : 'persona_id';
    const btnQuitarId = prefix ? `btn-${prefix}-quitar-persona` : 'btnQuitarCliente';
    const btnNuevaId = prefix ? null : 'btnNuevaPersona';

    // Restablecer el campo de autocompletado
    const personaAutocomplete = document.getElementById(inputId);
    const personaIdInput = document.getElementById(hiddenId);
    const btnQuitarCliente = document.getElementById(btnQuitarId);
    const btnNuevaPersona = btnNuevaId ? document.getElementById(btnNuevaId) : null;

    if (personaAutocomplete) {
        personaAutocomplete.value = '';
        personaAutocomplete.removeAttribute('disabled');
    }
    if (personaIdInput) {
        personaIdInput.value = '';
    }

    // Ocultar el boton "Quitar Cliente"
    if (btnQuitarCliente) {
        btnQuitarCliente.style.display = 'none';
    }

    // Mostrar el boton "Nueva Persona"
    if (btnNuevaPersona) {
        btnNuevaPersona.style.display = 'inline-block';
    }

    if (personaAutocomplete || personaIdInput) {
        // Mostrar un mensaje de confirmacion (opcional)
        M.toast({
            html: 'Seleccion de persona eliminada.',
            classes: 'blue darken-1 rounded'
        });
    }
}

// Función para eliminar una fila y su detalle en el array
function eliminarProducto(button, productoId) {
    const row = button.parentNode.parentNode;
    row.parentNode.removeChild(row);

    // Actualizar el array eliminando el producto correspondiente
    detallesProductos = detallesProductos.filter(producto => producto.producto_id !== productoId);

    // Actualizar el campo oculto con el array modificado
    document.getElementById('detalles_productos_json').value = JSON.stringify(detallesProductos);
}

// Función para ajustar la cantidad
function ajustarCantidad(incremento) {
    // Obtener el campo de cantidad en el modal
    const cantidadInput = document.getElementById('cantidad_producto');

    // Obtener el valor actual de la cantidad y sumarle el incremento
    let cantidadActual = parseInt(cantidadInput.value, 10) || 0; // Valor actual, con un fallback a 0
    cantidadActual += incremento;

    // Asegurarse de que la cantidad no sea menor a 1
    if (cantidadActual < 1) {
        cantidadActual = 1;
    }

    // Actualizar el valor del campo de cantidad en el modal
    cantidadInput.value = cantidadActual;
}


function prepararCampoVencimientoDeuda() {
    const tipoSelect = document.getElementById('tipo_detalle');
    if (!tipoSelect) return;

    let container = document.getElementById('fechavencimiento_detalle_container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'fechavencimiento_detalle_container';
        container.className = 'input-field col s12 m2';
        container.style.display = 'none';
        container.innerHTML = `
            <input type="date" id="fechavencimiento_detalle" name="fechavencimiento_detalle">
            <label for="fechavencimiento_detalle" class="active">F. venc.</label>
        `;

        const tipoContainer = tipoSelect.closest('.input-field');
        tipoContainer?.insertAdjacentElement('afterend', container);
    }

    if (!tipoSelect.dataset.vencimientoListener) {
        tipoSelect.addEventListener('change', actualizarCampoVencimientoDeuda);
        tipoSelect.dataset.vencimientoListener = '1';
    }
}

function actualizarCampoVencimientoDeuda() {
    const tipoSelect = document.getElementById('tipo_detalle');
    const container = document.getElementById('fechavencimiento_detalle_container');
    const input = document.getElementById('fechavencimiento_detalle');
    if (!tipoSelect || !container || !input) return;

    const esDeuda = tipoSelect.value === 'Deuda';
    container.style.display = esDeuda ? '' : 'none';
    input.required = esDeuda;
    if (!esDeuda) {
        input.value = '';
    }
    M.updateTextFields();
}

function getFechaVencimientoDetalle() {
    return document.getElementById('fechavencimiento_detalle')?.value || '';
}

function setFechaVencimientoDetalle(value) {
    const input = document.getElementById('fechavencimiento_detalle');
    if (!input) return;

    input.value = normalizarFechaInputDate(value);
}

function normalizarFechaInputDate(value) {
    if (!value || value === '-') return '';
    const text = String(value).trim();
    const isoMatch = text.match(/^(\d{4}-\d{2}-\d{2})/);
    if (isoMatch) return isoMatch[1];

    const localMatch = text.match(/^(\d{2})\/(\d{2})\/(\d{4})/);
    if (localMatch) {
        return `${localMatch[3]}-${localMatch[2]}-${localMatch[1]}`;
    }

    return '';
}

document.addEventListener('change', function (event) {
    if (event.target?.id === 'tipo_detalle') {
        prepararCampoVencimientoDeuda();
        actualizarCampoVencimientoDeuda();
    }
});

function agregarMovimiento() {
    // Limpiar los campos del modal
    document.getElementById('tipo_detalle').value = 'Pagado'; // Valor por defecto
    document.getElementById('monto_detalle').value = '0.00';  // Valor por defecto
    document.getElementById('medio_pago_detalle').value = '1'; // Asigna '1' (suponiendo que es 'Efectivo')
    document.getElementById('notas_detalle').value = '';      // Limpia las notas
    prepararCampoVencimientoDeuda();
    setFechaVencimientoDetalle('');
    actualizarCampoVencimientoDeuda();

    // Obtener el select de medio de pago
    const medioPagoSelect = document.getElementById('medio_pago_detalle');

    // Actualizar Materialize inputs y selects
    M.updateTextFields(); // Actualiza los campos de texto
    M.FormSelect.init(document.querySelectorAll('select')); // Actualiza los selects

    // Forzar la visibilidad correcta de los campos (mostrar solo lo que corresponde a 'Efectivo')
    mostrarSelects(medioPagoSelect);

    // Abrir el modal de Materialize
    const modal = M.Modal.getInstance(document.getElementById('detalleModal'));
    modal.open();
}
let productoEditandoId = null;
let filaEditandoElement = null;
let glosaTemporalId = -1;

function editarDetalle(row, event) {
    const clickedCell = event.target.closest('td');
    const clickedColumnIndex = Array.from(row.cells).indexOf(clickedCell);

    // Evitar acción si se hizo clic en la última celda (acciones)
    if (clickedColumnIndex === row.cells.length - 1) return;

    const productoId = row.getAttribute('data-producto-id');
    productoEditandoId = productoId;
    filaEditandoElement = row;


    // Extraer valores desde las celdas
    const descripcion = row.cells[0].innerText.trim();
    const precioCompra = parseFloat(row.cells[1].innerText.replace(',', '')).toFixed(2);
    const unidadTexto = row.cells[2].innerText.trim(); // ← Aquí tomamos la unidad visible
    const cantidad = row.cells[3].innerText.trim();
    const igvTexto = row.cells[4].innerText.trim(); // ← Aquí tomamos el IGV visible
    const precioUnitario = parseFloat(row.cells[5].innerText.replace(',', '')).toFixed(2);
    const total = parseFloat(row.cells[6].innerText.replace(',', '')).toFixed(2);

    const codigo = row.cells[7].innerText.trim();
    const estadoPago = row.cells[8]?.innerText.trim() ?? '';

    // Set inputs
    document.getElementById('producto_id').value = productoId;
    document.getElementById('codigo').value = codigo;
    document.getElementById('descripcion').value = descripcion;
    document.getElementById('cantidad').value = cantidad;
    // Mostrar el campo si está oculto
    document.getElementById('divDet_movventa_preciocompra').style.display = 'block';

    // Luego asignar el valor
    document.getElementById('det_movventa_preciocompra').value = precioCompra;

    document.getElementById('det_movventa_monto').value = total;

    // Set unidad seleccionada por texto
    const unidadSelect = document.getElementById('unidad_id');
    for (let option of unidadSelect.options) {
        if (option.text.trim() === unidadTexto) {
            unidadSelect.value = option.value;
            break;
        }
    }

    // Set IGV seleccionada por id (preferente) o por texto
    const igvSelect = document.getElementById('igv_id');
    const igvIdFila = row.getAttribute('data-igv-id');
    let igvAsignado = false;

    if (igvIdFila) {
        for (let option of igvSelect.options) {
            if (String(option.dataset.igvId) === String(igvIdFila)) {
                igvSelect.value = option.value;
                igvAsignado = true;
                break;
            }
        }
    }

    if (!igvAsignado) {
        const porcentajeFila = row.getAttribute('data-igv-porcentaje');
        if (porcentajeFila !== null && porcentajeFila !== '') {
            for (let option of igvSelect.options) {
                if (parseFloat(option.value) === parseFloat(porcentajeFila)) {
                    igvSelect.value = option.value;
                    igvAsignado = true;
                    break;
                }
            }
        }
    }

    if (!igvAsignado) {
        for (let option of igvSelect.options) {
            if (option.text.trim() === igvTexto) {
                igvSelect.value = option.value;
                break;
            }
        }
    }

    // Actualiza campos visuales de Materialize
    M.updateTextFields();
    reinicializarSelects();

    // Set modal title dinámico
    document.getElementById('det_movventa-submodal-title').innerHTML =
        `<i class="fa-solid fa-bag-shopping"></i> ${codigo} - ${descripcion}`;

    // Abre el modal
    const modal = M.Modal.getInstance(document.getElementById('det_movventa-maintenance-submodal'));
    modal.open();
}

function esFormularioMovventa(form) {
    const actionUrl =
        form?.getAttribute('action') ||
        form?.querySelector('#form-action-url')?.value ||
        document.getElementById('form-action-url')?.value ||
        '';

    return /\/movventa(\/|$)/.test(actionUrl);
}

function esFormularioMovcompra(form) {
    const actionUrl =
        form?.getAttribute('action') ||
        form?.querySelector('#form-action-url')?.value ||
        document.getElementById('form-action-url')?.value ||
        '';

    return /\/movcompra(\/|$)/.test(actionUrl) || !!form?.classList?.contains('form-movcompra');
}

document.addEventListener('click', function (event) {
    const submitter = event.target.closest('button, input[type="submit"], input[type="image"]');

    if (!submitter || !submitter.form) return;

    const tipo = (submitter.getAttribute('type') || 'submit').toLowerCase();
    if (submitter.tagName === 'BUTTON' && tipo !== 'submit') return;

    const form = submitter.form;
    const esFormularioDinamico = form.classList.contains('form-retroceder') || !!form.querySelector('#form-action-url');
    if (!esFormularioDinamico) return;

    form.__submitterDinamico = submitter;

    const onclick = submitter.getAttribute('onclick') || '';
    const accionMatch = onclick.match(/handleAction\(['"]([^'"]+)['"]\)/);
    const accion = submitter.dataset?.accion ||
        (submitter.id === 'btn-cobrar' ? 'cobrar' : null) ||
        (submitter.id === 'checkout-button' ? 'checkout' : null) ||
        (submitter.classList.contains('cobrar_checkout') ? 'cobrar_checkout' : null) ||
        accionMatch?.[1];

    if (accion) {
        handleAction(accion, form);
    }
}, true);

document.addEventListener('submit', function (event) {
    const form = event.target;

    if (!(form instanceof HTMLFormElement)) return;

    const esFormularioDinamico = form.classList.contains('form-retroceder') || !!form.querySelector('#form-action-url');
    if (!esFormularioDinamico) return;

    event.preventDefault();

    const token = `${Date.now()}-${Math.random()}`;
    form.dataset.submitGuardToken = token;
    form.__submitterDinamico = event.submitter || form.__submitterDinamico || document.activeElement;

    setTimeout(function () {
        if (form.dataset.submitHandledToken === token || !document.body.contains(form)) return;

        form.dataset.submitHandledToken = token;

        if (form.dataset.ajaxSubmitting === '1') return;
        form.dataset.ajaxSubmitting = '1';

        const submitter = form.__submitterDinamico instanceof HTMLElement ? form.__submitterDinamico : null;
        const onclick = submitter?.getAttribute('onclick') || '';
        const accionMatch = onclick.match(/handleAction\(['"]([^'"]+)['"]\)/);
        const accion = submitter?.dataset?.accion ||
            (submitter?.id === 'btn-cobrar' ? 'cobrar' : null) ||
            (submitter?.id === 'checkout-button' ? 'checkout' : null) ||
            (submitter?.classList?.contains('cobrar_checkout') ? 'cobrar_checkout' : null) ||
            accionMatch?.[1];

        if (accion) {
            handleAction(accion, form);
        }

        try {
            if (window.tinymce && typeof window.tinymce.triggerSave === 'function') {
                window.tinymce.triggerSave();
            }
        } catch (e) {
            // TinyMCE puede no estar cargado en todas las vistas.
        }

        if (esFormularioMovventa(form)) {
            sincronizarDetallesJsonMovVenta(form);
            if (!validarDescripcionGlosaMovventa(form)) {
                form.dataset.ajaxSubmitting = '0';
                ocultarPantallaDeCarga();
                return;
            }
        }

        if (esFormularioMovcompra(form) && !prepararEnvioCompra(event)) {
            form.dataset.ajaxSubmitting = '0';
            ocultarPantallaDeCarga();
            return;
        }

        const detallesMovimientosJsonField = form.querySelector('#detalles_movimientos_json') || document.getElementById('detalles_movimientos_json');
        if (detallesMovimientosJsonField && typeof actualizarDetallesMovimientos === 'function') {
            actualizarDetallesMovimientos();
            detallesMovimientosJsonField.value = JSON.stringify(detallesMovimientos);
        }

        let actionUrl = form.getAttribute('action') || form.querySelector('#form-action-url')?.value || document.getElementById('form-action-url')?.value;
        const accionInput = form.querySelector('#accion');

        if (actionUrl && actionUrl.includes('accion') && accionInput?.value) {
            actionUrl = actionUrl.replace('accion', accionInput.value);
            form.action = actionUrl;
        }

        if (!actionUrl) {
            form.dataset.ajaxSubmitting = '0';
            ocultarPantallaDeCarga();
            mostrarMensajeError('No se encontr&oacute; la URL para enviar el formulario.');
            return;
        }

        mostrarPantallaDeCarga();

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const headers = { 'Accept': 'application/json' };
        if (csrfToken) {
            headers['X-CSRF-TOKEN'] = csrfToken;
        }

        fetch(actionUrl, {
            method: (form.getAttribute('method') || 'POST').toUpperCase(),
            body: new FormData(form),
            headers: headers
        })
            .then(response => {
                if (!response.ok) throw response;
                return response.json();
            })
            .then(data => {
                if (!data.success) {
                    mostrarMensajeError(data.message || 'No se pudo completar la operaci&oacute;n.');
                    return null;
                }

                if (data.message) {
                    mostrarMensajeExito(data.message);
                }

                if (data.pdf_url) {
                    window.open(data.pdf_url, '_blank');
                }

                if (submitter?.classList?.contains('form-cobrar') && data.habitacion_id) {
                    const checkoutUrl = `/movhabitacion/checkout/${data.habitacion_id}`;
                    actualizarCabecera("fa-solid fa-cash-register", "Checkout", checkoutUrl);
                    return cargarContenidoDinamicamente(checkoutUrl);
                }

                if (data.redirect_url) {
                    if (esUrlListadoHabitaciones(data.redirect_url)) {
                        restaurarBreadcrumbHabitaciones();
                    } else if (esUrlMovcajaIndex(data.redirect_url)) {
                        restaurarBreadcrumbMovcaja();
                    }
                    return cargarContenidoDinamicamente(data.redirect_url);
                }

                return null;
            })
            .catch(async error => {
                let mensajeError = 'Ha ocurrido un error inesperado.';
                let redirectUrl = '';

                if (error instanceof Response) {
                    try {
                        const errorData = await error.json();
                        mensajeError = errorData.errors || errorData.message || mensajeError;
                        redirectUrl = errorData.redirect_url || '';
                    } catch (e) {
                        console.error('No se pudo parsear el error JSON:', e);
                    }
                }

                mostrarMensajeError(mensajeError);

                if (redirectUrl) {
                    return cargarContenidoDinamicamente(redirectUrl);
                }

                return null;
            })
            .finally(() => {
                form.dataset.ajaxSubmitting = '0';
                ocultarPantallaDeCarga();
            });
    }, 0);
}, true);

function obtenerFilasDetalleMovVenta(scope = document) {
    return Array.from(scope.querySelectorAll('#data-table tbody tr[data-producto-id]'))
        .filter(fila => !fila.classList.contains('child'));
}

function sincronizarDetallesJsonMovVenta(scope = document) {
    const detallesJsonField = scope.querySelector('#detalles_json');

    if (!detallesJsonField) return;

    const filas = obtenerFilasDetalleMovVenta(scope);

    let detallesPrevios = [];
    try {
        const parsed = JSON.parse(detallesJsonField.value || '[]');
        detallesPrevios = Array.isArray(parsed) ? parsed : [];
    } catch (e) {
        detallesPrevios = [];
    }

    const detalles = filas.map((fila, index) => {
        const celdas = fila.cells;
        const productoIdFila = parseInt(fila.getAttribute('data-producto-id') || '0');
        const descripcion = celdas[0]?.innerText.trim() || '';

        const detallePrevio =
            detallesPrevios.find(det =>
                parseInt(det?.producto_id || '0') === productoIdFila &&
                (det?.descripcion || '').trim() === descripcion
            ) ||
            detallesPrevios.find(det => (det?.codigo || '').trim() === (celdas[7]?.innerText.trim() || '')) ||
            detallesPrevios[index] ||
            {};

        const igvIdFila = parseInt(
            fila.getAttribute('data-igv-id') || detallePrevio.igv_id || '0',
            10
        );
        const estadoPagoTexto = celdas[8]?.innerText || '';

        return {
            producto_id: productoIdFila > 0 ? productoIdFila : null,
            situacion: estadoPagoTexto.includes('Pagado') ? 'N' : 'P',
            descripcion: descripcion,
            precio_compra: parseFloat((celdas[1]?.innerText || '').replace(/,/g, '')) || parseFloat(detallePrevio.precio_compra || '0') || 0,
            unidad: celdas[2]?.innerText.trim() || detallePrevio.unidad || '',
            cantidad: parseFloat((fila.querySelector('.cantidad')?.innerText || '').replace(/,/g, '')) || parseFloat(detallePrevio.cantidad || '0') || 0,
            igv: parseFloat((fila.querySelector('.igv')?.innerText || '').replace(/,/g, '')) || parseFloat(detallePrevio.igv || '0') || 0,
            igv_id: !isNaN(igvIdFila) && igvIdFila > 0 ? igvIdFila : null,
            precio_unitario: parseFloat((fila.querySelector('.precio-unitario')?.innerText || '').replace(/,/g, '')) || parseFloat(detallePrevio.precio_unitario || '0') || 0,
            total: parseFloat((fila.querySelector('.precio-total')?.innerText || '').replace(/,/g, '')) || parseFloat(detallePrevio.total || '0') || 0,
            codigo: celdas[7]?.innerText.trim() || detallePrevio.codigo || '',
            categoria: detallePrevio.categoria ?? 'Sin Categoría',
            opc_categoria: detallePrevio.opc_categoria ?? {},
            notas: detallePrevio.notas ?? '',
            dcto_porcentaje: detallePrevio.dcto_porcentaje ?? 0
        };
    });

    detallesJsonField.value = JSON.stringify(detalles);
}

function validarDescripcionGlosaMovventa(scope = document) {
    const tipoDetalle = (scope.querySelector('#tipo_detalle_consumo')?.value || '').toUpperCase();
    if (tipoDetalle !== 'GLOSA') {
        return true;
    }

    const detallesJson = scope.querySelector('#detalles_json')?.value || '[]';
    let detalles = [];
    try {
        const parsed = JSON.parse(detallesJson);
        detalles = Array.isArray(parsed) ? parsed : [];
    } catch (e) {
        mostrarMensajeError('No se pudo validar la glosa. Revisa los detalles e intenta nuevamente.');
        return false;
    }

    if (detalles.length === 0) {
        mostrarMensajeError('En glosa debes ingresar al menos una descripcion.');
        return false;
    }

    const existeDescripcionVacia = detalles.some(detalle => String(detalle?.descripcion || '').trim() === '');
    if (existeDescripcionVacia) {
        mostrarMensajeError('En glosa la descripcion no puede estar vacia. SUNAT rechazara el comprobante.');
        return false;
    }

    return true;
}

function actualizarMovVenta() {


    const tabla = document.querySelector('#data-table tbody');
    const productoIdInput = parseInt(document.getElementById('producto_id')?.value || '', 10);
    const productoIdEditando = parseInt(productoEditandoId, 10);
    const productoIdActual = !isNaN(productoIdInput)
        ? productoIdInput
        : (!isNaN(productoIdEditando) ? productoIdEditando : glosaTemporalId--);
    let row = filaEditandoElement;
    if (!row && !isNaN(productoIdActual)) {
        row = document.querySelector(`#data-table tbody tr[data-producto-id="${productoIdActual}"]`);
    }

    // Obtener valores del formulario
    const codigo = document.getElementById('codigo').value.trim();
    const descripcion = document.getElementById('descripcion').value.trim();
    const cantidad = parseFloat(document.getElementById('cantidad').value.trim());
    const unidad = document.getElementById('unidad_id').options[document.getElementById('unidad_id').selectedIndex].text;

    const igvSelect = document.getElementById('igv_id');
    const igvPorcentaje = parseFloat(igvSelect.value); // del value numérico
    const igvIdSeleccionado = parseInt(igvSelect.options[igvSelect.selectedIndex]?.dataset?.igvId || '0');
    const monto = parseFloat(document.getElementById('det_movventa_monto').value.trim());
    const precioCompra = parseFloat(document.getElementById('det_movventa_preciocompra').value.trim());

    const precioUnitario = (cantidad > 0) ? (monto / cantidad) : 0;
    const igvCalculado = monto * (igvPorcentaje / 100);

    if (row) {
        const celdas = row.cells;

        if (!row.getAttribute('data-producto-id')) {
            row.setAttribute('data-producto-id', String(productoIdActual));
        }

        // Obtener el precio_unitario ya existente en la tabla
        // const precioUnitarioExistente = parseFloat(row.querySelector('.precio-unitario')?.innerText || '0');
        const precioUnitarioExistente = parseFloat(document.getElementById('det_movventa_monto')?.value || '0');

        // Calcular el nuevo total según la nueva cantidad
        const nuevoTotal = cantidad * precioUnitarioExistente;
        const nuevoIGV = nuevoTotal * (igvPorcentaje / 100);

        // Actualizar campos visuales
        celdas[0].innerText = descripcion;
        celdas[1].innerText = precioCompra.toFixed(2);
        celdas[2].innerText = unidad;
        row.querySelector('.cantidad').innerText = cantidad;
        row.querySelector('.igv').innerText = nuevoIGV.toFixed(2);
        row.querySelector('.precio-unitario').innerText = precioUnitarioExistente.toFixed(2);
        row.querySelector('.precio-total').innerText = nuevoTotal.toFixed(2);
        if (celdas[7]) celdas[7].innerText = codigo;
        row.setAttribute('data-igv-id', isNaN(igvIdSeleccionado) ? '' : String(igvIdSeleccionado));
        row.setAttribute('data-igv-porcentaje', String(igvPorcentaje));

        // Actualizar el JSON de detalles
        sincronizarDetallesJsonMovVenta(document);
        actualizarTotales();
        reinicializarDataTables();
    }
    else {

        // Aquí calculamos precio unitario, total e IGV para nuevo producto
        const precioUnitario = monto; // Precio unitario = monto del modal
        const total = cantidad * monto; // Total = cantidad * precio unitario
        const igvCalculado = total * (igvPorcentaje / 100);
        const dataTable = $('#data-table').DataTable();

        const nuevaFila = dataTable.row.add([
            descripcion,
            `<span class="inpt-cantidad-mc">${precioCompra.toFixed(2)}</span>`,
            unidad,
            `<span class="inpt-cantidad-mc cantidad" data-id="${productoIdActual}">${cantidad}</span>`,
            `<span class="inpt-cantidad-mc igv">${igvCalculado.toFixed(2)}</span>`,
            `<span class="inpt-cantidad-mc precio-unitario" data-id="${productoIdActual}">${precioUnitario.toFixed(2)}</span>`,
            `<span class="inpt-cantidad-mc precio-total dtr-hidden" style="display:none;" data-id="${productoIdActual}">${total.toFixed(2)}</span>`,
            `<span class="dtr-hidden">${codigo}</span>`,
            `<span class="dtr-hidden"><span class="badge yellow black-text">Pendiente</span></span>`,
            `<button type="button" class="btn btn-floating btn-small tooltipped bd-primary white-text" data-tooltip="Sumar" onclick="actualizarRapidoDet_MovVenta(${productoIdActual}, 1);">
            <i class="fa-solid fa-plus"></i>
            </button>
            <button type="button" class="btn btn-floating btn-small tooltipped bd-primary white-text" data-tooltip="Restar" onclick="actualizarRapidoDet_MovVenta(${productoIdActual}, -1);">
                <i class="material-icons">remove</i>
            </button>
            <button type="button" class="btn btn-floating btn-small red white-text tooltipped" data-tooltip="Quitar" onclick="quitarProductoDeTabla(${productoIdActual});">
                <i class="fa-solid fa-trash-can"></i>
            </button>`
        ]).node();

        nuevaFila.setAttribute('data-producto-id', productoIdActual);
        nuevaFila.setAttribute('data-igv-id', isNaN(igvIdSeleccionado) ? '' : String(igvIdSeleccionado));
        nuevaFila.setAttribute('data-igv-porcentaje', String(igvPorcentaje));

        dataTable.draw(); // Redibuja la tabla

        // Agregar nuevo item al JSON de detalles
        const detallesActuales = JSON.parse(document.getElementById('detalles_json').value || "[]");

        detallesActuales.push({
            producto_id: productoIdActual > 0 ? productoIdActual : null,
            situacion: "P",
            descripcion,
            precio_compra: isNaN(precioCompra) ? 0 : precioCompra,
            unidad,
            cantidad,
            igv: igvCalculado,
            igv_id: (!isNaN(igvIdSeleccionado) && igvIdSeleccionado > 0) ? igvIdSeleccionado : null,
            precio_unitario: precioUnitario,
            total: total,
            codigo,
            categoria: "Sin Categoría",
            opc_categoria: {},
            notas: ""
        });

        document.getElementById('detalles_json').value = JSON.stringify(detallesActuales);
        actualizarTotales();


        reinicializarDataTables();
    }



    // Cerrar modal
    const modal = M.Modal.getInstance(document.getElementById('det_movventa-maintenance-submodal'));
    modal.close();

    productoEditandoId = null;
    filaEditandoElement = null;
}

function sumarMontoACaja(monto) {

    monto = parseFloat(monto) || 0;
    if (monto <= 0) return;

    const fila = document.querySelector('#tabla-medios-pago tbody tr');
    if (!fila) return;

    const tdMonto = fila.querySelector('.monto');
    let montoActual = parseFloat(tdMonto.innerText.replace(',', '')) || 0;

    const nuevoMonto = montoActual + monto;
    tdMonto.innerText = nuevoMonto.toFixed(2);

    actualizarJsonCaja();
}

function actualizarJsonCaja() {

    const fila = document.querySelector('#tabla-medios-pago tbody tr');
    if (!fila) return;

    const tipo = fila.querySelector('.badge')?.innerText || 'Pagado';

    const monto = parseFloat(
        fila.querySelector('.monto')?.innerText.replace(',', '')
    ) || 0;

    const medioPagoTd = fila.querySelector('.medio-pago');

    const json = [{
        tipo: tipo,
        monto: monto,
        mediopago_id: medioPagoTd?.dataset.mediopagoId || null,
        medio_pago: medioPagoTd?.innerText.trim() || '',
        notas: fila.querySelector('.notas')?.innerText.trim() || '',
        detalles: [] // 👈 SIEMPRE ARRAY
    }];

    document.getElementById('detalles_movimientos_json').value = JSON.stringify(json);
}


function agregarGlosa() {
    productoEditandoId = null;
    filaEditandoElement = null;
    // Limpiar campos del formulario del modal
    document.getElementById('producto_id').value = '';
    document.getElementById('codigo').value = '-';
    document.getElementById('descripcion').value = '';
    document.getElementById('cantidad').value = 1;
    document.getElementById('det_movventa_monto').value = 0;
    document.getElementById('det_movventa_preciocompra').value = 0;

    // Mostrar el campo de precio de compra si estaba oculto
    document.getElementById('divDet_movventa_preciocompra').style.display = 'none';

    // Restablecer selects a la primera opción disponible
    document.getElementById('unidad_id').selectedIndex = 0;


    // Limpiar campos opcionales
    document.getElementById('det_movventa_comentario').value = '';
    document.getElementById('dcto_porcentaje').value = '';
    document.getElementById('dcto_monto').value = '';

    // Actualiza campos visuales de Materialize
    M.updateTextFields();
    reinicializarSelects();

    // Cambiar título del modal
    document.getElementById('det_movventa-submodal-title').innerHTML =
        `<i class="fa-solid fa-bag-shopping"></i> Agregar Glosa`;

    // Abrir modal
    const modal = M.Modal.getInstance(document.getElementById('det_movventa-maintenance-submodal'));
    modal.open();
}

function editarMovimiento(row, event) {
    // Verifica si el clic fue en la primera columna
    const clickedColumnIndex = event.target.cellIndex;
    if (clickedColumnIndex === 0) return;

    // Continúa con la edición si no es la primera columna
    actualizarDetallesMovimientos();

    const index = Array.from(row.parentElement.children).indexOf(row);
    movimientoEditandoIndex = index;
    if (index >= 0 && index < detallesMovimientos.length) {
        const movimiento = detallesMovimientos[index];

        document.getElementById('tipo_detalle').value = movimiento.tipo || 'Pagado';
        document.getElementById('monto_detalle').value = movimiento.monto ? movimiento.monto.toFixed(2) : '0.00';
        document.getElementById('medio_pago_detalle').value = movimiento.mediopago_id || '';
        document.getElementById('notas_detalle').value = movimiento.notas || '';
        prepararCampoVencimientoDeuda();
        setFechaVencimientoDetalle(movimiento.fechavencimiento || movimiento.fecha_vencimiento || '');
        actualizarCampoVencimientoDeuda();

        // Obtener el select de medio de pago y activar la función de visibilidad de campos
        const medioPagoSelect = document.getElementById('medio_pago_detalle');
        mostrarSelects(medioPagoSelect);

        M.updateTextFields();
        M.FormSelect.init(document.querySelectorAll('select'));

        const modal = M.Modal.getInstance(document.getElementById('detalleModal'));
        modal.open();
    } else {
        console.error("El índice no corresponde a ningún movimiento en detallesMovimientos", index);
    }
}


let detallesMovimientos = [
    {
        tipo: "Pagado",
        monto: 0.00,
        mediopago_id: 1, // Si no hay un ID específico, lo dejamos vacío
        medio_pago: "Efectivo",
        notas: "" 
    }
];

let movimientoEditandoIndex = null; // Para identificar si se está editando un movimiento
function agregarMovimientoAlDetalle() {
    const tipo = document.getElementById('tipo_detalle').value;
    const monto = parseFloat(document.getElementById('monto_detalle').value) || 0.00;
    const medioPagoSelect = document.getElementById('medio_pago_detalle');
    prepararCampoVencimientoDeuda();
    const fechavencimiento = getFechaVencimientoDetalle();

    if (tipo === 'Deuda' && !fechavencimiento) {
        mostrarMensajeError('Selecciona la fecha de vencimiento de la deuda.');
        return;
    }

    if (medioPagoSelect.selectedIndex === -1) {
        console.error('No se seleccionó un medio de pago');
        return;
    }

    const medioPagoId = medioPagoSelect.value;
    const medioPagoDescripcion = medioPagoSelect.options[medioPagoSelect.selectedIndex]?.text || "Sin descripción";
    const notas = document.getElementById('notas_detalle').value;

    const nuevoMovimiento = {
        tipo: tipo,
        monto: monto,
        mediopago_id: medioPagoId,
        medio_pago: medioPagoDescripcion,
        fechavencimiento: tipo === 'Deuda' ? fechavencimiento : null,
        notas: notas,
        detalles: []
    };

    if (document.getElementById('pos_container').style.display === 'block') {
        const posDetalle = document.getElementById('pos_detalle');
        const posValor = posDetalle.options[posDetalle.selectedIndex]?.text || 'No seleccionado';
        nuevoMovimiento.detalles.push({ pos: posValor });
    }

    if (document.getElementById('tarjeta_container').style.display === 'block') {
        const tarjetaDetalle = document.getElementById('tarjeta_detalle');
        const tarjetaValor = tarjetaDetalle.options[tarjetaDetalle.selectedIndex]?.text || 'No seleccionado';
        nuevoMovimiento.detalles.push({ tarjeta: tarjetaValor });
    }

    if (document.getElementById('banco_container').style.display === 'block') {
        const bancoDetalle = document.getElementById('banco_detalle');
        const bancoValor = bancoDetalle.options[bancoDetalle.selectedIndex]?.text || 'No seleccionado';
        nuevoMovimiento.detalles.push({ banco: bancoValor });
    }

    if (document.getElementById('billetera_container').style.display === 'block') {
        const billeteraDetalle = document.getElementById('billetera_detalle');
        const billeteraValor = billeteraDetalle.options[billeteraDetalle.selectedIndex]?.text || 'No seleccionado';
        nuevoMovimiento.detalles.push({ billetera: billeteraValor });
    }

    if (movimientoEditandoIndex !== null) {
        // Editar movimiento existente
        detallesMovimientos[movimientoEditandoIndex] = nuevoMovimiento;
        const tableRow = document.querySelectorAll('#detalle-movcaja tr')[movimientoEditandoIndex];
        tableRow.innerHTML = generarHtmlFila(nuevoMovimiento);
        movimientoEditandoIndex = null;
    } else {
        // Agregar nuevo movimiento al array y la tabla
        detallesMovimientos.push(nuevoMovimiento);

        const newRowHtml = generarHtmlFila(nuevoMovimiento);

        if ($.fn.DataTable.isDataTable('#tabla-medios-pago')) {
            // Si la tabla usa DataTable, agrégalo de la manera correcta
            $('#tabla-medios-pago').DataTable().row.add($(newRowHtml)).draw();
        } else {
            // Si no hay DataTable, agrégalo manualmente
            const tableBody = document.getElementById('detalle-movcaja');
            const newRow = document.createElement('tr');
            newRow.innerHTML = newRowHtml;
            newRow.classList.add(tableBody.children.length % 2 === 0 ? 'even' : 'odd');
            newRow.addEventListener('click', function (event) {
                editarMovimiento(detallesMovimientos.length - 1, event);
            });
            tableBody.appendChild(newRow);
        }
    }

    document.getElementById('detalles_movimientos_json').value = JSON.stringify(detallesMovimientos);
    actualizarTotal();
    cerrarModal();
    reinicializarDataTables();
}

function generarHtmlFila(movimiento) {
    const detallesFormateados = Array.isArray(movimiento.detalles)
        ? movimiento.detalles.map(detalle => {
            if (typeof detalle === 'object') {
                return Object.values(detalle).join(' - ');
            }
            return detalle;
        }).join('; ')
        : '';

    const fechaVencimiento = movimiento.fechavencimiento || movimiento.fecha_vencimiento || '-';
    const notas = movimiento.notas || '';
    const medioPago = movimiento.medio_pago || '---';
    const medioPagoId = movimiento.mediopago_id || '';

    const colorBadge = movimiento.tipo === 'Pagado' ? 'green' : 'red';

    return `
        <tr onclick="editarMovimiento(this, event)">
            <td>
                <span class="new badge ${colorBadge} white-text"
                      data-badge-caption="${movimiento.tipo}"></span>
            </td>

            <td class="inpt-cantidad-mc monto" id="total-display">
                ${parseFloat(movimiento.monto).toFixed(2)}
            </td>

            <td class="medio-pago" data-mediopago-id="${medioPagoId}">
                ${medioPago}
            </td>

            <td class="detalles">${detallesFormateados}</td>
            <td class="notas">${notas}</td>
            <td class="fecha-vencimiento">${fechaVencimiento}</td>

            <td>
                <button type="button"
                    class="btn btn-small red darken-2"
                    onclick="eliminarFila(this); event.stopPropagation();"
                    style="border-radius: 50%; color: rgb(255, 255, 255); position: relative;">
                    <i class="material-icons"
                       style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">
                        remove
                    </i>
                </button>
            </td>
        </tr>
    `;
}
const detallesPorMedio = {
    1: [], // Efectivo no tiene detalles adicionales
    2: [{ pos: 'Izipay' }, { tarjeta: 'Visa - Débito' }], // Detalles para Tarjeta
    3: [{ banco: 'BCP' }], // Detalles para Transferencia
    4: [{ banco: 'BCP' }], // Detalles para Depósito
    5: [{ billetera: 'Yape' }], // Detalles para Billetera digital
};
function actualizarDetallesMovimientos() {
    detallesMovimientos = [];

    const filas = document.querySelectorAll('#detalle-movcaja tr');
    filas.forEach((fila) => {
        const badgeEl = fila.querySelector('td span.badge');
        const tipo = badgeEl
            ? (badgeEl.getAttribute('data-badge-caption') || badgeEl.textContent.trim() || 'Pagado')
            : 'Pagado';
        const monto = parseFloat(fila.querySelector('td.monto').textContent.replace(',', ''));
        const fechaVencimientoElement = fila.querySelector('td.fecha-vencimiento');
        const fechavencimiento = fechaVencimientoElement ? fechaVencimientoElement.textContent.trim() : null;
        const medioPago = fila.querySelector('td.medio-pago').textContent.trim();
        const medioPagoId = fila.querySelector('td.medio-pago').getAttribute('data-mediopago-id');

        let detallesTexto = fila.querySelector('td.detalles').textContent.trim();
        let notas = fila.querySelector('td.notas').textContent.trim();

        // Convertir detallesTexto a array de objetos según el medio de pago
        let detalles = [];
        if (detallesTexto !== '-') {
            if (detallesPorMedio[medioPagoId]) {
                let detallesArray = detallesTexto.split(',').map(d => d.trim()); // Separar detalles por coma
                detalles = detallesPorMedio[medioPagoId].filter(d =>
                    Object.values(d).some(val => detallesArray.includes(val))
                );
            }
        }

        detallesMovimientos.push({
            tipo: tipo,
            monto: monto,
            mediopago_id: parseInt(medioPagoId, 10),
            medio_pago: medioPago,
            fechavencimiento: fechavencimiento !== '-' ? fechavencimiento : null,
            detalles: detalles, // Ahora siempre es un array de objetos o vacío []
            notas: notas !== '' ? notas : ''
        });
    });
}



function limpiarDetalleMovCaja() {
    detallesMovimientos = [
        {
            tipo: "Pagado",
            monto: 0.00,
            mediopago_id: 1,
            medio_pago: "Efectivo",
            notas: ""
        }
    ];
    mostrarPantallaDeCarga();

    // Obtener el botón que disparó la función
    const button = document.querySelector('button[type="submit"] i[data-icono]');

    if (button) {
        const icono = button.getAttribute('data-icono');
        const titulo = button.getAttribute('data-titulo');
        const ruta = button.getAttribute('data-ruta');

        // Actualizar breadcrumb y cargar nueva vista
        if (breadcrumbStack.length > 1) {
            breadcrumbStack.pop(); // Retrocede un paso en el breadcrumb si es necesario
        }

        // Agregar nueva cabecera usando los datos del botón
        actualizarCabecera(icono, titulo, ruta);

    } else {
    }
}



function cerrarModal() {
    const modal = M.Modal.getInstance(document.getElementById('detalleModal'));
    modal.close();

    // Limpiar los campos del modal
    document.getElementById('tipo_detalle').value = "Pagado";
    document.getElementById('monto_detalle').value = "0.00";
    document.getElementById('notas_detalle').value = "";
    setFechaVencimientoDetalle('');
    actualizarCampoVencimientoDeuda();
    M.updateTextFields();
}

function actualizarTotal() {
    const totalInput = document.getElementById('total');
    const montos = document.querySelectorAll('#detalle-movcaja .monto');
    let total = 0;

    montos.forEach(monto => {
        const valor = parseFloat(monto.textContent);
        if (!isNaN(valor)) {
            total += valor;
        }
    });

    const totalFormateado = total.toFixed(2);
    const pagadoInput = document.getElementById('pagado');

    if ((totalInput && totalInput.classList.contains('totalapertura')) || pagadoInput) {
        totalInput.value = totalFormateado;
        totalInput.setAttribute('value', totalFormateado);
        if (pagadoInput) {
            pagadoInput.value = totalFormateado;
        }


    }


    // Refresca las etiquetas flotantes si estás usando MaterializeCSS
    M.updateTextFields();
}

function esFormularioAperturaCaja() {
    const totalInput = document.getElementById('total');
    const operacionInput = document.querySelector('input[name="operacion"]');
    return (totalInput && totalInput.classList.contains('totalapertura'))
        || (operacionInput && operacionInput.value === 'APERTURAR');
}

function crearDetalleAperturaPorDefecto() {
    return {
        tipo: 'Pagado',
        monto: 0.00,
        mediopago_id: 1,
        medio_pago: 'Efectivo',
        fechavencimiento: null,
        detalles: [],
        notas: ''
    };
}

function asegurarDetalleAperturaPorDefecto() {
    if (!esFormularioAperturaCaja()) {
        return;
    }

    if (!document.getElementById('tabla-medios-pago') || !$.fn.DataTable.isDataTable('#tabla-medios-pago')) {
        return;
    }

    const table = $('#tabla-medios-pago').DataTable();

    if (table.rows().count() > 0 && detallesMovimientos.length > 0) {
        return;
    }

    const detalleDefecto = crearDetalleAperturaPorDefecto();
    detallesMovimientos = [detalleDefecto];

    table.clear();
    table.row.add($(generarHtmlFila(detalleDefecto))).draw();

    const jsonField = document.getElementById('detalles_movimientos_json');
    if (jsonField) {
        jsonField.value = JSON.stringify(detallesMovimientos);
    }

    actualizarTotal();
}

function sincronizarDetalleAperturaCaja() {
    if (!esFormularioAperturaCaja()) {
        return;
    }

    if (!document.getElementById('tabla-medios-pago') || !$.fn.DataTable.isDataTable('#tabla-medios-pago')) {
        return;
    }

    const table = $('#tabla-medios-pago').DataTable();

    if (table.rows().count() > 0) {
        actualizarDetallesMovimientos();
        const jsonField = document.getElementById('detalles_movimientos_json');
        if (jsonField) {
            jsonField.value = JSON.stringify(detallesMovimientos);
        }
        actualizarTotal();
        return;
    }

    asegurarDetalleAperturaPorDefecto();
}

function eliminarFila(button) {
    // Obtener instancia de DataTable
    const table = $('#tabla-medios-pago').DataTable();

    // Obtener la fila del botón
    const row = button.closest('tr');
    const rowIndex = table.row(row).index(); // índice correcto dentro de DataTable

    if (rowIndex !== undefined) {
        // Eliminar del arreglo original
        detallesMovimientos.splice(rowIndex, 1);

        // Eliminar visualmente con la API de DataTables
        table.row(row).remove().draw();

        // Actualizar campo oculto
        document.getElementById('detalles_movimientos_json').value = JSON.stringify(detallesMovimientos);

        // Actualizar totales u otras operaciones
        actualizarTotal();
        asegurarDetalleAperturaPorDefecto();
    }
}


function filterByCaja() {
    const selectedCaja = document.getElementById('filter-caja').value;

    mostrarPantallaDeCarga();

    fetch(`/filter-by-caja/${selectedCaja}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('Error al obtener datos.');
            }
            return response.text(); // Recibimos toda la vista como texto
        })
        .then(html => {
            // Parsear la respuesta HTML completa
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');

            // Reemplazar la tabla y las operaciones
            const newTable = doc.querySelector('#tb-container').innerHTML;
            const newOperations = doc.querySelector('.operaciones-crear').innerHTML;

            document.querySelector('#tb-container').innerHTML = newTable;
            document.querySelector('.operaciones-crear').innerHTML = newOperations;

            // Re-inicializar componentes de Materialize
            M.AutoInit();
            reinicializarDataTables();
            ocultarPantallaDeCarga();
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarMensajeError('Error al filtrar por caja');
            ocultarPantallaDeCarga();
        });
}
function filterBySituacion() {
    const selectedSituacion = document.getElementById('situacion-filter').value;
    const perPage = document.getElementById('records-per-page').value;

    mostrarPantallaDeCarga();

    fetch(`/filter-by-situacion/${selectedSituacion}?perPage=${perPage}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(response => {
            return response.text().then(text => ({ response, text }));
        })
        .then(({ response, text }) => {
            const contentType = (response.headers.get('content-type') || '').toLowerCase();
            const trimmed = text.trim();

            if (contentType.includes('application/json') || /^\{/.test(trimmed) || /^\[/.test(trimmed)) {
                try {
                    const json = JSON.parse(text);
                    if (json && json.success === false) {
                        ocultarPantallaDeCarga();
                        mostrarMensajeError(json.errors || json.message || 'Error del servidor');
                        return;
                    }
                    ocultarPantallaDeCarga();
                    return;
                } catch (e) {
                    console.warn('Respuesta parecía JSON pero no se pudo parsear:', e);
                }
            }

            if (!response.ok) {
                ocultarPantallaDeCarga();
                mostrarMensajeError(`Error ${response.status}: ${response.statusText}`);
                return;
            }

            const parser = new DOMParser();
            const doc = parser.parseFromString(text, 'text/html');

            const newTb = doc.querySelector('#tb-container');
            if (!newTb) {
                ocultarPantallaDeCarga();
                mostrarMensajeError('Respuesta inválida del servidor (falta #tb-container).');
                return;
            }

            const newTable = newTb.innerHTML;
            const newOperations = doc.querySelector('.operaciones-crear')?.innerHTML;

            const tbContainer = document.querySelector('#tb-container');
            if (tbContainer) tbContainer.innerHTML = newTable;

            if (newOperations) {
                const operacionesEl = document.querySelector('.operaciones-crear');
                if (operacionesEl) operacionesEl.innerHTML = newOperations;
            }

            M.AutoInit();
            reinicializarDataTables();
            incializarTemporizador();
            ocultarCargaTrasContadores(window.location.pathname);
        })
        .catch(error => {
            console.error('Error en filterBySituacion:', error);
            ocultarPantallaDeCarga();
            mostrarMensajeError('Error al filtrar por situación');
        });
}

function actualizarBotonEliminarHabitacionesMasivo() {
    const cantidad = document.querySelectorAll('.chk-habitacion:checked').length;
    const btn = document.getElementById('btn-eliminar-habitaciones-masivo');

    if (!btn) {
        return;
    }

    btn.disabled = cantidad === 0;
    const badge = btn.querySelector('.cantidad-seleccion');
    if (badge) {
        badge.textContent = cantidad > 0 ? ` (${cantidad})` : '';
    }
}

function eliminarHabitacionesMasivo() {
    const ids = Array.from(document.querySelectorAll('.chk-habitacion:checked')).map((el) => el.value);

    if (!ids.length) {
        mostrarMensajeError('Seleccione al menos una habitación.');
        return;
    }

    Swal.fire({
        title: '¿Eliminar habitaciones seleccionadas?',
        text: `Se eliminarán ${ids.length} habitación(es). Las ocupadas serán omitidas.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e53935',
        cancelButtonColor: '#6e7881',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        customClass: {
            popup: 'rounded-swal'
        }
    }).then((result) => {
        if (!result.isConfirmed) {
            return;
        }

        mostrarPantallaDeCarga();

        const paginaActual = new URLSearchParams(window.location.search).get('page') || localStorage.getItem('paginaActual') || 1;

        fetch('/habitacion/destroy-many', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                ids: ids.map((id) => parseInt(id, 10)),
                pagina_actual: paginaActual
            })
        })
            .then((response) => response.json().then((data) => ({ ok: response.ok, data })))
            .then(({ ok, data }) => {
                ocultarPantallaDeCarga();

                if (ok && data.success) {
                    mostrarMensajeExito(data.message || 'Habitaciones eliminadas correctamente.');
                    const chkAll = document.getElementById('chk-habitacion-all');
                    if (chkAll) {
                        chkAll.checked = false;
                    }
                    if (data.redirect_url) {
                        actualizarTabla(data.redirect_url);
                    }
                    return;
                }

                mostrarMensajeError(data.message || data.errors || 'No se pudieron eliminar las habitaciones.');
            })
            .catch(() => {
                ocultarPantallaDeCarga();
                mostrarMensajeError('Error al eliminar las habitaciones seleccionadas.');
            });
    });
}

$(document).on('change', '#chk-habitacion-all', function () {
    const checked = this.checked;
    document.querySelectorAll('.chk-habitacion:not(:disabled)').forEach((chk) => {
        chk.checked = checked;
    });
    actualizarBotonEliminarHabitacionesMasivo();
});

$(document).on('change', '.chk-habitacion', function () {
    const total = document.querySelectorAll('.chk-habitacion:not(:disabled)').length;
    const marcados = document.querySelectorAll('.chk-habitacion:checked').length;
    const chkAll = document.getElementById('chk-habitacion-all');

    if (chkAll) {
        chkAll.checked = total > 0 && marcados === total;
        chkAll.indeterminate = marcados > 0 && marcados < total;
    }

    actualizarBotonEliminarHabitacionesMasivo();
});

function filterHabitaciones() {
    const searchInput = document.getElementById('search-input');
    const urlBase = searchInput ? (searchInput.getAttribute('data-url-base') || '') : '';

    if (document.getElementById('situacion-filter') && urlBase.indexOf('habitacion') !== -1) {
        realizarBusqueda();
        return;
    }

    const selectedSituacion = document.getElementById('situacion-filter')?.value || '';
    const selectedPiso = document.getElementById('piso-filter')?.value || '';
    const selectedTipohabitacion = document.getElementById('tipohabitacion-filter')?.value || '';

    mostrarPantallaDeCarga();

    const url = `/filter-habitaciones?situacion=${selectedSituacion}&piso=${selectedPiso}&tipohabitacion=${selectedTipohabitacion}`;

    fetch(url, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('Error al obtener datos.');
            }
            return response.text(); // Recibimos toda la vista como texto
        })
        .then(html => {
            // Parsear la respuesta HTML completa
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');

            // Reemplazar la tabla y las operaciones
            const newTable = doc.querySelector('#tb-container').innerHTML;
            const newOperations = doc.querySelector('.operaciones-crear')?.innerHTML;

            document.querySelector('#tb-container').innerHTML = newTable;
            if (newOperations) {
                document.querySelector('.operaciones-crear').innerHTML = newOperations;
            }

            // Re-inicializar componentes de Materialize
            M.AutoInit();
            reinicializarDataTables();
            ocultarPantallaDeCarga();
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarMensajeError('Error al filtrar habitaciones');
            ocultarPantallaDeCarga();
        });
}

function filterVentasTurnoCaja() {
    const selectedTurno = document.getElementById('rel_turnocaja_id').value;


    mostrarPantallaDeCarga();

    const url = `/movventareport?turno=${selectedTurno}`;

    fetch(url, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('Error al obtener datos.');
            }
            return response.text(); // Recibimos toda la vista como texto
        })
        .then(html => {
            // Parsear la respuesta HTML completa
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');

            // Reemplazar la tabla y las operaciones
            const newTable = doc.querySelector('#tb-container').innerHTML;
            const newOperations = doc.querySelector('.operaciones-crear')?.innerHTML;

            document.querySelector('#tb-container').innerHTML = newTable;
            if (newOperations) {
                document.querySelector('.operaciones-crear').innerHTML = newOperations;
            }

            // Re-inicializar componentes de Materialize
            M.AutoInit();
            reinicializarDataTables();
            ocultarPantallaDeCarga();
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarMensajeError('Error al filtrar ventas por turno de caja');
            ocultarPantallaDeCarga();
        });
}
function filterReservas() {
    const selectedEstado = document.getElementById('situacion-filter').value;
    const recordsPerPage = document.getElementById('records-per-page') ? document.getElementById('records-per-page').value : 10;

    mostrarPantallaDeCarga();

    const url = `/filter-reservas?estado=${selectedEstado}&perPage=${recordsPerPage}`;

    fetch(url, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
        .then(response => {
            if (!response.ok) throw new Error('Error al obtener datos.');
            return response.text();
        })
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');

            document.querySelector('#tb-container').innerHTML = doc.querySelector('#tb-container').innerHTML;
            const newOperations = doc.querySelector('.operaciones-crear')?.innerHTML;
            if (newOperations) document.querySelector('.operaciones-crear').innerHTML = newOperations;

            M.AutoInit();
            reinicializarDataTables();
            ocultarPantallaDeCarga();
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarMensajeError('Error al filtrar reservas');
            ocultarPantallaDeCarga();
        });
}

function filterStock() {
    const selectedCategoria = document.getElementById('categoria-filter').value;
    const selectedTipo = document.getElementById('tipo-filter').value;
    const selectedSituacion = document.getElementById('situacion-filter').value;
    const recordsPerPage = document.getElementById('records-per-page').value; // Capturar la cantidad seleccionada
    const searchInput = document.getElementById('search-input').value.trim();

    mostrarPantallaDeCarga();

    const url = `/filter-stockproductos?search=${encodeURIComponent(searchInput)}&categoria=${selectedCategoria}&tipo=${selectedTipo}&situacion=${selectedSituacion}&perPage=${recordsPerPage}`;

    fetch(url, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
        .then(response => {
            if (!response.ok) throw new Error('Error al obtener datos.');
            return response.text();
        })
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');

            document.querySelector('#tb-container').innerHTML = doc.querySelector('#tb-container').innerHTML;
            const newOperations = doc.querySelector('.operaciones-crear')?.innerHTML;
            if (newOperations) document.querySelector('.operaciones-crear').innerHTML = newOperations;

            M.AutoInit();
            reinicializarDataTables();
            ocultarPantallaDeCarga();
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarMensajeError('Error al filtrar stock de productos');
            ocultarPantallaDeCarga();
        });
}
function filtrarAlojamientos() {
    const searchInput = document.getElementById('search-input').value.trim();
    const selectedTurno = document.getElementById('rel_turnocaja_id').value;
    const fechaInicio = document.getElementById('fecha_inicio').value;
    const fechaFin = document.getElementById('fecha_fin').value;
    const modalidad = document.getElementById('modalidad-filter').value;
    const situacion = document.getElementById('situacion-filter').value;
    const recordsPerPage = document.getElementById('records-per-page').value;

    mostrarPantallaDeCarga();

    const url = `/filter-checkouts?turno=${selectedTurno}&search=${encodeURIComponent(searchInput)}&fechaInicio=${fechaInicio}&fechaFin=${fechaFin}&modalidad=${modalidad}&situacion=${situacion}&perPage=${recordsPerPage}`;

    fetch(url, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
        .then(response => {
            if (!response.ok) throw new Error('Error al obtener datos.');
            return response.text();
        })
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            document.querySelector('#tb-container').innerHTML = doc.querySelector('#tb-container').innerHTML;
            M.AutoInit();
            reinicializarDataTables();
            ocultarPantallaDeCarga();
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarMensajeError('Error al filtrar alojamientos');
            ocultarPantallaDeCarga();
        });
}
function generarResumenPDF() {
    const fechaInicio = document.getElementById('fecha_inicio').value;
    const fechaFin = document.getElementById('fecha_fin').value;
    const horaInicio = document.getElementById('hora_inicio')?.value || '';
    const horaFin = document.getElementById('hora_fin')?.value || '';
    const modalidad = document.getElementById('modalidad-filter').value;
    const situacion = document.getElementById('situacion-filter').value;

    const url = `/resumen-alojamientos/pdf?fecha_inicio=${fechaInicio}&hora_inicio=${horaInicio}&fecha_fin=${fechaFin}&hora_fin=${horaFin}&modalidad=${modalidad}&situacion=${situacion}`;

    window.open(url, '_blank');
}
function filterCajaAperturada(page = 1) {
    const selectedCaja = document.getElementById('caja_id').value;
    const selectedTipo = document.getElementById('tipo-filter').value;
    const selectedConcepto = document.getElementById('conceptospago-filter').value;
    const searchQuery = document.getElementById('txtBuscar').value.trim();
    const fechaInicio = document.getElementById('txtFechaInicio').value;
    const fechaFin = document.getElementById('txtFechaFin').value;
    const recordsPerPage = document.getElementById('records-per-page').value;

    mostrarPantallaDeCarga();

    const url = `/movcaja?page=${page}&caja_id=${selectedCaja}&tipo=${selectedTipo}&conceptospago=${selectedConcepto}&search=${searchQuery}&fechaInicio=${fechaInicio}&fechaFin=${fechaFin}&perPage=${recordsPerPage}`;

    fetch(url, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
        .then(response => {
            if (!response.ok) throw new Error('Error al obtener datos.');
            return response.text();
        })
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');

            // Reemplazar solo la tabla y operaciones
            document.querySelector('#tb-container').innerHTML = doc.querySelector('#tb-container').innerHTML;

            const newOperations = doc.querySelector('.operaciones-crear')?.innerHTML;
            if (newOperations) {
                document.querySelector('.operaciones-crear').innerHTML = newOperations;
            }

            M.AutoInit();
            reinicializarDataTables();
            ocultarPantallaDeCarga();
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarMensajeError('Error al filtrar caja aperturada');
            ocultarPantallaDeCarga();
        });
}

function filterCaja() {
    const selectedCaja = document.getElementById('caja_id').value;
    const selectedTurno = document.getElementById('rel_turnocaja_id').value;
    const selectedTipo = document.getElementById('tipo-filter').value;
    const selectedConcepto = document.getElementById('conceptospago-filter').value;
    const searchQuery = document.getElementById('txtBuscar').value.trim();
    const fechaInicio = document.getElementById('txtFechaInicio').value;
    const fechaFin = document.getElementById('txtFechaFin').value;
    const recordsPerPage = document.getElementById('records-per-page').value;

    mostrarPantallaDeCarga();

    const url = `/movcajareport/?caja_id=${selectedCaja}&turno=${selectedTurno}&tipo=${selectedTipo}&conceptospago=${selectedConcepto}&search=${searchQuery}&fechaInicio=${fechaInicio}&fechaFin=${fechaFin}&perPage=${recordsPerPage}`;

    fetch(url, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
        .then(response => {
            if (!response.ok) throw new Error('Error al obtener datos.');
            return response.text();
        })
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');

            // Reemplazar solo la tabla y operaciones
            document.querySelector('#tb-container').innerHTML = doc.querySelector('#tb-container').innerHTML;

            const newOperations = doc.querySelector('.operaciones-crear')?.innerHTML;
            if (newOperations) {
                document.querySelector('.operaciones-crear').innerHTML = newOperations;
            }

            M.AutoInit();
            reinicializarDataTables();
            ocultarPantallaDeCarga();
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarMensajeError('Error al filtrar caja');
            ocultarPantallaDeCarga();
        });
}

function filterVentas() {
    const selectedTurno = document.getElementById('rel_turnocaja_id').value;
    const searchQuery = document.getElementById('txtBuscar').value.trim();
    const fechaInicio = document.getElementById('txtFechaInicio').value;
    const fechaFin = document.getElementById('txtFechaFin').value;
    const selectedTipo = document.getElementById('tipo-filter').value;
    const recordsPerPage = document.getElementById('records-per-page').value; // Capturar la cantidad seleccionada

    mostrarPantallaDeCarga();

    const url = `/filter-ventas?turno=${selectedTurno}&search=${searchQuery}&fechaInicio=${fechaInicio}&fechaFin=${fechaFin}&tipo=${selectedTipo}&perPage=${recordsPerPage}`;

    fetch(url, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
        .then(response => {
            if (!response.ok) throw new Error('Error al obtener datos.');
            return response.text();
        })
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');

            document.querySelector('#tb-container').innerHTML = doc.querySelector('#tb-container').innerHTML;
            const newOperations = doc.querySelector('.operaciones-crear')?.innerHTML;
            if (newOperations) document.querySelector('.operaciones-crear').innerHTML = newOperations;

            M.AutoInit();
            reinicializarDataTables();
            ocultarPantallaDeCarga();
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarMensajeError('Error al filtrar ventas');
            ocultarPantallaDeCarga();
        });
}



let pantallaCargaContador = 0;

// Mostrar pantalla de carga (contador: soporta POST + carga de vista sin ocultar antes de tiempo)
function mostrarPantallaDeCarga() {
    pantallaCargaContador++;
    const loadingScreen = document.getElementById('loading-screen');

    if (loadingScreen && pantallaCargaContador === 1) {
        loadingScreen.style.display = 'flex';
        loadingScreen.style.opacity = '1';
        loadingScreen.setAttribute('aria-hidden', 'false');
    }
}

// Ocultar pantalla de carga (solo cuando no quedan operaciones en curso)
function ocultarPantallaDeCarga() {
    pantallaCargaContador = Math.max(0, pantallaCargaContador - 1);
    const loadingScreen = document.getElementById('loading-screen');

    if (!loadingScreen || pantallaCargaContador > 0) {
        return;
    }

    loadingScreen.style.transition = 'opacity 0.25s ease';
    loadingScreen.style.opacity = '0';

    setTimeout(() => {
        if (pantallaCargaContador === 0) {
            loadingScreen.style.display = 'none';
            loadingScreen.style.opacity = '1';
            loadingScreen.setAttribute('aria-hidden', 'true');
        }
    }, 250);
}

function forzarOcultarPantallaDeCarga() {
    pantallaCargaContador = 0;
    const loadingScreen = document.getElementById('loading-screen');
    if (!loadingScreen) {
        return;
    }
    loadingScreen.style.transition = 'opacity 0.2s ease';
    loadingScreen.style.opacity = '0';
    loadingScreen.setAttribute('aria-hidden', 'true');
    setTimeout(() => {
        loadingScreen.style.display = 'none';
        loadingScreen.style.opacity = '1';
    }, 200);
}

function cerrarMenuLateral() {
    const sidenavEl = document.getElementById('slide-out');
    if (!sidenavEl) {
        return;
    }
    if (typeof M !== 'undefined' && M.Sidenav) {
        const instancia = M.Sidenav.getInstance(sidenavEl);
        if (instancia) {
            instancia.close();
        }
    }
    document.body.style.overflow = '';
}

function ocultarCargaTrasContadores(urlRef) {
    const enMovhabitacion = esUrlListadoHabitaciones(urlRef || window.location.pathname);
    const hayContadores = document.querySelectorAll('.tiempo-restante[data-habitacion-id][data-hora-fin]').length > 0;

    const ocultar = () => forzarOcultarPantallaDeCarga();

    if (enMovhabitacion && hayContadores) {
        requestAnimationFrame(() => {
            requestAnimationFrame(ocultar);
        });
        return;
    }

    ocultar();
}

function finalizarCargaVistaDinamica(opcionesScroll) {
    const opts = Object.assign({ irArriba: true }, opcionesScroll || {});
    const urlRef = opts.urlNavegacion || window.location.pathname;
    const enMovhabitacion = esUrlListadoHabitaciones(urlRef);
    const hayContadores = document.querySelectorAll('.tiempo-restante[data-habitacion-id][data-hora-fin]').length > 0;

    const finalizar = () => {
        restaurarScrollPagina(opts);
        ocultarCargaTrasContadores(urlRef);
        cerrarMenuLateral();
    };

    if (enMovhabitacion && hayContadores) {
        requestAnimationFrame(() => {
            requestAnimationFrame(finalizar);
        });
        return;
    }

    requestAnimationFrame(() => {
        requestAnimationFrame(() => {
            setTimeout(finalizar, 120);
        });
    });
}

function esUrlInicio(url) {
    if (!url) {
        return false;
    }
    try {
        const parsed = new URL(url, window.location.origin);
        const path = parsed.pathname.replace(/\/+$/, '') || '/';
        return path === '/' || path === '/dashboard';
    } catch (e) {
        const path = String(url).split('?')[0].replace(/\/+$/, '') || '/';
        return path === '' || path === '/' || path === '/dashboard';
    }
}

function aplicarVisibilidadMainBody(url) {
    const mainBody = document.getElementById('main-body');
    if (!mainBody) {
        return;
    }
    mainBody.style.display = esUrlInicio(url) ? 'block' : 'none';
}

function restaurarScrollPagina(opciones) {
    const opts = Object.assign({ irArriba: false, mantenerPosicion: true }, opciones || {});

    document.body.classList.remove('modal-open');
    document.body.style.overflow = '';
    document.body.style.overflowY = '';
    document.body.style.height = '';
    document.body.style.position = '';
    document.body.style.top = '';
    document.body.style.left = '';
    document.body.style.width = '';
    document.body.style.paddingRight = '';
    document.documentElement.style.overflow = '';
    document.documentElement.style.overflowY = '';
    document.documentElement.style.height = '';
    document.documentElement.style.paddingRight = '';

    const hayModalAbierto = document.querySelector('.modal.open');
    if (!hayModalAbierto) {
        document.querySelectorAll('.modal-overlay').forEach((el) => el.remove());
    }

    const mainContent = document.getElementById('main-content');
    if (mainContent) {
        mainContent.style.overflow = 'visible';
        mainContent.style.height = 'auto';
        mainContent.style.maxHeight = 'none';
        mainContent.style.pointerEvents = '';
    }

    const wrapper = document.querySelector('.min-h-screen');
    if (wrapper) {
        wrapper.style.overflow = 'visible';
        wrapper.style.height = 'auto';
        wrapper.style.minHeight = '100vh';
    }

    if (opts.irArriba) {
        window.scrollTo(0, 0);
    }
}

function inicializarModalesConScroll(contenedor) {
    const root = contenedor || document;
    root.querySelectorAll('.modal').forEach((modalEl) => {
        let inst = M.Modal.getInstance(modalEl);
        const alCerrar = function () {
            setTimeout(() => restaurarScrollPagina({ mantenerPosicion: true }), 50);
        };

        if (!inst) {
            M.Modal.init(modalEl, { onCloseEnd: alCerrar });
            return;
        }

        const prevClose = inst.options.onCloseEnd;
        inst.options.onCloseEnd = function () {
            if (typeof prevClose === 'function') {
                prevClose.call(this);
            }
            alCerrar();
        };
    });
}

(function initRestaurarScrollGlobal() {
    if (window.__hhScrollGlobalInit) {
        return;
    }
    window.__hhScrollGlobalInit = true;

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            setTimeout(() => restaurarScrollPagina({ mantenerPosicion: true }), 400);
        }
    });
})();
function inicializarDataTablesPorSelector(selector = '.data-table, .data-table-2, #data-table', usarControlesExternos = true) {
    const $tables = $(selector);

    if (!$tables.length || !$.fn.DataTable) {
        return;
    }

    $tables.each(function () {
        if ($.fn.DataTable.isDataTable(this)) {
            $(this).DataTable().destroy();
        }
    });

    $tables.each(function () {
        var $table = $(this);
        var totalCols = $table.find('thead th').length;

        // 1) Marcar <th> para Responsive
        $table.find('thead th').each(function (index) {
            if ($(this).hasClass('no-hide') || index === totalCols - 1) {
                // Columna no-hide o última columna: prioridad 1 (siempre visible)
                $(this).attr('data-priority', 1).addClass('all');
            } else {
                // Otras columnas: prioridad alta para ocultar antes
                $(this).attr('data-priority', 100);
            }
        });

        // 2) Inicializar DataTable
        if (!$.fn.DataTable.isDataTable($table)) {
            var table = $table.DataTable({
                responsive: true,
                lengthChange: false,
                paging: false,
                dom: 'rtip',
                info: false,
                ordering: false,
                searching: false,
                autoWidth: false,
                rowGroup: { dataSrc: 2 },
                language: {
                    emptyTable: "Ningún dato disponible en esta tabla",
                    search: "Buscar:",
                    paginate: { first: "Primero", last: "Último", next: "Siguiente", previous: "Anterior" },
                    lengthMenu: "Mostrar _MENU_ registros",
                    zeroRecords: "No se encontraron resultados",
                    infoEmpty: "Mostrando 0 a 0 de 0 registros",
                },
                buttons: [
                    { extend: 'copyHtml5', exportOptions: { columns: ':not(:last-child)' } },
                    { extend: 'excelHtml5', exportOptions: { columns: ':not(:last-child)' } },
                    { extend: 'csvHtml5', exportOptions: { columns: ':not(:last-child)' } },
                    {
                        extend: 'pdfHtml5',
                        exportOptions: { columns: ':not(:last-child)' },
                        orientation: 'landscape',
                        pageSize: 'A4',
                        customize: function (doc) {
                            doc.defaultStyle.fontSize = 10;
                            doc.styles.tableHeader.fontSize = 11;
                            doc.styles.title.fontSize = 14;
                            doc.content[1].margin = [0, 0, 0, 0];
                        }
                    }
                ]
            });

            if (usarControlesExternos) {
                $('#search-input').off('keyup.dynamicDt').on('keyup.dynamicDt', function () { table.search(this.value).draw(); });
                $('#records-per-page').off('change.dynamicDt').on('change.dynamicDt', function () { table.page.len($(this).val()).draw(); });
                $('#export-copy').off('click.dynamicDt').on('click.dynamicDt', function () { table.button('.buttons-copy').trigger(); });
                $('#export-excel').off('click.dynamicDt').on('click.dynamicDt', function () { table.button('.buttons-excel').trigger(); });
                $('#export-csv').off('click.dynamicDt').on('click.dynamicDt', function () { table.button('.buttons-csv').trigger(); });
                $('#export-pdf').off('click.dynamicDt').on('click.dynamicDt', function () { table.button('.buttons-pdf').trigger(); });
            }
        }
    });
}

function reinicializarDataTables() {
    inicializarDataTablesPorSelector();
    sincronizarDetalleAperturaCaja();
    initFormularioCompra();
    inicializarFiltrosMovcompra();

    const formMovventa = document.getElementById('form');
    if (formMovventa && esFormularioMovventa(formMovventa) && document.getElementById('detalles_json')) {
        sincronizarDetallesJsonMovVenta(formMovventa);
    }

    if ($('#calendar').length) {
        const header = document.getElementById("calendarHeader");
        const body = document.getElementById("calendarBody");
        const monthYearLabel = document.getElementById("currentMonthYear");

        const prevWeekBtn = document.getElementById("prevWeek");
        const nextWeekBtn = document.getElementById("nextWeek");
        const prevMonthBtn = document.getElementById("prevMonth");
        const nextMonthBtn = document.getElementById("nextMonth");

        let startDate = new Date();
        startDate.setDate(startDate.getDate() - Math.floor(14 / 4)); // Ajustar inicio

        function parseCalendarDate(value) {
            if (value instanceof Date) {
                return Number.isNaN(value.getTime()) ? null : new Date(value.getTime());
            }

            if (value === null || value === undefined) return null;

            let text = String(value).trim();
            if (!text) return null;

            // Normaliza fechas que pueden llegar como "YYYY-MM-DD HH:mm:ss"
            // o como "DD/MM/YYYY", evitando que el navegador las interprete
            // de forma diferente según el sistema operativo.
            const datePart = text.split(/[ T]/)[0];
            if (/^\d{2}\/\d{2}\/\d{4}$/.test(datePart)) {
                const [day, month, year] = datePart.split('/');
                text = `${year}-${month}-${day}T00:00:00Z`;
            } else if (/^\d{4}-\d{2}-\d{2}$/.test(datePart)) {
                text = `${datePart}T00:00:00Z`;
            } else if (/^\d{4}-\d{2}-\d{2} /.test(text)) {
                text = text.replace(' ', 'T');
            }

            const parsed = new Date(text);
            return Number.isNaN(parsed.getTime()) ? null : parsed;
        }

        function formatDate(date) {
            const parsed = parseCalendarDate(date);
            return parsed ? parsed.toISOString().split('T')[0] : null;
        }
        function formatDatePeru(date) {
            return new Intl.DateTimeFormat('es-PE', {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                timeZone: 'America/Lima' // Asegura que usa la zona horaria de Perú
            }).format(date).split('/').reverse().join('-'); // Convierte a formato YYYY-MM-DD
        }

        function updateMonthYearLabel(date) {
            const options = { month: "long", year: "numeric" };
            monthYearLabel.textContent = date.toLocaleDateString("es-PE", options);
        }


        // Obtener datos de habitaciones y reservas
        const habitacionesDataElement = document.getElementById("habitacionesData");
        const habitaciones = JSON.parse(habitacionesDataElement.textContent || "[]");

        const reservasDataElement = document.getElementById("reservasData");
        const origenesReservasDataElement = document.getElementById("origenesReservasData");
        const buscarReservaInput = document.getElementById("buscarReservaCalendario");
        const resultadoBusqueda = document.getElementById("resultadoBusquedaCalendario");
        const estadoReservaSelect = document.getElementById("estadoReservaCalendario");
        const habitacionReservaSelect = document.getElementById("habitacionReservaCalendario");
        const filtrosCalendario = document.getElementById("filtrosCalendario");
        const timelineCalendario = document.getElementById("timelineCalendario");
        const calendarControls = timelineCalendario?.querySelector(".calendar-controls");
        const calendarStickySentinel = document.getElementById("calendarStickySentinel");
        const calendarWrapper = calendarControls?.closest(".calendar-wrapper");
        let origenesReservas = JSON.parse(origenesReservasDataElement?.textContent || "{}");

        if (calendarControls && calendarStickySentinel && calendarWrapper) {
            let actualizacionStickyPendiente = false;

            const actualizarCabeceraFijaCalendario = () => {
                actualizacionStickyPendiente = false;
                const sentinelRect = calendarStickySentinel.getBoundingClientRect();
                const wrapperRect = calendarWrapper.getBoundingClientRect();
                const alturaCabecera = calendarControls.offsetHeight;
                const debeFijarse = sentinelRect.top <= 0 && wrapperRect.bottom > alturaCabecera;

                calendarControls.classList.toggle("is-stuck", debeFijarse);

                if (debeFijarse) {
                    calendarControls.style.left = `${wrapperRect.left}px`;
                    calendarControls.style.width = `${wrapperRect.width}px`;
                    calendarStickySentinel.style.height = `${alturaCabecera + 10}px`;
                } else {
                    calendarControls.style.removeProperty("left");
                    calendarControls.style.removeProperty("width");
                    calendarStickySentinel.style.height = "1px";
                }
            };

            const solicitarActualizacionSticky = () => {
                if (actualizacionStickyPendiente) return;
                actualizacionStickyPendiente = true;
                requestAnimationFrame(actualizarCabeceraFijaCalendario);
            };

            window.addEventListener("scroll", solicitarActualizacionSticky, true);
            window.addEventListener("resize", solicitarActualizacionSticky);
            solicitarActualizacionSticky();
        }
        let tooltipCalendario = document.getElementById("tooltip-reserva-calendario");

        if (!tooltipCalendario) {
            tooltipCalendario = document.createElement("div");
            tooltipCalendario.id = "tooltip-reserva-calendario";
            tooltipCalendario.className = "tooltip-reserva-calendario";
            document.body.appendChild(tooltipCalendario);
        }
        tooltipCalendario.hidden = true;

        let temporizadorTooltipCalendario = null;
        let ultimoEventoTooltipCalendario = null;

        function ocultarTooltipCalendario() {
            clearTimeout(temporizadorTooltipCalendario);
            temporizadorTooltipCalendario = null;
            tooltipCalendario.classList.remove("is-visible");
            tooltipCalendario.hidden = true;
        }

        window.addEventListener("scroll", ocultarTooltipCalendario, true);
        window.addEventListener("blur", ocultarTooltipCalendario);
        document.addEventListener("visibilitychange", ocultarTooltipCalendario);
        timelineCalendario?.addEventListener("mouseleave", ocultarTooltipCalendario);

        function reinicializarSelectsCalendario() {
            if (!window.M?.FormSelect) return;

            [estadoReservaSelect, habitacionReservaSelect].forEach(select => {
                if (!select) return;
                const instancia = M.FormSelect.getInstance(select);
                if (instancia) instancia.destroy();
                M.FormSelect.init(select);
            });
        }

        function normalizarTextoBusqueda(valor) {
            return String(valor || "")
                .normalize("NFD")
                .replace(/[\u0300-\u036f]/g, "")
                .toLocaleLowerCase("es");
        }

        function normalizarReservasCalendario(items) {
            return items.map(reserva => {
                const fechaInicio = parseCalendarDate(reserva.fechavencimiento);

                // Una reserva sin fecha de inicio no puede dibujarse en el
                // calendario. Se omite únicamente esa reserva para evitar que
                // un dato incompleto deje toda la pantalla en blanco.
                if (!fechaInicio) return null;

                let fechaFin = new Date(fechaInicio);

                const estadoNormalizado = normalizarTextoBusqueda(reserva.estado).toUpperCase();
                const esFinalizado = estadoNormalizado === "FINALIZADO";

                const fechaSalidaRealStr = reserva.fecha_salida_real || reserva.fechacheckout || (esFinalizado ? reserva.fechasalida : null);

                if (esFinalizado && fechaSalidaRealStr) {
                    const fechaReal = parseCalendarDate(fechaSalidaRealStr);
                    if (fechaReal && fechaReal > fechaInicio) {
                        fechaFin = fechaReal;
                    } else {
                        fechaFin.setDate(fechaFin.getDate() + 1);
                    }
                } else {
                    fechaFin.setDate(fechaFin.getDate() + (reserva.dias || 1));
                }

                const origen = reserva.origen || origenesReservas[String(reserva.origen_id)] || null;
                let logoSrc = origen ? `data:image/png;base64,${origen}` : null;

                return {
                    id: reserva.id,
                    tipo: reserva.tipo,
                    habitacion: String(reserva.habitacion_principal || '').trim(),
                    habitacionprincipal_id: reserva.habitacionprincipal_id,

                    inicio: formatDate(fechaInicio),
                    fin: formatDate(fechaFin),
                    nombre: `
                    <div style="display: flex; align-items: center; gap: 8px; width: 100%;">
                        ${logoSrc
                        ? `<img src="${logoSrc}" alt="Logo" 
                                style="width: 24px; height: 24px; border-radius: 50%; object-fit: cover;">`
                        : `<i class="fas fa-check-circle" style="font-size: 24px; color: white;"></i>`}
                        <span class="reserva-nombre" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; flex-grow: 1;">
                            ${reserva.cliente}
                        </span>
                    </div>
                `,
                    tooltipNombre: `${reserva.nrodoc ? `${reserva.nrodoc} - ` : ""}${reserva.cliente || "Sin huésped"}`,
                    textoBusqueda: normalizarTextoBusqueda(`${reserva.cliente || ""} ${reserva.nrodoc || ""}`),
                    origenNombre: reserva.origennombre && String(reserva.origennombre).toLowerCase() !== "undefined"
                        ? reserva.origennombre
                        : "Checkin directo",
                    observacion: reserva.observacion,
                    early_checkin: reserva.early_checkin,
                    late_checkout: reserva.late_checkout,
                    color: reserva.color,
                    total: reserva.total,
                    estado: reserva.estado || "PENDIENTE"
                };
            }).filter(Boolean);
        }

        let reservas = normalizarReservasCalendario(
            JSON.parse(reservasDataElement.textContent || "[]")
        );

        function obtenerReservasVisibles() {
            const termino = normalizarTextoBusqueda(buscarReservaInput?.value).trim();
            const estadoSeleccionado = estadoReservaSelect?.value || "";
            const habitacionSeleccionada = habitacionReservaSelect?.value || "";
            const estadosBuscables = new Set(["PENDIENTE", "EN ATENCION"]);
            const coincidencias = reservas.filter(reserva => {
                const estadoNormalizado = normalizarTextoBusqueda(reserva.estado).toUpperCase();

                if (termino && (!estadosBuscables.has(estadoNormalizado) || !reserva.textoBusqueda.includes(termino))) {
                    return false;
                }
                if (estadoSeleccionado && estadoNormalizado !== estadoSeleccionado) {
                    return false;
                }
                if (habitacionSeleccionada && String(reserva.habitacionprincipal_id) !== habitacionSeleccionada) {
                    return false;
                }

                return true;
            });

            if (resultadoBusqueda) {
                const hayFiltros = termino || estadoSeleccionado || habitacionSeleccionada;
                resultadoBusqueda.textContent = hayFiltros
                    ? (coincidencias.length === 1 ? "1 reserva encontrada" : `${coincidencias.length} reservas encontradas`)
                    : "";
            }

            return coincidencias;
        }



        // **Función para calcular los días según el ancho**
        function getVisibleDays() {
            const containerWidth = body.clientWidth; // Ancho disponible
            const cellWidth = 80; // Ancho fijo de cada celda
            return Math.floor(containerWidth / cellWidth); // Cantidad de días que caben
        }

        function generateCalendar(start) {
            ocultarTooltipCalendario();
            header.innerHTML = "";
            body.innerHTML = "";

            let today = formatDatePeru(new Date());
            updateMonthYearLabel(start);

            let numDays = getVisibleDays(); // Calcular dinámicamente
            let daysArray = [];

            for (let i = 0; i < numDays; i++) {
                let date = new Date(start); // Copia de la fecha base
                date.setDate(start.getDate() + i); // 🔹 Cambiar setUTCDate() por setDate()

                date.setHours(0, 0, 0, 0); // 🔹 Asegurar que no tenga hora

                let formattedDate = formatDate(date);
                daysArray.push(formattedDate);

                let dayColumn = document.createElement("div");
                dayColumn.classList.add("day");
                if (formattedDate === today) {
                    dayColumn.classList.add("today");
                }

                let dayNumber = document.createElement("div");
                dayNumber.classList.add("day-number");
                dayNumber.textContent = date.getDate();

                let dayText = document.createElement("div");
                dayText.classList.add("day-text");
                dayText.textContent = date.toLocaleDateString('es-PE', { weekday: 'short' });

                dayColumn.appendChild(dayNumber);
                dayColumn.appendChild(dayText);
                header.appendChild(dayColumn);
            }

            // Las reservas pueden ocultarse por los filtros visuales, pero sus fechas
            // deben continuar bloqueadas para impedir reservas superpuestas.
            const espaciosOcupados = new Set();
            const estadosQueBloquean = new Set(["PENDIENTE", "EN ATENCION"]);

            reservas.forEach(reserva => {
                const estado = normalizarTextoBusqueda(reserva.estado).toUpperCase();
                if (!estadosQueBloquean.has(estado)) return;

                daysArray.forEach(fecha => {
                    if (fecha >= reserva.inicio && fecha < reserva.fin) {
                        espaciosOcupados.add(`${reserva.habitacion}|${fecha}`);
                    }
                });
            });

            habitaciones.forEach(habitacion => {
                let row = document.createElement("div");
                row.classList.add("calendar-row");
                row.setAttribute("data-room", habitacion.numero.trim());

                let esMantenimiento = habitacion.situacion === "Mantenimiento";

                daysArray.forEach((date, index) => {
                    let formattedAdjustedDate = formatDate(new Date(date));

                    let cell = document.createElement("div");
                    cell.classList.add("calendar-cell");
                    cell.style.position = "relative";

                    if (esMantenimiento) {
                        cell.onclick = function () {
                            mostrarModalMantenimiento(habitacion.numero);
                        };
                    } else {
                        // Solo permitir enlaces en fechas de hoy en adelante
                        const cellDate = new Date(formattedAdjustedDate);
                        cellDate.setHours(0, 0, 0, 0);

                        const todayDate = new Date();
                        todayDate.setHours(0, 0, 0, 0);

                        // Calcular la diferencia en días
                        const diffInMs = cellDate.getTime() - todayDate.getTime();
                        const diffInDays = diffInMs / (1000 * 60 * 60 * 24);

                        // Si la fecha es hoy, en el futuro o 1 día antes
                        const espacioOcupado = espaciosOcupados.has(`${habitacion.numero.trim()}|${formattedAdjustedDate}`);

                        if (diffInDays >= -1 && !espacioOcupado) {
                            let link = document.createElement("a");
                            link.href = `/movreserva/create?fecha=${formattedAdjustedDate}&habitacion=${habitacion.id}`;
                            link.classList.add("btn-cargar-crud", "waves-effect", "waves-light");

                            link.setAttribute("data-icono", "fa-solid fa-plus left");
                            link.setAttribute("data-titulo", "Nueva reserva");
                            link.setAttribute("aria-label", `Nueva reserva para la habitación ${habitacion.numero} el ${formattedAdjustedDate}`);
                            cell.classList.add("calendar-cell-disponible");

                            link.style.position = "absolute";
                            link.style.top = "0";
                            link.style.left = "0";
                            link.style.right = "0";
                            link.style.bottom = "0";
                            link.style.zIndex = "1";

                            cell.appendChild(link);
                        }

                    }

                    row.appendChild(cell);
                });

                body.appendChild(row);
            });


            // 📌 Ajuste en la inserción de reservas
            const habitacionIndexPorNumero = new Map(
                habitaciones.map((habitacion, index) => [habitacion.numero.trim(), index])
            );
            const roomRows = body.querySelectorAll(".calendar-row");
            const visibleStartDate = new Date(`${daysArray[0]}T00:00:00`);
            const visibleEndDate = new Date(`${daysArray[daysArray.length - 1]}T23:59:59`);

            const reservasVisibles = obtenerReservasVisibles();

            reservasVisibles.forEach(reserva => {
                const reservaInicio = new Date(`${reserva.inicio}T00:00:00`);
                const reservaFin = new Date(`${reserva.fin}T23:59:59`);
                if (reservaFin < visibleStartDate || reservaInicio > visibleEndDate) {
                    return;
                }

                let habitacionIndex = habitacionIndexPorNumero.get(reserva.habitacion);

                if (habitacionIndex === undefined || habitacionIndex >= roomRows.length) {
                    return;
                }

                let roomRow = roomRows[habitacionIndex];
                let startIndex = daysArray.indexOf(formatDate(new Date(reserva.inicio)));
                let endIndex = daysArray.indexOf(formatDate(new Date(reserva.fin)));

                if (startIndex === 0 && endIndex === 0) return;

                let adjustedStartIndex = startIndex < 0 ? 0 : startIndex;
                let adjustedEndIndex = endIndex < 0 ? numDays - 1 : Math.min(numDays - 1, endIndex);

                if (adjustedStartIndex > adjustedEndIndex) return;

                let reservaElement = document.createElement("div");
                reservaElement.classList.add("reserva");
                reservaElement.setAttribute("data-id", reserva.id);
                reservaElement.setAttribute("data-estado", reserva.estado);
                reservaElement.setAttribute("data-habitacionprincipal-id", reserva.habitacionprincipal_id);
                reservaElement.innerHTML = reserva.nombre;
                reservaElement.style.backgroundColor = reserva.color;
                reservaElement.style.position = "absolute";
                reservaElement.style.zIndex = "2";


                let totalDays = adjustedEndIndex - adjustedStartIndex + 1;
                let widthPx = totalDays * 80;

                // Verifica si tiene early o late
                let early = reserva.early_checkin > 0;
                let late = reserva.late_checkout > 0;

                // Ajustar el ancho
                if (!early && startIndex >= 0) widthPx -= 40;
                if (!late && endIndex >= 0 && endIndex < numDays) widthPx -= 40;

                reservaElement.style.width = `${widthPx}px`;

                // Ajustar la posición inicial
                if (startIndex >= 0) {
                    reservaElement.style.left = early ? "0" : "50%";
                } else {
                    reservaElement.style.left = "0";
                }

                let startCell = roomRow.children[adjustedStartIndex];
                if (startCell) {
                    startCell.style.position = "relative";
                    startCell.appendChild(reservaElement);
                }

                reservaElement.style.display = "block";
            });






            document.querySelectorAll(".reserva").forEach(reserva => {
                // 🎯 Tooltip al pasar el mouse
                reserva.addEventListener("mouseenter", function (event) {
                    ultimoEventoTooltipCalendario = event;
                    const reservaId = this.getAttribute("data-id");
                    const reservaData = reservas.find(r => r.id == reservaId);

                    if (!reservaData) return;

                    // Construir contenido del tooltip
                    tooltipCalendario.innerHTML = `
                        <strong>${reservaData.tooltipNombre}</strong><br>
                        🕒 ${reservaData.inicio} - ${reservaData.fin}<br>
                        🌍 ${reservaData.origenNombre}<br>
                        💵 ${reservaData.total ? reservaData.total + " S/." : "Sin precio"}<br>
                        📝 ${reservaData.observacion ? reservaData.observacion : 'Sin observaciones'}
                        `;

                    clearTimeout(temporizadorTooltipCalendario);
                    temporizadorTooltipCalendario = setTimeout(() => {
                        if (this.isConnected && this.matches(":hover")) {
                            tooltipCalendario.hidden = false;
                            positionTooltip(ultimoEventoTooltipCalendario);
                            requestAnimationFrame(() => {
                                tooltipCalendario.classList.add("is-visible");
                            });
                        }
                    }, 250);
                });

                reserva.addEventListener("mousemove", function (event) {
                    ultimoEventoTooltipCalendario = event;
                    if (!tooltipCalendario.hidden) positionTooltip(event);
                });

                reserva.addEventListener("mouseleave", function () {
                    ocultarTooltipCalendario();
                });
                // 🎯 Abrir el modal al hacer clic en la reserva
                reserva.addEventListener("click", function () {
                    ocultarTooltipCalendario();
                    let reservaId = this.getAttribute("data-id");
                    let estado = this.getAttribute("data-estado");
                    let habitacionPrincipalId = this.getAttribute("data-habitacionprincipal-id");

                    let modalId = "";
                    if (estado === "PENDIENTE") {
                        modalId = "#modalPendiente";
                    } else if (estado === "EN ATENCIÓN") {
                        modalId = "#modalAtencion";
                    } else if (estado === "FINALIZADO") {
                        modalId = "#modalFinalizado";
                    }

                    if (modalId) {
                        let modal = document.querySelector(modalId);
                        if (modal) {
                            // 🔄 Actualizar enlaces de acciones
                            modal.querySelectorAll(".btn-cargar-crud, .btn-pdf, .btn-activar").forEach(btn => {
                                let urlBase = btn.getAttribute("data-url-base");
                                if (urlBase) {
                                    let nuevaUrl;

                                    if (urlBase.includes("movhabitacion/checkout")) {
                                        // Si la URL tiene ?vista=reserva o ?vista=alojamiento, lo dejamos al final
                                        if (urlBase.includes("?vista=reserva") || urlBase.includes("?vista=alojamiento")) {
                                            nuevaUrl = urlBase.replace(":id", habitacionPrincipalId);
                                        } else {
                                            // Si no tiene el query, lo agregamos igual al final
                                            nuevaUrl = `${urlBase.replace(":id", habitacionPrincipalId)}?vista=reserva`;
                                        }
                                    } else if (urlBase.includes("movhabitacion/anular")) {
                                        nuevaUrl = urlBase.replace(":id", habitacionPrincipalId);
                                    } else {
                                        nuevaUrl = urlBase.replace(":id", reservaId);

                                        // Si requiere habitación, agregarla como parámetro
                                        if (btn.getAttribute("data-requiere-habitacion") === "1") {
                                            // Si ya hay un '?', usamos '&', si no, '?'
                                            const joinChar = nuevaUrl.includes("?") ? "&" : "?";
                                            nuevaUrl += `${joinChar}habitacion_id=${habitacionPrincipalId}`;
                                        }
                                    }

                                    btn.setAttribute("href", nuevaUrl);
                                }
                            });



                            // 🔄 Actualizar `action` del formulario de "Anular"
                            modal.querySelectorAll(".anular-form").forEach(form => {
                                let urlBase = form.querySelector(".btn-actualizar-url").getAttribute("data-url-base");
                                if (urlBase) {
                                    form.setAttribute("action", urlBase.replace(":id", reservaId));
                                }
                            });

                            let instance = M.Modal.getInstance(modal);
                            instance.open();
                        }
                    }
                });

            });



            // Función para posicionar el tooltip arriba del mouse y dentro de la pantalla
            function positionTooltip(event) {
                let tooltipHeight = tooltipCalendario.offsetHeight;
                let tooltipWidth = tooltipCalendario.offsetWidth;
                let offsetY = 15; // Separación desde el mouse hacia arriba
                let x = event.pageX - tooltipWidth / 2;
                let y = event.pageY - tooltipHeight - offsetY;

                // Evitar que el tooltip se salga de la pantalla
                if (x < 10) x = 10; // No salir por la izquierda
                if (x + tooltipWidth > window.innerWidth - 10) x = window.innerWidth - tooltipWidth - 10; // No salir por la derecha
                if (y < 10) y = event.pageY + offsetY; // Si se sale por arriba, lo movemos abajo

                tooltipCalendario.style.left = `${x}px`;
                tooltipCalendario.style.top = `${y}px`;
            }

        }


        // **Actualizar calendario dinámicamente al cambiar de tamaño**
        window.addEventListener("resize", () => {
            generateCalendar(startDate);
        });

        if (buscarReservaInput) {
            buscarReservaInput.addEventListener("input", () => generateCalendar(startDate));
        }

        [estadoReservaSelect, habitacionReservaSelect].forEach(control => {
            control?.addEventListener("change", () => generateCalendar(startDate));
        });

        document.getElementById("btn-filtro-Calendario")?.addEventListener("click", function () {
            const mostrar = filtrosCalendario?.style.display === "none";
            if (!filtrosCalendario) return;

            filtrosCalendario.style.display = mostrar ? "" : "none";
            document.getElementById("mostrar-filtro-Calendario").value = mostrar ? "S" : "N";
            this.querySelector("i")?.classList.toggle("fa-magnifying-glass-plus", !mostrar);
            this.querySelector("i")?.classList.toggle("fa-magnifying-glass-minus", mostrar);
            if (mostrar) {
                reinicializarSelectsCalendario();
                window.M?.updateTextFields?.();
                buscarReservaInput?.focus();
            }
        });

        document.getElementById("btn-expand-compress-Calendario")?.addEventListener("click", function () {
            if (!timelineCalendario) return;

            const expandido = timelineCalendario.classList.toggle("calendar-expanded");
            document.getElementById("expand-compress-rows-Calendario").value = expandido ? "S" : "N";
            this.dataset.tooltip = expandido ? "Contraer filas" : "Expandir filas";
            this.querySelector("i")?.classList.toggle("fa-expand", !expandido);
            this.querySelector("i")?.classList.toggle("fa-compress", expandido);
        });

        document.getElementById("limpiarFiltrosCalendario")?.addEventListener("click", () => {
            if (buscarReservaInput) buscarReservaInput.value = "";
            if (estadoReservaSelect) estadoReservaSelect.value = "";
            if (habitacionReservaSelect) habitacionReservaSelect.value = "";
            reinicializarSelectsCalendario();
            generateCalendar(startDate);
            buscarReservaInput?.focus();
        });

        // **Eventos de navegación**
        prevWeekBtn.addEventListener("click", function () {
            startDate.setDate(startDate.getDate() - 1);
            generateCalendar(startDate);
        });

        nextWeekBtn.addEventListener("click", function () {
            startDate.setDate(startDate.getDate() + 1);
            generateCalendar(startDate);
        });

        prevMonthBtn.addEventListener("click", function () {
            startDate.setDate(startDate.getDate() - 7);
            generateCalendar(startDate);
        });

        nextMonthBtn.addEventListener("click", function () {
            startDate.setDate(startDate.getDate() + 7);
            generateCalendar(startDate);
        });

        // **Generar el calendario inicialmente**
        generateCalendar(startDate);

        async function cargarHistorialReservas() {
            const historyUrl = reservasDataElement.dataset.historyUrl;
            if (!historyUrl) return;

            try {
                const response = await fetch(historyUrl, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }

                const data = await response.json();
                origenesReservas = { ...origenesReservas, ...(data.origenes || {}) };
                reservas = reservas.concat(normalizarReservasCalendario(data.reservas || []));

                const inicioMesActual = new Date();
                inicioMesActual.setDate(1);
                inicioMesActual.setHours(0, 0, 0, 0);

                if (startDate < inicioMesActual && document.getElementById("calendar")) {
                    generateCalendar(startDate);
                }
            } catch (error) {
                console.warn('No se pudo cargar el historial de reservas:', error);
            }
        }

        if ('requestIdleCallback' in window) {
            window.requestIdleCallback(cargarHistorialReservas, { timeout: 2000 });
        } else {
            setTimeout(cargarHistorialReservas, 0);
        }

    }


    if ($('#chips_container').length) {

        var hiddenInput = document.getElementById("chips_hidden");
        var valorInicial = hiddenInput.value;
        var chipsData = [];

        try {
            chipsData = JSON.parse(valorInicial);
            if (!Array.isArray(chipsData)) throw new Error('Formato incorrecto');
            chipsData = chipsData.map(item => ({ tag: item.trim() }));
        } catch (e) {
            chipsData = valorInicial ? valorInicial.split(',').map(item => ({ tag: item.trim() })) : [];
        }

        var chipsInstance = M.Chips.init(document.getElementById("chips_container"), {
            data: chipsData,
            placeholder: '+Correo',
            secondaryPlaceholder: '+ Añadir otro',
            onChipAdd: actualizarChipsHidden,
            onChipDelete: actualizarChipsHidden
        });

        function actualizarChipsHidden() {
            let instance = M.Chips.getInstance(document.getElementById("chips_container"));
            let opciones = instance.chipsData.map(chip => chip.tag);
            document.getElementById("chips_hidden").value = opciones.join(',');
        }
    }
    if ($('#chips_celulares').length) {

        var hiddenInput = document.getElementById("chips_celulares_hidden");
        var valorInicial = hiddenInput.value;
        var chipsData = [];

        try {
            chipsData = JSON.parse(valorInicial);
            if (!Array.isArray(chipsData)) throw new Error('Formato incorrecto');
            chipsData = chipsData.map(item => ({ tag: item.trim() }));
        } catch (e) {
            chipsData = valorInicial ? valorInicial.split(',').map(item => ({ tag: item.trim() })) : [];
        }

        var chipsInstance = M.Chips.init(document.getElementById("chips_celulares"), {
            data: chipsData,
            placeholder: '+ Celular',
            secondaryPlaceholder: '+ Añadir otro',
            onChipAdd: actualizarChipsHidden,
            onChipDelete: actualizarChipsHidden
        });

        function actualizarChipsHidden() {
            let instance = M.Chips.getInstance(document.getElementById("chips_celulares"));
            let opciones = instance.chipsData.map(chip => chip.tag);

            // Guardar como JSON stringificado
            document.getElementById("chips_celulares_hidden").value = JSON.stringify(opciones);
        }
    }

    if ($('#tipos_documento_data').length) {
        const rawData = $('#tipos_documento_data').val();
        let tiposDocumentoData = {};

        try {
            tiposDocumentoData = JSON.parse(rawData);
        } catch (error) {
            console.error("Error al parsear tiposDocumentoData", error);
            return;
        }

        const data = Object.values(tiposDocumentoData).map(item => {
            const total = item.totales.PEN ?? 0;
            return {
                category: `${item.nombre}`,
                value: total,
                label: `${item.nombre}: S/${total.toFixed(2)}`
            };
        }).filter(item => item.value > 0);

        am5.ready(function () {
            var root = am5.Root.new("amchart-tipos-documento");

            root.setThemes([am5themes_Animated.new(root)]);

            var chart = root.container.children.push(am5percent.PieChart.new(root, {
                layout: root.verticalLayout
            }));

            var series = chart.series.push(am5percent.PieSeries.new(root, {
                valueField: "value",
                categoryField: "label", // <- Mostramos la etiqueta personalizada
                tooltip: am5.Tooltip.new(root, {
                    labelText: "{category}:"
                })
            }));

            series.data.setAll(data);

            // Leyenda personalizada con valores
            var legend = chart.children.push(am5.Legend.new(root, {
                centerX: am5.percent(50),
                x: am5.percent(50),
                marginTop: 15,
                marginBottom: 15
            }));

            legend.data.setAll(series.dataItems);

            series.appear(1000, 100);
        });
    }
    if ($('#top_habitaciones_descuento_data').length) {
        const rawData = $('#top_habitaciones_descuento_data').val();
        let habitacionesData = {};

        try {
            habitacionesData = JSON.parse(rawData);
        } catch (error) {
            console.error("Error al parsear top_habitaciones_descuento_data", error);
            return;
        }

        // Formatear datos para amCharts
        const data = Object.values(habitacionesData).map(item => ({
            category: `${item.nombre}`,
            value: item.descuento ?? 0,
            label: `${item.nombre}: S/${(item.descuento ?? 0).toFixed(2)}`
        })).filter(item => item.value > 0);

        am5.ready(function () {
            var root = am5.Root.new("top-habitaciones-descuento");

            root.setThemes([am5themes_Animated.new(root)]);

            var chart = root.container.children.push(am5percent.PieChart.new(root, {
                layout: root.verticalLayout
            }));

            var series = chart.series.push(am5percent.PieSeries.new(root, {
                valueField: "value",
                categoryField: "label",
                tooltip: am5.Tooltip.new(root, {
                    labelText: "{category}: S/{value}"
                })
            }));

            series.data.setAll(data);

            // Leyenda personalizada con valores
            var legend = chart.children.push(am5.Legend.new(root, {
                centerX: am5.percent(50),
                x: am5.percent(50),
                marginTop: 15,
                marginBottom: 15
            }));

            legend.data.setAll(series.dataItems);

            series.appear(1000, 100);
        });
    }
    if ($('#top_habitaciones_cortesia_data').length) {
        const rawData = $('#top_habitaciones_cortesia_data').val();
        let habitacionesData = {};

        try {
            habitacionesData = JSON.parse(rawData);
        } catch (error) {
            console.error("Error al parsear top_habitaciones_cortesia_data", error);
            return;
        }

        // Formatear datos para amCharts
        const data = Object.values(habitacionesData).map(item => ({
            category: `Hab. ${item.nombre}`,
            value: item.cortesia ?? 0,
            label: `Hab. ${item.nombre}: ${item.cortesia ?? 0}`
        })).filter(item => item.value > 0);

        am5.ready(function () {
            var root = am5.Root.new("top-habitaciones-cortesia");

            root.setThemes([am5themes_Animated.new(root)]);

            var chart = root.container.children.push(am5percent.PieChart.new(root, {
                layout: root.verticalLayout
            }));

            var series = chart.series.push(am5percent.PieSeries.new(root, {
                valueField: "value",
                categoryField: "label",
                tooltip: am5.Tooltip.new(root, {
                    labelText: "Total de cortesía (PEN): {value}"
                })
            }));

            series.data.setAll(data);

            // Leyenda personalizada con valores
            var legend = chart.children.push(am5.Legend.new(root, {
                centerX: am5.percent(50),
                x: am5.percent(50),
                marginTop: 15,
                marginBottom: 15
            }));

            legend.data.setAll(series.dataItems);

            series.appear(1000, 100);
        });
    }
    if ($('#cortesias_persona_data').length && $('#chartdiv-cortesias-persona').length) {
        am5.ready(function () {
            const rawData = document.getElementById('cortesias_persona_data').value;
            let chartData = [];

            try {
                chartData = JSON.parse(rawData);
            } catch (e) {
                console.error("Error al parsear cortesias_persona_data", e);
                return;
            }

            chartData = chartData.filter(item => item.value > 0);

            var root = am5.Root.new("chartdiv-cortesias-persona");
            root.setThemes([am5themes_Animated.new(root)]);

            var chart = root.container.children.push(am5xy.XYChart.new(root, {
                panX: false,
                panY: false,
                wheelX: "none",
                wheelY: "none",
                paddingLeft: 0
            }));

            var cursor = chart.set("cursor", am5xy.XYCursor.new(root, {}));
            cursor.lineY.set("visible", false);

            var xRenderer = am5xy.AxisRendererX.new(root, {
                minGridDistance: 30,
                minorGridEnabled: true
            });

            var xAxis = chart.xAxes.push(am5xy.CategoryAxis.new(root, {
                maxDeviation: 0,
                categoryField: "name",
                renderer: xRenderer,
                tooltip: am5.Tooltip.new(root, {})
            }));

            xRenderer.grid.template.set("visible", false);

            var yRenderer = am5xy.AxisRendererY.new(root, {});
            var yAxis = chart.yAxes.push(am5xy.ValueAxis.new(root, {
                maxDeviation: 0,
                min: 0,
                extraMax: 0.1,
                renderer: yRenderer
            }));

            yRenderer.grid.template.setAll({
                strokeDasharray: [2, 2]
            });

            var series = chart.series.push(am5xy.ColumnSeries.new(root, {
                name: "Cortesías",
                xAxis: xAxis,
                yAxis: yAxis,
                valueYField: "value",
                sequencedInterpolation: true,
                categoryXField: "name",
                tooltip: am5.Tooltip.new(root, {
                    dy: -25,
                    labelText: "[bold]{name}[/]\nCortesías: {valueY}"
                })
            }));

            series.columns.template.setAll({
                cornerRadiusTL: 5,
                cornerRadiusTR: 5,
                strokeOpacity: 0
            });

            series.columns.template.adapters.add("fill", (fill, target) => {
                return chart.get("colors").getIndex(series.columns.indexOf(target));
            });

            series.columns.template.adapters.add("stroke", (stroke, target) => {
                return chart.get("colors").getIndex(series.columns.indexOf(target));
            });

            xAxis.data.setAll(chartData);
            series.data.setAll(chartData);

            series.appear(1000);
            chart.appear(1000, 100);
        });
    }
    if ($('#cortesias_usuario_data').length && $('#chartdiv-cortesias-usuario').length) {
        am5.ready(function () {
            const rawData = document.getElementById('cortesias_usuario_data').value;
            let chartData = [];

            try {
                chartData = JSON.parse(rawData);
            } catch (e) {
                console.error("Error al parsear cortesias_usuario_data", e);
                return;
            }

            var root = am5.Root.new("chartdiv-cortesias-usuario");
            root.setThemes([am5themes_Animated.new(root)]);

            var chart = root.container.children.push(am5xy.XYChart.new(root, {
                panX: false,
                panY: false,
                wheelX: "none",
                wheelY: "none",
                paddingLeft: 0
            }));

            var cursor = chart.set("cursor", am5xy.XYCursor.new(root, {}));
            cursor.lineY.set("visible", false);

            var xRenderer = am5xy.AxisRendererX.new(root, {
                minGridDistance: 30,
                minorGridEnabled: true
            });

            var xAxis = chart.xAxes.push(am5xy.CategoryAxis.new(root, {
                maxDeviation: 0,
                categoryField: "name",
                renderer: xRenderer,
                tooltip: am5.Tooltip.new(root, {})
            }));

            xRenderer.grid.template.set("visible", false);

            var yRenderer = am5xy.AxisRendererY.new(root, {});
            var yAxis = chart.yAxes.push(am5xy.ValueAxis.new(root, {
                maxDeviation: 0,
                min: 0,
                extraMax: 0.1,
                renderer: yRenderer
            }));

            yRenderer.grid.template.setAll({
                strokeDasharray: [2, 2]
            });

            var series = chart.series.push(am5xy.ColumnSeries.new(root, {
                name: "Cortesías",
                xAxis: xAxis,
                yAxis: yAxis,
                valueYField: "value",
                sequencedInterpolation: true,
                categoryXField: "name",
                tooltip: am5.Tooltip.new(root, {
                    dy: -25,
                    labelText: "[bold]{name}[/]\nCortesías: {valueY}"
                })
            }));

            series.columns.template.setAll({
                cornerRadiusTL: 5,
                cornerRadiusTR: 5,
                strokeOpacity: 0
            });

            series.columns.template.adapters.add("fill", (fill, target) => {
                return chart.get("colors").getIndex(series.columns.indexOf(target));
            });

            series.columns.template.adapters.add("stroke", (stroke, target) => {
                return chart.get("colors").getIndex(series.columns.indexOf(target));
            });

            xAxis.data.setAll(chartData);
            series.data.setAll(chartData);

            series.appear(1000);
            chart.appear(1000, 100);
        });
    }
    if ($('#evolucion_tipos_documento_ventas_data').length && $('#chartdiv-evolucion-tipos-documento').length) {
        am5.ready(function () {
            const rawData = document.getElementById('evolucion_tipos_documento_ventas_data').value;
            let evolucionData = {};

            try {
                evolucionData = JSON.parse(rawData);
            } catch (e) {
                console.error("Error al parsear evolucion_tipos_documento_ventas_data", e);
                return;
            }

            const fechas = Object.keys(evolucionData);
            const chartData = fechas.map(fecha => ({
                fecha,
                boleta: evolucionData[fecha][4]?.PEN ?? 0,
                factura: evolucionData[fecha][10]?.PEN ?? 0,
                ticket: evolucionData[fecha][9]?.PEN ?? 0
            }));

            var root = am5.Root.new("chartdiv-evolucion-tipos-documento");
            root.setThemes([am5themes_Animated.new(root)]);

            var chart = root.container.children.push(
                am5xy.XYChart.new(root, {
                    panX: true,
                    panY: true,
                    wheelX: "panX",
                    wheelY: "zoomX",
                    layout: root.verticalLayout,
                    pinchZoomX: true
                })
            );

            var xRenderer = am5xy.AxisRendererX.new(root, { minorGridEnabled: true });
            xRenderer.grid.template.set("location", 0.5);
            xRenderer.labels.template.setAll({ location: 0.5, multiLocation: 0.5 });

            var xAxis = chart.xAxes.push(
                am5xy.CategoryAxis.new(root, {
                    categoryField: "fecha",
                    renderer: xRenderer,
                    tooltip: am5.Tooltip.new(root, {}),
                    snapTooltip: true
                })
            );
            xAxis.data.setAll(chartData);

            var yAxis = chart.yAxes.push(
                am5xy.ValueAxis.new(root, {
                    maxPrecision: 0,
                    renderer: am5xy.AxisRendererY.new(root, { inversed: false })
                })
            );

            // Cursor sin alwaysShow ni positionX
            var cursor = chart.set("cursor", am5xy.XYCursor.new(root, {
                xAxis: xAxis
            }));
            cursor.lineY.set("visible", false);

            function createSeries(name, field, color) {
                var series = chart.series.push(
                    am5xy.LineSeries.new(root, {
                        name: name,
                        xAxis: xAxis,
                        yAxis: yAxis,
                        valueYField: field,
                        categoryXField: "fecha",
                        stroke: am5.color(color),
                        fill: am5.color(color),
                        tooltip: am5.Tooltip.new(root, {
                            pointerOrientation: "horizontal",
                            labelText: "[bold]{name}[/]\n{categoryX}: {valueY}"
                        })
                    })
                );
                series.bullets.push(function () {
                    return am5.Bullet.new(root, {
                        sprite: am5.Circle.new(root, {
                            radius: 5,
                            fill: series.get("fill")
                        })
                    });
                });
                series.set("setStateOnChildren", true);
                series.states.create("hover", {});
                series.mainContainer.set("setStateOnChildren", true);
                series.mainContainer.states.create("hover", {});
                series.strokes.template.states.create("hover", { strokeWidth: 4 });
                series.data.setAll(chartData);
                series.appear(1000);
            }

            createSeries("Boleta de venta (PEN)", "boleta", 0x42a5f5);
            createSeries("Factura de venta (PEN)", "factura", 0x78909c);
            createSeries("Ticket de venta (PEN)", "ticket", 0xec407a);

            chart.set("scrollbarX", am5.Scrollbar.new(root, { orientation: "horizontal", marginBottom: 20 }));

            var legend = chart.children.push(
                am5.Legend.new(root, { centerX: am5.p50, x: am5.p50 })
            );
            legend.itemContainers.template.states.create("hover", {});
            legend.itemContainers.template.events.on("pointerover", function (e) {
                e.target.dataItem.dataContext.hover();
            });
            legend.itemContainers.template.events.on("pointerout", function (e) {
                e.target.dataItem.dataContext.unhover();
            });
            legend.data.setAll(chart.series.values);

            // Elimina los eventos que forzaban el tooltip al final
            // chart.plotContainer.events.on("pointerout", function () {
            //     cursor.set("positionX", 1)
            // });
            // chart.plotContainer.events.on("pointerover", function () {
            //     cursor.set("positionX", undefined)
            // });

            chart.appear(1000, 100);
        });
    }
    if ($('#evolucion_ventas_data').length && $('#chartdiv-evolucion-ventas').length) {
        am5.ready(function () {
            const rawData = document.getElementById('evolucion_ventas_data').value;
            let evolucionVentas = {};

            try {
                evolucionVentas = JSON.parse(rawData);
            } catch (e) {
                console.error("Error al parsear evolucion_ventas_data", e);
                return;
            }

            // Preparar datos para amCharts
            const fechas = Object.keys(evolucionVentas);
            const chartData = fechas.map(fecha => ({
                fecha,
                total: evolucionVentas[fecha].total ?? 0,
                credito: evolucionVentas[fecha].credito ?? 0,
                anuladas: evolucionVentas[fecha].anuladas ?? 0
            }));

            var root = am5.Root.new("chartdiv-evolucion-ventas");
            root.setThemes([am5themes_Animated.new(root)]);

            var chart = root.container.children.push(
                am5xy.XYChart.new(root, {
                    panX: true,
                    panY: true,
                    wheelX: "panX",
                    wheelY: "zoomX",
                    layout: root.verticalLayout,
                    pinchZoomX: true
                })
            );

            var xRenderer = am5xy.AxisRendererX.new(root, { minorGridEnabled: true });
            xRenderer.grid.template.set("location", 0.5);
            xRenderer.labels.template.setAll({ location: 0.5, multiLocation: 0.5 });

            var xAxis = chart.xAxes.push(
                am5xy.CategoryAxis.new(root, {
                    categoryField: "fecha",
                    renderer: xRenderer,
                    tooltip: am5.Tooltip.new(root, {}),
                    snapTooltip: true
                })
            );
            xAxis.data.setAll(chartData);

            var yAxis = chart.yAxes.push(
                am5xy.ValueAxis.new(root, {
                    maxPrecision: 0,
                    renderer: am5xy.AxisRendererY.new(root, { inversed: false })
                })
            );

            var cursor = chart.set("cursor", am5xy.XYCursor.new(root, {
                xAxis: xAxis
            }));
            cursor.lineY.set("visible", false);

            function createSeries(name, field, color) {
                var series = chart.series.push(
                    am5xy.LineSeries.new(root, {
                        name: name,
                        xAxis: xAxis,
                        yAxis: yAxis,
                        valueYField: field,
                        categoryXField: "fecha",
                        stroke: am5.color(color),
                        fill: am5.color(color),
                        tooltip: am5.Tooltip.new(root, {
                            pointerOrientation: "horizontal",
                            labelText: "[bold]{name}[/]\n{categoryX}: {valueY}"
                        })
                    })
                );
                series.bullets.push(function () {
                    return am5.Bullet.new(root, {
                        sprite: am5.Circle.new(root, {
                            radius: 5,
                            fill: series.get("fill")
                        })
                    });
                });
                series.set("setStateOnChildren", true);
                series.states.create("hover", {});
                series.mainContainer.set("setStateOnChildren", true);
                series.mainContainer.states.create("hover", {});
                series.strokes.template.states.create("hover", { strokeWidth: 4 });
                series.data.setAll(chartData);
                series.appear(1000);
            }

            createSeries("Total de ventas (PEN)", "total", 0x8e24aa);
            createSeries("Total de ventas al crédito (PEN)", "credito", 0xa5d6a7);
            createSeries("Total de ventas anuladas (PEN)", "anuladas", 0xffeb3b);

            chart.set("scrollbarX", am5.Scrollbar.new(root, { orientation: "horizontal", marginBottom: 20 }));

            var legend = chart.children.push(
                am5.Legend.new(root, { centerX: am5.p50, x: am5.p50 })
            );
            legend.itemContainers.template.states.create("hover", {});
            legend.itemContainers.template.events.on("pointerover", function (e) {
                e.target.dataItem.dataContext.hover();
            });
            legend.itemContainers.template.events.on("pointerout", function (e) {
                e.target.dataItem.dataContext.unhover();
            });
            legend.data.setAll(chart.series.values);

            chart.appear(1000, 100);
        });
    }

    if ($('#ingresos_vs_egresos_data').length) {
        var root = am5.Root.new("amchart-ingresos-egresos");

        root.setThemes([
            am5themes_Animated.new(root)
        ]);

        var chart = root.container.children.push(am5percent.PieChart.new(root, {
            layout: root.verticalLayout,
            innerRadius: am5.percent(50)
        }));

        var series = chart.series.push(am5percent.PieSeries.new(root, {
            name: "Series",
            valueField: "value",
            categoryField: "category"
        }));

        series.slices.template.setAll({
            cornerRadius: 10,
            draggable: false,
            tooltipText: "{category}: {value}"
        });

        // Datos que serán insertados desde el input hidden
        const rawData = document.getElementById('ingresos_vs_egresos_data').value;
        let parsedData = {};

        try {
            parsedData = JSON.parse(rawData);
        } catch (e) {
            console.error("Error al parsear ingresos_vs_egresos_data", e);
            return;
        }

        const chartData = Object.entries(parsedData).map(([category, value]) => ({
            category,
            value
        }));

        series.data.setAll(chartData);

        // Branding legal
        chart.children.push(am5.Label.new(root, {
            text: "HotelHub | Garzasoft\nIngresos vs egresos",
            fontSize: 12,
            centerX: am5.p100,
            x: am5.p100,
            centerY: am5.percent(100),
            y: am5.percent(100),
            textAlign: "end",
            paddingTop: 20
        }));

        chart.appear(1000, 100);
        series.appear(1000, 100);
    }
    if ($('#egresos_metodo_pago_data').length) {
        am5.ready(function () {
            const rawData = document.getElementById('egresos_metodo_pago_data').value;
            let parsedData = {};

            try {
                parsedData = JSON.parse(rawData);
            } catch (e) {
                console.error("Error al parsear egresos_metodo_pago_data", e);
                return;
            }
            const colorMap = {
                "Efectivo": "#F44336",           // red
                "Tarjeta": "#2196F3",            // blue
                "Transferencia": "#9C27B0",      // purple
                "Depósito": "#FF9800",           // orange
                "Billetera digital": "#E91E63"   // pink
            };

            const iconMap = {
                "Efectivo": "/img/money-bill-wave-solid.png",
                "Tarjeta": "/img/credit-card-solid.png",
                "Transferencia": "/img/comments-dollar-solid.png",
                "Depósito": "/img/piggy-bank-solid.png",
                "Billetera digital": "/img/wallet-solid.png"
            };

            const chartData = Object.entries(parsedData).map(([name, value]) => ({
                name,
                steps: value,
                pictureSettings: {
                    src: iconMap[name] || iconMap["Efectivo"]
                },
                columnFill: am5.color(colorMap[name] || "#cccccc")
            }));

            const root = am5.Root.new("chart-egresos-metodo-pago");

            root.setThemes([am5themes_Animated.new(root)]);

            const chart = root.container.children.push(
                am5xy.XYChart.new(root, {
                    panX: false,
                    panY: false,
                    wheelX: "none",
                    wheelY: "none",
                    paddingBottom: 50,
                    paddingTop: 40,
                    paddingLeft: 0,
                    paddingRight: 0
                })
            );

            const xRenderer = am5xy.AxisRendererX.new(root, {
                minGridDistance: 60,
                minorGridEnabled: true
            });
            xRenderer.grid.template.set("visible", false);

            const xAxis = chart.xAxes.push(am5xy.CategoryAxis.new(root, {
                categoryField: "name",
                paddingTop: 40,
                renderer: xRenderer
            }));

            const yRenderer = am5xy.AxisRendererY.new(root, {});
            yRenderer.grid.template.set("strokeDasharray", [3]);

            const yAxis = chart.yAxes.push(am5xy.ValueAxis.new(root, {
                min: 0,
                renderer: yRenderer
            }));

            const series = chart.series.push(am5xy.ColumnSeries.new(root, {
                name: "Egresos",
                xAxis: xAxis,
                yAxis: yAxis,
                valueYField: "steps",
                categoryXField: "name",
                sequencedInterpolation: true,
                calculateAggregates: true,
                maskBullets: false,
                tooltip: am5.Tooltip.new(root, {
                    dy: -30,
                    pointerOrientation: "vertical",
                    labelText: "{valueY}"
                })
            }));

            series.columns.template.setAll({
                strokeOpacity: 0,
                cornerRadiusBR: 10,
                cornerRadiusTR: 10,
                cornerRadiusBL: 10,
                cornerRadiusTL: 10,
                maxWidth: 50,
                fillOpacity: 0.8
            });
            series.columns.template.adapters.add("fill", (fill, target) => {
                return target.dataItem.dataContext.columnFill;
            });
            series.columns.template.adapters.add("stroke", (stroke, target) => {
                return target.dataItem.dataContext.columnFill;
            });

            const circleTemplate = am5.Template.new({});

            series.bullets.push(function (root, series, dataItem) {
                const bulletContainer = am5.Container.new(root, {});

                const circle = bulletContainer.children.push(am5.Circle.new(root, {
                    radius: 24,
                    fill: am5.color(0xcccccc) // default
                }, circleTemplate));

                circle.adapters.add("fill", function (fill, target) {
                    return target.dataItem.dataContext.columnFill;
                });

                const maskCircle = bulletContainer.children.push(am5.Circle.new(root, {
                    radius: 18
                }));

                const imageContainer = bulletContainer.children.push(am5.Container.new(root, {
                    mask: maskCircle
                }));

                imageContainer.children.push(am5.Picture.new(root, {
                    templateField: "pictureSettings",
                    centerX: am5.p50,
                    centerY: am5.p50,
                    width: 40,
                    height: 40
                }));

                return am5.Bullet.new(root, {
                    locationY: 0,
                    sprite: bulletContainer
                });
            });

            let currentlyHovered;

            series.columns.template.events.on("pointerover", function (e) {
                handleHover(e.target.dataItem);
            });

            series.columns.template.events.on("pointerout", function (e) {
                handleOut();
            });

            function handleHover(dataItem) {
                if (dataItem && currentlyHovered != dataItem) {
                    handleOut();
                    currentlyHovered = dataItem;
                    const bullet = dataItem.bullets[0];
                    bullet.animate({
                        key: "locationY",
                        to: 1,
                        duration: 600,
                        easing: am5.ease.out(am5.ease.cubic)
                    });
                }
            }

            function handleOut() {
                if (currentlyHovered) {
                    const bullet = currentlyHovered.bullets[0];
                    bullet.animate({
                        key: "locationY",
                        to: 0,
                        duration: 600,
                        easing: am5.ease.out(am5.ease.cubic)
                    });
                }
            }

            series.set("heatRules", [
                {
                    dataField: "valueY",
                    min: am5.color(0xe5dc36),
                    max: am5.color(0xf44336),
                    target: series.columns.template,
                    key: "fill"
                },
                {
                    dataField: "valueY",
                    min: am5.color(0xe5dc36),
                    max: am5.color(0xf44336),
                    target: circleTemplate,
                    key: "fill"
                }
            ]);

            series.data.setAll(chartData);
            xAxis.data.setAll(chartData);

            const cursor = chart.set("cursor", am5xy.XYCursor.new(root, {}));
            cursor.lineX.set("visible", false);
            cursor.lineY.set("visible", false);

            cursor.events.on("cursormoved", function () {
                const dataItem = series.get("tooltip").dataItem;
                if (dataItem) {
                    handleHover(dataItem);
                } else {
                    handleOut();
                }
            });

            series.appear();
            chart.appear(1000, 100);
        });
    }
    if ($('#egresos_concepto_pago_data').length) {
        am5.ready(function () {
            const rawData = document.getElementById('egresos_concepto_pago_data').value;
            let parsedData = {};

            try {
                parsedData = JSON.parse(rawData);
            } catch (e) {
                console.error("Error al parsear egresos_concepto_pago_data", e);
                return;
            }

            const chartData = Object.entries(parsedData).map(([name, value]) => ({
                country: name,
                value: value
            }));

            var root = am5.Root.new("chart-egresos-concepto-pago");
            var myTheme = am5.Theme.new(root);
            myTheme.rule("Grid", ["base"]).setAll({ strokeOpacity: 0.1 });

            root.setThemes([am5themes_Animated.new(root), myTheme]);

            var chart = root.container.children.push(
                am5xy.XYChart.new(root, {
                    panX: false,
                    panY: false,
                    wheelX: "none",
                    wheelY: "none",
                    paddingLeft: 0
                })
            );

            var yRenderer = am5xy.AxisRendererY.new(root, {
                minGridDistance: 30,
                minorGridEnabled: true
            });
            yRenderer.grid.template.set("location", 1);

            var yAxis = chart.yAxes.push(
                am5xy.CategoryAxis.new(root, {
                    maxDeviation: 0,
                    categoryField: "country",
                    renderer: yRenderer
                })
            );

            var xAxis = chart.xAxes.push(
                am5xy.ValueAxis.new(root, {
                    maxDeviation: 0,
                    min: 0,
                    renderer: am5xy.AxisRendererX.new(root, {
                        visible: true,
                        strokeOpacity: 0.1,
                        minGridDistance: 80
                    })
                })
            );

            var series = chart.series.push(
                am5xy.ColumnSeries.new(root, {
                    name: "Egresos por concepto",
                    xAxis: xAxis,
                    yAxis: yAxis,
                    valueXField: "value",
                    sequencedInterpolation: true,
                    categoryYField: "country",
                    tooltip: am5.Tooltip.new(root, {
                        labelText: "{categoryY}: {valueX}"
                    })
                })
            );

            var columnTemplate = series.columns.template;
            columnTemplate.setAll({
                draggable: true,
                cursorOverStyle: "pointer",
                tooltipText: "Arrastra para reordenar",
                cornerRadiusBR: 10,
                cornerRadiusTR: 10,
                strokeOpacity: 0
            });
            columnTemplate.adapters.add("fill", (fill, target) => {
                return chart.get("colors").getIndex(series.columns.indexOf(target));
            });
            columnTemplate.adapters.add("stroke", (stroke, target) => {
                return chart.get("colors").getIndex(series.columns.indexOf(target));
            });

            columnTemplate.events.on("dragstop", () => {
                sortCategoryAxis();
            });

            function getSeriesItem(category) {
                for (var i = 0; i < series.dataItems.length; i++) {
                    var dataItem = series.dataItems[i];
                    if (dataItem.get("categoryY") == category) {
                        return dataItem;
                    }
                }
            }
            function sortCategoryAxis() {
                series.dataItems.sort(function (x, y) {
                    return y.get("graphics").y() - x.get("graphics").y();
                });
                var easing = am5.ease.out(am5.ease.cubic);
                am5.array.each(yAxis.dataItems, function (dataItem) {
                    var seriesDataItem = getSeriesItem(dataItem.get("category"));
                    if (seriesDataItem) {
                        var index = series.dataItems.indexOf(seriesDataItem);
                        var column = seriesDataItem.get("graphics");
                        var fy = yRenderer.positionToCoordinate(yAxis.indexToPosition(index)) - column.height() / 2;
                        if (index != dataItem.get("index")) {
                            dataItem.set("index", index);
                            var x = column.x();
                            var y = column.y();
                            column.set("dy", -(fy - y));
                            column.set("dx", x);
                            column.animate({ key: "dy", to: 0, duration: 600, easing: easing });
                            column.animate({ key: "dx", to: 0, duration: 600, easing: easing });
                        } else {
                            column.animate({ key: "y", to: fy, duration: 600, easing: easing });
                            column.animate({ key: "x", to: 0, duration: 600, easing: easing });
                        }
                    }
                });
                yAxis.dataItems.sort(function (x, y) {
                    return x.get("index") - y.get("index");
                });
            }

            yAxis.data.setAll(chartData);
            series.data.setAll(chartData);

            series.appear(1000);
            chart.appear(1000, 100);
        });
    }
    if ($('#ingresos_concepto_pago_data').length) {
        am5.ready(function () {
            const rawData = document.getElementById('ingresos_concepto_pago_data').value;
            let parsedData = {};

            try {
                parsedData = JSON.parse(rawData);
            } catch (e) {
                console.error("Error al parsear ingresos_concepto_pago_data", e);
                return;
            }

            // Formatear datos para amCharts
            const chartData = Object.entries(parsedData).map(([name, value]) => ({
                country: name,
                value: value
            }));

            var root = am5.Root.new("chart-ingresos-concepto-pago");
            var myTheme = am5.Theme.new(root);
            myTheme.rule("Grid", ["base"]).setAll({ strokeOpacity: 0.1 });

            root.setThemes([am5themes_Animated.new(root), myTheme]);

            var chart = root.container.children.push(
                am5xy.XYChart.new(root, {
                    panX: false,
                    panY: false,
                    wheelX: "none",
                    wheelY: "none",
                    paddingLeft: 0
                })
            );

            var yRenderer = am5xy.AxisRendererY.new(root, {
                minGridDistance: 30,
                minorGridEnabled: true
            });
            yRenderer.grid.template.set("location", 1);

            var yAxis = chart.yAxes.push(
                am5xy.CategoryAxis.new(root, {
                    maxDeviation: 0,
                    categoryField: "country",
                    renderer: yRenderer
                })
            );

            var xAxis = chart.xAxes.push(
                am5xy.ValueAxis.new(root, {
                    maxDeviation: 0,
                    min: 0,
                    renderer: am5xy.AxisRendererX.new(root, {
                        visible: true,
                        strokeOpacity: 0.1,
                        minGridDistance: 80
                    })
                })
            );

            var series = chart.series.push(
                am5xy.ColumnSeries.new(root, {
                    name: "Ingresos por concepto",
                    xAxis: xAxis,
                    yAxis: yAxis,
                    valueXField: "value",
                    sequencedInterpolation: true,
                    categoryYField: "country",
                    tooltip: am5.Tooltip.new(root, {
                        labelText: "{categoryY}: {valueX}"
                    })
                })
            );

            var columnTemplate = series.columns.template;
            columnTemplate.setAll({
                draggable: true,
                cursorOverStyle: "pointer",
                tooltipText: "Arrastra para reordenar",
                cornerRadiusBR: 10,
                cornerRadiusTR: 10,
                strokeOpacity: 0
            });
            columnTemplate.adapters.add("fill", (fill, target) => {
                return chart.get("colors").getIndex(series.columns.indexOf(target));
            });
            columnTemplate.adapters.add("stroke", (stroke, target) => {
                return chart.get("colors").getIndex(series.columns.indexOf(target));
            });

            columnTemplate.events.on("dragstop", () => {
                sortCategoryAxis();
            });

            function getSeriesItem(category) {
                for (var i = 0; i < series.dataItems.length; i++) {
                    var dataItem = series.dataItems[i];
                    if (dataItem.get("categoryY") == category) {
                        return dataItem;
                    }
                }
            }
            function sortCategoryAxis() {
                series.dataItems.sort(function (x, y) {
                    return y.get("graphics").y() - x.get("graphics").y();
                });
                var easing = am5.ease.out(am5.ease.cubic);
                am5.array.each(yAxis.dataItems, function (dataItem) {
                    var seriesDataItem = getSeriesItem(dataItem.get("category"));
                    if (seriesDataItem) {
                        var index = series.dataItems.indexOf(seriesDataItem);
                        var column = seriesDataItem.get("graphics");
                        var fy = yRenderer.positionToCoordinate(yAxis.indexToPosition(index)) - column.height() / 2;
                        if (index != dataItem.get("index")) {
                            dataItem.set("index", index);
                            var x = column.x();
                            var y = column.y();
                            column.set("dy", -(fy - y));
                            column.set("dx", x);
                            column.animate({ key: "dy", to: 0, duration: 600, easing: easing });
                            column.animate({ key: "dx", to: 0, duration: 600, easing: easing });
                        } else {
                            column.animate({ key: "y", to: fy, duration: 600, easing: easing });
                            column.animate({ key: "x", to: 0, duration: 600, easing: easing });
                        }
                    }
                });
                yAxis.dataItems.sort(function (x, y) {
                    return x.get("index") - y.get("index");
                });
            }

            yAxis.data.setAll(chartData);
            series.data.setAll(chartData);

            series.appear(1000);
            chart.appear(1000, 100);
        });
    }
    if ($('#ingresos_metodo_pago_data').length) {
        am5.ready(function () {
            const rawData = document.getElementById('ingresos_metodo_pago_data').value;
            let parsedData = {};

            try {
                parsedData = JSON.parse(rawData);
            } catch (e) {
                console.error("Error al parsear ingresos_metodo_pago_data", e);
                return;
            }
            const colorMap = {
                "Efectivo": "#4CAF50",           // green
                "Tarjeta": "#2196F3",            // blue
                "Transferencia": "#9C27B0",      // purple
                "Depósito": "#FF9800",           // orange
                "Billetera digital": "#E91E63"   // pink
            };

            const iconMap = {
                "Efectivo": "/img/money-bill-wave-solid.png",
                "Tarjeta": "/img/credit-card-solid.png",
                "Transferencia": "/img/comments-dollar-solid.png",
                "Depósito": "/img/piggy-bank-solid.png",
                "Billetera digital": "/img/wallet-solid.png"
            };

            const chartData = Object.entries(parsedData).map(([name, value]) => ({
                name,
                steps: value,
                pictureSettings: {
                    src: iconMap[name] || iconMap["Efectivo"]
                },
                columnFill: am5.color(colorMap[name] || "#cccccc")
            }));


            const root = am5.Root.new("chart-ingresos-metodo-pago");

            root.setThemes([am5themes_Animated.new(root)]);

            const chart = root.container.children.push(
                am5xy.XYChart.new(root, {
                    panX: false,
                    panY: false,
                    wheelX: "none",
                    wheelY: "none",
                    paddingBottom: 50,
                    paddingTop: 40,
                    paddingLeft: 0,
                    paddingRight: 0
                })
            );

            const xRenderer = am5xy.AxisRendererX.new(root, {
                minGridDistance: 60,
                minorGridEnabled: true
            });
            xRenderer.grid.template.set("visible", false);

            const xAxis = chart.xAxes.push(am5xy.CategoryAxis.new(root, {
                categoryField: "name",
                paddingTop: 40,
                renderer: xRenderer
            }));

            const yRenderer = am5xy.AxisRendererY.new(root, {});
            yRenderer.grid.template.set("strokeDasharray", [3]);

            const yAxis = chart.yAxes.push(am5xy.ValueAxis.new(root, {
                min: 0,
                renderer: yRenderer
            }));

            const series = chart.series.push(am5xy.ColumnSeries.new(root, {
                name: "Ingresos",
                xAxis: xAxis,
                yAxis: yAxis,
                valueYField: "steps",
                categoryXField: "name",
                sequencedInterpolation: true,
                calculateAggregates: true,
                maskBullets: false,
                tooltip: am5.Tooltip.new(root, {
                    dy: -30,
                    pointerOrientation: "vertical",
                    labelText: "{valueY}"
                })
            }));

            series.columns.template.setAll({
                strokeOpacity: 0,
                cornerRadiusBR: 10,
                cornerRadiusTR: 10,
                cornerRadiusBL: 10,
                cornerRadiusTL: 10,
                maxWidth: 50,
                fillOpacity: 0.8
            });
            series.columns.template.adapters.add("fill", (fill, target) => {
                return target.dataItem.dataContext.columnFill;
            });
            series.columns.template.adapters.add("stroke", (stroke, target) => {
                return target.dataItem.dataContext.columnFill;
            });

            const circleTemplate = am5.Template.new({});

            series.bullets.push(function (root, series, dataItem) {
                const bulletContainer = am5.Container.new(root, {});

                const circle = bulletContainer.children.push(am5.Circle.new(root, {
                    radius: 24,
                    fill: am5.color(0xcccccc) // default
                }, circleTemplate));

                // ⬇️ Aquí aplicamos el color personalizado
                circle.adapters.add("fill", function (fill, target) {
                    return target.dataItem.dataContext.columnFill;
                });

                const maskCircle = bulletContainer.children.push(am5.Circle.new(root, {
                    radius: 18
                }));

                const imageContainer = bulletContainer.children.push(am5.Container.new(root, {
                    mask: maskCircle
                }));

                imageContainer.children.push(am5.Picture.new(root, {
                    templateField: "pictureSettings",
                    centerX: am5.p50,
                    centerY: am5.p50,
                    width: 40,
                    height: 40
                }));

                return am5.Bullet.new(root, {
                    locationY: 0,
                    sprite: bulletContainer
                });
            });


            // Efecto hover como la demo
            let currentlyHovered;

            series.columns.template.events.on("pointerover", function (e) {
                handleHover(e.target.dataItem);
            });

            series.columns.template.events.on("pointerout", function (e) {
                handleOut();
            });

            function handleHover(dataItem) {
                if (dataItem && currentlyHovered != dataItem) {
                    handleOut();
                    currentlyHovered = dataItem;
                    const bullet = dataItem.bullets[0];
                    bullet.animate({
                        key: "locationY",
                        to: 1,
                        duration: 600,
                        easing: am5.ease.out(am5.ease.cubic)
                    });
                }
            }

            function handleOut() {
                if (currentlyHovered) {
                    const bullet = currentlyHovered.bullets[0];
                    bullet.animate({
                        key: "locationY",
                        to: 0,
                        duration: 600,
                        easing: am5.ease.out(am5.ease.cubic)
                    });
                }
            }

            // Calor visual por cantidad
            series.set("heatRules", [
                {
                    dataField: "valueY",
                    min: am5.color(0xe5dc36),
                    max: am5.color(0x5faa46),
                    target: series.columns.template,
                    key: "fill"
                },
                {
                    dataField: "valueY",
                    min: am5.color(0xe5dc36),
                    max: am5.color(0x5faa46),
                    target: circleTemplate,
                    key: "fill"
                }
            ]);

            series.data.setAll(chartData);
            xAxis.data.setAll(chartData);

            const cursor = chart.set("cursor", am5xy.XYCursor.new(root, {}));
            cursor.lineX.set("visible", false);
            cursor.lineY.set("visible", false);

            cursor.events.on("cursormoved", function () {
                const dataItem = series.get("tooltip").dataItem;
                if (dataItem) {
                    handleHover(dataItem);
                } else {
                    handleOut();
                }
            });

            series.appear();
            chart.appear(1000, 100);
        });
    }
    if ($('#top_habitaciones_data').length) {
        var root = am5.Root.new("chartdiv");

        root.setThemes([
            am5themes_Animated.new(root)
        ]);

        // Crear el gráfico como donut
        var chart = root.container.children.push(am5percent.PieChart.new(root, {
            layout: root.verticalLayout,
            innerRadius: am5.percent(50)
        }));

        // Crear la serie
        var series = chart.series.push(am5percent.PieSeries.new(root, {
            name: "Top Habitaciones",
            valueField: "value",
            categoryField: "category"
        }));

        series.slices.template.setAll({
            cornerRadius: 10,
            draggable: false,
            tooltipText: "Hab: {category}\nAlojamientos: {value}",
            interactive: true,
            cursorOverStyle: "pointer"
        });

        // Obtener y parsear los datos
        let rawData = document.getElementById('top_habitaciones_data').value;
        let parsedData = [];

        try {
            parsedData = JSON.parse(rawData);
        } catch (e) {
            console.error("Error al parsear top_habitaciones_data", e);
            return;
        }

        const ordenTopHabitaciones = document.getElementById('asc_desc_top_productos')?.value || 'desc';
        const rankingTopHabitaciones = ordenTopHabitaciones === 'asc' ? 'menos alquiladas' : 'mas alquiladas';

        parsedData.sort((a, b) => ordenTopHabitaciones === 'asc'
            ? a.value - b.value
            : b.value - a.value);

        const abrirDetalleTopHabitacion = (dataContext) => {
            if (!dataContext?.id) {
                return;
            }

            abrirModalAlojamientosHabitacion(dataContext.id, dataContext.category);
        };

        // Convertir formato a lo que necesita el gráfico
        const chartData = parsedData.map(item => ({
            id: item.id,
            category: item.name,
            value: item.value
        }));

        series.data.setAll(chartData);

        (series.dataItems || []).forEach(function (dataItem) {
            const slice = dataItem.get("slice");
            if (!slice) {
                return;
            }

            slice.events.on("click", function () {
                abrirDetalleTopHabitacion(dataItem.dataContext);
            });
        });

        // Añadir leyenda personalizada debajo
        var legend = chart.children.push(am5.Legend.new(root, {
            centerX: am5.p50,
            x: am5.p50,
            marginTop: 15,
            layout: root.horizontalLayout
        }));

        legend.itemContainers.template.adapters.add("children", function (_, target) {
            const label = target.findByName("label");
            if (label) {
                label.setAll({
                    text: "{category}: {value}"
                });
            }
            return target.children;
        });

        legend.labels.template.setAll({
            text: "{category}: {value}"
        });

        legend.valueLabels.template.setAll({
            forceHidden: true
        });

        legend.data.setAll(series.dataItems);

        // Etiqueta inferior
        chart.children.push(am5.Label.new(root, {
            text: `HotelHub | Garzasoft\nRanking habitaciones ${rankingTopHabitaciones}`,
            fontSize: 12,
            centerX: am5.p100,
            x: am5.p100,
            centerY: am5.percent(100),
            y: am5.percent(100),
            textAlign: "end",
            paddingTop: 20
        }));

        // Animaciones
        chart.appear(1000, 100);
        series.appear(1000, 100);
    }


    if ($('#top_categorias_data').length) {
        am5.ready(function () {
            const rawData = document.getElementById("top_categorias_data").value;
            const parsedData = JSON.parse(rawData);

            const root = am5.Root.new("chartdiv-categorias");

            root.setThemes([am5themes_Animated.new(root)]);

            root.numberFormatter.setAll({
                numberFormat: "#a",
                bigNumberPrefixes: [
                    { number: 1e6, suffix: "M" },
                    { number: 1e9, suffix: "B" }
                ],
                smallNumberPrefixes: []
            });

            const chart = root.container.children.push(am5xy.XYChart.new(root, {
                panX: true,
                panY: true,
                wheelX: "none",
                wheelY: "none",
                paddingLeft: 0
            }));

            chart.zoomOutButton.set("forceHidden", true);

            const yRenderer = am5xy.AxisRendererY.new(root, {
                minGridDistance: 20,
                inversed: true,
                minorGridEnabled: true
            });

            yRenderer.grid.template.set("visible", false);

            const yAxis = chart.yAxes.push(am5xy.CategoryAxis.new(root, {
                categoryField: "category",
                renderer: yRenderer
            }));

            const xAxis = chart.xAxes.push(am5xy.ValueAxis.new(root, {
                min: 0,
                strictMinMax: true,
                extraMax: 0.1,
                renderer: am5xy.AxisRendererX.new(root, {})
            }));

            const series = chart.series.push(am5xy.ColumnSeries.new(root, {
                xAxis: xAxis,
                yAxis: yAxis,
                valueXField: "value",
                categoryYField: "category"
            }));

            series.columns.template.setAll({
                cornerRadiusBR: 5,
                cornerRadiusTR: 5
            });

            series.columns.template.adapters.add("fill", (fill, target) => {
                return chart.get("colors").getIndex(series.columns.indexOf(target));
            });
            series.columns.template.adapters.add("stroke", (stroke, target) => {
                return chart.get("colors").getIndex(series.columns.indexOf(target));
            });

            series.bullets.push(() => {
                return am5.Bullet.new(root, {
                    locationX: 1,
                    sprite: am5.Label.new(root, {
                        text: "{valueXWorking.formatNumber('#.# a')}",
                        fill: root.interfaceColors.get("alternativeText"),
                        centerX: am5.p100,
                        centerY: am5.p50,
                        populateText: true
                    })
                });
            });

            // Cargar data
            yAxis.data.setAll(parsedData);
            series.data.setAll(parsedData);

            series.appear(1000);
            chart.appear(1000, 100);
        });
    }
    if ($('#respuestas_responsable_data').length && $('#chartdiv-respuestas-responsable').length) {
        am5.ready(function () {
            const rawData = JSON.parse(document.getElementById("respuestas_responsable_data").value);

            const data = rawData.map(item => ({
                responsable: item.responsable || 'Sin responsable',
                respuestas: item.respuestas,
                alojamientos: item.alojamientos
            }));

            // Ajustar tamaño dinámico
            let chartDiv = document.getElementById('chartdiv-respuestas-responsable');
            if (data.length > 8) {
                chartDiv.style.height = '500px';
                chartDiv.style.width = (data.length * 80) + 'px';
            } else {
                chartDiv.style.height = '350px';
                chartDiv.style.width = '100%';
            }

            var root = am5.Root.new("chartdiv-respuestas-responsable");
            root.setThemes([am5themes_Animated.new(root)]);

            var chart = root.container.children.push(am5xy.XYChart.new(root, {
                panX: true,
                panY: true,
                wheelX: "panX",
                wheelY: "zoomX",
                pinchZoomX: true
            }));

            var xRenderer = am5xy.AxisRendererX.new(root, { minGridDistance: 30 });
            xRenderer.labels.template.setAll({
                rotation: -30,
                centerY: am5.p100,
                centerX: am5.p50,
                paddingRight: 10,
                fontSize: 12,
                oversizedBehavior: "truncate"
            });

            var xAxis = chart.xAxes.push(am5xy.CategoryAxis.new(root, {
                categoryField: "responsable",
                renderer: xRenderer,
                tooltip: am5.Tooltip.new(root, {})
            }));

            var yAxis = chart.yAxes.push(am5xy.ValueAxis.new(root, {
                renderer: am5xy.AxisRendererY.new(root, {})
            }));

            // Column series (Respuestas)
            var columnSeries = chart.series.push(am5xy.ColumnSeries.new(root, {
                name: "Respuestas",
                xAxis: xAxis,
                yAxis: yAxis,
                valueYField: "respuestas",
                categoryXField: "responsable"
            }));

            columnSeries.columns.template.setAll({
                strokeWidth: 2,
                stroke: am5.color(0x00897B),
                fill: am5.color(0x00897B),
                fillOpacity: 0.4,
                tooltipText: "{name}: {valueY}" // ✅ Tooltip al pasar el cursor
            });

            // Line series (Alojamientos)
            var lineSeries = chart.series.push(am5xy.LineSeries.new(root, {
                name: "Alojamientos",
                xAxis: xAxis,
                yAxis: yAxis,
                valueYField: "alojamientos",
                categoryXField: "responsable",
                stroke: am5.color(0xFF9F40),
                fill: am5.color(0xFF9F40)
            }));

            lineSeries.strokes.template.setAll({
                strokeWidth: 2,
                tooltipText: "{name}: {valueY}" // ✅ Tooltip al pasar el cursor en la línea
            });

            lineSeries.bullets.push(function () {
                return am5.Bullet.new(root, {
                    sprite: am5.Circle.new(root, {
                        radius: 4,
                        fill: lineSeries.get("fill"),
                        tooltipText: "{name}: {valueY}" // ✅ Tooltip en el punto
                    })
                });
            });

            // Asignar datos
            xAxis.data.setAll(data);
            columnSeries.data.setAll(data);
            lineSeries.data.setAll(data);

            // Leyenda
            var legend = chart.children.push(am5.Legend.new(root, {
                centerX: am5.p50,
                x: am5.p50
            }));
            legend.data.setAll(chart.series.values);

            chart.appear(1000, 100);
        });
    }


    if ($('#alojamientos_piso_data').length) {
        am5.ready(function () {
            const rawData = JSON.parse(document.getElementById("alojamientos_piso_data").value);

            const root = am5.Root.new("chartdiv-alojamientos-piso");
            root.setThemes([am5themes_Animated.new(root)]);

            const chart = root.container.children.push(am5xy.XYChart.new(root, {
                panX: false,
                panY: false,
                wheelX: "none",
                wheelY: "none",
                paddingLeft: 0
            }));

            const cursor = chart.set("cursor", am5xy.XYCursor.new(root, {}));
            cursor.lineY.set("visible", false);

            // Preparar colores únicos para cada tipo
            let tiposUnicos = [];
            rawData.forEach(item => {
                Object.keys(item.data).forEach(tipo => {
                    if (tipo !== 'quantity' && !tiposUnicos.includes(tipo)) {
                        tiposUnicos.push(tipo);
                    }
                });
            });

            // Asignar color a cada tipo
            const colorPalette = am5.ColorSet.new(root, { step: 2 });
            let tipoColorMap = {};
            tiposUnicos.forEach((tipo, idx) => {
                tipoColorMap[tipo] = colorPalette.getIndex(idx);
            });

            const xRenderer = am5xy.AxisRendererX.new(root, {
                minGridDistance: 30,
                minorGridEnabled: true
            });
            xRenderer.labels.template.setAll({ text: "" }); // Oculta los textos superpuestos

            const xAxis = chart.xAxes.push(am5xy.CategoryAxis.new(root, {
                categoryField: "category",
                renderer: xRenderer,
                tooltip: am5.Tooltip.new(root, { labelText: "{realName}" })
            }));

            const yAxis = chart.yAxes.push(am5xy.ValueAxis.new(root, {
                maxDeviation: 0.3,
                renderer: am5xy.AxisRendererY.new(root, {})
            }));

            const yAxis2 = chart.yAxes.push(am5xy.ValueAxis.new(root, {
                maxDeviation: 0.3,
                syncWithAxis: yAxis,
                renderer: am5xy.AxisRendererY.new(root, { opposite: true })
            }));

            const columnSeries = chart.series.push(am5xy.ColumnSeries.new(root, {
                name: "Alojamientos",
                xAxis: xAxis,
                yAxis: yAxis2,
                valueYField: "value",
                categoryXField: "category",
                tooltip: am5.Tooltip.new(root, {
                    labelText: "{provider} {realName}: {valueY}"
                })
            }));

            columnSeries.columns.template.setAll({ fillOpacity: 0.9, strokeOpacity: 0 });
            columnSeries.columns.template.adapters.add("fill", (fill, target) => {
                const tipo = target.dataItem.dataContext.realName;
                return tipoColorMap[tipo] || chart.get("colors").getIndex(columnSeries.columns.indexOf(target));
            });
            columnSeries.columns.template.adapters.add("stroke", (stroke, target) => {
                const tipo = target.dataItem.dataContext.realName;
                return tipoColorMap[tipo] || chart.get("colors").getIndex(columnSeries.columns.indexOf(target));
            });

            const lineSeries = chart.series.push(am5xy.LineSeries.new(root, {
                name: "Totales",
                xAxis: xAxis,
                yAxis: yAxis,
                valueYField: "quantity",
                categoryXField: "category",
                stroke: chart.get("colors").getIndex(13),
                fill: chart.get("colors").getIndex(13),
                tooltip: am5.Tooltip.new(root, { labelText: "{valueY}" })
            }));

            lineSeries.strokes.template.set("strokeWidth", 2);
            lineSeries.bullets.push(() =>
                am5.Bullet.new(root, {
                    locationX: 0.5,
                    sprite: am5.Circle.new(root, {
                        radius: 5,
                        fill: lineSeries.get("fill")
                    })
                })
            );

            let chartData = [];

            rawData.forEach(item => {
                const piso = item.provider;
                const tipos = item.data;
                const tempArray = [];
                let count = 0;

                for (const tipo in tipos) {
                    if (tipo !== 'quantity') {
                        count++;
                        tempArray.push({
                            category: piso + "_" + tipo,
                            realName: tipo,
                            value: tipos[tipo],
                            provider: piso
                        });
                    }
                }

                tempArray.sort((a, b) => a.value - b.value);

                const mid = Math.floor(count / 2);
                tempArray[mid].quantity = tipos.quantity;

                chartData.push(...tempArray);

                const range = xAxis.makeDataItem({});
                xAxis.createAxisRange(range);
                range.set("category", tempArray[0].category);
                range.set("endCategory", tempArray[tempArray.length - 1].category);
                range.get("label").setAll({
                    text: piso,
                    dy: 30,
                    fontWeight: "bold"
                });
                range.get("tick").setAll({ visible: true, strokeOpacity: 1, length: 50 });
                range.get("grid").setAll({ strokeOpacity: 1 });
            });

            const lastRange = xAxis.makeDataItem({});
            xAxis.createAxisRange(lastRange);
            lastRange.set("category", chartData.at(-1).category);
            lastRange.get("tick").setAll({ visible: true, strokeOpacity: 1, length: 50, location: 1 });
            lastRange.get("grid").setAll({ strokeOpacity: 1, location: 1 });

            xAxis.data.setAll(chartData);
            columnSeries.data.setAll(chartData);
            lineSeries.data.setAll(chartData);

            // Leyenda de colores por tipo
            var legend = chart.children.push(am5.Legend.new(root, {
                centerX: am5.percent(50),
                x: am5.percent(50),
                marginTop: 15,
                marginBottom: 15,
                layout: root.horizontalLayout
            }));

            legend.data.setAll(
                tiposUnicos.map((tipo, idx) => ({
                    name: tipo,
                    fill: tipoColorMap[tipo]
                }))
            );


            legend.markers.template.setAll({
                width: 18,
                height: 18,
                strokeWidth: 0
            });
            legend.valueLabels.template.setAll({ forceHidden: true });

            columnSeries.appear(1000);
            chart.appear(1000, 100);
        });
    }
    // Gráfico de Alojamientos por Responsable
    if ($('#alojamientos_responsable_data').length && $('#chartdiv-alojamientos-responsable').length) {
        am5.ready(function () {
            const rawData = JSON.parse(document.getElementById("alojamientos_responsable_data").value);
            // Formatear datos para amCharts
            const data = rawData.map(item => ({
                country: item.responsable || 'Sin responsable',
                value: item.cantidad
            }));

            // Ajustar el ancho dinámicamente si hay muchos responsables
            let chartDiv = document.getElementById('chartdiv-alojamientos-responsable');
            if (data.length > 8) {
                chartDiv.style.height = '500px';
                chartDiv.style.width = (data.length * 80) + 'px';
            } else {
                chartDiv.style.height = '350px';
                chartDiv.style.width = '100%';
            }

            var root = am5.Root.new("chartdiv-alojamientos-responsable");
            root.setThemes([am5themes_Animated.new(root)]);

            var chart = root.container.children.push(am5xy.XYChart.new(root, {
                panX: true,
                panY: true,
                wheelX: "panX",
                wheelY: "zoomX",
                pinchZoomX: true,
                paddingLeft: 0,
                paddingRight: 1
            }));

            var cursor = chart.set("cursor", am5xy.XYCursor.new(root, {}));
            cursor.lineY.set("visible", false);

            var xRenderer = am5xy.AxisRendererX.new(root, {
                minGridDistance: 40,
                minorGridEnabled: true
            });
            xRenderer.labels.template.setAll({
                rotation: -45,
                centerY: am5.p100,
                centerX: am5.p50,
                paddingRight: 10,
                fontSize: 12,
                maxWidth: 120,
                oversizedBehavior: "truncate"
            });
            xRenderer.grid.template.setAll({ location: 1 });

            var xAxis = chart.xAxes.push(am5xy.CategoryAxis.new(root, {
                maxDeviation: 0.3,
                categoryField: "country",
                renderer: xRenderer,
                tooltip: am5.Tooltip.new(root, {})
            }));

            var yRenderer = am5xy.AxisRendererY.new(root, { strokeOpacity: 0.1 });
            var yAxis = chart.yAxes.push(am5xy.ValueAxis.new(root, {
                maxDeviation: 0.3,
                renderer: yRenderer
            }));

            var series = chart.series.push(am5xy.ColumnSeries.new(root, {
                name: "Alojamientos",
                xAxis: xAxis,
                yAxis: yAxis,
                valueYField: "value",
                sequencedInterpolation: true,
                categoryXField: "country",
                tooltip: am5.Tooltip.new(root, {
                    labelText: "{valueY}"
                })
            }));

            series.columns.template.setAll({ cornerRadiusTL: 5, cornerRadiusTR: 5, strokeOpacity: 0 });
            series.columns.template.adapters.add("fill", function (fill, target) {
                return chart.get("colors").getIndex(series.columns.indexOf(target));
            });
            series.columns.template.adapters.add("stroke", function (stroke, target) {
                return chart.get("colors").getIndex(series.columns.indexOf(target));
            });

            xAxis.data.setAll(data);
            series.data.setAll(data);

            series.appear(1000);
            chart.appear(1000, 100);
        });
    }
    if ($('#evolucion_tipos_documento_data').length && $('#chartdiv-evolucion-tipos-documento').length) {
        am5.ready(function () {
            const rawData = JSON.parse(document.getElementById("evolucion_tipos_documento_data").value);

            const myTheme = am5.Theme.new();
            myTheme.rule("AxisLabel", ["minor"]).setAll({ dy: 1 });
            myTheme.rule("Grid", ["minor"]).setAll({ strokeOpacity: 0.08 });

            var root = am5.Root.new("chartdiv-evolucion-tipos-documento");
            root.setThemes([am5themes_Animated.new(root), myTheme]);

            var chart = root.container.children.push(am5xy.XYChart.new(root, {
                panX: false,
                panY: false,
                wheelX: "panX",
                wheelY: "zoomX",
                paddingLeft: 0
            }));

            var cursor = chart.set("cursor", am5xy.XYCursor.new(root, { behavior: "zoomX" }));
            cursor.lineY.set("visible", false);

            var xAxis = chart.xAxes.push(am5xy.DateAxis.new(root, {
                maxDeviation: 0,
                baseInterval: { timeUnit: "day", count: 1 },
                renderer: am5xy.AxisRendererX.new(root, {
                    minorGridEnabled: true,
                    minGridDistance: 80,
                    minorLabelsEnabled: true
                }),
                tooltip: am5.Tooltip.new(root, {})
            }));
            xAxis.set("minorDateFormats", { day: "dd", month: "MM" });

            var yAxis = chart.yAxes.push(am5xy.ValueAxis.new(root, {
                renderer: am5xy.AxisRendererY.new(root, {})
            }));

            // Serie: Ingresos
            var seriesIngresos = chart.series.push(am5xy.LineSeries.new(root, {
                name: "Ingresos",
                xAxis: xAxis,
                yAxis: yAxis,
                valueYField: "ingresos",
                valueXField: "fecha",
                stroke: am5.color(0x43A047),
                fill: am5.color(0x43A047),
                tooltip: am5.Tooltip.new(root, { labelText: "Ingresos: {valueY}" })
            }));
            seriesIngresos.strokes.template.setAll({ strokeWidth: 2 });
            seriesIngresos.bullets.push(function () {
                var bulletCircle = am5.Circle.new(root, { radius: 5, fill: seriesIngresos.get("fill") });
                return am5.Bullet.new(root, { sprite: bulletCircle });
            });

            // Serie: Egresos
            var seriesEgresos = chart.series.push(am5xy.LineSeries.new(root, {
                name: "Egresos",
                xAxis: xAxis,
                yAxis: yAxis,
                valueYField: "egresos",
                valueXField: "fecha",
                stroke: am5.color(0xE53935),
                fill: am5.color(0xE53935),
                tooltip: am5.Tooltip.new(root, { labelText: "Egresos: {valueY}" })
            }));
            seriesEgresos.strokes.template.setAll({ strokeWidth: 2 });
            seriesEgresos.bullets.push(function () {
                var bulletCircle = am5.Circle.new(root, { radius: 5, fill: seriesEgresos.get("fill") });
                return am5.Bullet.new(root, { sprite: bulletCircle });
            });

            // Scrollbar
            chart.set("scrollbarX", am5.Scrollbar.new(root, { orientation: "horizontal" }));

            // Formatear fechas para amCharts
            let chartData = rawData.map(item => ({
                fecha: new Date(item.fecha).getTime(),
                ingresos: item.ingresos,
                egresos: item.egresos
            }));

            seriesIngresos.data.setAll(chartData);
            seriesEgresos.data.setAll(chartData);

            seriesIngresos.appear(1000);
            seriesEgresos.appear(1000);
            chart.appear(1000, 100);
        });
    }
    // Gráfico de Evolución de Alojamientos
    if ($('#evolucion_alojamientos_data').length && $('#chartdiv-evolucion-alojamientos').length) {
        am5.ready(function () {
            const rawData = JSON.parse(document.getElementById("evolucion_alojamientos_data").value);
            const myTheme = am5.Theme.new();
            myTheme.rule("AxisLabel", ["minor"]).setAll({ dy: 1 });
            myTheme.rule("Grid", ["minor"]).setAll({ strokeOpacity: 0.08 });

            var root = am5.Root.new("chartdiv-evolucion-alojamientos");
            root.setThemes([am5themes_Animated.new(root), myTheme]);

            var chart = root.container.children.push(am5xy.XYChart.new(root, {
                panX: false,
                panY: false,
                wheelX: "panX",
                wheelY: "zoomX",
                paddingLeft: 0
            }));

            var cursor = chart.set("cursor", am5xy.XYCursor.new(root, { behavior: "zoomX" }));
            cursor.lineY.set("visible", false);

            var xAxis = chart.xAxes.push(am5xy.DateAxis.new(root, {
                maxDeviation: 0,
                baseInterval: { timeUnit: "day", count: 1 },
                renderer: am5xy.AxisRendererX.new(root, {
                    minorGridEnabled: true,
                    minGridDistance: 80,
                    minorLabelsEnabled: true
                }),
                tooltip: am5.Tooltip.new(root, {})
            }));
            xAxis.set("minorDateFormats", { day: "dd", month: "MM" });

            var yAxis = chart.yAxes.push(am5xy.ValueAxis.new(root, {
                renderer: am5xy.AxisRendererY.new(root, {})
            }));

            // Series: Anulados
            var seriesAnulados = chart.series.push(am5xy.LineSeries.new(root, {
                name: "Anulados",
                xAxis: xAxis,
                yAxis: yAxis,
                valueYField: "anulados",
                valueXField: "fecha",
                stroke: am5.color(0xE53935),
                fill: am5.color(0xE53935),
                tooltip: am5.Tooltip.new(root, { labelText: "Anulados: {valueY}" })
            }));
            seriesAnulados.strokes.template.setAll({ strokeWidth: 2 });
            seriesAnulados.bullets.push(function () {
                var bulletCircle = am5.Circle.new(root, { radius: 5, fill: seriesAnulados.get("fill") });
                return am5.Bullet.new(root, { sprite: bulletCircle });
            });

            // Series: Finalizados
            var seriesFinalizados = chart.series.push(am5xy.LineSeries.new(root, {
                name: "Finalizados",
                xAxis: xAxis,
                yAxis: yAxis,
                valueYField: "finalizados",
                valueXField: "fecha",
                stroke: am5.color(0x43A047),
                fill: am5.color(0x43A047),
                tooltip: am5.Tooltip.new(root, { labelText: "Finalizados: {valueY}" })
            }));
            seriesFinalizados.strokes.template.setAll({ strokeWidth: 2 });
            seriesFinalizados.bullets.push(function () {
                var bulletCircle = am5.Circle.new(root, { radius: 5, fill: seriesFinalizados.get("fill") });
                return am5.Bullet.new(root, { sprite: bulletCircle });
            });

            // Series: Con productos
            var seriesConProductos = chart.series.push(am5xy.LineSeries.new(root, {
                name: "Con productos",
                xAxis: xAxis,
                yAxis: yAxis,
                valueYField: "con_productos",
                valueXField: "fecha",
                stroke: am5.color(0x1E88E5),
                fill: am5.color(0x1E88E5),
                tooltip: am5.Tooltip.new(root, { labelText: "Con productos: {valueY}" })
            }));
            seriesConProductos.strokes.template.setAll({ strokeWidth: 2 });
            seriesConProductos.bullets.push(function () {
                var bulletCircle = am5.Circle.new(root, { radius: 5, fill: seriesConProductos.get("fill") });
                return am5.Bullet.new(root, { sprite: bulletCircle });
            });

            // Scrollbar
            chart.set("scrollbarX", am5.Scrollbar.new(root, { orientation: "horizontal" }));

            // Formatear fechas para amCharts
            let chartData = rawData.map(item => ({
                fecha: new Date(item.fecha).getTime(),
                anulados: item.anulados,
                finalizados: item.finalizados,
                con_productos: item.con_productos
            }));

            seriesAnulados.data.setAll(chartData);
            seriesFinalizados.data.setAll(chartData);
            seriesConProductos.data.setAll(chartData);

            seriesAnulados.appear(1000);
            seriesFinalizados.appear(1000);
            seriesConProductos.appear(1000);
            chart.appear(1000, 100);
        });
    }
    if ($('#descuentos_persona_data').length && $('#chartdiv-descuentos-persona').length) {
        am5.ready(function () {
            const rawData = document.getElementById('descuentos_persona_data').value;
            let descuentosData = {};

            try {
                descuentosData = JSON.parse(rawData);
            } catch (e) {
                console.error("Error al parsear descuentos_persona_data", e);
                return;
            }

            // Convertir el objeto en array para amCharts
            const chartData = Object.entries(descuentosData).map(([persona, valores]) => ({
                name: persona,
                value: Math.round((valores.descuento ?? 0) * 100) / 100,
                subtotal: Math.round((valores.subtotal ?? 0) * 100) / 100
            }));

            var root = am5.Root.new("chartdiv-descuentos-persona");
            root.setThemes([am5themes_Animated.new(root)]);

            var chart = root.container.children.push(am5xy.XYChart.new(root, {
                panX: false,
                panY: false,
                wheelX: "none",
                wheelY: "none",
                paddingLeft: 0
            }));

            var cursor = chart.set("cursor", am5xy.XYCursor.new(root, {}));
            cursor.lineY.set("visible", false);

            var xRenderer = am5xy.AxisRendererX.new(root, {
                minGridDistance: 30,
                minorGridEnabled: true
            });

            var xAxis = chart.xAxes.push(am5xy.CategoryAxis.new(root, {
                maxDeviation: 0,
                categoryField: "name",
                renderer: xRenderer,
                tooltip: am5.Tooltip.new(root, {})
            }));

            xRenderer.grid.template.set("visible", false);

            var yRenderer = am5xy.AxisRendererY.new(root, {});
            var yAxis = chart.yAxes.push(am5xy.ValueAxis.new(root, {
                maxDeviation: 0,
                min: 0,
                extraMax: 0.1,
                renderer: yRenderer
            }));

            yRenderer.grid.template.setAll({
                strokeDasharray: [2, 2]
            });

            var series = chart.series.push(am5xy.ColumnSeries.new(root, {
                name: "Descuento",
                xAxis: xAxis,
                yAxis: yAxis,
                valueYField: "value",
                sequencedInterpolation: true,
                categoryXField: "name",
                tooltip: am5.Tooltip.new(root, {
                    dy: -25,
                    labelText: "[bold]{name}[/]\nDescuento: S/{valueY}\nSubtotal: S/{subtotal}"
                })
            }));

            series.columns.template.setAll({
                cornerRadiusTL: 5,
                cornerRadiusTR: 5,
                strokeOpacity: 0
            });

            series.columns.template.adapters.add("fill", (fill, target) => {
                return chart.get("colors").getIndex(series.columns.indexOf(target));
            });

            series.columns.template.adapters.add("stroke", (stroke, target) => {
                return chart.get("colors").getIndex(series.columns.indexOf(target));
            });

            xAxis.data.setAll(chartData);
            series.data.setAll(chartData);

            series.appear(1000);
            chart.appear(1000, 100);
        });
    }
    if ($('#descuentos_usuario_data').length && $('#chartdiv-descuentos-usuario').length) {
        am5.ready(function () {
            const rawData = document.getElementById('descuentos_usuario_data').value;
            let chartData = [];

            try {
                chartData = JSON.parse(rawData);
            } catch (e) {
                console.error("Error al parsear descuentos_usuario_data", e);
                return;
            }

            var root = am5.Root.new("chartdiv-descuentos-usuario");
            root.setThemes([am5themes_Animated.new(root)]);

            var chart = root.container.children.push(am5xy.XYChart.new(root, {
                panX: false,
                panY: false,
                wheelX: "none",
                wheelY: "none",
                paddingLeft: 0
            }));

            var cursor = chart.set("cursor", am5xy.XYCursor.new(root, {}));
            cursor.lineY.set("visible", false);

            var xRenderer = am5xy.AxisRendererX.new(root, {
                minGridDistance: 30,
                minorGridEnabled: true
            });

            var xAxis = chart.xAxes.push(am5xy.CategoryAxis.new(root, {
                maxDeviation: 0,
                categoryField: "name",
                renderer: xRenderer,
                tooltip: am5.Tooltip.new(root, {})
            }));

            xRenderer.grid.template.set("visible", false);

            var yRenderer = am5xy.AxisRendererY.new(root, {});
            var yAxis = chart.yAxes.push(am5xy.ValueAxis.new(root, {
                maxDeviation: 0,
                min: 0,
                extraMax: 0.1,
                renderer: yRenderer
            }));

            yRenderer.grid.template.setAll({
                strokeDasharray: [2, 2]
            });

            var series = chart.series.push(am5xy.ColumnSeries.new(root, {
                name: "Descuento",
                xAxis: xAxis,
                yAxis: yAxis,
                valueYField: "value",
                sequencedInterpolation: true,
                categoryXField: "name",
                tooltip: am5.Tooltip.new(root, {
                    dy: -25,
                    labelText: "[bold]{name}[/]\nDescuento: S/{valueY}\nSubtotal: S/{subtotal}"
                })
            }));

            series.columns.template.setAll({
                cornerRadiusTL: 5,
                cornerRadiusTR: 5,
                strokeOpacity: 0
            });

            series.columns.template.adapters.add("fill", (fill, target) => {
                return chart.get("colors").getIndex(series.columns.indexOf(target));
            });

            series.columns.template.adapters.add("stroke", (stroke, target) => {
                return chart.get("colors").getIndex(series.columns.indexOf(target));
            });

            xAxis.data.setAll(chartData);
            series.data.setAll(chartData);

            series.appear(1000);
            chart.appear(1000, 100);
        });
    }
    //Encuestas
    // Gráfico para "¿Cómo calificas la calidad de atención de nuestro personal?"
    if ($('#2_data').length) {
        const rawData = $('#2_data').val();
        let data = [];

        try {
            const decodedData = rawData.replace(/&quot;/g, '"');
            data = JSON.parse(decodedData);
        } catch (error) {
            console.error("Error al parsear datos para el gráfico 2", error);
            console.error("Datos crudos:", rawData);
            return;
        }



        if (data && data.respuestas && data.respuestas.length > 0) {
            am5.ready(function () {
                const chartContainer = document.getElementById("det_encuesta_2");
                if (!chartContainer) {
                    console.error("El contenedor del gráfico no se encuentra.");
                    return;
                }

                const root = am5.Root.new("det_encuesta_2");
                root.setThemes([am5themes_Animated.new(root)]);

                const chart = root.container.children.push(am5percent.PieChart.new(root, {
                    layout: root.verticalLayout
                }));

                const series = chart.series.push(am5percent.PieSeries.new(root, {
                    valueField: "value",
                    categoryField: "category",
                    tooltip: am5.Tooltip.new(root, {
                        labelText: "{category} - {value}"
                    })
                }));

                const chartData = data.respuestas.map(item => ({
                    category: item,
                    value: 1
                }));

                series.data.setAll(chartData);

                const legend = chart.children.push(am5.Legend.new(root, {
                    centerX: am5.percent(50),
                    x: am5.percent(50),
                    marginTop: 15,
                    marginBottom: 15
                }));

                legend.data.setAll(series.dataItems);
                series.appear(1000, 100);
            });
        } else {
            console.warn("No hay respuestas para mostrar.");
        }
    }


    // Gráfico para "¿Cómo calificas el ambiente de nuestro hotel?"
    if ($('#3_data').length) {
        const rawData = $('#3_data').val();
        let data = [];

        try {
            const decodedData = rawData.replace(/&quot;/g, '"');
            data = JSON.parse(decodedData);
        } catch (error) {
            console.error("Error al parsear datos para el gráfico 3", error);
            console.error("Datos crudos:", rawData);
            return;
        }



        if (data && data.respuestas && data.respuestas.length > 0) {
            am5.ready(function () {
                const chartContainer = document.getElementById("det_encuesta_3");
                if (!chartContainer) {
                    console.error("El contenedor del gráfico no se encuentra.");
                    return;
                }

                const root = am5.Root.new("det_encuesta_3");
                root.setThemes([am5themes_Animated.new(root)]);

                const chart = root.container.children.push(am5percent.PieChart.new(root, {
                    layout: root.verticalLayout
                }));

                const series = chart.series.push(am5percent.PieSeries.new(root, {
                    valueField: "value",
                    categoryField: "category",
                    tooltip: am5.Tooltip.new(root, {
                        labelText: "{category} - {value}"
                    })
                }));

                const chartData = data.respuestas.map(item => ({
                    category: item,
                    value: 1
                }));

                series.data.setAll(chartData);

                const legend = chart.children.push(am5.Legend.new(root, {
                    centerX: am5.percent(50),
                    x: am5.percent(50),
                    marginTop: 15,
                    marginBottom: 15
                }));

                legend.data.setAll(series.dataItems);
                series.appear(1000, 100);
            });
        } else {
            console.warn("No hay respuestas para mostrar.");
        }
    }

    // Gráfico para "¿Cómo calificas la calidad de nuestro productos?"
    if ($('#5_data').length) {
        const rawData = $('#5_data').val();
        let data = [];

        try {
            const decodedData = rawData.replace(/&quot;/g, '"');
            data = JSON.parse(decodedData);
        } catch (error) {
            console.error("Error al parsear datos para el gráfico 5", error);
            console.error("Datos crudos:", rawData);
            return;
        }



        if (data && data.respuestas && data.respuestas.length > 0) {
            am5.ready(function () {
                const chartContainer = document.getElementById("det_encuesta_5");
                if (!chartContainer) {
                    console.error("El contenedor del gráfico no se encuentra.");
                    return;
                }

                const root = am5.Root.new("det_encuesta_5");
                root.setThemes([am5themes_Animated.new(root)]);

                const chart = root.container.children.push(am5percent.PieChart.new(root, {
                    layout: root.verticalLayout
                }));

                const series = chart.series.push(am5percent.PieSeries.new(root, {
                    valueField: "value",
                    categoryField: "category",
                    tooltip: am5.Tooltip.new(root, {
                        labelText: "{category} - {value}"
                    })
                }));

                const chartData = data.respuestas.map(item => ({
                    category: item,
                    value: 1
                }));

                series.data.setAll(chartData);

                const legend = chart.children.push(am5.Legend.new(root, {
                    centerX: am5.percent(50),
                    x: am5.percent(50),
                    marginTop: 15,
                    marginBottom: 15
                }));

                legend.data.setAll(series.dataItems);
                series.appear(1000, 100);
            });
        } else {
            console.warn("No hay respuestas para mostrar.");
        }
    }

    // Gráfico para "¿Volverías a visitarnos?"
    if ($('#4_data').length) {
        const rawData = $('#4_data').val();
        let data = [];

        try {
            const decodedData = rawData.replace(/&quot;/g, '"');
            data = JSON.parse(decodedData);
        } catch (error) {
            console.error("Error al parsear datos para el gráfico 4", error);
            console.error("Datos crudos:", rawData);
            return;
        }


        if (data && data.respuestas && data.respuestas.length > 0) {
            am5.ready(function () {
                const chartContainer = document.getElementById("det_encuesta_4");
                if (!chartContainer) {
                    console.error("El contenedor del gráfico no se encuentra.");
                    return;
                }

                const root = am5.Root.new("det_encuesta_4");
                root.setThemes([am5themes_Animated.new(root)]);

                const chart = root.container.children.push(am5percent.PieChart.new(root, {
                    layout: root.verticalLayout
                }));

                const series = chart.series.push(am5percent.PieSeries.new(root, {
                    valueField: "value",
                    categoryField: "category",
                    tooltip: am5.Tooltip.new(root, {
                        labelText: "{category} - {value}"
                    })
                }));

                const chartData = data.respuestas.map(item => ({
                    category: item,
                    value: 1
                }));

                series.data.setAll(chartData);

                const legend = chart.children.push(am5.Legend.new(root, {
                    centerX: am5.percent(50),
                    x: am5.percent(50),
                    marginTop: 15,
                    marginBottom: 15
                }));

                legend.data.setAll(series.dataItems);
                series.appear(1000, 100);
            });
        } else {
            console.warn("No hay respuestas para mostrar.");
        }
    }

    // Gráfico para "¿Cómo calificas la limpieza de la habitación?"
    if ($('#6_data').length) {
        am5.ready(function () {
            const rawData = document.getElementById("6_data").value;
            const data = JSON.parse(rawData);

            const chartData = data.respuestas.reduce((acc, val) => {
                acc[val] = (acc[val] || 0) + 1;
                return acc;
            }, {});

            const parsedData = Object.entries(chartData).map(([key, value]) => ({
                category: key,
                value: value
            }));

            const root = am5.Root.new("det_encuesta_6");
            root.setThemes([am5themes_Animated.new(root)]);

            root.numberFormatter.setAll({
                numberFormat: "#a",
                bigNumberPrefixes: [
                    { number: 1e6, suffix: "M" },
                    { number: 1e9, suffix: "B" }
                ],
                smallNumberPrefixes: []
            });

            const chart = root.container.children.push(am5xy.XYChart.new(root, {
                panX: true,
                panY: true,
                wheelX: "none",
                wheelY: "none",
                paddingLeft: 0
            }));

            chart.zoomOutButton.set("forceHidden", true);

            const yRenderer = am5xy.AxisRendererY.new(root, {
                minGridDistance: 20,
                inversed: true,
                minorGridEnabled: true
            });

            yRenderer.grid.template.set("visible", false);

            const yAxis = chart.yAxes.push(am5xy.CategoryAxis.new(root, {
                categoryField: "category",
                renderer: yRenderer
            }));

            const xAxis = chart.xAxes.push(am5xy.ValueAxis.new(root, {
                min: 0,
                strictMinMax: true,
                extraMax: 0.1,
                renderer: am5xy.AxisRendererX.new(root, {})
            }));

            const series = chart.series.push(am5xy.ColumnSeries.new(root, {
                xAxis: xAxis,
                yAxis: yAxis,
                valueXField: "value",
                categoryYField: "category"
            }));

            series.columns.template.setAll({
                cornerRadiusBR: 5,
                cornerRadiusTR: 5
            });

            series.columns.template.adapters.add("fill", (fill, target) => {
                return chart.get("colors").getIndex(series.columns.indexOf(target));
            });
            series.columns.template.adapters.add("stroke", (stroke, target) => {
                return chart.get("colors").getIndex(series.columns.indexOf(target));
            });

            series.bullets.push(() => {
                return am5.Bullet.new(root, {
                    locationX: 1,
                    sprite: am5.Label.new(root, {
                        text: "{valueXWorking.formatNumber('#.# a')}",
                        fill: root.interfaceColors.get("alternativeText"),
                        centerX: am5.p100,
                        centerY: am5.p50,
                        populateText: true
                    })
                });
            });

            yAxis.data.setAll(parsedData);
            series.data.setAll(parsedData);

            series.appear(1000);
            chart.appear(1000, 100);
        });
    }

    // Gráfico para "En una escala del 1 al 10, ¿con qué probabilidad nos recomendarías?"
    if ($('#7_data').length) {
        am5.ready(function () {
            const rawData = document.getElementById("7_data").value;
            const data = JSON.parse(rawData);

            const chartData = data.respuestas.reduce((acc, val) => {
                acc[val] = (acc[val] || 0) + 1;
                return acc;
            }, {});

            const parsedData = Object.entries(chartData).map(([key, value]) => ({
                category: key,
                value: value
            }));

            const root = am5.Root.new("det_encuesta_7");
            root.setThemes([am5themes_Animated.new(root)]);

            root.numberFormatter.setAll({
                numberFormat: "#a",
                bigNumberPrefixes: [
                    { number: 1e6, suffix: "M" },
                    { number: 1e9, suffix: "B" }
                ],
                smallNumberPrefixes: []
            });

            const chart = root.container.children.push(am5xy.XYChart.new(root, {
                panX: true,
                panY: true,
                wheelX: "none",
                wheelY: "none",
                paddingLeft: 0
            }));

            chart.zoomOutButton.set("forceHidden", true);

            const yRenderer = am5xy.AxisRendererY.new(root, {
                minGridDistance: 20,
                inversed: true,
                minorGridEnabled: true
            });

            yRenderer.grid.template.set("visible", false);

            const yAxis = chart.yAxes.push(am5xy.CategoryAxis.new(root, {
                categoryField: "category",
                renderer: yRenderer
            }));

            const xAxis = chart.xAxes.push(am5xy.ValueAxis.new(root, {
                min: 0,
                strictMinMax: true,
                extraMax: 0.1,
                renderer: am5xy.AxisRendererX.new(root, {})
            }));

            const series = chart.series.push(am5xy.ColumnSeries.new(root, {
                xAxis: xAxis,
                yAxis: yAxis,
                valueXField: "value",
                categoryYField: "category"
            }));

            series.columns.template.setAll({
                cornerRadiusBR: 5,
                cornerRadiusTR: 5
            });

            series.columns.template.adapters.add("fill", (fill, target) => {
                return chart.get("colors").getIndex(series.columns.indexOf(target));
            });
            series.columns.template.adapters.add("stroke", (stroke, target) => {
                return chart.get("colors").getIndex(series.columns.indexOf(target));
            });

            series.bullets.push(() => {
                return am5.Bullet.new(root, {
                    locationX: 1,
                    sprite: am5.Label.new(root, {
                        text: "{valueXWorking.formatNumber('#.# a')}",
                        fill: root.interfaceColors.get("alternativeText"),
                        centerX: am5.p100,
                        centerY: am5.p50,
                        populateText: true
                    })
                });
            });

            yAxis.data.setAll(parsedData);
            series.data.setAll(parsedData);

            series.appear(1000);
            chart.appear(1000, 100);
        });
    }


    // Carrusel de categorias: una pagina muestra 6 botones en 3 columnas x 2 filas.
    $('#slider-categoria-1').each(function () {
        const $slider = $(this);
        if ($slider.data('categoriaCarouselInitialized')) return;

        if ($slider.hasClass('slick-initialized')) {
            $slider.slick('unslick');
        }

        const items = $slider.children('.categoria-wrapper').not('.empty-slide').detach().toArray();
        if (!items.length) return;

        const itemsPorPagina = 6;
        let paginaActual = 0;
        const paginas = [];

        $slider.empty().addClass('categoria-carousel');

        for (let i = 0; i < items.length; i += itemsPorPagina) {
            const page = document.createElement('div');
            page.className = 'categoria-carousel-page';
            if (i === 0) page.classList.add('active');
            items.slice(i, i + itemsPorPagina).forEach(item => page.appendChild(item));
            paginas.push(page);
            $slider.append(page);
        }

        const $prev = $('<button type="button" class="slick-prev slick-arrow" aria-label="Anterior">Anterior</button>');
        const $next = $('<button type="button" class="slick-next slick-arrow" aria-label="Siguiente">Siguiente</button>');

        const actualizarPagina = function () {
            paginas.forEach((page, index) => {
                page.classList.toggle('active', index === paginaActual);
            });
            $prev.toggleClass('slick-disabled', paginaActual === 0);
            $next.toggleClass('slick-disabled', paginaActual === paginas.length - 1);
        };

        $prev.on('click', function () {
            if (paginaActual > 0) {
                paginaActual -= 1;
                actualizarPagina();
            }
        });

        $next.on('click', function () {
            if (paginaActual < paginas.length - 1) {
                paginaActual += 1;
                actualizarPagina();
            }
        });

        $slider.append($prev, $next);
        $slider.data('categoriaCarouselInitialized', true);
        actualizarPagina();
    });

    // Inicializar el slider slick solo si no está ya inicializado
    $('#slider-productos').each(function () {
        if (!$(this).hasClass('slick-initialized')) {
            $(this).slick({
                slidesToShow: 3,
                slidesToScroll: 3,
                infinite: false,
                rows: 2, // Esto hace que se muestren en 2 filas
                arrows: true,
                responsive: [
                    {
                        breakpoint: 1024,
                        settings: {
                            slidesToShow: 3,
                            rows: 2
                        }
                    },
                    {
                        breakpoint: 768,
                        settings: {
                            slidesToShow: 2,
                            rows: 2
                        }
                    },
                    {
                        breakpoint: 480,
                        settings: {
                            slidesToShow: 1,
                            rows: 2
                        }
                    }
                ]
            });
        }
    });


    // Inicializar tooltips si se requiere
    inicializartooltipped();
    reinicializarTiny();
}

function enviarFormularioAjax(event) {
    event.preventDefault(); // Evitar la redirección normal del formulario
    
    if (event.target.dataset.ajaxSubmitting === '1') return;
    event.target.dataset.ajaxSubmitting = '1';

    // Mostrar pantalla de carga
    document.getElementById('loading-screen').style.display = 'flex';

    // Obtener los datos del formulario
    let formData = new FormData(event.target);

    // Obtener la URL dinámica desde el campo oculto
    let url = document.getElementById('form-action-url').value;

    fetch(url, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
        .then(response => {
            if (response.ok) {
                return response.json();
            } else if (response.status === 422) {
                // Capturar los errores de validación
                return response.json().then(errors => {
                    throw { validationErrors: errors.errors };
                });
            } else {
                return response.text().then(text => { throw new Error(text); });
            }
        })
        .then(data => {
            if (data.success) {
                mostrarMensajeExito(data.message || 'Operación exitosa');

                var openModal = M.Modal.getInstance(document.querySelector('.modal'));
                if (openModal) {
                    openModal.close();
                }

                ocultarPantallaDeCarga();

                if (data.pdf_url) {
                    window.open(data.pdf_url, '_blank');
                }

                // Si existe una 'redirect_url', cargamos el contenido dinámico
                if (data.redirect_url) {
                    if (esUrlMovcajaIndex(data.redirect_url)) {
                        restaurarBreadcrumbMovcaja();
                    }
                    cargarContenidoDinamicamente(data.redirect_url).then(function (resultado) {
                        if (esUrlMovcajaIndex(data.redirect_url)) {
                            restaurarBreadcrumbMovcaja();
                        }
                        return resultado;
                    });
                } else {
                    // En caso de que no haya una URL de redirección, actualizar la tabla normalmente
                    actualizarTabla(data.redirect_url);
                }
            }
        })

        .catch(error => {
            ocultarPantallaDeCarga();

            // Limpiar errores anteriores
            document.querySelectorAll('.helper-text').forEach(element => {
                element.setAttribute('data-error', '');
                element.innerHTML = '';
            });

            document.querySelectorAll('.invalid').forEach(element => {
                element.classList.remove('invalid');
            });

            // Mostrar errores de validación en los campos correspondientes
            if (error.validationErrors) {
                for (let field in error.validationErrors) {
                    let input = document.querySelector(`[name="${field}"]`);
                    if (input) {
                        // Agregar clase "invalid" al campo
                        input.classList.add('invalid');

                        // Obtener el mensaje de error del campo
                        let errorMessage = error.validationErrors[field][0];

                        // Buscar el helper text asociado al campo
                        let helperText = input.closest('.input-field').querySelector('.helper-text');
                        if (helperText) {
                            // Asignar el mensaje de error al helper text
                            helperText.innerHTML = `<strong>${errorMessage}</strong>`;
                            helperText.setAttribute('data-error', errorMessage);
                        } else {
                            // Crear un helper text dinámicamente si no existe
                            const newHelper = document.createElement('span');
                            newHelper.classList.add('helper-text', 'red-text');
                            newHelper.innerHTML = `<strong>${errorMessage}</strong>`;
                            input.closest('.input-field').appendChild(newHelper);
                        }
                    }
                }

                M.toast({
                    html: '<i class="material-icons left">error</i> Error de validación en algunos campos.',
                    classes: 'red darken-1 rounded',
                    displayLength: 4000
                });
            } else {
                M.toast({
                    html: '<i class="material-icons left">error</i> Error al crear o actualizar: ' + error.message,
                    classes: 'red darken-1 rounded',
                    displayLength: 4000
                });
            }
        })
        .finally(() => {
            event.target.dataset.ajaxSubmitting = '0';
        });

}
let peticionEnCurso = null;
let urlActualCargada = null;

function obtenerPathDeUrl(url) {
    if (!url) {
        return '';
    }
    try {
        const parsed = new URL(url, window.location.origin);
        return parsed.pathname.replace(/\/+$/, '').toLowerCase() || '/';
    } catch (e) {
        let path = String(url).split('?')[0].replace(/\/+$/, '').toLowerCase() || '/';
        if (path.indexOf('://') !== -1) {
            path = path.replace(/^https?:\/\/[^/]+/i, '') || '/';
        }
        if (path && path.charAt(0) !== '/') {
            path = '/' + path;
        }
        return path;
    }
}

function esUrlListadoHabitaciones(url) {
    return obtenerPathDeUrl(url) === '/movhabitacion';
}

function obtenerUrlHabitacionesTrasError() {
    const crumbHabitaciones = breadcrumbStack.find(item =>
        item.url && String(item.url).includes('/movhabitacion') && !String(item.url).includes('/checkout')
    );
    if (crumbHabitaciones?.url) {
        return crumbHabitaciones.url;
    }

    return '/movhabitacion';
}

function restaurarBreadcrumbHabitaciones() {
    const inicio = breadcrumbStack.find(item => item.title === 'Inicio')
        || { title: 'Inicio', url: '/', icono: 'fa-solid fa-home left' };
    const crumbHabitaciones = breadcrumbStack.find(item =>
        item.title && item.title.toLowerCase() === 'habitaciones'
    ) || breadcrumbStack.find(item =>
        obtenerPathDeUrl(item.url) === '/movhabitacion'
    );
    const iconoHabitaciones = (crumbHabitaciones && crumbHabitaciones.icono)
        ? crumbHabitaciones.icono
        : 'fa-solid fa-bed left';

    breadcrumbStack = [
        { title: 'Inicio', url: '/', icono: inicio.icono || 'fa-solid fa-home left' },
        { title: 'Habitaciones', url: '/movhabitacion', icono: iconoHabitaciones }
    ];
    actualizarCabecera(iconoHabitaciones, 'Habitaciones', '/movhabitacion');
}

function esUrlMovcajaIndex(url) {
    return obtenerPathDeUrl(url) === '/movcaja';
}

function restaurarBreadcrumbMovcaja() {
    const inicio = breadcrumbStack.find(item => item.title === 'Inicio')
        || { title: 'Inicio', url: '/', icono: 'fa-solid fa-home left' };
    const crumbMovcaja = breadcrumbStack.find(item =>
        item.title && item.title.toLowerCase().startsWith('caja chica')
    ) || breadcrumbStack.find(item =>
        obtenerPathDeUrl(item.url) === '/movcaja'
    );
    const iconoMovcaja = (crumbMovcaja && crumbMovcaja.icono)
        ? crumbMovcaja.icono
        : 'fa-solid fa-cash-register left';

    breadcrumbStack = [
        { title: 'Inicio', url: '/', icono: inicio.icono || 'fa-solid fa-home left' },
        { title: 'Caja chica', url: '/movcaja', icono: iconoMovcaja }
    ];
    actualizarCabecera(iconoMovcaja, 'Caja chica', '/movcaja');
}

function cargarContenidoDinamicamente(url) {
    let urlNavegacion = url;
    try {
        const parsed = new URL(url, window.location.origin);
        urlNavegacion = parsed.pathname + parsed.search;
    } catch (e) {
        urlNavegacion = String(url || '');
    }

    // Si la petición a esta URL sigue en curso, bloquear
    if (peticionEnCurso === urlNavegacion || peticionEnCurso === url) {
        return Promise.resolve();
    }

    peticionEnCurso = urlNavegacion; // Marcamos que esta URL está en carga
    mostrarPantallaDeCarga();
    aplicarVisibilidadMainBody(urlNavegacion);

    return fetch(urlNavegacion)
        .then(async response => {
            // Leemos el body como texto siempre (para poder detectar HTML o JSON)
            const text = await response.text();
            const contentType = response.headers.get('content-type') || '';

            // Si el servidor devolvió JSON (content-type) o el body parece JSON -> parsear y rechazar/retornar
            const pareceJson = contentType.includes('application/json') ||
                (/^\s*[\{\[]/.test(text)); // empieza por { o [

            if (pareceJson) {
                try {
                    const json = JSON.parse(text);
                    // Si server devuelve success:false, rechazamos con la info del JSON para que el caller lo muestre.
                    if (json && json.success === false) {
                        // Rechazamos con un objeto estructurado (no un Error) para que el caller lo detecte
                        return Promise.reject({ type: 'server-json', data: json, url: url });
                    }
                    // Si es un JSON con success true y contiene redirect/url/html, podemos manejar según convenga.
                    // Si el JSON indica redirección a HTML, devolverlo para que el caller lo use.
                    return Promise.reject({ type: 'server-json-success', data: json, url: url });
                } catch (e) {
                    // No es JSON válido, continuar intentando parsear como HTML
                    console.warn('Parecía JSON pero no pudo parsearse:', e);
                }
            }

            // Si la respuesta no es OK (status >= 400), y no vino JSON útil, lanzar Response-like error
            if (!response.ok) {
                // Intenta parsear JSON para extraer mensaje si existe
                try {
                    const maybeJson = JSON.parse(text);
                    if (maybeJson && maybeJson.errors) {
                        return Promise.reject({ type: 'server-json', data: maybeJson, url: url });
                    }
                } catch (e) {
                    // no es JSON
                }
                throw new Error(`Error ${response.status}: ${response.statusText}`);
            }

            // Si llegamos aquí, intentamos parsear HTML
            const parser = new DOMParser();
            const doc = parser.parseFromString(text, 'text/html');
            const mainEl = doc.querySelector('#main-content');

            if (!mainEl) {
                // Si no encontramos #main-content, puede que el servidor devolviera JSON "camuflado"
                // intentamos parsearlo y, si contiene errors, rechazamos con esa info.
                try {
                    const maybeJson = JSON.parse(text);
                    if (maybeJson && maybeJson.errors) {
                        return Promise.reject({ type: 'server-json', data: maybeJson, url: url });
                    }
                } catch (e) {
                    // No es JSON -> rechazamos con el mismo error de siempre para que el caller identifique login
                    return Promise.reject(new Error("Cannot read properties of null (reading 'innerHTML')"));
                }
            }

            const newContent = mainEl ? mainEl.innerHTML : '';

            // Limpiar solo el contenedor del contenido dinámico
            const contenedor = document.getElementById('main-content');

            // Restaurar la altura del contenedor
            if (contenedor) {
                contenedor.style.height = 'auto';
                contenedor.innerHTML = newContent;
            } else {
                console.warn('#main-content no existe en el DOM actual');
            }

            document.querySelectorAll('.material-tooltip').forEach(el => el.remove());

            aplicarVisibilidadMainBody(urlNavegacion);
            restaurarScrollPagina({ irArriba: true });

            if (esUrlListadoHabitaciones(urlNavegacion)) {
                restaurarBreadcrumbHabitaciones();
            }
            if (esUrlMovcajaIndex(urlNavegacion)) {
                restaurarBreadcrumbMovcaja();
            }

            if (contenedor) {
                M.AutoInit(contenedor);
                inicializarModalesConScroll(contenedor);
            } else {
                M.AutoInit();
                inicializarModalesConScroll(document);
            }


            // Manejar el Formulario
            const form = document.getElementById('form');
            if (form) {

                // Comprobar si el formulario tiene la clase "form-retroceder"
                if (form.classList.contains('form-retroceder')) {
                    let botonClickeado = null; // Variable para almacenar el botón presionado

                    // Detectar qué botón fue clickeado antes de enviar el formulario
                    document.querySelectorAll('.form-cobrar').forEach(boton => {
                        boton.addEventListener('click', function () {
                            botonClickeado = this; // Guardamos el botón clickeado
                        });
                    });

                    // Si tiene la clase "form-retroceder", no enviar el formulario y realizar una acción especial
                    form.addEventListener('submit', function (event) {
                        event.preventDefault();
                        form.dataset.submitHandledToken = form.dataset.submitGuardToken || `${Date.now()}-${Math.random()}`;
                        try {
                            if (window.tinymce && typeof window.tinymce.triggerSave === 'function') {
                                window.tinymce.triggerSave();
                            }
                        } catch (e) {
                            // Silencia cualquier error, TinyMCE no está definido
                        }

                        if (esFormularioMovventa(form)) {
                            sincronizarDetallesJsonMovVenta(form);
                            if (!validarDescripcionGlosaMovventa(form)) {
                                ocultarPantallaDeCarga();
                                return;
                            }
                        }

                        if (esFormularioMovcompra(form) && !prepararEnvioCompra(event)) {
                            ocultarPantallaDeCarga();
                            return;
                        }

                        // Verificar si existe un campo con name="total" y si su valor es 0.00
                        let totalField = form.querySelector('[name="total"]');

                        if (!totalField) {
                            totalField = form.querySelector('[name="total_a_pagar_productos"]');
                        }

                        if (totalField) {
                            const total = parseFloat(totalField.value) || 0;

                        } else {
                            console.warn('No se encontró ningún campo de total.');
                        }
                        const personaIdField = form.querySelector('[name="persona_id"]'); // Verificar el campo persona_id
                        const cajaIdField = form.querySelector('[name="caja_id"]'); // Verificar el campo persona_id
                        const tipoDoc = document.getElementById('tipodocumento_id')?.value;
                        const personaId = document.getElementById('persona_id')?.value;
                        const personaAuto = document.getElementById('persona_autocomplete')?.value || '';
                        const nrodocSeleccionado = personaAuto.split(' - ')[0]?.trim(); // Extrae el primer campo del autocomplete

                        if (tipoDoc === '10') {
                            if (!/^\d{11}$/.test(nrodocSeleccionado)) {
                                const modalError = document.getElementById('modal-error');
                                const errorMessageContainer = document.getElementById('error-message');

                                if (modalError && errorMessageContainer) {
                                    calcularEarlylate()
                                    mostrarMensajeInterrogante('Para facturas, debe seleccionar una persona con RUC (11 dígitos).')
                                }

                                ocultarPantallaDeCarga();
                                return;
                            }
                        }

                        // Validar boletas con monto mayor a 700 y documento 0
                        if (tipoDoc === '4') {
                            const montoTotal = parseFloat(totalField?.value || '0');
                            if (nrodocSeleccionado === '0' && montoTotal > 700) {
                                const modalError = document.getElementById('modal-error');
                                const errorMessageContainer = document.getElementById('error-message');

                                if (modalError && errorMessageContainer) {
                                    const modalInstance = M.Modal.init(modalError);
                                    errorMessageContainer.innerText = 'Sunat no permite emitir boletas por montos mayores a S/ 700 con documento 0. Selecciona un cliente válido.';
                                    modalInstance.open();
                                }

                                ocultarPantallaDeCarga();
                                return;
                            }
                        }

                        // Antes de la validación, comprobamos si existe el campo total_ventas
                        const totalVentasField = document.getElementById('total_ventas');
                        if (totalVentasField) {
                            // Existe total_ventas: saltamos toda la validación de total cero

                        } else {

                            const totalField = document.getElementById('total') || document.getElementById('preciototal');
                            const checkoutButton = document.getElementById('checkout-button'); // Selección más segura del botón

                            if (totalField && (totalField.value === '0' || totalField.value === '0.00')) {
                                // Verificar si el botón está deshabilitado
                                if (checkoutButton && checkoutButton.disabled) {
                                    const tipoDocumentoId = document.getElementById('tipodocumento_id')?.value;
                                    const permitirCero = totalField.classList.contains('totalapertura') || tipoDocumentoId === '9';

                                    if (permitirCero) {
                                        // Acción cuando el total es 0 pero se permite (como en el caso de un tipo de documento específico)
                                    } else {
                                        const modalError = document.getElementById('modal-error');
                                        const errorMessageContainer = document.getElementById('error-message');

                                        if (modalError && errorMessageContainer) {
                                            const modalInstance = M.Modal.init(modalError);
                                            errorMessageContainer.innerText =
                                                'El valor de un comprobante tributable no debe ser igual o menor a 0, cámbialo por ticket.';
                                            modalInstance.open();
                                        } else {
                                            console.error('El modal o el contenedor de mensaje no se encuentran en el DOM');
                                        }

                                        ocultarPantallaDeCarga();
                                        return;
                                    }
                                }
                            }

                        }

                        // Validar si la suma de montos en tabla medios de pago coincide con el total
                        const tablaMediosPago = document.getElementById('detalle-movcaja');
                        let sumaMontos = 0;

                        if (tablaMediosPago) {
                            const filas = tablaMediosPago.querySelectorAll('tr');

                            filas.forEach(fila => {
                                const celdaMonto = fila.querySelector('.inpt-cantidad-mc.monto');
                                if (celdaMonto) {
                                    const valor = parseFloat(celdaMonto.textContent.trim());
                                    if (!isNaN(valor)) {
                                        sumaMontos += valor;
                                    }
                                }
                            });
                        }

                        // Obtener el valor total desde el campo principal de total a pagar
                        let totalPagarField = document.getElementById('totalapagar');
                        let totalPagar;

                        if (totalPagarField) {
                            totalPagar = parseFloat(totalPagarField.value) || 0;
                        } else {
                            const totalFallbackField = document.getElementById('total');
                            if (totalFallbackField) {
                                totalPagar = parseFloat(totalFallbackField.value) || 0;
                            } else {
                                totalPagar = 0;
                            }
                        }


                        const motivoSelect = document.getElementById('motivo_id');

                        if (!motivoSelect && sumaMontos !== totalPagar && sumaMontos > 0) {
                            mostrarMensajeError(`La suma de los montos ingresados en medios de pago (S/ ${sumaMontos.toFixed(2)}) no coincide con el total a pagar (S/ ${totalPagar.toFixed(2)}).`);


                            ocultarPantallaDeCarga();
                            return;
                        }




                        // Verificar si el campo persona_id está vacío o es null
                        if (personaIdField && (!personaIdField.value || personaIdField.value === 'null')) {
                            // Verificar si el modal y el contenedor del mensaje de error existen
                            const modalError = document.getElementById('modal-error');
                            const errorMessageContainer = document.getElementById('error-message');

                            if (modalError && errorMessageContainer) {
                                const modalInstance = M.Modal.init(modalError); // Inicializar el modal de Materialize
                                errorMessageContainer.innerText = 'Debe haber un cliente seleccionado.'; // Asignar el mensaje de error

                                modalInstance.open(); // Abrir el modal con el error
                                ocultarPantallaDeCarga(); // Ocultar la pantalla de carga si no hay cliente seleccionado
                            } else {
                                console.error('El modal o el contenedor de mensaje no se encuentran en el DOM');
                            }

                            ocultarPantallaDeCarga(); // Ocultar la pantalla de carga si el cliente no está seleccionado
                            return; // No continuar con el envío del formulario
                        }

                        // Verificar si el campo persona_id está vacío o es null
                        if (cajaIdField && (!cajaIdField.value || cajaIdField.value === 'null')) {
                            // Verificar si el modal y el contenedor del mensaje de error existen
                            const modalError = document.getElementById('modal-error');
                            const errorMessageContainer = document.getElementById('error-message');

                            if (modalError && errorMessageContainer) {
                                mostrarMensajeError('Ninguna caja está aperturada.');
                                ocultarPantallaDeCarga(); // Ocultar la pantalla de carga si no hay cliente seleccionado
                            } else {
                                console.error('El modal o el contenedor de mensaje no se encuentran en el DOM');
                            }

                            ocultarPantallaDeCarga(); // Ocultar la pantalla de carga si el cliente no está seleccionado
                            return; // No continuar con el envío del formulario
                        }



                        // Si no hay error, continuar con el flujo normal
                        mostrarPantallaDeCarga();
                        event.preventDefault();

                        if (esFormularioMovventa(form)) {
                            sincronizarDetallesJsonMovVenta(form);
                        }

                        if (esFormularioMovcompra(form) && !prepararEnvioCompra(event)) {
                            forzarOcultarPantallaDeCarga();
                            return;
                        }

                        const detallesMovimientosJsonField = document.getElementById('detalles_movimientos_json');
                        if (detallesMovimientosJsonField && typeof actualizarDetallesMovimientos === 'function') {
                            actualizarDetallesMovimientos();
                            detallesMovimientosJsonField.value = JSON.stringify(detallesMovimientos);
                        }

                        const formData = new FormData(form);
                        const actionUrl = form.getAttribute('action') || document.getElementById('form-action-url').value;

                        fetch(actionUrl, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json'
                            }
                        })
                            .then(response => {
                                if (!response.ok) throw response; // Esto lanza un Response si hay error
                                return response.json();
                            })
                            .then(data => {
                                if (!data.success) {
                                    forzarOcultarPantallaDeCarga();
                                    return null;
                                }

                                mostrarMensajeExito(data.message);
                                let promesaNavegacion = null;

                                if (data.sync_url) {
                                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                                        const headers = { 'Accept': 'application/json' };
                                        if (csrfToken) {
                                            headers['X-CSRF-TOKEN'] = csrfToken;
                                        }
                                        // 🔥 ACTUALIZAR ICONO Y TOOLTIP DE LA CAJA
                                        const li = document.getElementById('keypad-cashier');
                                        if (!li) return;

                                        const icon = li.querySelector('i');
                                        if (!icon) return;

                                        // Cambiar icono
                                        icon.classList.remove('fa-circle', 'red-text');
                                        icon.classList.add('fa-circle-check', 'green-text');

                                        // Cambiar tooltip
                                        li.setAttribute('data-tooltip', 'Aperturada');

                                        // Reinicializar tooltip (Materialize)
                                        const instance = M.Tooltip.getInstance(li);
                                        if (instance) instance.destroy();
                                        M.Tooltip.init(li);

                                        fetch(data.sync_url, {
                                            method: 'POST',
                                            headers: headers,
                                            keepalive: true
                                        })
                                            .then(() => {

                                            })
                                            .catch(error => {
                                                console.error('Error al ejecutar sync en segundo plano:', error);
                                            });
                                    }
                                    if (data.caja) {
                                        const li = document.getElementById('keypad-cashier');
                                        if (!li) return;

                                        const icon = li.querySelector('i');
                                        if (!icon) return;

                                        // Cambiar icono
                                        icon.classList.remove('fa-circle-check', 'green-text');
                                        icon.classList.add('fa-circle', 'red-text');

                                        // Cambiar tooltip
                                        li.setAttribute('data-tooltip', 'Cerrada');

                                        // Reinicializar tooltip (Materialize)
                                        const instance = M.Tooltip.getInstance(li);
                                        if (instance) instance.destroy();
                                        M.Tooltip.init(li);
                                    }




                                    if (botonClickeado && botonClickeado.classList.contains('form-cobrar')) {
                                        const checkoutUrl = `/movhabitacion/checkout/${data.habitacion_id}`;
                                        promesaNavegacion = cargarContenidoDinamicamente(checkoutUrl);
                                        actualizarCabecera("fa-solid fa-cash-register", "Checkout", checkoutUrl);
                                    } else if (data.redirect_url) {
                                        if (esUrlListadoHabitaciones(data.redirect_url)) {
                                            restaurarBreadcrumbHabitaciones();
                                        } else if (esUrlMovcajaIndex(data.redirect_url)) {
                                            restaurarBreadcrumbMovcaja();
                                        }
                                        promesaNavegacion = cargarContenidoDinamicamente(data.redirect_url).then(function (resultado) {
                                            if (esUrlListadoHabitaciones(data.redirect_url)) {
                                                restaurarBreadcrumbHabitaciones();
                                            } else if (esUrlMovcajaIndex(data.redirect_url)) {
                                                restaurarBreadcrumbMovcaja();
                                            }
                                            return resultado;
                                        });
                                    } else {
                                        forzarOcultarPantallaDeCarga();
                                    }

                                    if (data.pdf_url) {
                                        window.open(data.pdf_url, '_blank');
                                    }

                                return promesaNavegacion;
                            })
                            .catch(async error => {
                                let mensajeError = 'Ha ocurrido un error inesperado.';
                                let redirectUrl = '';

                                // Si el error es un objeto Response, intenta extraer JSON
                                if (error instanceof Response) {
                                    try {
                                        const errorData = await error.json();
                                        mensajeError = errorData.errors || mensajeError;
                                        redirectUrl = errorData.redirect_url || '';
                                    } catch (e) {
                                        console.error('No se pudo parsear el error JSON:', e);
                                    }
                                }

                                mostrarMensajeError(mensajeError);

                                const urlRecargar = redirectUrl || obtenerUrlHabitacionesTrasError();
                                restaurarBreadcrumbHabitaciones();
                                return cargarContenidoDinamicamente(urlRecargar);
                            })
                            .finally(() => {
                                ocultarPantallaDeCarga();
                            });
                    });
                } else {
                    // Si no tiene la clase "form-retroceder", ejecuta el envío del formulario normal
                    form.addEventListener('submit', function (event) {
                        event.preventDefault();
                        form.dataset.submitHandledToken = form.dataset.submitGuardToken || `${Date.now()}-${Math.random()}`;

                        if (esFormularioMovventa(form)) {
                            sincronizarDetallesJsonMovVenta(form);
                            if (!validarDescripcionGlosaMovventa(form)) {
                                ocultarPantallaDeCarga();
                                return;
                            }
                        }

                        const formData = new FormData(form);
                        const actionUrl = form.getAttribute('action') || document.getElementById('form-action-url').value;

                        fetch(actionUrl, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json'
                            }
                        })
                            .then(response => {
                                if (!response.ok) throw response;
                                return response.json();
                            })
                            .then(data => {
                                if (data.success) {
                                    // Inserta el mensaje del servidor

                                    mostrarMensajeExito(data.message);


                                    // Redirecciona si es necesario
                                    if (data.redirect_url) {
                                        if (esUrlListadoHabitaciones(data.redirect_url)) {
                                            restaurarBreadcrumbHabitaciones();
                                        } else if (esUrlMovcajaIndex(data.redirect_url)) {
                                            restaurarBreadcrumbMovcaja();
                                        }
                                        cargarContenidoDinamicamente(data.redirect_url).then(function (resultado) {
                                            if (esUrlListadoHabitaciones(data.redirect_url)) {
                                                restaurarBreadcrumbHabitaciones();
                                            } else if (esUrlMovcajaIndex(data.redirect_url)) {
                                                restaurarBreadcrumbMovcaja();
                                            }
                                            return resultado;
                                        });
                                    }
                                }

                            })
                            .catch(error => {
                                ocultarPantallaDeCarga();
                                console.error('Error al enviar el formulario:', error);
                            })
                            .finally(() => {
                                // Anular el formulario
                                document.getElementById('form')?.remove(); // Elimina el nodo del formulario
                            });
                    });
                }
            }
            let urlParams = null;
            let paginaActual = 1;

            try {
                let urlParams = null;
                let paginaActual = 1;
                if (urlNavegacion) {
                    const parsedUrl = new URL(urlNavegacion, window.location.origin);
                    urlParams = new URLSearchParams(parsedUrl.search);
                    paginaActual = urlParams.get('page') || 1;
                }
                localStorage.setItem('paginaActual', paginaActual);
            } catch (e) {
                // ignore
            }

            incializarTemporizador();
            reinicializarSelects(contenedor || document);
            if (esUrlIndiceHabitacion(urlNavegacion)) {
                inicializarFiltrosHabitacion();
            }
            reinicializarDataTables();
            inicializarEventosDeMovimiento();
            inicializarBusquedaRUC();
            initializeAutocomplete();
            iniciarCarrusel();
            if (String(urlNavegacion).indexOf('/checkin') !== -1) {
                inicializarFormularioCheckin();
            }
            if (String(urlNavegacion).indexOf('checkout') !== -1) {
                mostrarModalExcesoCheckout();
            }

            finalizarCargaVistaDinamica({ irArriba: true, urlNavegacion: urlNavegacion });

            return true;
        })
        .catch(err => {
            // Pasamos hacia afuera: puede ser:
            // - un objeto { type: 'server-json', data: {...} } (rechazo intencional)
            // - un Error normal (ej: innerHTML null)
            // - otro error de fetch/network
            console.error('Error en cargarContenidoDinamicamente (interno):', err);
            forzarOcultarPantallaDeCarga();
            return Promise.reject(err);
        })
        .finally(() => {
            peticionEnCurso = null;
        });
}

function actualizarBreadcrumb() {
    const breadcrumbContainer = $('#breadcrumb');
    breadcrumbContainer.html(''); // Limpiar el breadcrumb existente

    breadcrumbStack.forEach((crumb, index) => {
        const activeClass = index === breadcrumbStack.length - 1 ? 'active' : '';
        const linkHtml = `
            <a href="${crumb.url}" class="breadcrumb ${activeClass}" ${activeClass ? '' : 'data-icono="' + crumb.icono + '" data-titulo="' + crumb.title + '"'}>
                ${activeClass ? `<i class="${crumb.icono}" style="margin-right: 5px;"></i>` : ''}
                ${crumb.title}
            </a>`;
        breadcrumbContainer.append(linkHtml);
    });

    // Aplicar el interceptor de clics a los enlaces del breadcrumb
    interceptarClicksEnBreadcrumbs();
}

function actualizarTabla(url, paginaActual) {
    // Mostrar pantalla de carga
    mostrarPantallaDeCarga();

    // Si paginaActual es undefined o null, establecer el valor predeterminado como 1
    paginaActual = paginaActual || localStorage.getItem('paginaActual') || 1;

    // Verificar si la URL ya tiene parámetros (si tiene un '?')
    if (url.includes('?')) {
        // Si la URL ya tiene un parámetro, usamos '&page='
        url = url + '&page=' + paginaActual;
    } else {
        // Si no tiene parámetros, añadimos '?page='
        url = url + '?page=' + paginaActual;
    }

    // Hacer la solicitud AJAX para obtener los nuevos datos de la tabla
    $.ajax({
        url: url,
        type: 'GET',
        success: function (response) {
            // Reemplazar solo el contenedor de la tabla y la paginación
            $('#tb-container').html($(response).find('#tb-container').html());

            // Reinicializar componentes
            reinicializarDataTables(); // Reinicializa la tabla con los nuevos datos

            // Aquí es donde llamamos a la función que reinicializa los modales
            inicializarModal(); // Añade esta línea para reinicializar los modales
            inicializarEventosDeMovimiento();

            const chkAllHabitacion = document.getElementById('chk-habitacion-all');
            if (chkAllHabitacion) {
                chkAllHabitacion.checked = false;
                chkAllHabitacion.indeterminate = false;
            }
            actualizarBotonEliminarHabitacionesMasivo();

            ocultarPantallaDeCarga();
        },
        error: function (xhr) {
            console.error('Error al actualizar la tabla:', xhr);
            ocultarPantallaDeCarga();
        }
    });
}




// Función que reinicializa los selects y sus eventos
function reinicializarSelects(scope) {
    const root = scope && scope.querySelectorAll ? scope : document;
    const selectElems = root.querySelectorAll('select');

    selectElems.forEach(function (select) {
        const instancia = M.FormSelect.getInstance(select);
        if (instancia) {
            instancia.destroy();
        }
    });

    if (selectElems.length) {
        M.FormSelect.init(selectElems);
    }

    const recepcionista = root.querySelector('#recepcionista_id');
    if (recepcionista) {
        const labelRecepcionista = recepcionista.closest('.input-field')?.querySelector('label');
        if (labelRecepcionista) {
            labelRecepcionista.classList.remove('active');
        }
    }
}

function esUrlIndiceHabitacion(url) {
    const path = obtenerPathDeUrl(url);
    return path === '/habitacion';
}

function agregarFiltrosHabitacionAParams(params) {
    const situacionFilter = document.getElementById('situacion-filter');
    const pisoFilter = document.getElementById('piso-filter');
    const tipohabitacionFilter = document.getElementById('tipohabitacion-filter');

    if (!situacionFilter && !pisoFilter && !tipohabitacionFilter) {
        return;
    }

    if (situacionFilter && situacionFilter.value) {
        params.set('situacion', situacionFilter.value);
    }
    if (pisoFilter && pisoFilter.value) {
        params.set('piso', pisoFilter.value);
    }
    if (tipohabitacionFilter && tipohabitacionFilter.value) {
        params.set('tipohabitacion', tipohabitacionFilter.value);
    }
}

function agregarFiltrosMovcompraAParams(params) {
    const cajaFilter = document.getElementById('caja-filter');
    if (!cajaFilter) {
        return;
    }

    if (cajaFilter.value) {
        params.set('caja_id', cajaFilter.value);
    }

    const fechaInicio = document.getElementById('fecha-inicio-filter')?.value || '';
    const fechaFin = document.getElementById('fecha-fin-filter')?.value || '';
    const tipoFilter = document.getElementById('tipo-filter')?.value || '';
    const conceptoFilter = document.getElementById('conceptopago-filter')?.value || '';

    if (fechaInicio) {
        params.set('fecha_inicio', fechaInicio);
    }
    if (fechaFin) {
        params.set('fecha_fin', fechaFin);
    }
    if (tipoFilter) {
        params.set('tipodocumento_id', tipoFilter);
    }
    if (conceptoFilter) {
        params.set('conceptopago_id', conceptoFilter);
    }
}

function inicializarFiltrosMovcompra() {
    const cajaFilter = document.getElementById('caja-filter');
    if (!cajaFilter) {
        return;
    }

    const selects = document.querySelectorAll('#caja-filter, #tipo-filter, #conceptopago-filter');
    selects.forEach(function (select) {
        const instancia = M.FormSelect.getInstance(select);
        if (instancia) {
            instancia.destroy();
        }
    });
    M.FormSelect.init(selects);

    const params = new URLSearchParams(window.location.search);
    const tieneFiltros = ['caja_id', 'fecha_inicio', 'fecha_fin', 'tipodocumento_id', 'conceptopago_id', 'search']
        .some(function (key) {
            return params.get(key);
        });

    if (tieneFiltros) {
        const filtro = document.getElementById('filtros');
        const icono = document.querySelector('#btn-filtro i');
        if (filtro) {
            filtro.style.display = '';
        }
        if (icono) {
            icono.classList.remove('fa-magnifying-glass-plus');
            icono.classList.add('fa-magnifying-glass-minus');
        }
    }
}

function filterMovcompra() {
    realizarBusqueda();
}

function inicializarFiltrosHabitacion() {
    const $filtros = $('#situacion-filter, #piso-filter, #tipohabitacion-filter');
    if (!$filtros.length) {
        return;
    }

    $filtros.each(function () {
        const instancia = M.FormSelect.getInstance(this);
        if (instancia) {
            instancia.destroy();
        }
    });

    M.FormSelect.init($filtros);
    $filtros.off('change.filtroHabitacion').on('change.filtroHabitacion', function () {
        realizarBusqueda();
    });
}

function inicializarFormularioCheckin() {
    const recepcionista = document.getElementById('recepcionista_id');
    if (recepcionista) {
        
        const instancia = M.FormSelect.getInstance(recepcionista);
        if (instancia) {
            instancia.destroy();
        }
        M.FormSelect.init(recepcionista);
        const labelRecepcionista = recepcionista.closest('.input-field')?.querySelector('label');
        if (labelRecepcionista) {
            labelRecepcionista.classList.remove('active');
        }
    }

    const switchHoras = document.getElementById('switchHoras');
    if (switchHoras && typeof toggleTiempoHabitacion === 'function') {
        toggleTiempoHabitacion();
    }
}

function parseJsonArray(value) {
    if (!value) return [];
    try {
        const parsed = JSON.parse(value);
        return Array.isArray(parsed) ? parsed : [];
    } catch (e) {
        return [];
    }
}

function getAutocompleteParams($field) {
    if (!$field || !$field.length) return {};
    const data = $field.data();
    const params = {};

    if (data.clasificacion) params.clasificacion = data.clasificacion;
    if (data.rolId) params.rol_id = data.rolId;
    if (data.tipoDocumento) params.tipo_documento = data.tipoDocumento;
    if (data.sucursalId) params.sucursal_id = data.sucursalId;

    return params;
}

function resolveAutocompleteHiddenField($field, fallbackSelector) {
    if (!$field || !$field.length) return $();

    const explicitTarget = $field.data('autocomplete-target');
    if (explicitTarget && typeof explicitTarget === 'string') {
        const selector = explicitTarget.startsWith('#') ? explicitTarget : `#${explicitTarget}`;
        const $target = $(selector);
        if ($target.length) {
            return $target;
        }
    }

    const $closestHidden = $field.closest('.input-field').find('input[type="hidden"]').first();
    if ($closestHidden.length) {
        return $closestHidden;
    }

    return fallbackSelector ? $(fallbackSelector) : $();
}

function buildAutocompleteMap(items) {
    const data = {};
    const map = {};

    if (!Array.isArray(items)) {
        return { data, map };
    }

    items.forEach(item => {
        const label = item?.label || item?.value || item?.text;
        if (!label) return;
        data[label] = null;
        map[label] = item;
    });

    return { data, map };
}

function updateAutocompleteData(element, items) {
    const payload = buildAutocompleteMap(items);
    element._autocompleteItems = payload.map;

    const instance = M.Autocomplete.getInstance(element);
    if (!instance) {
        return;
    }

    if (typeof instance.updateData === 'function') {
        instance.updateData(payload.data);
    } else {
        instance.options.data = payload.data;
    }

    if (Object.keys(payload.data).length > 0) {
        instance.open();
    } else if (typeof instance.close === 'function') {
        instance.close();
    }
}

function fetchAutocompleteResults(element, url, query, extraParams, limit) {
    const params = new URLSearchParams();
    params.set('q', query);
    params.set('limit', limit);

    Object.keys(extraParams || {}).forEach(key => {
        const value = extraParams[key];
        if (value !== undefined && value !== null && value !== '') {
            params.set(key, value);
        }
    });

    if (element._autocompleteAbort) {
        element._autocompleteAbort.abort();
    }

    const controller = new AbortController();
    element._autocompleteAbort = controller;

    fetch(`${url}?${params.toString()}`, {
        headers: { 'Accept': 'application/json' },
        signal: controller.signal
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('Autocomplete request failed');
            }
            return response.json();
        })
        .then(data => {
            const items = Array.isArray(data) ? data : (data.data || []);
            updateAutocompleteData(element, items);
        })
        .catch(error => {
            if (error.name !== 'AbortError') {
                console.error('Autocomplete error:', error);
            }
        });
}

function setupRemoteAutocomplete($field, config) {
    if (!$field || !$field.length) return;
    const element = $field[0];
    const url = config?.url || $field.data('autocomplete-url');
    if (!url) return;

    const autocompleteToken = `hotelhub-${Date.now()}-${Math.random().toString(36).slice(2)}`;

    element.setAttribute('type', 'search');
    element.setAttribute('autocomplete', autocompleteToken);
    element.setAttribute('name', autocompleteToken);
    element.setAttribute('autocapitalize', 'off');
    element.setAttribute('autocorrect', 'off');
    element.setAttribute('spellcheck', 'false');
    element.setAttribute('data-lpignore', 'true');
    element.setAttribute('data-form-type', 'other');
    if (element.dataset.autocompleteInitialized === 'true') {
        return;
    }

    const limit = config?.limit || parseInt($field.data('autocomplete-limit'), 10) || 12;
    const minLength = config?.minLength || parseInt($field.data('autocomplete-min'), 10) || 2;
    const getParams = config?.getParams || function () {
        return config?.extraParams || {};
    };
    const onSelect = config?.onSelect || function () { };

    element.dataset.autocompleteInitialized = 'true';
    element._autocompleteOnSelect = onSelect;

    if (!M.Autocomplete.getInstance(element)) {
        $field.autocomplete({
            data: {},
            limit: limit,
            onAutocomplete: function (selectedLabel) {
                const item = element._autocompleteItems ? element._autocompleteItems[selectedLabel] : null;
                if (item) {
                    element._autocompleteSelectedLabel = selectedLabel;
                    element._autocompleteSelectedAt = Date.now();
                    element._autocompleteOnSelect(item, selectedLabel, element);
                }
            }
        });
    }

    let debounceTimer;
    $field.off('input.autocompleteRemote').on('input.autocompleteRemote', function () {
        const query = this.value.trim();
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(function () {
            if (query.length < minLength) {
                updateAutocompleteData(element, []);
                return;
            }
            fetchAutocompleteResults(element, url, query, getParams(), limit);
        }, 200);
    });

    $field.off('change.autocompleteRemote').on('change.autocompleteRemote', function () {
        const selectedLabel = this.value.trim();
        if (!selectedLabel) return;

        if (
            element._autocompleteSelectedLabel === selectedLabel &&
            element._autocompleteSelectedAt &&
            Date.now() - element._autocompleteSelectedAt < 800
        ) {
            return;
        }

        const items = element._autocompleteItems || {};
        let item = items[selectedLabel];

        if (!item) {
            const normalized = selectedLabel.replace(/\s+/g, ' ').trim();
            item = items[normalized];
        }

        if (!item) {
            const matchKey = Object.keys(items).find(key => key.trim() === selectedLabel);
            if (matchKey) {
                item = items[matchKey];
            }
        }

        if (item) {
            element._autocompleteOnSelect(item, selectedLabel, element);
        }
    });
}

function initializeAutocomplete() {
    const personaAutocompleteField = $('#persona_autocomplete');

    if (personaAutocompleteField.length > 0) {
        setupRemoteAutocomplete(personaAutocompleteField, {
            url: personaAutocompleteField.data('autocomplete-url'),
            getParams: function () {
                return getAutocompleteParams(personaAutocompleteField);
            },
            onSelect: function (item, _label, element) {
                const $hidden = resolveAutocompleteHiddenField($(element), '#persona_id');
                if ($hidden.length > 0) {
                    $hidden.val(item.id);
                }
                const btnNuevaPersona = document.getElementById('btnNuevaPersona');
                const btnQuitarCliente = document.getElementById('btnQuitarCliente');
                const personaAutocomplete = document.getElementById('persona_autocomplete');
                if (btnNuevaPersona) btnNuevaPersona.style.display = 'none';
                if (btnQuitarCliente) btnQuitarCliente.style.display = 'block';
                if (personaAutocomplete) personaAutocomplete.disabled = true;
            }
        });
    }

    const arrendadorAutocompleteField = $('#arrendador_autocomplete');

    if (arrendadorAutocompleteField.length > 0) {
        setupRemoteAutocomplete(arrendadorAutocompleteField, {
            url: arrendadorAutocompleteField.data('autocomplete-url'),
            getParams: function () {
                return getAutocompleteParams(arrendadorAutocompleteField);
            },
            onSelect: function (item, _label, element) {
                const $hidden = resolveAutocompleteHiddenField($(element), '#arrendador_id');
                if ($hidden.length > 0) {
                    $hidden.val(item.id);
                }
            }
        });
    }

    const personaPrincipalAutocompleteField = $('#persona_principal_autocomplete');

    if (personaPrincipalAutocompleteField.length > 0) {
        setupRemoteAutocomplete(personaPrincipalAutocompleteField, {
            url: personaPrincipalAutocompleteField.data('autocomplete-url'),
            getParams: function () {
                return getAutocompleteParams(personaPrincipalAutocompleteField);
            },
            onSelect: function (item, _label, element) {
                const $hidden = resolveAutocompleteHiddenField($(element), '#persona_id');
                if ($hidden.length > 0) {
                    $hidden.val(item.id);
                }
            }
        });
    }

    const autocompleteField = $('#agregarOpcionmenu');
    if (autocompleteField.length > 0) {
        const context = autocompleteField.data('autocomplete-context');
        const isSolicitudContext = context === 'solicitud' || $('#opcionmenusTable').length > 0;

        if (isSolicitudContext) {
            let table;
            if ($.fn.DataTable.isDataTable('#opcionmenusTable')) {
                table = $('#opcionmenusTable').DataTable();
            } else {
                table = $('#opcionmenusTable').DataTable({
                    responsive: true,
                    searching: false,
                    paging: false,
                    info: false,
                    language: {
                        emptyTable: "Ningun dato disponible en esta tabla"
                    }
                });
            }

            setupRemoteAutocomplete(autocompleteField, {
                url: autocompleteField.data('autocomplete-url'),
                getParams: function () {
                    return getAutocompleteParams(autocompleteField);
                },
                onSelect: function (item, selectedLabel) {
                    const solicitudId = item.id;
                    const hiddenField = $('#solicitud_id');
                    if (hiddenField.length > 0) {
                        hiddenField.val(solicitudId);
                    }

                    const exists = table
                        .rows()
                        .data()
                        .toArray()
                        .some(row => $(row[0]).data('id') == solicitudId);

                    if (exists) {
                        return;
                    }

                    const label = selectedLabel || item.label || '';
                    const colComprobante = `
                    <div>
                        <input type="hidden" name="solicitud_id[]" value="${solicitudId}">
                        <span data-id="${solicitudId}">${label}</span>
                    </div>`;

                    const colMotivo = `<input type="text" class="validate" name="motivos[${solicitudId}]" value="Error de comprobante">`;
                    const colAccion = `<button type="button" class="btn red lighten-1 btn-sm remove-row">
                                            <i class="material-icons">remove</i>
                                       </button>`;

                    table.row.add([
                        colComprobante,
                        colMotivo,
                        colAccion
                    ]).draw();

                    autocompleteField.val('');
                    if (hiddenField.length > 0) {
                        hiddenField.val('');
                    }
                }
            });

            $('#opcionmenusTable tbody').off('click', '.remove-row');
            $('#opcionmenusTable tbody').on('click', '.remove-row', function () {
                table.row($(this).closest('tr')).remove().draw();
                reinicializarDataTables();
            });
        } else {
            setupRemoteAutocomplete(autocompleteField, {
                url: autocompleteField.data('autocomplete-url'),
                getParams: function () {
                    return getAutocompleteParams(autocompleteField);
                },
                onSelect: function (item) {
                    agregarMenuATabla(item);
                    autocompleteField.val('');
                }
            });
        }
    }

    const acompanantesAutocompleteField = $('#acompanantes_autocomplete');
    const huespedesAutocompleteField = $('#huespedes_autocomplete');
    const acompanantesHiddenField = $('#acompanantes');
    const acompanantesRow = $('#acompanantes-row');
    
    function agregarAcompananteATabla(id, descripcion) {
        const [dni, nombreCompleto] = descripcion.split(' - ');
        const table = $('#acompanantes-table').DataTable();

        table.row.add([
            dni,
            nombreCompleto,
            `<button type="button" class="btn-small red lighten-1 waves-effect waves-light" onclick="eliminarAcompanante(this,${id})">
            <i class="material-icons">delete</i>
        </button>`
        ]).draw(false);
    }

    function cargarAcompanantesIniciales(url, ids, callback) {
        if (!url || !ids.length) return;
        const params = new URLSearchParams();
        params.set('ids', ids.join(','));
        params.set('limit', ids.length);

        fetch(`${url}?${params.toString()}`, {
            headers: { 'Accept': 'application/json' }
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Autocomplete preload failed');
                }
                return response.json();
            })
            .then(items => {
                if (!Array.isArray(items)) return;
                items.forEach(item => {
                    if (item && item.label) {
                        callback(item);
                    }
                });
            })
            .catch(error => {
                console.error('Autocomplete preload error:', error);
            });
    }

    if (acompanantesAutocompleteField.length > 0) {
        setupRemoteAutocomplete(acompanantesAutocompleteField, {
            url: acompanantesAutocompleteField.data('autocomplete-url'),
            getParams: function () {
                return getAutocompleteParams(acompanantesAutocompleteField);
            },
            onSelect: function (item, selectedLabel) {
                const selectedId = item.id;
                const currentAcompanantes = parseJsonArray(acompanantesHiddenField.val());

                if (!currentAcompanantes.includes(selectedId)) {
                    currentAcompanantes.push(selectedId);
                    acompanantesHiddenField.val(JSON.stringify(currentAcompanantes));

                    if (acompanantesRow.length > 0 && acompanantesRow.css('display') === 'none') {
                        acompanantesRow.css('display', 'block');
                    }

                    agregarAcompananteATabla(selectedId, selectedLabel || item.label);
                } else {
                    mostrarMensajeError('Este acompanante ya ha sido agregado.');
                }

                acompanantesAutocompleteField.val('');
            }
        });

        const initialAcompanantes = parseJsonArray(acompanantesHiddenField.val());
        if (initialAcompanantes.length > 0 && $('#acompanantes-table tbody tr').length === 0) {
            if (acompanantesRow.length > 0) {
                acompanantesRow.css('display', 'block');
            }

            cargarAcompanantesIniciales(
                acompanantesAutocompleteField.data('autocomplete-url'),
                initialAcompanantes,
                function (item) {
                    agregarAcompananteATabla(item.id, item.label);
                }
            );
        }
    }

    if (huespedesAutocompleteField.length > 0) {
        setupRemoteAutocomplete(huespedesAutocompleteField, {
            url: huespedesAutocompleteField.data('autocomplete-url'),
            getParams: function () {
                return getAutocompleteParams(huespedesAutocompleteField);
            },
            onSelect: function (item, selectedLabel) {
                const selectedId = item.id;
                const currentAcompanantes = parseJsonArray(acompanantesHiddenField.val());

                if (!currentAcompanantes.includes(selectedId)) {
                    currentAcompanantes.push(selectedId);
                    acompanantesHiddenField.val(JSON.stringify(currentAcompanantes));

                    if (acompanantesRow.length > 0 && acompanantesRow.css('display') === 'none') {
                        acompanantesRow.css('display', 'block');
                    }

                    const label = selectedLabel || item.label || '';
                    const parts = label.split(' - ');
                    const nrodoc = parts.shift() || '';
                    const nombreCompleto = parts.join(' ').trim();

                    agregarAcompanante(selectedId, nrodoc, nombreCompleto);
                } else {
                    mostrarMensajeError('Huesped ya seleccionado');
                }

                huespedesAutocompleteField.val('');
            }
        });

        const initialAcompanantes = parseJsonArray(acompanantesHiddenField.val());
        if (initialAcompanantes.length > 0 && $('#acompanantes-table tbody tr').length === 0) {
            if (acompanantesRow.length > 0) {
                acompanantesRow.css('display', 'block');
            }

            cargarAcompanantesIniciales(
                huespedesAutocompleteField.data('autocomplete-url'),
                initialAcompanantes,
                function (item) {
                    const parts = (item.label || '').split(' - ');
                    const nrodoc = parts.shift() || '';
                    const nombreCompleto = parts.join(' ').trim();
                    agregarAcompanante(item.id, nrodoc, nombreCompleto);
                }
            );
        }
    }

    const productoAutocompleteField = $('#producto_autocomplete');
    if (productoAutocompleteField.length > 0) {
        const hasModal = document.getElementById('producto-modal') !== null;

        setupRemoteAutocomplete(productoAutocompleteField, {
            url: productoAutocompleteField.data('autocomplete-url'),
            getParams: function () {
                return getAutocompleteParams(productoAutocompleteField);
            },
            onSelect: function (item, selectedLabel) {
                if (hasModal) {
                    $('#producto_id').val(item.id);
                    $('#producto-nombre').text(selectedLabel || item.label);

                    const unidadSelect = $('#unidad_producto');
                    const unidadId = item.unidad_id || '';
                    const unidadTexto = item.unidad || 'Unidad';

                    unidadSelect.empty();
                    unidadSelect.append(
                        `<option value="${unidadId}" selected>${unidadTexto}</option>`
                    );

                    M.FormSelect.init(unidadSelect);

                    const modal = M.Modal.getInstance(document.getElementById('producto-modal'));
                    if (modal) {
                        modal.open();
                    }
                } else {
                    agregarProductoATabla(item);
                }
            }
        });
    }

    const servicioAutocompleteField = $('#servicio_autocomplete');
    if (servicioAutocompleteField.length > 0) {
        setupRemoteAutocomplete(servicioAutocompleteField, {
            url: servicioAutocompleteField.data('autocomplete-url'),
            getParams: function () {
                return getAutocompleteParams(servicioAutocompleteField);
            },
            onSelect: function (item) {
                agregarProductoATabla(item);
                servicioAutocompleteField.val('');
            }
        });
    }

    const habField = $('#evolucion-habitacion');
    if (habField.length > 0) {
        setupRemoteAutocomplete(habField, {
            url: habField.data('autocomplete-url'),
            getParams: function () {
                return getAutocompleteParams(habField);
            },
            onSelect: function (item) {
                $('#evolucion-habitacion_id').val(item.id);
                $('#evolucion-btn-quitar-habitacion').show();
                cargarEvolucionHabitacion(item.id);
            }
        });
    }

    const medioPagoField = $('#agregarMedioPago');
    if (medioPagoField.length > 0) {
        setupRemoteAutocomplete(medioPagoField, {
            url: medioPagoField.data('autocomplete-url'),
            getParams: function () {
                const pasarelaId = $('#id_pasarela').val();
                return { pasarela_id: pasarelaId };
            },
            onSelect: function (item) {
                agregarMedioPagoATabla(item);
                medioPagoField.val('');
            }
        });
    }

    function setupReportPersonaAutocomplete(prefix) {
        const field = $(`#${prefix}-persona`);
        if (!field.length) return;

        setupRemoteAutocomplete(field, {
            url: field.data('autocomplete-url'),
            getParams: function () {
                return getAutocompleteParams(field);
            },
            onSelect: function (item) {
                $(`#${prefix}-persona_id`).val(item.id);
                const btnQuitar = document.getElementById(`btn-${prefix}-quitar-persona`);
                if (btnQuitar) {
                    btnQuitar.style.display = 'inline-block';
                }
            }
        });
    }

    [
        'movventa-responsable',
        'movventa-descuento-persona',
        'movventa-cortesia-persona',
        'movventa-descuentos-responsable',
        'movventa-cortesias-responsable',
        'movventa-compromisos-responsable',
        'movventa-cliente',
        'movventa-productos-cliente'
    ].forEach(setupReportPersonaAutocomplete);
}

// Función que delega el evento de búsqueda de RUC
function inicializarBusquedaRUC() {
    document.removeEventListener('click', handleBuscarRUC);
    document.addEventListener('click', handleBuscarRUC);
}

// Manejador del evento buscar RUC
function handleBuscarRUC(event) {
    const buscarRUCButton = event.target.closest('#buscarRUC');
    if (buscarRUCButton) {
        event.preventDefault(); // Prevenir la acción predeterminada

        const ruc = document.getElementById('ruc').value;
        if (ruc) {
            buscarRUCButton.disabled = true;

            // Llamar a la función para hacer la consulta y habilitar el botón después
            consultaRUC(ruc).finally(() => {
                buscarRUCButton.disabled = false;
            });
        } else {
            mostrarMensajeError('Por favor, ingrese un RUC válido.');
        }
    }
}






function inicializarModal() {
    // Inicializar el modal

    var elems = document.querySelectorAll('.modal');
    M.Modal.init(elems);

    $(document).off('click', '.btn-crear');

    $(document).on('click', '.btn-crear', function (event) {
        event.preventDefault();
        mostrarPantallaDeCarga();

        const url = $(this).attr('href');

        fetch(url)
            .then(response => response.text())
            .then(html => {
                document.getElementById('modal-body-content').innerHTML = html;

                const modalInstance = M.Modal.getInstance(document.getElementById('modal'));
                modalInstance.open();

                const selectElems = document.querySelectorAll('select');
                M.FormSelect.init(selectElems);
                ocultarPantallaDeCarga();
                reinicializarDataTables();
                initializeAutocomplete();
                // Inicializar selects
                M.FormSelect.init(document.querySelectorAll('select'));

                // Obtener el valor actual de opciones
                var opcionesElement = document.getElementById('opciones');
                var opciones = opcionesElement ? opcionesElement.value : '[]'; // Si no existe, usa un array vacío
                var chipsData = [];

                try {
                    chipsData = JSON.parse(opciones);
                    if (!Array.isArray(chipsData)) throw new Error('Formato incorrecto');
                    chipsData = chipsData.map(item => ({ tag: item.trim() }));
                } catch (e) {
                    chipsData = opciones ? opciones.split(',').map(item => ({ tag: item.trim() })) : [];
                }

                // Inicializar Chips
                var chipsElement = document.querySelector('#chips-opciones');
                if (chipsElement) {
                    M.Chips.init(chipsElement, {
                        data: chipsData,
                        placeholder: '+Opción',
                        secondaryPlaceholder: 'Añadir más',
                        onChipAdd: actualizarOpcionesHidden,
                        onChipDelete: actualizarOpcionesHidden
                    });
                }
                function actualizarOpcionesHidden(chipInstance) {
                    var chipsInstance = M.Chips.getInstance(document.querySelector('#chips-opciones'));
                    if (!chipsInstance) return;

                    var chipValues = chipsInstance.chipsData.map(chip => chip.tag);
                    document.getElementById('opciones').value = JSON.stringify(chipValues);
                }

                const form = document.getElementById('form');
                form?.addEventListener('submit', enviarFormularioAjax);

                inicializarFormularioTransferenciaProductos();

                // Cuando se cierre el modal, eliminar el contenido del formulario
                modalInstance.options.onCloseEnd = function () {
                    document.getElementById('modal-body-content').innerHTML = ''; // Limpia el contenido del modal
                };
            })
            .catch(error => {
                console.error('Error al cargar el formulario:', error);
            });
    });


    $(document).off('click', '.btn-crear-persona');
    $(document).on('click', '.btn-crear-persona', function (event) {
        event.preventDefault();
        const sucursal_id = document.getElementById('sucursal_id')?.value;

        Swal.fire({
            title: 'Registrar persona',
            text: '¿De qué manera desea registrar a la nueva persona?',
            icon: 'question',
            showCancelButton: true,
            cancelButtonText: 'Nombre',
            confirmButtonText: 'Nro. doc',
            showCloseButton: true,
            closeButtonHtml: '&times;',
            customClass: {
                popup: 'custom-swal-popup',
                confirmButton: 'btn btn-large black lighten-1 waves-effect waves-light z-depth-1',
                cancelButton: 'btn btn-large red lighten-1 waves-effect waves-light z-depth-1',
            },
            buttonsStyling: false,
            backdrop: true,
            didRender: () => {
                // Separar botones manualmente con espacio
                const actions = document.querySelector('.swal2-actions');
                if (actions) {
                    actions.style.display = 'flex';
                    actions.style.justifyContent = 'center';
                    actions.style.gap = '1rem'; // separación entre botones
                }
            }
        }).then((result) => {
            if (result.isConfirmed || result.dismiss === Swal.DismissReason.cancel) {
                const opcionSeleccionada = result.isConfirmed ? 'documento' : 'nombre';
                const urlFormulario = `/persona/create/${sucursal_id}/CRUD?opcion=${opcionSeleccionada}`;

                mostrarPantallaDeCarga();

                fetch(urlFormulario)
                    .then(response => response.text())
                    .then(html => {
                        document.getElementById('modal-body-content').innerHTML = html;
                        const modalInstance = M.Modal.getInstance(document.getElementById('modal'));
                        modalInstance.open();

                        M.FormSelect.init(document.querySelectorAll('select'));
                        const input = document.getElementById('nrodoc');
                        if (input) input.focus();

                        ocultarPantallaDeCarga();

                        document.getElementById('form').addEventListener('submit', enviarFormularioAjax);
                    })
                    .catch(error => {
                        console.error('Error al cargar el formulario:', error);
                        ocultarPantallaDeCarga();
                    });
            }
        });
    });


    // Abrir directamente el formulario con opción por documento (sin mostrar el modal de opciones)
    $(document).on('click', '.btn-crear-cliente-directo', function (event) {
        event.preventDefault();

        const sucursal_id = $(this).data('sucursal-id');
        const accion = $(this).data('accion') || 'RESERVA';
        const movventaId = $(this).data('movventa-id');
        const opcionSeleccionada = 'documento';
        let urlFormulario = `/cliente/create/${sucursal_id}/${accion}?opcion=${opcionSeleccionada}`;

        if (movventaId) {
            urlFormulario += `&movventa_id=${movventaId}`;
        }

        mostrarPantallaDeCarga(); // Mostrar pantalla de carga

        fetch(urlFormulario)
            .then(response => response.text())
            .then(html => {
                // Cargar contenido en el modal principal
                document.getElementById('modal-body-content').innerHTML = html;

                // Abrir el modal principal
                var modalInstance = M.Modal.getInstance(document.getElementById('modal'));
                modalInstance.open();

                // Inicializar select de materialize
                M.FormSelect.init(document.querySelectorAll('select'));

                ocultarPantallaDeCarga(); // Ocultar pantalla de carga

                // Enlace al form handler en modal
                document.getElementById('form').addEventListener('submit', enviarFormularioAjax);
            })
            .catch(error => {
                console.error('Error al cargar el formulario:', error);
                ocultarPantallaDeCarga();
            });
    });

    $(document).off('click', '.btn-crear-cliente');

    $(document).on('click', '.btn-crear-cliente', function (event) {
        event.preventDefault();
        const sucursal_id = document.getElementById('sucursal_id')?.value;

        Swal.fire({
            title: 'Registrar cliente',
            text: '¿De qué manera desea registrar al cliente?',
            icon: 'question',
            showCancelButton: true,
            cancelButtonText: 'Nombre',
            confirmButtonText: 'Nro. doc',
            showCloseButton: true,
            closeButtonHtml: '&times;',
            customClass: {
                popup: 'custom-swal-popup',
                confirmButton: 'btn btn-large black lighten-1 waves-effect waves-light z-depth-1',
                cancelButton: 'btn btn-large red lighten-1 waves-effect waves-light z-depth-1',
            },
            buttonsStyling: false,
            backdrop: true,
            didRender: () => {
                const actions = document.querySelector('.swal2-actions');
                if (actions) {
                    actions.style.display = 'flex';
                    actions.style.justifyContent = 'center';
                    actions.style.gap = '1rem';
                }
            }
        }).then((result) => {
            let opcionSeleccionada = null;

            if (result.isConfirmed) {
                opcionSeleccionada = 'documento';
            } else if (result.dismiss === Swal.DismissReason.cancel) {
                opcionSeleccionada = 'nombre';
            }

            if (opcionSeleccionada) {
                const urlFormulario = `/cliente/create/${sucursal_id}/RESERVA?opcion=${opcionSeleccionada}`;
                mostrarPantallaDeCarga();

                fetch(urlFormulario)
                    .then(response => response.text())
                    .then(html => {
                        document.getElementById('modal-body-content').innerHTML = html;

                        var modalInstance = M.Modal.getInstance(document.getElementById('modal'));
                        modalInstance.open();

                        var selectElems = document.querySelectorAll('select');
                        M.FormSelect.init(selectElems);

                        ocultarPantallaDeCarga();
                        document.getElementById('form').addEventListener('submit', enviarFormularioAjax);
                    })
                    .catch(error => {
                        console.error('Error al cargar el formulario:', error);
                        ocultarPantallaDeCarga();
                    });
            }
        });
    });



}
// Función para manejar la inicialización de eventos para mover el orden
function inicializarEventosDeMovimiento() {
    document.querySelectorAll('.move-order').forEach(function (button) {
        button.removeEventListener('click', handleMoveOrder); // Eliminar cualquier evento anterior
        button.addEventListener('click', handleMoveOrder); // Añadir el evento
    });
}


let breadcrumbStack = [{ title: 'Inicio', url: '/', icono: 'fa-solid fa-home left' }]; // Inicializa con 'Inicio'

function actualizarCabecera(iconoClase, titulo, rutaSeccion) {
    // Validar si la URL corresponde a la ruta específica que no debe actualizar la cabecera
    const urlActual = window.location.pathname;
    const rutaNoActualizar = '/habitacion/dejar-limpiar/';

    if (urlActual.startsWith(rutaNoActualizar)) {
        // Si estamos en una ruta de "dejar-limpiar", no actualizamos la cabecera
        return;
    }

    // Actualiza el ícono de la sección solo con la clase
    const iconoSeccion = document.getElementById('icono-seccion');
    iconoSeccion.className = `fa ${iconoClase} left`; // Asigna la nueva clase del ícono

    // Actualiza el título de la sección
    document.getElementById('titulo-seccion').textContent = titulo;

    const tituloLower = (titulo || '').toLowerCase();
    const rutaLower = String(rutaSeccion || '').toLowerCase();
    if (tituloLower === 'checkout' || rutaLower.indexOf('/checkout') !== -1) {
        breadcrumbStack = breadcrumbStack.filter(function (item) {
            const t = (item.title || '').toLowerCase();
            const u = String(item.url || '').toLowerCase();
            return t !== 'checkin' && u.indexOf('/checkin') === -1 && u.indexOf('editcheckin') === -1;
        });
    }
    if (tituloLower === 'habitaciones' || esUrlListadoHabitaciones(rutaSeccion)) {
        breadcrumbStack = breadcrumbStack.filter(function (item) {
            const t = (item.title || '').toLowerCase();
            const u = String(item.url || '').toLowerCase();
            return t !== 'checkout' && t !== 'checkin' && u.indexOf('/checkout') === -1 && u.indexOf('/checkin') === -1 && u.indexOf('editcheckin') === -1;
        });
    }

    // Verifica si ya existe la sección en el breadcrumbStack para evitar duplicados
    const existsInStack = breadcrumbStack.some(item => item.title === titulo);

    if (!existsInStack) {
        breadcrumbStack.push({ title: titulo, url: rutaSeccion, icono: iconoClase }); // Incluye el ícono
    }

    // Construir el breadcrumb dinámicamente
    const breadcrumbContainer = document.getElementById('breadcrumb-container');
    breadcrumbContainer.innerHTML = ''; // Limpiar el contenido anterior

    breadcrumbStack.forEach((item, index) => {
        const breadcrumbLink = document.createElement('a');
        breadcrumbLink.href = item.url;
        breadcrumbLink.className = 'breadcrumb';
        breadcrumbLink.textContent = item.title;

        // Agregar los atributos `data-icono` y `data-titulo` para cada elemento
        breadcrumbLink.setAttribute('data-icono', item.icono);
        breadcrumbLink.setAttribute('data-titulo', item.title);

        // Solo el último breadcrumb no es clicable
        if (index === breadcrumbStack.length - 1) {
            breadcrumbLink.classList.add('active'); // Última sección no clicable
        } else {
            // Asigna función de navegación al hacer clic en breadcrumbs anteriores
            breadcrumbLink.addEventListener('click', function (event) {
                event.preventDefault(); // Prevenir la redirección predeterminada
                //cargarContenidoDinamicamente(item.url); // Cargar contenido dinámicamente
                // Truncar el breadcrumbStack hasta este punto
                breadcrumbStack = breadcrumbStack.slice(0, index + 1);
                actualizarCabecera(item.icono, item.title, item.url); // Actualiza solo hasta el punto clicado
            });
        }

        // Agregar breadcrumb al contenedor
        breadcrumbContainer.appendChild(breadcrumbLink);
    });

    // Si el título es "Inicio", oculta el breadcrumb extra
    if (titulo.toLowerCase() === 'inicio' || rutaSeccion === '/') {
        breadcrumbStack = [{ title: 'Inicio', url: '/', icono: 'fa-solid fa-home left' }];
        breadcrumbContainer.innerHTML = '<a href="/" class="breadcrumb" data-icono="fa-solid fa-home left" data-titulo="Inicio">Inicio</a>';
        aplicarVisibilidadMainBody(rutaSeccion || '/');

    } else {
        aplicarVisibilidadMainBody(rutaSeccion || '');
    }

    // Aplicar el interceptor de clics a los enlaces del breadcrumb
    interceptarClicksEnBreadcrumbs();
    // ocultarPantallaDeCarga();
}




function interceptarClicksEnBreadcrumbs() {
    // Interceptar los clics solo en los enlaces que tienen una ruta en el breadcrumb
    document.querySelectorAll('#breadcrumb-container a').forEach(function (link) {
        link.addEventListener('click', function (event) {
            event.preventDefault(); // Prevenir la acción predeterminada de redirección
            localStorage.setItem('paginaActual', 1); // Resetear la paginación

            const url = this.getAttribute('href');
            if (url) {
                if (url === '/') {
                    mostrarPantallaDeCarga();
                    aplicarVisibilidadMainBody('/');
                    document.getElementById('main-content').innerHTML = '';
                    finalizarCargaVistaDinamica();

                } else {
                    cargarContenidoDinamicamente(url); // Cargar el contenido dinámicamente

                }

            }
        });
    });
}


// Función que se ejecuta al hacer clic en un botón de mover orden
function handleMoveOrder() {
    const url = this.getAttribute('data-url'); // Obtener la URL desde el atributo data-url

    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Recargar el contenido de la tabla dinámicamente
                actualizarTabla(data.redirect_url);
            } else {
                M.toast({
                    html: data.message,
                    classes: 'red darken-1 rounded'
                });
            }
        })
        .catch(error => console.error('Error:', error));
}



function toggleTiempoHabitacion() {
    const switchHoras = document.getElementById('switchHoras');
    const switchStateInput = document.getElementById('switchState');
    const fechaIngresoDiv = document.getElementById('fecha-ingreso-div');
    const fechaSalidaDiv = document.getElementById('fecha-salida-div');
    const horaIngresoDiv = document.getElementById('hora-ingreso-div');
    const horaSalidaDiv = document.getElementById('hora-salida-div');
    const horacardDiv = document.getElementById('hora-card');
    const diastotales = document.getElementById('diastotales');
    const calcularboton = document.getElementById('calcularboton');
    const horastotales = document.getElementById('horastotales');
    const botonmodalhabitacion = document.getElementById('botonmodalhabitacion');
    const preciohabitaciondia = document.getElementById('preciohabitaciondia');

    // 🔥 INPUT TARIFA (grupal)
    const habitacionId = document.getElementById('habitacion_id')?.value;
    const tarifaInput = document.getElementById('tarifa-' + habitacionId);

    // ====== POR HORAS ======
    if (switchHoras.checked) {
        fechaIngresoDiv.style.display = 'none';
        fechaSalidaDiv.style.display = 'none';
        diastotales.style.display = 'none';
        calcularboton.style.display = 'none';

        if (horacardDiv) horacardDiv.style.display = 'block';
        if (botonmodalhabitacion) botonmodalhabitacion.style.display = 'none';

        switchStateInput.value = 'horas';

        horaIngresoDiv.style.display = 'block';
        horaSalidaDiv.style.display = 'block';
        horastotales.style.display = 'block';

        // 🔥 OBTENER TARIFA POR HORAS Y SETEARLA
        const primeraOpcionHoras = document.querySelector('.card-panel.teal.white-text span');
        if (primeraOpcionHoras && tarifaInput) {
            const texto = primeraOpcionHoras.innerText.trim(); // "10 h - S/ 40"
            const matchPrecio = texto.match(/S\/\s*(\d+(\.\d+)?)/);

            if (matchPrecio) {
                tarifaInput.value = parseFloat(matchPrecio[1]).toFixed(2);
            }

            const horas = parseInt(texto.split(' ')[0], 10);
            if (!isNaN(horas)) {
                setHoraSalida(horas);
            }
        }

        // ====== POR DÍAS ======
    } else {
        horaIngresoDiv.style.display = 'none';
        horaSalidaDiv.style.display = 'none';
        horastotales.style.display = 'none';

        if (horacardDiv) horacardDiv.style.display = 'none';
        if (botonmodalhabitacion) botonmodalhabitacion.style.display = 'block';

        switchStateInput.value = 'dias';

        fechaIngresoDiv.style.display = 'block';
        fechaSalidaDiv.style.display = 'block';
        diastotales.style.display = 'block';
        calcularboton.style.display = 'block';

        // 🔥 RESTAURAR TARIFA POR DÍA
        if (tarifaInput && preciohabitaciondia) {
            tarifaInput.value = parseFloat(preciohabitaciondia.value).toFixed(2);
        }

        calcularTotalCheckin(); // 👈 no se toca
    }
}

function toggleUsuario(checkbox) {
    var usuarioDiv = document.getElementById('divUsuario');
    if (checkbox.checked) {
        usuarioDiv.style.display = 'block';
    } else {
        usuarioDiv.style.display = 'none';
    }
}


var medioPagoSeleccionados = [];

// Definir la función para listar medios de pago
function listarMediopago(idPasarela) {
    $.ajax({
        url: "{{ route('listarmediopago', '') }}/" + idPasarela, // Añadir el idPasarela a la URL
        type: "GET",
        dataType: "json",
        success: function (data) {
            if (data.status === 200) {
                var mediosPago = {};
                $.each(data.mediospago, function (key, medio) {
                    mediosPago[key] = null; // Usado por el autocompletado de Materialize
                });
                $('#agregarMedioPago').autocomplete({
                    data: mediosPago,
                    onAutocomplete: function (selected) {
                        // Agregar el medio de pago seleccionado a la tabla
                        const medio = data.mediospago[selected];
                        agregarMedioPagoATabla(medio);
                    }
                });
            }
        },
        error: function (error) {
            console.error("Error al listar medios de pago: ", error);
        }
    });
}

// Agregar el medio de pago a la tabla dinámica
function agregarMedioPagoATabla(medio) {
    if (medio && !medioPagoSeleccionados.includes(medio.id)) {
        const tbody = $('#mediopagoBody');
        // Eliminar el mensaje "Vincular un nuevo medio de pago" si existe
        $('#mediopagoComentario').remove();

        // Crear una nueva fila en la tabla
        const nuevaFila = `
        <tr data-medio-id="${medio.id}">
            <td class="center">${medio.descripcion}</td>
            <td class="center">
                <a href="#" class="btn red btn-small remove-medio-pago">
                    <i class="material-icons">delete</i>
                </a>
            </td>
        </tr>
    `;
        tbody.append(nuevaFila);

        // Agregar el ID al arreglo de medios de pago seleccionados
        medioPagoSeleccionados.push(medio.id);
        actualizarCampoMedioPagoIds();
    }
}

// Actualizar el valor del campo oculto con los IDs seleccionados
function actualizarCampoMedioPagoIds() {
    $('#medio_pago_ids').val(medioPagoSeleccionados.join(','));
}


function activarAutocomplete() {
    initializeAutocomplete();
}
function activarAutocompleteHabitaciones() {
    initializeAutocomplete();
}
function activarAutocompleteMenu() {
    initializeAutocomplete();
}
function activarAutocompleteServicio() {
    initializeAutocomplete();
}
function actualizarRapidoDet_MovVenta(productId, cantidad) {
    if (document.querySelector('.form-movcompra')) {
        actualizarRapidoDetCompra(productId, cantidad);
        return;
    }

    const tabla = $('#data-table').DataTable();
    let filaExistente = null;

    // Buscar la fila existente para el producto
    tabla.rows().every(function () {
        const rowNode = this.node();
        if ($(rowNode).data('producto-id') === productId) {
            filaExistente = $(rowNode);
            return false; // Detener la iteración
        }
    });

    if (filaExistente) {
        // Obtener el estado de pago del producto
        const estadoPago = filaExistente.find('td:nth-child(9) .badge').text().trim(); // "Pagado" o "Pendiente"

        // Si el estado es "Pagado", crear una nueva fila con los mismos datos
        if (estadoPago === "Pagado") {
            fetch(`/productos/detalle/${productId}`)
                .then(response => response.json())
                .then(producto => {
                    producto.cantidad = cantidad;
                    agregarProductoATabla(producto); // Crear nueva fila para el producto
                })
                .catch(error => {
                    console.error('Error al obtener el producto:', error);
                });
            return; // No realizar ninguna actualización en la fila existente
        }

        // Si el estado es "Pendiente", actualizar la fila existente
        const cantidadActual = parseInt(filaExistente.find('td:nth-child(4)').text().trim()) || 0;
        const nuevaCantidad = cantidadActual + cantidad;

        // Si la cantidad es menor o igual a 0, elimina el producto
        if (nuevaCantidad <= 0) {
            quitarProductoDeTabla(productId);
            return;
        }

        // Actualizar la cantidad
        filaExistente.find('td:nth-child(4)').html(
            `<span class="inpt-cantidad-mc cantidad">${Number(nuevaCantidad).toLocaleString(undefined, { minimumFractionDigits: 0, maximumFractionDigits: 0 })}</span>`
        );

        // Obtener el precio unitario
        const precioUnitario = parseFloat(filaExistente.find('td:nth-child(6)').text().trim().replace(/,/g, '')) || 0;

        // Calcular el nuevo total
        const nuevoTotal = (precioUnitario * nuevaCantidad).toFixed(2);

        // Actualizar el total manteniendo clase y formato
        filaExistente.find('td:nth-child(7)').html(
            `<span class="inpt-cantidad-mc precio-total">${nuevoTotal}</span>`
        );


        // Actualizar totales generales
        actualizarTotales();
        actualizarDetallesProductos(); // Actualizar el JSON después de los cambios
    } else {
        // Si no existe, buscar el producto y agregarlo
        fetch(`/productos/detalle/${productId}`)
            .then(response => response.json())
            .then(producto => {
                producto.cantidad = cantidad;
                agregarProductoATabla(producto); // Agregar el producto a la tabla
            })
            .catch(error => {
                console.error('Error al obtener el producto:', error);
            });
    }
}
function agregarMenuATabla(menu) {
    if (!menu || !menu.id) {
        mostrarMensajeError('Seleccione una opcion de menu valida.');
        return;
    }

    const selectorTabla = $('#seccion-opciones-menu-table').length ? '#seccion-opciones-menu-table' : '#data-table';
    const tabla = $.fn.DataTable.isDataTable(selectorTabla)
        ? $(selectorTabla).DataTable()
        : $(selectorTabla).DataTable();
    let filaExistente = null;

    //Verificar si la opción de menú ya existe en la tabla
    tabla.rows().every(function () {
        const rowNode = this.node();
        if (String($(rowNode).data('menu-id')) === String(menu.id)) {
            filaExistente = this;
            return false; // Detener la iteración
        }
    });

    // Si el menú ya existe, no lo duplicamos
    if (filaExistente) {
        mostrarMensajeError('La opción de menú ya está agregada.');
        return;
    }

    // Crear nueva fila con los datos del menú
    const nuevaFila = [
        `<span> ${menu.tipo_opcion} -  ${menu.descripcion}</span>`,
        `<button type="button" class="btn btn-floating btn-small red white-text tooltipped" 
            data-tooltip="Quitar" onclick="quitarMenuDeTabla(${menu.id});">
            <i class="fa-solid fa-trash-can"></i>
        </button>`
    ];

    // Agregar la fila a la tabla
    const nuevaRowNode = tabla.row.add(nuevaFila).node();
    $(nuevaRowNode).attr('data-menu-id', menu.id);

    tabla.draw();
    actualizarOpcionesMenu(); // Llamar función para actualizar datos
    evaluarOpcionesMenus(); // Evaluar si hay que ocultar el mensaje "No hay opciones disponibles"
}
function evaluarOpcionesMenus() {
    const selectorTabla = $('#seccion-opciones-menu-table').length ? '#seccion-opciones-menu-table' : '#data-table';
    const tabla = $.fn.DataTable.isDataTable(selectorTabla)
        ? $(selectorTabla).DataTable()
        : $(selectorTabla).DataTable();
    const mensaje = $('#opcionmenusComentario');

    if (tabla.rows().count() > 0) {
        mensaje.hide();
    } else {
        mensaje.show();
    }
}
// Función para eliminar opción de menú de la tabla
function quitarMenuDeTablaLegacy(menuId) {
    const tabla = $('#opcionmenusTable').DataTable();
    tabla.rows().every(function () {
        const rowNode = this.node();
        if ($(rowNode).data('menu-id') === menuId) {
            this.remove();
            return false; // Detener la iteración después de eliminar
        }
    });

    tabla.draw();
    actualizarOpcionesMenus();
    //  evaluarOpcionesMenus();
}

// Ocultar mensaje si hay opciones, mostrar si no hay


// Simula actualización de JSON o almacenamiento de opciones
function actualizarOpcionesMenu() {
    let opciones = [];
    const selectorTabla = document.getElementById('seccion-opciones-menu-table')
        ? '#seccion-opciones-menu-table'
        : '#data-table';

    document.querySelectorAll(`${selectorTabla} tbody tr[data-menu-id]`).forEach(tr => {
        const menuId = parseInt(tr.dataset.menuId, 10);
        if (Number.isInteger(menuId) && menuId > 0 && !opciones.includes(menuId)) {
            opciones.push(menuId);
        }
    });
    document.getElementById("opciones_menu_json").value = JSON.stringify(opciones);
}

function quitarMenuDeTabla(menuId) {
    const selectorTabla = document.getElementById('seccion-opciones-menu-table')
        ? '#seccion-opciones-menu-table'
        : '#data-table';
    const row = document.querySelector(`${selectorTabla} tr[data-menu-id="${menuId}"]`);
    if (row) {
        row.remove();
    }
    actualizarOpcionesMenu();
}


function agregarProductoATabla(producto) {
    if (document.querySelector('.form-movcompra')) {
        agregarProductoATablaCompra(producto);
        return;
    }

    const tabla = $('#data-table').DataTable();

    // Verificar si el producto ya existe en la tabla
    let filaExistente = null;

    tabla.rows().every(function () {
        const rowNode = this.node();
        if ($(rowNode).data('producto-id') === producto.id) {
            filaExistente = this;
            return false; // Detener la iteración
        }
    });

    // Obtener el estado de pago de la fila existente (si la fila ya existe)
    const estadoPago = filaExistente ? $(filaExistente.node()).find('td:nth-child(9) .badge').text().trim() : null;

    // Si el estado es "Pagado", crear una nueva fila con los mismos datos
    if (estadoPago === "Pagado" || !filaExistente) {
        // Agregar nueva fila
        const nuevaFila = [
            producto.descripcion,
            `<td><span class="inpt-cantidad-mc">${producto.precio_compra}</span></td>`, // Mostrar con 2 decimales
            producto.unidad,
            `<span class="inpt-cantidad-mc cantidad" data-id="${producto.id}">${producto.cantidad || 1}</span>`, // Clase "cantidad"
            `<span class="inpt-cantidad-mc igv">${producto.igv}</span>`,
            `<span class="inpt-cantidad-mc precio-unitario" data-id="${producto.id}">${producto.precio_unitario}</span>`, // Clase "precio-unitario"
            `<span class="inpt-cantidad-mc precio-total" data-id="${producto.id}">${(producto.precio_unitario * (producto.cantidad || 1)).toFixed(2)}</span>`, // Clase "precio-total"
            producto.codigo,

            // Situación con badge dinámico
            `<td><span class="badge yellow black-text">Pendiente</span></td>`, // Se asigna "Pendiente" por defecto, se puede cambiar si es necesario

            // Botones de acción
            `<button type="button" class="btn btn-floating btn-small tooltipped bd-primary white-text" data-tooltip="Sumar" onclick="actualizarRapidoDet_MovVenta(${producto.id}, 1);">
            <i class="fa-solid fa-plus"></i>
        </button>
        <button type="button" class="btn btn-floating btn-small tooltipped bd-primary white-text" data-tooltip="Restar" onclick="actualizarRapidoDet_MovVenta(${producto.id}, -1);">
            <i class="material-icons">remove</i>
        </button>
        <button type="button" class="btn btn-floating btn-small red white-text tooltipped" data-tooltip="Quitar" onclick="quitarProductoDeTabla(${producto.id});">
            <i class="fa-solid fa-trash-can"></i>
        </button>`
        ];

        // Agregar la fila a la tabla
        const nuevaRowNode = tabla.row.add(nuevaFila).node();
        $(nuevaRowNode)
            .attr('data-producto-id', producto.id)
            .attr('data-igv-id', producto.igv_id || '')
            .attr('data-igv-porcentaje', producto.porcentaje_igv ?? 0)
            .attr('onclick', 'editarDetalle(this, event)'); // ← Evento de edición en toda la fila


        tabla.draw();
        actualizarTotales();
        actualizarDetallesProductos(); // Actualizar JSON
        evaluarBotones(); // Evaluar habilitación de botones y visibilidad del campo "Total a Pagar"
        reinicializarDataTables();
    } else {
        // Si la fila ya existe y el estado es "Pendiente", actualizar la cantidad y el total
        const nuevaCantidad = parseInt($(filaExistente.node()).find(`.cantidad[data-id="${producto.id}"]`).text()) + parseInt(producto.cantidad || 1);
        $(filaExistente.node()).find(`.cantidad[data-id="${producto.id}"]`).text(nuevaCantidad);

        // Actualizar precio total
        const nuevoTotal = (producto.precio_unitario * nuevaCantidad).toFixed(2);
        $(filaExistente.node()).find(`.precio-total[data-id="${producto.id}"]`).text(nuevoTotal);

        actualizarTotales();
        actualizarDetallesProductos(); // Actualizar JSON
        evaluarBotones(); // Evaluar habilitación de botones y visibilidad del campo "Total a Pagar"
    }
}



function evaluarBotones() {
    const tabla = $('#data-table').DataTable();
    const checkoutButton = document.querySelector("button[onclick=\"handleAction('checkout')\"]");
    const cobrarButton = document.getElementById('btn-cobrar') || document.querySelector("button[onclick=\"handleAction('cobrar')\"]");
    const totalField = document.querySelector(".total");
    const cobrarBloqueado = cobrarButton?.dataset.forceDisabled === '1';

    if (tabla.rows().count() > 0) {
        // Si hay productos, habilitar el botón "Cobrar" y deshabilitar "Check-Out"
        if (cobrarButton && !cobrarBloqueado) cobrarButton.disabled = false;
        if (checkoutButton) checkoutButton.disabled = true;

        // Mostrar el campo "Total a Pagar"
        if (totalField) totalField.style.display = "block";
    } else {
        // Si no hay productos, deshabilitar ambos botones como medida de seguridad
        if (cobrarButton) cobrarButton.disabled = true;
        if (checkoutButton) checkoutButton.disabled = false;

        // Ocultar el campo "Total a Pagar"
        if (totalField) totalField.style.display = "none";
    }
}

function evaluarBotonesAlCalcular() {
    const checkoutButton = document.querySelector("button[onclick=\"handleAction('checkout')\"]");
    const cobrarButton = document.getElementById('btn-cobrar') || document.querySelector("button[onclick=\"handleAction('cobrar')\"]");
    const cobrarcheckoutButton = document.querySelector("button[onclick=\"handleAction('cobrar_checkout')\"]");
    const totalField = document.querySelector(".total");
    const cobrarBloqueado = cobrarButton?.dataset.forceDisabled === '1';

    // Si hay productos, habilitar el botón "Cobrar" y deshabilitar "Check-Out"
    if (cobrarButton && !cobrarBloqueado) cobrarButton.disabled = false;
    if (cobrarcheckoutButton) cobrarcheckoutButton.disabled = false;

    if (checkoutButton) checkoutButton.disabled = true;
    // Mostrar elementos ocultos si existen
    const documento = document.getElementById('documento');
    if (documento) documento.style.display = 'block';

    const tipopago = document.getElementById('tipopago');
    if (tipopago) tipopago.style.display = 'block';
    // Mostrar el campo "Total a Pagar"
    if (totalField) totalField.style.display = "block";

}


function agregarMontoMedioPago(monto) {
    const tablaMediosPago = document.querySelector('#tabla-medios-pago tbody');
    if (!tablaMediosPago) return;

    // 🔥 Buscar fila que tenga badge Pagado
    let filaExistente = Array.from(tablaMediosPago.querySelectorAll('tr')).find(tr =>
        tr.querySelector('span.badge[data-badge-caption="Pagado"]')
    );

    if (filaExistente) {
        // 👉 Columna Monto (2da columna)
        let montoCelda = filaExistente.querySelector('td.monto') || filaExistente.querySelector('td:nth-child(2)');

        if (montoCelda) {
            montoCelda.textContent = parseFloat(monto).toFixed(2);
            montoCelda.classList.add('inpt-cantidad-mc', 'monto');
            montoCelda.id = 'total-display';
        }
    } else {
        // Crear fila si no existe
        const nuevaFila = `
            <tr onclick="editarMovimiento(this, event)">
                <td>
                    <span class="new badge green white-text" data-badge-caption="Pagado"></span>
                </td>
                <td class="inpt-cantidad-mc monto" id="total-display">${parseFloat(monto).toFixed(2)}</td>
                <td class="medio-pago" data-mediopago-id="1">Efectivo</td>
                <td class="detalles dtr-hidden" style="display:none;"></td>
                <td class="notas dtr-hidden" style="display:none;"></td>
                <td class="fecha-vencimiento dtr-hidden" style="display:none;"></td>
                <td>
                    <button type="button"
                        class="btn btn-small red darken-2"
                        style="border-radius:50%;"
                        onclick="eliminarFila(this); event.stopPropagation();">
                        <i class="material-icons">remove</i>
                    </button>
                </td>
            </tr>
        `;
        tablaMediosPago.insertAdjacentHTML('beforeend', nuevaFila);
    }

    // 🔹 Mantener sincronizado
    actualizarTotalMediosPago();
    actualizarDetallesMovimientosVenta();
}

function actualizarTotalMediosPago() {
    const tablaMediosPago = document.querySelector('#tabla-medios-pago tbody');
    let totalMonto = 0;

    tablaMediosPago.querySelectorAll('tr').forEach(fila => {
        let monto = parseFloat(fila.querySelector('td:nth-child(2)').textContent) || 0;
        totalMonto += monto;
    });

}

function eliminarMedioPago(boton) {
    const fila = boton.closest('tr');
    if (fila) {
        fila.remove();
        actualizarTotalMediosPago();
    }
    actualizarDetallesMovimientosVenta();

}




function cargarProductosPorCategoria(categoriaId, clasificacion) {

    fetch(`/productos/filtrar/${categoriaId}/${clasificacion}`)
        .then(response => response.json())
        .then(data => {
            renderizarProductos(data);
            reinicializarDataTables();

        })
        .catch(error => {
            console.error('Error al cargar productos:', error);
        });
}

function renderizarProductos(productos) {
    const container = document.querySelector('.row.products-container');
    container.innerHTML = ''; // Limpiar productos previos

    productos.forEach(producto => {
        let productCard;

        if (producto.parametroCarrito === 'Si') {
            productCard = `
                <div class="col s12 m4 l4">
                    <div class="card" style="margin: 5px; padding: 5px; min-width:130px;">
                        <div class="card-content" style="padding: 5px;">
                            <div style="font-weight:600;font-size:0.85rem;padding-top:-6px;line-height:0.85rem; text-align: center">
                                ${producto.descripcion}
                            </div>
                            <div style="font-size:0.75rem;padding-left:10px;padding-right:10px; text-align: center">
                                ${producto.stock}  ${producto.unidad}
                            </div>
                            <p style="font-weight:800;font-size: 14px; text-align: center;">
                                S/. ${producto.precio_total.toFixed(2)}
                            </p>
                        </div>
                        <div class="card-action" style="padding: 5px; max-height: 105px;">
                            <div class="row without-tb valign-wrapper" style="padding-right:7px">
                                <div class="col s2 center" style="padding: 0;">
                                    <button class="btn-floating btn-small waves-effect waves-light"
                                        id="btn-restar_${producto.id}"
                                        onclick="manipularCantidadDeCartaDeProducto('${producto.id}', -1);"
                                        type="button"
                                        style="width:24px;height:24px;display:flex;align-items:center;justify-content:center;"
                                        tabindex="0">
                                        <i style="font-size:14px;" class="material-icons">remove</i>
                                    </button>
                                </div>
                                <div class="col s6 center" style="padding: 0;">
                                    <input autocomplete="off"
                                        class="inpt-cantidad-mc submit-form"
                                        id="cantidad_${producto.id}"
                                        name="cantidad_${producto.id}" type="text"
                                        value="1"
                                        style="height:2rem!important;font-size:12px;text-align:center;"
                                        oninput="validateThreeDecimalNumbers(this);"
                                        inputmode="numeric" tabindex="0">
                                </div>
                                <div class="col s2 center" style="padding: 0;">
                                    <button class="btn-floating btn-small waves-effect waves-light"
                                        id="btn-sumar_${producto.id}"
                                        onclick="manipularCantidadDeCartaDeProducto('${producto.id}', 1);"
                                        type="button"
                                        style="width:24px;height:24px;display:flex;align-items:center;justify-content:center;"
                                        tabindex="0">
                                        <i style="font-size:14px;" class="material-icons">add</i>
                                    </button>
                                </div>
                            </div>
                            <div class="row without-bottom">
                                <div class="col s6" style="padding-right:2px;">
                                    <button class="btn btn-small bg-secondary center seleccionar-producto truncate"
                                        type="button"
                                        style="width:100%;padding: 5px; font-size:12px; display:flex; align-items:center; justify-content:center;"
                                        title="Seleccionar producto"
                                        onclick="actualizarRapidoDet_MovVenta('${producto.id}', obtenerCantidadInput('${producto.id}'));"
                                        tabindex="0">
                                        <i class="fa-solid fa-cart-plus center"></i>
                                    </button>
                                    </div>
                            <div class="col s6" style="padding-left:2px;">
                                    <button class="btn btn-small bg-primary center seleccionar-producto truncate"
                                        type="button"
                                        style="width:100%;padding: 5px; font-size:12px; display:flex; align-items:center; justify-content:center;"
                                        title="Añadir al carrito"
                                        onclick="actualizarRapidoDet_MovVenta('${producto.id}', obtenerCantidadInput('${producto.id}'));"
                                        tabindex="0">
                                        <i class="fa-solid fa-plus center"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        } else {
            productCard = `
                <div class="col s12 m4 l4">
                    <div class="card" style="margin: 5px; padding: 5px; min-width:130px;">
                        <div class="card-content" style="padding: 5px;">
                            <div style="font-weight:600;font-size:0.85rem;padding-top:-6px;line-height:0.85rem; text-align: center">
                                ${producto.descripcion}
                            </div>
                            <div style="font-size:0.75rem;padding-left:10px;padding-right:10px; text-align: center">
                                ${producto.stock}  ${producto.unidad}
                            </div>
                            <p style="font-weight:800;font-size: 14px; text-align: center;">
                                S/. ${producto.precio_total.toFixed(2)}
                            </p>
                        </div>
                        <div class="card-action" style="padding: 5px; max-height: 105px;">
                            <div class="row without-tb valign-wrapper" style="padding-right:7px">
                                <div class="col s2 center" style="padding: 0;">
                                    <button class="btn-floating btn-small waves-effect waves-light"
                                        id="btn-restar_${producto.id}"
                                        onclick="manipularCantidadDeCartaDeProducto('${producto.id}', -1);"
                                        type="button"
                                        style="width:24px;height:24px;display:flex;align-items:center;justify-content:center;"
                                        tabindex="0">
                                        <i style="font-size:14px;" class="material-icons">remove</i>
                                    </button>
                                </div>
                                <div class="col s6 center" style="padding: 0;">
                                    <input autocomplete="off"
                                        class="inpt-cantidad-mc submit-form"
                                        id="cantidad_${producto.id}"
                                        name="cantidad_${producto.id}" type="text"
                                        value="1"
                                        style="height:2rem!important;font-size:12px;text-align:center;"
                                        oninput="validateThreeDecimalNumbers(this);"
                                        inputmode="numeric" tabindex="0">
                                </div>
                                <div class="col s2 center" style="padding: 0;">
                                    <button class="btn-floating btn-small waves-effect waves-light"
                                        id="btn-sumar_${producto.id}"
                                        onclick="manipularCantidadDeCartaDeProducto('${producto.id}', 1);"
                                        type="button"
                                        style="width:24px;height:24px;display:flex;align-items:center;justify-content:center;"
                                        tabindex="0">
                                        <i style="font-size:14px;" class="material-icons">add</i>
                                    </button>
                                </div>
                            </div>
                            <div class="row without-bottom">
                                <div class="col s12">
                                    <button class="btn btn-small bg-primary center seleccionar-producto truncate"
                                        type="button"
                                        style="width:100%;padding: 5px; font-size:12px; display:flex; align-items:center; justify-content:center;"
                                        title="Añadir al carrito"
                                        onclick="actualizarRapidoDet_MovVenta('${producto.id}', obtenerCantidadInput('${producto.id}'));"
                                        tabindex="0">
                                        <i class="fa-solid fa-cart-plus center" style="margin-right: 5px;"></i> Agregar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        container.insertAdjacentHTML('beforeend', productCard);
    });
}


function obtenerCantidadInput(productId) {
    const cantidadInput = document.querySelector(`.inpt-cantidad-mc#cantidad_${productId}`);
    if (cantidadInput) {
        return parseInt(cantidadInput.value) || 1; // Devuelve el valor actual o 1 si no es válido
    }
    console.error(`El input con id "cantidad_${productId}" no se encontró.`);
    return 1; // Valor predeterminado en caso de error
}

function actualizarTotales() {
    const tabla = $('#data-table').DataTable(); // Usar la instancia de DataTables
    let totalGeneral = 0;
    let totalProductosServicios = 0;

    // Mostrar elementos ocultos si existen
    const documento = document.getElementById('documento');
    if (documento) documento.style.display = 'block';

    const tipopago = document.getElementById('tipopago');
    if (tipopago) tipopago.style.display = 'block';

    // Iterar sobre todas las filas visibles en la tabla
    tabla.rows({ search: "applied" }).every(function () {
        const rowNode = this.node(); // Obtener el nodo de la fila

        // Ignorar filas con estado "Pagado"
        const estadoPagoCelda = $(rowNode).find('td:nth-child(9) span');
        // if (estadoPagoCelda.length && estadoPagoCelda.text().trim() === 'Pagado') {
        //     return; // Saltar esta fila
        // }

        const cantidadCelda = $(rowNode).find('td:nth-child(6)'); // Cantidad
        const precioUnitarioCelda = $(rowNode).find('td:nth-child(4)'); // Precio Unitario


        if (cantidadCelda.length && precioUnitarioCelda.length) {
            const cantidad = parseFloat(cantidadCelda.text().trim()) || 0;
            const precioUnitario = parseFloat(precioUnitarioCelda.text().trim()) || 0;
            const subtotal = cantidad * precioUnitario;
            totalProductosServicios += subtotal; // Calcular total de productos/servicios
            totalGeneral += subtotal; // Calcular total general
        }
    });

    // Verificar si el tfoot existe, si no, crearlo dinámicamente
    const table = document.querySelector('#data-table');

    if (table) {
        let tfoot = table.querySelector('tfoot');
        if (!tfoot) { // Si no existe, crear uno
            tfoot = document.createElement('tfoot');
            tfoot.innerHTML = `
                <tr>
                    <td colspan="6" class="right-align" style="font-weight: bold;">Total Productos / Servicios</td>
                    <td colspan="4" class="inpt-cantidad-mc" style="font-weight: bold;">
                        0.00
                    </td>
                </tr>`;
            table.appendChild(tfoot);
        }
    }

    // Actualizar el total de productos/servicios en el tfoot (reemplazando, no sumando)
    const tfootTotal = document.querySelector('#data-table tfoot td[colspan="4"]');
    if (tfootTotal) {
        tfootTotal.textContent = `${totalProductosServicios.toFixed(2)}`; // Reemplazar total
    }

    // Verificar si la situación es "A" (alojamiento) y sumar el total de alojamiento
    const situacion = document.getElementById('situacion');
    if (situacion && situacion.value === 'A') {
        const totalAlojamientoField = document.getElementById('totalalojamiento');
        if (totalAlojamientoField) {
            const totalAlojamiento = parseFloat(totalAlojamientoField.value) || 0;
            totalGeneral += totalAlojamiento;
        }
    }

    // Actualizar el total general en el campo de entrada
    const totalField = document.getElementById('totalapagar') || document.getElementById('total');

    if (totalField) {
        const totalFormateado = totalGeneral.toFixed(2);
        totalField.value = totalFormateado;
        totalField.setAttribute('value', totalFormateado);
    }


    // Actualizar los medios de pago con el total
    agregarMontoMedioPago(totalGeneral.toFixed(2));
}




function manipularCantidadDeCartaDeProducto(productId, cambio) {
    // Seleccionar específicamente el input dentro de la tarjeta del producto
    const cantidadInput = document.querySelector(`.inpt-cantidad-mc#cantidad_${productId}`);


    if (cantidadInput) {
        let cantidadActual = parseInt(cantidadInput.value) || 1; // Obtener cantidad actual
        let nuevaCantidad = cantidadActual + cambio;

        if (nuevaCantidad < 1) {
            nuevaCantidad = 1; // Evitar cantidades menores a 1
        }

        // Actualizar el valor del input
        cantidadInput.value = nuevaCantidad;

        // Forzar la actualización visual del input
        cantidadInput.setAttribute('value', nuevaCantidad);


        // Disparar un evento para reflejar otros cambios, si fuera necesario
        cantidadInput.dispatchEvent(new Event('change'));
    } else {
        console.error(`El input dentro de la tarjeta con id "cantidad_${productId}" no se encontró.`);
    }
}
function quitarProductoDeTabla(productId) {
    const tablaDT = $('#data-table').DataTable();

    // Buscar la fila con el producto
    tablaDT.rows().every(function () {
        const fila = this.node();
        if (fila && fila.getAttribute('data-producto-id') === String(productId)) {
            this.remove(); // Usar método oficial de DataTables
        }
    });

    tablaDT.draw(); // Redibujar tabla

    actualizarTotales(); // Tu función para actualizar el total
    actualizarDetallesProductos(productId); // Tu función para actualizar el JSON
}


function actualizarDetallesProductos(productIdToRemove = null) {
    const form = document.getElementById('form');
    if (esFormularioMovventa(form)) {
        sincronizarDetallesJsonMovVenta();
        return;
    }

    const tabla = $('#data-table').DataTable(); // Usar la instancia de DataTables
    if (!tabla) {
        console.error("No se encontró la tabla de detalle de productos.");
        return;
    }

    let detallesProductos = [];

    // Iterar sobre todas las filas visibles de DataTables
    tabla.rows().every(function () {
        const fila = $(this.node()); // Obtener la fila como un nodo jQuery
        const productoId = fila.data("producto-id");
        if (!productoId) return;

        // Si se proporcionó un ID para eliminar, omitir este producto
        if (productIdToRemove && productoId === productIdToRemove) return;

        const descripcion = fila.find("td:nth-child(1)").text().trim();
        const precioCompra = parseFloat(fila.find("td:nth-child(2)").text().trim()) || 0;
        const unidad = fila.find("td:nth-child(3)").text().trim();
        const cantidad = parseFloat(fila.find("td:nth-child(4)").text().trim()) || 0;
        const igv = parseFloat(fila.find("td:nth-child(5)").text().trim()) || 0;
        const precioUnitario = parseFloat(fila.find("td:nth-child(6)").text().trim()) || 0;
        const total = parseFloat(fila.find("td:nth-child(7)").text().trim()) || 0;
        const codigo = fila.find("td:nth-child(8)").text().trim();
        const categoria = 'Sin Categoría'; // Si necesitas manejar categorías, puedes agregar otra columna o atributo.
        const situacion = fila.find("td:nth-child(9)").text().trim() === 'Pagado' ? 'P' : 'N';

        detallesProductos.push({
            producto_id: productoId,
            situacion: situacion,

            descripcion: descripcion,
            precio_compra: precioCompra,
            unidad: unidad,
            cantidad: cantidad,
            igv: igv,
            precio_unitario: precioUnitario,
            total: total,
            codigo: codigo,
            categoria: categoria,
            opc_categoria: {},
            notas: ""
        });
    });


    // Actualizar el campo hidden con los detalles serializados
    const detallesJsonField = document.getElementById("detalles_json");
    if (detallesJsonField) {
        detallesJsonField.value = JSON.stringify(detallesProductos);
    } else {
        console.error("No se encontró el campo hidden #detalles_json para guardar los detalles.");
    }
}





function actualizarDetallesMovimientosVenta() {
    const tabla = document.querySelector("#tabla-medios-pago tbody");
    const detallesMovimientos = [];

    if (!tabla) return;

    tabla.querySelectorAll("tr").forEach(fila => {

        // 🔥 OBTENER TIPO DESDE EL BADGE
        const badge = fila.querySelector('span.badge');
        const tipo = badge
            ? badge.getAttribute('data-badge-caption') || 'Pagado'
            : 'Pagado';

        // Monto
        const montoCelda = fila.querySelector("td.monto") || fila.querySelector("td:nth-child(2)");
        const monto = parseFloat(montoCelda?.textContent.trim()) || 0;

        // Medio de pago
        const medioPagoCelda = fila.querySelector(".medio-pago") || fila.querySelector("td:nth-child(3)");
        const medioPago = medioPagoCelda?.textContent.trim() || '';

        const mediopagoId = medioPagoCelda?.dataset.mediopagoId || 1;

        // Notas
        const notasCelda = fila.querySelector(".notas") || fila.querySelector("td:nth-child(5)");
        const notas = notasCelda?.textContent.trim() || '';

        detallesMovimientos.push({
            tipo: tipo,
            monto: monto,
            medio_pago: medioPago,
            mediopago_id: mediopagoId,
            notas: notas
        });
    });

    document.getElementById("detalles_movimientos_json").value =
        JSON.stringify(detallesMovimientos);
}

function mostrarAlertaCajaCerrada() {
    return Swal.fire({
        icon: 'warning',
        title: 'Caja cerrada',
        text: 'Debes abrir la caja para ingresar a una habitación.',
        confirmButtonText: 'Ir a Caja Chica',
        showCancelButton: false,
        showCloseButton: false,
        allowOutsideClick: true,
        allowEscapeKey: true
    }).then((resultado) => {
        if (!resultado.isConfirmed) {
            return resultado;
        }

        if (typeof cargarContenidoDinamicamente === 'function') {
            return cargarContenidoDinamicamente('/movcaja').then(() => {
                if (typeof restaurarBreadcrumbMovcaja === 'function') {
                    restaurarBreadcrumbMovcaja();
                }
                return resultado;
            });
        }

        window.location.href = '/movcaja';
        return resultado;
    });
}

function abrirModal(situacion, habitacionId) {
    const habitacionCard = document.querySelector(
        `.card-habitacion[data-habitacion-id="${habitacionId}"]`
    );

    if (habitacionCard?.dataset.cajaAbierta === '0') {
        mostrarAlertaCajaCerrada();
        return;
    }

    if (situacion === 'Disponible') {
        $('#modalDisponible').find('.btn-cargar-crud[data-url-base]').each(function () {
            const baseUrl = $(this).attr('data-url-base');
            $(this).attr('href', `${baseUrl}/${habitacionId}`);
        });
        $('#modalDisponible').modal('open');
    } else if (situacion === 'Ocupada') {
        // Cargar botones como siempre
        $('#modalOcupada')
            .find('[data-url-base]')
            .each(function () {

                const baseUrl = $(this).data('url-base');
                const finalUrl = `${baseUrl}/${habitacionId}?vista=alojamiento`;

                // Caso 1: es un <a>
                if ($(this).is('a')) {
                    $(this).attr('href', finalUrl);
                    return;
                }

                // Caso 2: es un input dentro de un form
                const $form = $(this).closest('form');
                if ($form.length) {
                    $form.attr('action', finalUrl);
                }
            });


        // 🔵 Ahora haremos una consulta AJAX para saber si está pagado o pendiente
        $.ajax({
            url: `/verificar-estado-habitacion/${habitacionId}`, // creamos esta nueva API
            type: 'GET',
            success: function (response) {
                if (response.success) {
                    const situacionPago = response.data.situacion_pago; // "P" o "A"

                    // Botón ANULAR (nuevo diseño)
                    const botonAnular = $('#modalOcupada')
                        .find('.anular-form button[type="submit"]');

                    if (situacionPago === 'P') { // Pagado → NO se puede anular
                        botonAnular
                            .prop('disabled', true)
                            .addClass('disabled')
                            .css({
                                'pointer-events': 'none',
                                'opacity': '0.45'
                            });
                    } else { // Pendiente → sí se puede anular
                        botonAnular
                            .prop('disabled', false)
                            .removeClass('disabled')
                            .css({
                                'pointer-events': 'auto',
                                'opacity': '1'
                            });
                    }
                }

            },
            error: function (xhr) {
                console.error('Error al consultar estado de pago', xhr);
            }
        });

        // Obtener el comentario de la habitación haciendo clic en la habitación
        const comentario = $(`[data-habitacion-id="${habitacionId}"]`).find('.ribbon-wrapper.comentario').data('comentario');

        // Mostrar comentario en el modal
        $('#modalOcupada').find('.comentario-habitacion').text(comentario || ""); // Asignar comentario al modal

        // Obtener el número de habitación y tipo de habitación
        const numeroHabitacion = $(`[data-habitacion-id="${habitacionId}"]`).find('.numerohabitacion').text();
        const tipoHabitacion = $(`[data-habitacion-id="${habitacionId}"]`).find('.tipohabitacion-nombre').text();

        // Actualizar el título del modal con el número y tipo de habitación
        $('#modalOcupada').find('.modal-header-titulo').text(`Opciones de la Habitación ocupada ${tipoHabitacion} ${numeroHabitacion}`);

        $('#modalOcupada').modal('open');
    } else if (situacion === 'Mantenimiento') {
        $('#modalMantenimiento').find('.btn-cargar-crud[data-url-base]').each(function () {
            const baseUrl = $(this).attr('data-url-base');
            $(this).attr('href', `${baseUrl}/${habitacionId}`);
        });
        $('#modalMantenimiento').modal('open');
    } else if (situacion === 'Limpieza') {
        $('#modalLimpieza').find('.btn-cargar-crud[data-url-base]').each(function () {
            const baseUrl = $(this).attr('data-url-base');
            $(this).attr('href', `${baseUrl}/${habitacionId}`);
        });
        $('#modalLimpieza').modal('open');
    }
}



function toggleModoReserva() {
    let switchElement = document.getElementById("switchHoras");
    let switchStateInput = document.getElementById("switchState");
    let agregarHabitacionElement = document.querySelector(".col.s12.center-align[style*='display']");
    let tarifaPrincipalElement = document.getElementById("divtarifaprincipal");
    let tarifaGrupoElement = document.getElementById("tarifa-grupo");
    let inputHidden = document.getElementById("detalles_habitaciones_json");

    if (!inputHidden) return; // Evitar errores si el input no existe

    // **Vaciar habitacionesSeleccionadas antes de agregar nuevas**
    habitacionesSeleccionadas.clear();

    // Obtener la data de la habitación actual desde el input hidden
    let habitacionesArray = JSON.parse(inputHidden.value || "[]");

    if (switchElement.checked) {
        // **Modo Grupal**
        switchStateInput.value = "grupo";


        // Mostrar el botón "Agregar Habitación"
        if (agregarHabitacionElement) agregarHabitacionElement.style.display = "block";

        // Ocultar la tarifa principal
        if (tarifaPrincipalElement) tarifaPrincipalElement.style.display = "none";

        // Mostrar la tarifa de grupo
        if (tarifaGrupoElement) tarifaGrupoElement.style.display = "block";

        // **Agregar la habitación actual a `habitacionesSeleccionadas` y marcar su checkbox**
        habitacionesArray.forEach(habitacion => {
            let habitacionId = String(habitacion.id);
            habitacionesSeleccionadas.set(habitacionId, habitacion);

            // Marcar el checkbox de la habitación
            let checkbox = document.querySelector(`.habitacion-checkbox[data-id="${habitacionId}"]`);
            if (checkbox) {
                checkbox.checked = true;
            }
        });

    } else {
        // **Modo Único**
        switchStateInput.value = "unico";

        // Ocultar el botón "Agregar Habitación"
        if (agregarHabitacionElement) agregarHabitacionElement.style.display = "none";

        // Mostrar la tarifa principal
        if (tarifaPrincipalElement) tarifaPrincipalElement.style.display = "block";

        // Ocultar la tarifa de grupo
        if (tarifaGrupoElement) tarifaGrupoElement.style.display = "none";

        // **Limpiar habitaciones seleccionadas y desmarcar checkboxes**
        habitacionesArray.forEach(habitacion => {
            let habitacionId = String(habitacion.id);
            let checkbox = document.querySelector(`.habitacion-checkbox[data-id="${habitacionId}"]`);
            if (checkbox) {
                checkbox.checked = false;
            }
        });

        habitacionesSeleccionadas.clear();
    }

    // **Actualizar el input hidden con los cambios**
    inputHidden.value = JSON.stringify([...habitacionesSeleccionadas.values()]);


}


function actualizarHabitacionesInput() {
    let chipsInstance = M.Chips.getInstance(document.getElementById("chips-habitaciones"));
    let habitaciones = chipsInstance.chipsData.map(chip => chip.tag); // Obtener los valores de los chips
    document.getElementById("habitacionesInput").value = JSON.stringify(habitaciones);
}

function toggleModoCheckout() {
    let switchElement = document.getElementById("switchHoras");
    let switchStateInput = document.getElementById("switchState");
    let habitacionesRow = document.getElementById("habitacionesRow");
    let habitacionContainer = document.getElementById("habitacionContainer");
    let habitacionesInputHidden = document.getElementById("habitacionesgrupales");

    let totalAlojamientoInput = document.getElementById("totalalojamiento");
    let totalapagaralojamientoInput = document.getElementById("totalapagaralojamiento");
    let totalalojamientopagadoInput = document.getElementById("total_pagado");
    let totalPagarInput = document.getElementById("total");
    let diasInput = document.getElementById("dias");

    let descripcionInput = document.getElementById("descripcion_alojamiento");

    // Obtener medio de pago si aplica
    let medioPagoAdelantoId = parseInt(document.getElementById("medio_pago")?.value) || 1;

    if (!habitacionesInputHidden) return;

    let habitacionesData = JSON.parse(habitacionesInputHidden.value || "[]");

    // =====================================================
    // ===============     MODO GRUPAL     =================
    // =====================================================
    if (switchElement.checked) {

        switchStateInput.value = "grupo";
        habitacionesRow.style.display = 'flex';
        habitacionContainer.innerHTML = '';

        let habitacionesHTML = `<div class="row" id="habitacionesSeleccionadas">`;
        let totalTarifas = 0;
        let listaHabitaciones = [];

        habitacionesData.forEach(habitacion => {
            let tarifa = parseFloat(habitacion.precio) || 0;
            totalTarifas += tarifa;
            listaHabitaciones.push(habitacion.numero);
            const estaPagada = String(habitacion.situacion_detalle || '').toUpperCase() === 'P';
            const badgeLabel = estaPagada ? 'Pagado' : 'Pendiente';
            const badgeIcon = estaPagada ? 'check_circle' : 'schedule';
            const badgeBg = estaPagada ? '#e8f5e9' : '#fff3e0';
            const badgeColor = estaPagada ? '#2e7d32' : '#ef6c00';
            const badgeHtml = `
                <span class="habitacion-estado-badge" style="position:absolute; top:6px; right:6px; z-index:3; display:inline-flex; align-items:center; gap:4px; background:${badgeBg}; color:${badgeColor}; padding:2px 6px; border-radius:12px; font-size:11px; font-weight:600; box-shadow:0 2px 6px rgba(0,0,0,0.15);">
                    <i class="material-icons" style="font-size:14px; line-height:1;">${badgeIcon}</i>
                    ${badgeLabel}
                </span>
            `;

            habitacionesHTML += `
                <div class="col s12 m3 l2 habitacion-item" id="habitacion-${habitacion.id}" style="position: relative;">
                    <div class="card custom-card card-habitacion disponible" data-habitacion-id="${habitacion.id}" style="position: relative; overflow: visible;">
                        ${badgeHtml}
                        <div class="card-body" style="display: flex; flex-direction: column; justify-content: center; align-items: center;">
                            <div class="habitacion-detalles d-flex flex-column">
                                <h4 class="font-weight-bold text-dark text-light numerohabitacion">${habitacion.numero}</h4>
                            </div>
                        </div>
                    </div>

                    <div class="input-field col s12">
                        <input type="number" id="tarifa-${habitacion.id}" 
                               name="tarifa_habitacion[]" 
                               value="${tarifa}" 
                               class="tarifa-input" 
                               readonly
                               oninput="deshabilitarBotonGuardar()">
                        <label for="tarifa-${habitacion.id}" class="active">Tarifa</label>
                    </div>
                </div>
            `;
        });

        habitacionesHTML += `</div>`;
        habitacionContainer.innerHTML = habitacionesHTML;

        // ==== CALCULAR MONTOS ====
        let dias = parseFloat(diasInput.value) || 1;
        let totalAlojamiento = totalTarifas * dias;
        let totalAlojamientoapagar = totalAlojamiento - (parseFloat(totalalojamientopagadoInput.value) || 0);

        totalAlojamientoInput.value = totalAlojamiento.toFixed(2);
        totalapagaralojamientoInput.value = totalAlojamientoapagar.toFixed(2);
        totalPagarInput.value = totalAlojamientoapagar.toFixed(2);

        // === ACTUALIZAR DESCRIPCIÓN ===
        if (descripcionInput) {
            let lista = listaHabitaciones.join(", ");
            descripcionInput.value = `Alojamiento en las habitaciones ${lista}`;
        }

        actualizarDetallesPago(totalAlojamientoapagar, true, medioPagoAdelantoId);
    }

    // =====================================================
    // ===============     MODO ÚNICO     ==================
    // =====================================================
    else {

        switchStateInput.value = "unico";
        habitacionesRow.style.display = 'none';

        let hab = habitacionesData[0];
        let numeroHabitacion = hab ? hab.numero : "";
        let tarifaUnica = hab ? parseFloat(hab.precio) || 0 : 0;

        habitacionContainer.innerHTML = `
            <i class="material-icons prefix">meeting_room</i>
            <input id="habitacion" type="text"
                style="font-size: 32px !important; font-weight: bold;"
                value="${numeroHabitacion}" readonly>
            <label for="habitacion" class="active">Habitación</label>
        `;

        let dias = parseFloat(diasInput.value) || 1;
        let totalAlojamiento = tarifaUnica * dias;

        totalAlojamientoInput.value = totalAlojamiento.toFixed(2);
        totalPagarInput.value = totalAlojamiento.toFixed(2);

        // === DESCRIPCIÓN PARA MODO ÚNICO ===
        if (descripcionInput) {
            descripcionInput.value = `Alojamiento en la habitación ${numeroHabitacion}`;
        }

        actualizarDetallesPago(totalAlojamiento, false);
    }
}

function actualizarDetallesPago(total, esGrupal, medioPagoAdelantoId = null) {
    let detallesJsonInput = document.getElementById("detalles_movimientos_json");
    let tablaDetalles = document.getElementById("detalle-movcaja");

    let adelanto = parseFloat(document.getElementById("adelanto")?.value) || 0;
    let medioPagoInput = document.getElementById("medio_pago");

    // ✅ Si no se pasa `medioPagoAdelantoId`, obtenerlo del input oculto
    if (!medioPagoAdelantoId) {
        medioPagoAdelantoId = medioPagoInput ? parseInt(medioPagoInput.value) : 1;
    }

    let mediosPago = {
        1: "Efectivo",
        2: "Tarjeta",
        3: "Transferencia",
        4: "Depósito",
        5: "Billetera Digital",
    };

    let detallesMediosPago = {
        1: [],
        2: ["Izipay", "Visa - Débito"],
        3: ["BCP"],
        4: ["BCP"],
        5: ["Yape"],
    };

    let medioPagoAdelanto = mediosPago[medioPagoAdelantoId] || "Efectivo"; // ✅ Mantener el medio de pago del adelanto
    let detallesAdelanto = detallesMediosPago[medioPagoAdelantoId] || [];

    let totalPagar = total - adelanto;
    if (totalPagar < 0) totalPagar = 0;

    // ✅ **Recuperar pagos previos del JSON (si existen)**
    let detallesPrevios = JSON.parse(detallesJsonInput.value || "[]");

    // ✅ **Filtrar pagos previos**
    let detallesAdelantoExistente = detallesPrevios.filter(detalle => detalle.notas === "Pago anticipado");
    let detallesSinAdelanto = detallesPrevios.filter(detalle => detalle.notas !== "Pago anticipado");

    // **Lista de pagos actualizada**
    let detallesJson = [];

    // ✅ **Mantener el adelanto si ya estaba en el JSON**
    if (detallesAdelantoExistente.length > 0) {
        detallesJson = [...detallesAdelantoExistente];
    } else if (esGrupal && adelanto > 0) {
        detallesJson.push({
            tipo: "Pagado",
            monto: parseFloat(adelanto), // ✅ Asegurar que sea número
            medio_pago: medioPagoAdelanto,
            mediopago_id: medioPagoAdelantoId,
            detalles: detallesAdelanto,
            notas: "Pago anticipado",
            fecha_vencimiento: "-",
        });
    }

    // ✅ **Si había una fila sin "Pago anticipado", actualizar solo esa fila**
    if (detallesSinAdelanto.length > 0) {
        detallesSinAdelanto[0].monto = parseFloat(totalPagar); // ✅ Asegurar número
    } else {
        // ✅ **Si no había fila previa, agregar una nueva fila**
        detallesSinAdelanto.push({
            tipo: "Pendiente",
            monto: parseFloat(totalPagar), // ✅ Asegurar número
            medio_pago: mediosPago[1], // **Usar efectivo por defecto**
            mediopago_id: 1,
            detalles: [],
            notas: "",
            fecha_vencimiento: "-",
        });
    }

    // ✅ **Unir las filas del adelanto con las actualizadas**
    detallesJson = [...detallesJson, ...detallesSinAdelanto];

    // **Actualizar el input JSON**
    detallesJsonInput.value = JSON.stringify(detallesJson);

    // ✅ **Actualizar DataTable sin modificar el medio de pago del adelanto**
    let dataTable = $("#tabla-medios-pago").DataTable();
    dataTable.clear();

    detallesJson.forEach((detalle) => {
        let montoNumerico = parseFloat(detalle.monto) || 0; // ✅ Asegurar que siempre sea número

        let rowHtml = `
            <tr onclick="editarMovimiento(this, event)">
                <td><span class="new badge green white-text" data-badge-caption="${detalle.tipo}"></span></td>
                <td class="inpt-cantidad-mc monto" id="total-display">${montoNumerico.toFixed(2)}</td>
                <td class="medio-pago" data-mediopago-id="${detalle.mediopago_id}">${detalle.medio_pago}</td>
                <td class="detalles">${Array.isArray(detalle.detalles) && detalle.detalles.length > 0 ? detalle.detalles.join(", ") : ""}</td>
                <td class="notas">${detalle.notas || ""}</td>
                <td class="fecha-vencimiento">${detalle.fecha_vencimiento || "-"}</td>
                <td>
                    <button type="button" class="btn btn-small red darken-2" onclick="eliminarFila(this); event.stopPropagation();" style=" border-radius: 50%; color: rgb(255, 255, 255); position: relative;">
                        <i class="material-icons" style="position: absolute;top: 50%;left: 50%;transform: translate(-50%, -50%);">remove</i>
                    </button>
                </td>
            </tr>`;

        dataTable.row.add($(rowHtml));
    });

    dataTable.draw();
}



function togglePagoHabitacion() {
    const isPagoCaja = document.getElementById('switchPago').checked;
    const pagoCajaSections = document.querySelectorAll('.pagoCajaSection');
    const switchStateInput = document.getElementById('switchState');
    const totalField = document.getElementById('total');
    const tablaMediosPago = document.querySelector('#tabla-medios-pago tbody');
    const detallesMovimientosJsonField = document.getElementById('detalles_movimientos_json');
    const total = parseFloat(totalField?.value) || 0;
    const detallePagoCaja = {
        tipo: 'Pagado',
        monto: total.toFixed(2),
        medio_pago: 'Efectivo',
        mediopago_id: 1,
        detalles: [],
        notas: '',
        fecha_vencimiento: ''
    };

    const filaHtml = `
        <tr onclick="editarMovimiento(this, event)">
            <td><span class="new badge green white-text" data-badge-caption="Pagado"></span></td>
            <td class="inpt-cantidad-mc monto">${total.toFixed(2)}</td>
            <td class="medio-pago" data-mediopago-id="1">Efectivo</td>
            <td class="detalles"></td>
            <td class="notas"></td>
            <td class="fecha-vencimiento"></td>
            <td>
                <button type="button" class="btn btn-small red darken-2"
                    onclick="eliminarFila(this); event.stopPropagation();"
                    style="border-radius: 50%; color: rgb(255, 255, 255); position: relative;">
                    <i class="material-icons"
                        style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">remove</i>
                </button>
            </td>
        </tr>
    `;

    if (isPagoCaja) {
        // Mostrar las secciones de pago en caja
        pagoCajaSections.forEach(section => section.style.display = 'block');
        switchStateInput.value = 'pago_caja';
        detallesMovimientosJsonField.value = JSON.stringify([detallePagoCaja]);

        if ($.fn.DataTable.isDataTable('#tabla-medios-pago')) {
            const dt = $('#tabla-medios-pago').DataTable();
            dt.clear();
            dt.row.add($(filaHtml)).draw(false);
        } else if (tablaMediosPago) {
            tablaMediosPago.innerHTML = filaHtml;
        }

    } else {
        // Ocultar las secciones de pago en caja
        pagoCajaSections.forEach(section => section.style.display = 'none');
        switchStateInput.value = 'habitacion';

        // Limpiar la tabla de medios de pago
        tablaMediosPago.innerHTML = `
            <tr>
                <td colspan="6" class="center-align">Ningún dato disponible en esta tabla</td>
            </tr>
        `;

        // Limpiar el JSON de detalles movimientos
        detallesMovimientosJsonField.value = '[]';
    }
    reinicializarDataTables();
}



let modoConfiguracionActivo = false;

function Settings() {
    const settingsSections = document.querySelectorAll('.settingsSection');

    // Mostrar u ocultar secciones según estado
    settingsSections.forEach(section => {
        section.style.display = modoConfiguracionActivo ? 'none' : 'block';
    });

    // Botón actual
    const contenedor = document.getElementById('settings-btn-container'); // Debes envolver el botón original en este div
    if (contenedor) {
        contenedor.innerHTML = modoConfiguracionActivo
            ? `<a onclick="Settings()" class="btn-floating waves-effect waves-light blue darken-2"
                    data-tooltip="Más opciones">
                    <i class="material-icons">settings</i>
                </a>`
            : `<button id="btn-advanced-options" class="btn btn-floating waves-effect waves-light white-text right tooltipped white" 
                    data-tooltip="Más opciones" type="button" onclick="Settings()">
                    <i class="fa-solid fa-gears blue-text"></i>
                </button>`;
    }

    // Reinicializar tooltip
    M.Tooltip.init(document.querySelectorAll('.tooltipped'));

    // Cambiar estado
    modoConfiguracionActivo = !modoConfiguracionActivo;

    setTimeout(toggleDetraccionFields, 50);
}

function toggleDetraccionFields() {
    const chk = $('#detraccion_checkbox');
    const container = $('#detraccion_fields_container');
    if (!chk.length || !container.length) return;

    const isChecked = chk.is(':checked');
    if (isChecked) {
        container.css('display', 'flex').css('flex-wrap', 'wrap');
    } else {
        container.css('display', 'none');
    }
    calcularMontoDetraccion();
}

function calcularMontoDetraccion() {
    const totalEl = document.getElementById('total') || document.getElementById('totalapagar');
    const pctEl = document.getElementById('porcentaje_detraccion');
    const helperEl = document.getElementById('monto_detraccion_helper');
    if (!pctEl || !helperEl) return;

    const totalVal = parseFloat(totalEl ? totalEl.value : 0) || 0;
    const pctVal = parseFloat(pctEl.value) || 0;
    const monto = totalVal * (pctVal / 100);

    const montoFormateado = (monto % 1 === 0) ? monto : monto.toFixed(2);
    helperEl.innerText = `Monto: ${montoFormateado}`;
}

$(document).on('change click', '#detraccion_checkbox', function () {
    setTimeout(toggleDetraccionFields, 10);
});

$(document).on('click', 'label[for="detraccion_checkbox"], #detraccion_checkbox + span', function () {
    setTimeout(toggleDetraccionFields, 10);
});

$(document).on('input change', '#porcentaje_detraccion, #total, #totalapagar', function () {
    calcularMontoDetraccion();
});
function toggleBotonAgregar() {
    const tipoDetalle = document.getElementById('tipo_detalle_consumo').value;
    const botonAgregar = document.getElementById('det_movventa-btn-agregar');

    if (tipoDetalle === 'GLOSA') {
        // Mostrar botón
        botonAgregar.style.display = 'inline-block';
        // Eliminar todas las filas de la tabla
        const filas = document.querySelectorAll('#data-table tbody tr');
        filas.forEach(fila => {
            const productoId = fila.getAttribute('data-producto-id');
            if (productoId) {
                quitarProductoDeTabla(parseInt(productoId));
            }
        });

        // Limpiar input oculto
        document.getElementById('detalles_json').value = '[]';

    } else {
        // Ocultar botón
        botonAgregar.style.display = 'none';
    }

}


function opcionesAvanzadas() {
    // Ocultar las secciones de configuración
    const settingsSections = document.querySelectorAll('.settingsSection');
    settingsSections.forEach(section => section.style.display = 'none');

    // Reemplazar el botón de vuelta al original
    const oldButton = document.getElementById("btn-advanced-options");

    if (oldButton) {
        const newButton = document.createElement('a');
        newButton.className = "btn-floating waves-effect waves-light blue darken-2 tooltipped";
        newButton.setAttribute("data-tooltip", "Más opciones");
        newButton.setAttribute("onclick", "Settings()");
        newButton.innerHTML = '<i class="material-icons">settings</i>';

        oldButton.replaceWith(newButton);

    }
}



function calcularTotal() {
    // Obtener valores del precio de la habitación y los días
    const precioHabitacion = parseFloat(document.getElementById('preciohabitacion').value) || 0;
    const diasTotales = parseFloat(document.getElementById('dias').value) || 0;

    // Calcular subtotal de la habitación
    const subtotalHabitacion = precioHabitacion * diasTotales;

    // Calcular el total de los productos/servicios
    let totalProductos = 0;
    let totalProductospedientes = 0;
    let totalProductospagados = 0;

    const filas = document.querySelectorAll('.detalle-productos tbody tr'); // Seleccionar solo filas dentro de la tabla con clase 'detalle-productos'
    if (filas.length > 0) {
        filas.forEach(fila => {
            const cantidadElement = fila.querySelector('.cantidad');
            const precioUnitarioElement = fila.querySelector('.precio-unitario');
            const situacionElement = fila.querySelector('span.badge');

            // Obtener los valores de cantidad, precioUnitario y situacion, manejando si no existen
            const cantidad = cantidadElement ? parseFloat(cantidadElement.textContent) : 0;
            const precioUnitario = precioUnitarioElement ? parseFloat(precioUnitarioElement.textContent) : 0;
            const situacion = situacionElement ? situacionElement.textContent.trim() : ''; // Situación del producto, si existe

            // Solo sumar si la situación es 'Pendiente'
            if (situacion === 'Pendiente') {
                totalProductospedientes += cantidad * precioUnitario;
            } else {
                totalProductospagados += cantidad * precioUnitario;
            }
            totalProductos += cantidad * precioUnitario;

        });
    }


    // Obtener el valor de Total Ya Pagado
    // Verificar si el campo 'total_pagado' existe
    const totalPagadoElement = document.getElementById('total_pagado');
    const totalYaPagado = totalPagadoElement ? parseFloat(totalPagadoElement.value) || 0 : 0;

    // Calcular Total a Pagar
    let totalFinal = subtotalHabitacion + totalProductospedientes - totalYaPagado; // Total Alojamiento + Total Productos Pendientes - Total Ya Pagado

    // Si el campo 'total_pagado' existe, incluir 'totalProductospagados' en el cálculo
    if (totalPagadoElement) {
        totalFinal += totalProductospagados;
    }

    // Mostrar el resultado en el campo de entrada y en el <td>
    document.getElementById('totalapagar').value = totalFinal.toFixed(2); // Total a Pagar
    document.getElementById('totalalojamiento').value = subtotalHabitacion.toFixed(2); // Total Alojamiento

    // Mostrar en el display
    document.getElementById('total-display').textContent = totalFinal.toFixed(2);

    // Crear el objeto detalles_json con la estructura adecuada
    const detallesJson = [{
        tipo: 'Pagado', // Tipo de pago
        monto: totalFinal, // Monto calculado
        medio_pago: 'Efectivo', // Puedes actualizar esto según el medio de pago
        mediopago_id: 1,
        notas: '' // Puedes agregar notas aquí si es necesario
    }];

    // Actualizar el valor del campo oculto detalles_json
    document.getElementById('detalles_movimientos_json').value = JSON.stringify(detallesJson);
    evaluarBotonesAlCalcular();
}
function calcularEarlylate() {
    evaluarBotones();

    /* =========================
       1. DETERMINAR TARIFA BASE
       ========================= */

    const switchStateElement = document.getElementById('switchState');
    const switchState = switchStateElement ? switchStateElement.value : '';

    let precioHabitacion = 0;

    if (switchState === 'grupo') {
        // 👉 SUMAR TARIFAS DE TODAS LAS HABITACIONES SELECCIONADAS
        const tarifas = document.querySelectorAll('#habitacionesSeleccionadas .tarifa-input');

        tarifas.forEach(input => {
            precioHabitacion += parseFloat(input.value) || 0;
        });

    } else {
        // 👉 USAR TARIFA NORMAL
        const precioHabitacionElement = document.getElementById('preciohabitacion');
        precioHabitacion = precioHabitacionElement ? parseFloat(precioHabitacionElement.value) || 0 : 0;
    }

    /* =========================
       2. CALCULAR SUBTOTAL
       ========================= */

    const diasElement = document.getElementById('dias');
    const totalAlojamientoElement = document.getElementById('totalalojamiento');

    let subtotalHabitacion = 0;
    let diasTotales = 0;

    if (diasElement && diasElement.value) {
        diasTotales = parseFloat(diasElement.value) || 0;
        subtotalHabitacion = precioHabitacion * diasTotales;
    } else if (totalAlojamientoElement) {
        subtotalHabitacion = parseFloat(totalAlojamientoElement.value) || 0;
    }

    /* =========================
       3. EARLY / LATE
       ========================= */

    const earlyValue = parseFloat(document.getElementById('early')?.value) || 0;
    const lateValue = parseFloat(document.getElementById('late')?.value) || 0;

    let totalAlojamiento = subtotalHabitacion + earlyValue + lateValue;

    /* =========================
       4. DESCUENTO
       ========================= */

    const descuento = parseFloat(document.getElementById('descuento')?.value) || 0;

    if (descuento > 0) {
        totalAlojamiento -= descuento;
        if (totalAlojamiento < 0) totalAlojamiento = 0;
    }

    /* =========================
       5. PRODUCTOS / SERVICIOS
       ========================= */

    let totalProductos = 0;
    let totalProductospendientes = 0;
    let totalProductospagados = 0;

    document.querySelectorAll('.detalle-productos tbody tr').forEach(fila => {
        const cantidad = parseFloat(fila.querySelector('.cantidad')?.textContent) || 0;
        const precioUnitario = parseFloat(fila.querySelector('.precio-unitario')?.textContent) || 0;
        const situacion = fila.querySelector('span.badge')?.textContent.trim() || '';

        const totalFila = cantidad * precioUnitario;

        if (situacion === 'Pendiente') {
            totalProductospendientes += totalFila;
        } else {
            totalProductospagados += totalFila;
        }

        totalProductos += totalFila;
    });

    /* =========================
       6. TOTAL YA PAGADO
       ========================= */

    const totalPagadoElement = document.getElementById('total_pagado');
    const totalYaPagado = totalPagadoElement ? parseFloat(totalPagadoElement.value) || 0 : 0;

    /* =========================
       7. TOTAL FINAL
       ========================= */

    let totalFinal = totalAlojamiento + totalProductospendientes - totalYaPagado;

    if (totalPagadoElement) {
        totalFinal += totalProductospagados;
    }

    if (totalFinal < 0) totalFinal = 0;

    /* =========================
       8. SETEAR INPUTS
       ========================= */

    const inputTotal = document.getElementById('totalapagar') || document.getElementById('total');
    const inputTotalAlojamiento = document.getElementById('totalapagaralojamiento');

    if (inputTotal) inputTotal.value = totalFinal.toFixed(2);
    if (inputTotalAlojamiento) inputTotalAlojamiento.value = totalFinal.toFixed(2);
    if (totalAlojamientoElement) totalAlojamientoElement.value = totalAlojamiento.toFixed(2);

    /* =========================
       9. PRECIO POR DÍA (NO GRUPO)
       ========================= */

    if (switchState !== 'grupo' && diasTotales > 0) {
        const precioHabitacionElement = document.getElementById('preciohabitacion');
        if (precioHabitacionElement) {
            let nuevoPrecioPorDia = precioHabitacion;
            if (descuento > 0) {
                nuevoPrecioPorDia = totalAlojamiento / diasTotales;
            }
            precioHabitacionElement.value = nuevoPrecioPorDia.toFixed(2);
        }
    }

    /* =========================
       10. DISPLAY
       ========================= */

    document.getElementById('total-display').textContent = totalFinal.toFixed(2);

    /* =========================
       11. JSON MOVIMIENTO
       ========================= */

    const detallesJson = [{
        tipo: 'Pagado',
        monto: totalFinal,
        medio_pago: 'Efectivo',
        mediopago_id: 1,
        notas: ''
    }];

    document.getElementById('detalles_movimientos_json').value = JSON.stringify(detallesJson);

    evaluarBotonesAlCalcular();
}

function obtenerContextoExcesoCheckout() {
    const meta = document.getElementById('late-checkout-meta');
    if (!meta) return null;

    const modoSuite = (meta.dataset.modoSuite || '').toString().trim().toLowerCase();
    // Mostrar modal de exceso solo cuando el parametro Suite = "Si"
    if (modoSuite !== 'si') return null;

    const fechaSalidaStr = meta.dataset.fechaSalida || '';
    if (!fechaSalidaStr) return null;

    const fechaSalida = new Date(fechaSalidaStr);
    if (isNaN(fechaSalida.getTime())) return null;

    const ahora = new Date();
    if (ahora <= fechaSalida) return null;

    const overstayMs = ahora - fechaSalida;
    const overstayMinutes = Math.floor(overstayMs / 60000);
    const horasCobrables = Math.ceil(overstayMs / 3600000);
    const habitacionNumero = meta.dataset.habitacion || '';

    const switchStateElement = document.getElementById('switchState');
    const switchState = switchStateElement ? switchStateElement.value : '';

    let tarifaHoraSugerida = parseFloat(meta.dataset.tarifaHora || '0') || 0;
    if (switchState === 'grupo') {
        tarifaHoraSugerida = 0;
        const tarifas = document.querySelectorAll('#habitacionesSeleccionadas .tarifa-input, #habitacionesSeleccionadas .tarifa-input-grupal');
        tarifas.forEach(input => {
            tarifaHoraSugerida += parseFloat(input.value) || 0;
        });
    } else {
        const precioHabitacionElement = document.getElementById('preciohabitacion');
        if (precioHabitacionElement) {
            tarifaHoraSugerida = parseFloat(precioHabitacionElement.value) || tarifaHoraSugerida;
        }
    }

    return {
        overstayMinutes,
        horasCobrables,
        habitacionNumero,
        tarifaHoraSugerida: tarifaHoraSugerida > 0 ? tarifaHoraSugerida : 0,
    };
}

function renderLateOverstayInfo(overstayMinutes, horasCobrables) {
    const infoNodes = document.querySelectorAll('.late-overstay-info');
    if (!infoNodes.length) return;

    const horas = Math.floor(overstayMinutes / 60);
    const minutos = overstayMinutes % 60;
    infoNodes.forEach((node) => {
        node.textContent = `Tiempo excedido: ${horas}h ${minutos}m. Se cobra ${horasCobrables}  adicional.`;
    });
}

function aplicarLateCheckoutAuto(tarifaHoraManual = null) {
    const contexto = obtenerContextoExcesoCheckout();
    if (!contexto) return;

    const tarifaHora = tarifaHoraManual !== null
        ? (parseFloat(tarifaHoraManual) || 0)
        : contexto.tarifaHoraSugerida;

    if (tarifaHora <= 0) return;

    const lateInput = document.getElementById('late');
    if (!lateInput) return;

    const lateMonto = contexto.horasCobrables * tarifaHora;
    lateInput.value = lateMonto.toFixed(2);

    renderLateOverstayInfo(contexto.overstayMinutes, contexto.horasCobrables);

    if (typeof calcularEarlylate === 'function') {
        calcularEarlylate();
    }
}

function mostrarModalExcesoCheckout() {
    const contexto = obtenerContextoExcesoCheckout();
    const contenedor = document.getElementById('contenedorExcesoCheckout');
    if (!contenedor) return;
    if (!contexto) {
        contenedor.style.display = 'none';
        return;
    }

    renderLateOverstayInfo(contexto.overstayMinutes, contexto.horasCobrables);

    const tiempoNodo = document.getElementById('exceso_tiempo_transcurrido');
    const horasNodo = document.getElementById('exceso_horas_cobrables');
    const habitacionNodo = document.getElementById('exceso_habitacion_numero');
    const precioHoraInput = document.getElementById('exceso_precio_hora');
    const totalInput = document.getElementById('exceso_total');
    const aceptarBtn = document.getElementById('btnAceptarExcesoCheckout');

    if (!tiempoNodo || !horasNodo || !habitacionNodo || !precioHoraInput || !totalInput || !aceptarBtn) {
        return;
    }

    const horas = Math.floor(contexto.overstayMinutes / 60);
    const minutos = contexto.overstayMinutes % 60;
    tiempoNodo.textContent = `${horas}h ${minutos}m`;
    horasNodo.textContent = `${contexto.horasCobrables} hora(s)`;
    habitacionNodo.textContent = contexto.habitacionNumero;
    precioHoraInput.value = contexto.tarifaHoraSugerida.toFixed(2);

    const recalcularTotalExceso = () => {
        const tarifa = parseFloat(precioHoraInput.value || '0') || 0;
        totalInput.value = (contexto.horasCobrables * tarifa).toFixed(2);
    };

    precioHoraInput.oninput = recalcularTotalExceso;
    recalcularTotalExceso();

    aceptarBtn.onclick = function (event) {
        event.preventDefault();
        const tarifa = parseFloat(precioHoraInput.value || '0') || 0;
        if (tarifa <= 0) {
            if (typeof M !== 'undefined' && M.toast) {
                M.toast({ html: 'Ingresa un monto por hora válido', classes: 'red darken-1' });
            }
            return;
        }
        aplicarLateCheckoutAuto(tarifa);
        const cobrarButton = document.getElementById('btn-cobrar') || document.querySelector("button[onclick=\"handleAction('cobrar')\"]");
        if (cobrarButton) {
            cobrarButton.disabled = true;
            cobrarButton.dataset.forceDisabled = '1';
            cobrarButton.classList.add('disabled');
        }
        contenedor.style.display = 'none';
        restaurarScrollPagina({ mantenerPosicion: true });
    };

    contenedor.style.display = 'block';
}



function tarifaCheckinEsSoloLectura() {
    const modificarPreciosEl = document.getElementById('modificar-precios-checkin');
    if (!modificarPreciosEl) {
        return false;
    }

    return modificarPreciosEl.value.trim().toLowerCase() !== 'si';
}

function calcularTotalCheckin() {
    // Obtener cantidad de días
    const diasTotales = parseFloat(document.getElementById('dias')?.value) || 0;

    // Obtener todas las tarifas de habitaciones seleccionadas
    const tarifasInputs = document.querySelectorAll('.tarifa-input-grupal');
    let total = 0;

    tarifasInputs.forEach(input => {
        const tarifa = parseFloat(input.value) || 0;
        total += tarifa * diasTotales;
    });

    // Mostrar el total calculado en el campo de total si existe
    const inputTotal = document.getElementById('preciototal') || document.getElementById('total');
    if (inputTotal) inputTotal.value = total.toFixed(2);

    // === 📅 Actualizar fecha de salida ===
    const fechaIngresoInput = document.getElementById('fechaingreso');
    const fechaSalidaInput = document.getElementById('fechasalida');

    if (fechaIngresoInput && fechaSalidaInput) {
        const fechaIngreso = fechaIngresoInput.value;

        // Validar formato correcto
        if (!fechaIngreso || isNaN(new Date(fechaIngreso))) {
            console.error('⚠️ Fecha de ingreso inválida');
            return;
        }

        // Convertir la fecha a objeto Date
        const fechaIngresoDate = new Date(fechaIngreso);
        fechaIngresoDate.setDate(fechaIngresoDate.getDate() + diasTotales);
        fechaIngresoDate.setHours(0, 0, 0, 0);

        // Mantener la hora actual del campo fechasalida (si existe)
        const horaSalida = fechaSalidaInput.value ? fechaSalidaInput.value.slice(11) : '12:00';
        const nuevaFechaSalida = `${fechaIngresoDate.toISOString().split('T')[0]}T${horaSalida}`;
        fechaSalidaInput.value = nuevaFechaSalida;
    }

    // (Opcional) Mostrar el total en consola
}



function cambiarTipoDocVenta(tipoId, boton) {
    // Cambiar el valor del input oculto
    document.getElementById('tipodocumento_id').value = tipoId;

    // Obtener todos los botones con la clase 'tipodocumento'
    const botones = document.querySelectorAll('.tipodocumento');

    // Restaurar los estilos de todos los botones
    botones.forEach(btn => {
        btn.classList.remove('bg-primary');
        btn.classList.add('bg-secondary');
    });

    // Aplicar el estilo al botón seleccionado
    boton.classList.remove('bg-secondary');
    boton.classList.add('bg-primary');

}





function mostrarVistaGeneral() {
    mostrarPantallaDeCarga();
    fetch('/movhabitacionvista-general')
        .then(response => response.json())
        .then(data => {
            const tabla = document.getElementById('tablaHabitaciones');
            tabla.innerHTML = ''; // Limpia la tabla antes de llenar
            data.habitaciones.forEach(habitacion => {
                const estadoClase = habitacion.situacion.toLowerCase();
                const precio = habitacion.tipohabitacion.precio
                    ? parseFloat(habitacion.tipohabitacion.precio).toFixed(2)
                    : '0.00'; // Valor predeterminado si precio es null o no numérico

                const fila = `
                        <tr class="${estadoClase}">
                            <td>${habitacion.numero}</td>
                            <td>${habitacion.tipohabitacion.nombre}</td>
                            <td>${habitacion.piso.nombre}</td>
                            <td>S/ ${precio}</td>
                            <td>${habitacion.situacion}</td>
                            <td>
                                <button class="btn blue" onclick="abrirModal('${habitacion.situacion}', ${habitacion.id})">Detalles</button>
                            </td>
                        </tr>`;
                tabla.insertAdjacentHTML('beforeend', fila);
            });
            // Abre el modal
            const modal = M.Modal.getInstance(document.getElementById('modalVistaGeneral'));
            modal.open();
            setTimeout(function () {
                ocultarPantallaDeCarga();
            }, 200);
        })
        .catch(error => console.error('Error al cargar la vista general:', error));
}

function filtrarPorCategoria(categoriaId) {
    // Actualizar la clase activa
    const items = document.querySelectorAll('.collection-item');
    items.forEach(item => item.classList.remove('active', 'teal', 'lighten-4'));
    event.target.classList.add('active', 'teal', 'lighten-4');


}
function iniciarCarrusel() {
    const wrapper = document.querySelector('.niveles-wrapper');

    if (!wrapper) return;

    const items = Array.from(wrapper.children);
    const totalItems = items.length;

    if (totalItems > 0) {
        const itemWidth = 200; // Ajusta el ancho de la tarjeta
        const cloneCount = 5; // Ajusta el número de clonaciones

        // Clonar los elementos
        for (let i = 0; i < cloneCount; i++) {
            items.forEach(item => {
                const clone = item.cloneNode(true);
                wrapper.appendChild(clone);
            });
        }

        let isDown = false; // Para detectar si el mouse está presionado
        let startX; // Almacena la posición inicial
        let scrollLeft; // Almacena el desplazamiento inicial

        // **Eventos para el arrastre**
        const container = document.querySelector('.niveles-carousel');

        container.addEventListener('mousedown', (e) => {
            isDown = true;
            container.classList.add('active');
            startX = e.pageX - container.offsetLeft;
            scrollLeft = container.scrollLeft;
        });

        container.addEventListener('mouseleave', () => {
            isDown = false;
            container.classList.remove('active');
        });

        container.addEventListener('mouseup', () => {
            isDown = false;
            container.classList.remove('active');
        });

        container.addEventListener('mousemove', (e) => {
            if (!isDown) return;
            e.preventDefault();
            const x = e.pageX - container.offsetLeft;
            const walk = (x - startX) * 2; // Velocidad de desplazamiento
            container.scrollLeft = scrollLeft - walk;
        });

        // **Eventos táctiles para dispositivos móviles**
        let touchStartX = 0;
        let touchScrollLeft = 0;

        container.addEventListener('touchstart', (e) => {
            touchStartX = e.touches[0].pageX - container.offsetLeft;
            touchScrollLeft = container.scrollLeft;
        });

        container.addEventListener('touchmove', (e) => {
            const touchX = e.touches[0].pageX - container.offsetLeft;
            const touchMove = (touchX - touchStartX) * 2;
            container.scrollLeft = touchScrollLeft - touchMove;
        });
    }
}

function handleAction(accion, formElement = null) {
    // Obtener el formulario y el campo oculto
    const form = formElement ||
        (document.activeElement?.form instanceof HTMLFormElement ? document.activeElement.form : null) ||
        document.getElementById("form");
    const formActionUrlField = form?.querySelector("#form-action-url") || document.getElementById("form-action-url");
    const accionField = form?.querySelector("#accion") || document.getElementById("accion");

    // Establecer el valor de la acción en el campo oculto
    if (!form || !formActionUrlField || !accionField) return;

    accionField.value = accion;

    // Actualizar la URL del formulario con la acción seleccionada
    form.action = formActionUrlField.value.replace("accion", accion);

}




function actualizarTodoDet_MovCaja(medioPagoId, billeteraNombre = null) {

    const medioPagoNombres = {
        1: 'Efectivo',
        2: 'Tarjeta',
        3: 'Transferencia',
        4: 'Depósito',
        5: 'Billetera digital',
    };

    const medioPagoNombre = medioPagoNombres[medioPagoId] || 'Desconocido';

    let detallesMedioPago = [];

    if (medioPagoId === 5 && billeteraNombre) {
        detallesMedioPago = [{ billetera: billeteraNombre }];
    } else if (medioPagoId === 2) {
        detallesMedioPago = [{ pos: 'Izipay' }, { tarjeta: 'Visa - Débito' }];
    } else if (medioPagoId === 3 || medioPagoId === 4) {
        detallesMedioPago = [{ banco: 'BCP' }];
    }

    const formatearDetalles = (detalles) =>
        detalles.map(d => Object.values(d).join(', ')).join(' | ');

    const detallesJsonInput = document.getElementById('detalles_movimientos_json');
    const montoInput = document.getElementById('totalapagar') || document.getElementById('total');
    const montoTotal = montoInput ? parseFloat(montoInput.value) || 0 : 0;

    let detalles = [];
    try {
        detalles = JSON.parse(detallesJsonInput.value || '[]');
    } catch {
        detalles = [];
    }

    const modalidad = document.getElementById('modalidad-det_movcaja')?.value || 'reemplazar';

    const isDataTable = $.fn.DataTable.isDataTable('#tabla-medios-pago');
    const dt = isDataTable ? $('#tabla-medios-pago').DataTable() : null;

    const crearFila = (detalle) => ([
        // TD 1: Tipo
        `<span class="new badge green white-text" data-badge-caption="${detalle.tipo}"></span>`,

        // TD 2: Monto (con clases correctas)
        `<span class="inpt-cantidad-mc monto">${parseFloat(detalle.monto).toFixed(2)}</span>`,


        // TD 3: Medio de pago (solo texto)
        detalle.medio_pago,

        // TD 4: Detalles
        formatearDetalles(detalle.detalles),

        // TD 5: Notas
        detalle.notas || '',

        // TD 6: Fecha vencimiento
        detalle.fecha_vencimiento || '',

        // TD 7: Botón eliminar
        `
    <button type="button"
        class="btn btn-small red darken-2"
        onclick="eliminarFila(this); event.stopPropagation();"
        style="border-radius:50%; color:white; position:relative;">
        <i class="material-icons"
           style="position:absolute; top:50%; left:50%; transform:translate(-50%, -50%);">
            remove
        </i>
    </button>
    `
    ]);


    const agregarFilaDataTable = (detalle, limpiar = false) => {
        if (!isDataTable) return;

        if (limpiar) {
            dt.clear();
        }

        dt.row.add(crearFila(detalle)).draw(false);

        const filaNode = dt.row(':last').node();

        // onclick del TR
        filaNode.setAttribute('onclick', 'editarMovimiento(this, event)');
        filaNode.classList.add('odd');

        const celdas = filaNode.querySelectorAll('td');

        // Aplicar clases EXACTAS como tu HTML original
        celdas[1].classList.add('monto');
        celdas[2].classList.add('medio-pago');
        celdas[2].setAttribute('data-mediopago-id', detalle.mediopago_id);
        celdas[3].classList.add('detalles');
        celdas[4].classList.add('notas');
        celdas[5].classList.add('fecha-vencimiento');
    };


    if (modalidad === 'reemplazar') {

        const nuevoDetalle = {
            tipo: detalles[0]?.tipo || 'Pagado',
            monto: montoTotal,
            medio_pago: medioPagoNombre,
            mediopago_id: medioPagoId,
            detalles: detallesMedioPago,
            notas: detalles[0]?.notas || '',
            fecha_vencimiento: detalles[0]?.fecha_vencimiento || '-',
        };

        detalles = [nuevoDetalle];
        detallesJsonInput.value = JSON.stringify(detalles);

        agregarFilaDataTable(nuevoDetalle, true);

        mostrarMensajeInterrogante(
            medioPagoId === 5
                ? `Medio de pago actualizado a ${medioPagoNombre} (${billeteraNombre})`
                : `Medio de pago actualizado a ${medioPagoNombre}`
        );

    } else {

        const sumaActual = detalles.reduce((acc, d) => acc + (parseFloat(d.monto) || 0), 0);

        if (sumaActual < montoTotal) {

            const nuevoDetalle = {
                tipo: 'Pagado',
                monto: montoTotal - sumaActual,
                medio_pago: medioPagoNombre,
                mediopago_id: medioPagoId,
                detalles: detallesMedioPago,
                notas: '',
                fecha_vencimiento: '-',
            };

            detalles.push(nuevoDetalle);
            detallesJsonInput.value = JSON.stringify(detalles);

            agregarFilaDataTable(nuevoDetalle);

            mostrarMensajeInterrogante(
                medioPagoId === 5
                    ? `Medio de pago agregado: ${billeteraNombre}`
                    : `Medio de pago agregado: ${medioPagoNombre}`
            );

        } else {
            mostrarMensajeInterrogante('No se puede agregar: el monto ya está cubierto.');
        }
    }
}




function mostrarSelects(selectElement) {
    // Obtener el valor seleccionado
    const medioPagoId = selectElement.value; // ID del medio de pago
    const medioPagoDescripcion = selectElement.options[selectElement.selectedIndex].getAttribute('data-descripcion'); // Descripción

    // Ocultar y deshabilitar todos los selects secundarios
    const selects = ['pos_container', 'tarjeta_container', 'banco_container', 'billetera_container'];
    selects.forEach(id => {
        const container = document.getElementById(id);
        container.style.display = 'none'; // Oculta el contenedor

    });

    // Mostrar los selects según la descripción del medio de pago
    switch (medioPagoDescripcion) {
        case 'Tarjeta':
            document.getElementById('pos_container').style.display = 'block';
            document.getElementById('tarjeta_container').style.display = 'block';

            document.getElementById('pos_detalle').disabled = false;
            document.getElementById('tarjeta_detalle').disabled = false;
            break;

        case 'Transferencia':
        case 'Depósito':
            document.getElementById('banco_container').style.display = 'block';
            document.getElementById('det_numero').style.display = 'block';

            document.getElementById('banco_detalle').disabled = false;
            break;

        case 'Billetera digital':
            document.getElementById('det_numero').style.display = 'none';

            document.getElementById('billetera_container').style.display = 'block';
            document.getElementById('billetera_detalle').disabled = false;
            break;

        default:
            // Si no coincide con ninguna opción, no hace nada
            break;
    }

}


function agregarPrecio() {
    const hora = document.getElementById('hora').value;
    const precioHora = document.getElementById('precio_hora').value;
    const tabla = document.getElementById('preciosPorHoras').getElementsByTagName('tbody')[0];
    const jsonPrecioHoras = document.getElementById('json_precio_horas');

    if (!hora || !precioHora) {
        alert('Por favor, ingrese tanto las horas como el precio.');
        return;
    }

    // Crear una nueva fila
    const row = tabla.insertRow();
    const cell1 = row.insertCell(0);
    const cell2 = row.insertCell(1);
    const cell3 = row.insertCell(2);

    // Agregar los valores a la fila con el formato adecuado
    cell1.innerHTML = `<input class="hora" value="${hora}" min="1" readonly>`;
    cell2.innerHTML = `<input type="number" class="precio" value="${precioHora}" onchange="actualizarJson()">`;
    cell3.innerHTML = `<button type="button" class="btn red waves-effect waves-light" onclick="eliminarPrecio(this)">Eliminar</button>`;

    // Limpiar los campos de entrada
    document.getElementById('hora').value = '';
    document.getElementById('precio_hora').value = '';

    // Actualizar el JSON
    actualizarJson();
}

function eliminarPrecio(button) {
    const row = button.parentElement.parentElement;
    row.remove();
    actualizarJson();
}

function actualizarJson() {
    const precios = [];
    const filas = document.getElementById('preciosPorHoras').getElementsByTagName('tbody')[0].rows;

    for (let i = 0; i < filas.length; i++) {
        // Obtener el valor de la hora desde el campo input (en vez de textContent)
        const hora = filas[i].cells[0].getElementsByTagName('input')[0].value.trim();
        const precio = filas[i].cells[1].getElementsByTagName('input')[0].value.trim();
        precios.push({ hora: hora, precio: precio });
    }

    // Actualizar el campo JSON oculto
    document.getElementById('json_precio_horas').value = JSON.stringify(precios);
}


// Obtener el JSON desde el atributo data

function setHoraSalida(hours) {
    const currentTime = new Date();
    const preciosHoras = JSON.parse(document.getElementById('precios-data').getAttribute('data-precios'));

    // Sumar las horas seleccionadas a la hora actual
    currentTime.setHours(currentTime.getHours() + hours);

    // Formatear la hora y minutos
    const hoursFormatted = currentTime.getHours().toString().padStart(2, '0');
    const minutesFormatted = currentTime.getMinutes().toString().padStart(2, '0');

    // Actualizar la hora de salida
    document.getElementById('horasalida').value = `${hoursFormatted}:${minutesFormatted}`;

    // Actualizar el campo de horas totales
    document.getElementById('horas').value = hours;

    // Buscar el precio correspondiente
    const precioSeleccionado = preciosHoras.find(precio => parseInt(precio.hora) === hours);

    if (precioSeleccionado) {
        // ❌ NO TOCAR la lógica del total
        document.getElementById('preciototal').value = precioSeleccionado.precio;

        // ✅ SIEMPRE actualizar la tarifa por horas
        const habitacionId = document.getElementById('habitacion_id')?.value;
        const tarifaInput = document.getElementById('tarifa-' + habitacionId);

        if (tarifaInput) {
            tarifaInput.value = parseFloat(precioSeleccionado.precio).toFixed(2);
        }
    } else {
        console.error('Precio no encontrado para la hora seleccionada.', precioSeleccionado);
    }
}

function setHoraSalidacheckout(hours) {
    // Obtener la fecha de ingreso actual
    const fechaIngresoValue = document.getElementById('fechaingreso').value;

    // Convertir la fecha de ingreso a un objeto Date
    const fechaIngreso = new Date(fechaIngresoValue);

    // Crear una nueva instancia de fecha para la salida basada en la misma fecha de ingreso
    const fechaSalida = new Date(fechaIngreso);

    // Sumar las horas seleccionadas a la fecha de salida
    fechaSalida.setHours(fechaIngreso.getHours() + hours);

    // Formatear la nueva hora de salida (solo la hora y minutos)
    const hoursFormatted = fechaSalida.getHours().toString().padStart(2, '0');
    const minutesFormatted = fechaSalida.getMinutes().toString().padStart(2, '0');

    // Actualizar el campo de fecha de salida (manteniendo la misma fecha, pero con nueva hora)
    document.getElementById('fechasalida').value = `${fechaIngresoValue.split(' ')[0]} ${hoursFormatted}:${minutesFormatted}`;

    // Actualizar el campo de horas totales
    document.getElementById('horas').value = hours;

    // Obtener los precios desde el dataset
    const preciosHoras = JSON.parse(document.getElementById('precios-data').getAttribute('data-precios'));

    // Buscar el precio correspondiente
    const precioSeleccionado = preciosHoras.find(precio => parseInt(precio.hora) === hours);

    if (precioSeleccionado) {
        const precioHabitacion = parseFloat(precioSeleccionado.precio) || 0;

        // Actualizar el total del alojamiento
        document.getElementById('totalalojamiento').value = precioHabitacion.toFixed(2);

        // Actualizar el total a pagar
        document.getElementById('total').value = precioHabitacion.toFixed(2);

        // Actualizar el JSON de movimientos
        const detallesMovimientosJsonField = document.getElementById('detalles_movimientos_json');
        let movimientos = JSON.parse(detallesMovimientosJsonField.value || '[]');

        // Actualizar el monto en el JSON (primera entrada de tipo "Pagado")
        const movimientoPagado = movimientos.find(mov => mov.tipo === "Pagado");
        if (movimientoPagado) {
            movimientoPagado.monto = precioHabitacion.toFixed(2);
        } else {
            // Si no existe un movimiento de tipo "Pagado", agregar uno nuevo
            movimientos.push({
                tipo: "Pagado",
                monto: precioHabitacion.toFixed(2),
                medio_pago: "Efectivo",
                mediopago_id: 1,
                notas: ""
            });
        }

        // Actualizar el campo oculto con el nuevo JSON
        detallesMovimientosJsonField.value = JSON.stringify(movimientos);

        // Actualizar la tabla de movimientos
        const filasTabla = document.querySelectorAll('#detalle-movcaja tr');
        filasTabla.forEach(fila => {
            const tipo = fila.querySelector('.badge')?.textContent.trim();
            if (tipo === "Pagado") {
                fila.querySelector('.monto').textContent = precioHabitacion.toFixed(2);
            }
        });
    } else {
        console.error('Precio no encontrado para la hora seleccionada.');
    }
}
function mostrarModalLogin() {
    Swal.fire({
        icon: 'warning',
        title: 'Tiempo de inactividad',
        text: 'Has superado el límite de tiempo de inactividad. La página se recargará',
        confirmButtonText: 'Aceptar',
        confirmButtonColor: '#3085d6',
        allowOutsideClick: false,
        allowEscapeKey: false,
        backdrop: true,
        customClass: {
            popup: 'animated fadeInDown faster rounded-popup', // animación de entrada
            confirmButton: 'waves-effect waves-light btn darken-2'
        }
    }).then(() => {
        window.location.href = '/';
    });
}

// Función para inicializar un temporizador
function TemporizadorHabitaciones(habitacionId, horaFin) {
    // Validar que exista el elemento con el ID correspondiente
    const timerElement = document.getElementById(`timer-${habitacionId}`);
    if (!timerElement) {
        console.warn(`Elemento con ID 'timer-${habitacionId}' no encontrado.`);
        return; // Salir de la función si el elemento no existe
    }

    // Parseo robusto de fecha/hora (evita desfases por zona horaria del navegador).
    const parseFechaLocal = (valor) => {
        if (!valor) return new Date(NaN);

        // Soporta "YYYY-MM-DD HH:mm:ss(.u)" y variantes ISO.
        const limpio = String(valor).trim().replace('T', ' ').split('.')[0];
        const match = limpio.match(/^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2})(?::(\d{2}))?$/);

        if (match) {
            const year = parseInt(match[1], 10);
            const month = parseInt(match[2], 10) - 1;
            const day = parseInt(match[3], 10);
            const hour = parseInt(match[4], 10);
            const minute = parseInt(match[5], 10);
            const second = parseInt(match[6] || '0', 10);
            return new Date(year, month, day, hour, minute, second);
        }

        // Fallback para cadenas ISO válidas.
        return new Date(valor);
    };

    // Validar que horaFin sea una fecha válida
    const fechaFin = parseFechaLocal(horaFin);
    if (isNaN(fechaFin.getTime())) {
        console.error(`Hora de fin inválida para la habitación ${habitacionId}: ${horaFin}`);
        timerElement.textContent = "Error en la fecha";
        return; // Salir de la función si la fecha es inválida
    }

    // Variable para controlar si es la primera actualización
    function actualizarCuentaRegresiva() {
        const elTimer = document.getElementById(`timer-${habitacionId}`);
        const cardElement = document.querySelector(`.card-habitacion[data-habitacion-id="${habitacionId}"]`);
        const contenedorTiempo = document.getElementById(`tiempo-restante-${habitacionId}`);

        // Tras checkout / recarga AJAX el #timer-* desaparece pero el setInterval sigue vivo.
        if (!elTimer || !document.body.contains(elTimer)) {
            clearInterval(interval);
            if (cardElement) {
                cardElement.classList.remove('palpitar');
            }
            return;
        }

        // Vista de tarjetas (habitaciones): validar tarjeta ocupada.
        if (cardElement) {
            if (!cardElement.classList.contains('ocupada')) {
                clearInterval(interval);
                cardElement.classList.remove('palpitar');
                return;
            }
        } else if (!contenedorTiempo || !document.body.contains(contenedorTiempo)) {
            // Vista tabla (alojamientos): solo validar que el contenedor siga en el DOM.
            clearInterval(interval);
            return;
        }

        const ahora = new Date();
        const tiempoRestante = fechaFin - ahora;

        if (tiempoRestante <= 0) {
            const tiempoPasado = Math.abs(tiempoRestante);
            const totalSegundos = Math.floor(tiempoPasado / 1000);
            const segundos = totalSegundos % 60;
            const minutos = Math.floor((totalSegundos / 60) % 60);
            const horas = Math.floor((totalSegundos / 3600) % 24);
            const dias = Math.floor(totalSegundos / 86400);

            let tiempoFormateado = "+";
            if (dias > 0) {
                tiempoFormateado += `${dias}d `;
                tiempoFormateado += `${horas.toString().padStart(2, "0")}h `;
                tiempoFormateado += `${minutos.toString().padStart(2, "0")}m`;
            } else {
                if (horas > 0) {
                    tiempoFormateado += `${horas.toString().padStart(2, "0")}h `;
                }
                tiempoFormateado += `${minutos.toString().padStart(2, "0")}m ${segundos.toString().padStart(2, "0")}s`;
            }

            elTimer.textContent = tiempoFormateado.trim();

            if (cardElement && !cardElement.classList.contains("palpitar")) {
                cardElement.classList.add("palpitar");
            }
        } else {
            const totalSegundos = Math.floor(tiempoRestante / 1000);
            const segundos = totalSegundos % 60;
            const minutos = Math.floor((totalSegundos / 60) % 60);
            const horas = Math.floor((totalSegundos / 3600) % 24);
            const dias = Math.floor(totalSegundos / 86400);

            let tiempoFormateado = "";

            if (dias > 0) {
                tiempoFormateado += `${dias}d `;
                tiempoFormateado += `${horas.toString().padStart(2, "0")}h `;
                tiempoFormateado += `${minutos.toString().padStart(2, "0")}m`; // sin segundos
            } else {
                if (horas > 0) {
                    tiempoFormateado += `${horas.toString().padStart(2, "0")}h `;
                }
                tiempoFormateado += `${minutos.toString().padStart(2, "0")}m ${segundos.toString().padStart(2, "0")}s`;
            }

            elTimer.textContent = tiempoFormateado.trim();

            if (tiempoRestante <= 15 * 60 * 1000) {
                if (cardElement && !cardElement.classList.contains("palpitar")) {
                    cardElement.classList.add("palpitar");
                }
            }
        }
    }

    actualizarCuentaRegresiva();
    const interval = setInterval(actualizarCuentaRegresiva, 1000);

}


function incializarTemporizador() {
    const temporizadores = document.querySelectorAll(".tiempo-restante[data-habitacion-id][data-hora-fin]");

    temporizadores.forEach((temporizador) => {
        TemporizadorHabitaciones(temporizador.dataset.habitacionId, temporizador.dataset.horaFin);
    });
}

function cambiarId(tipopersona) {
    const selectedValue = tipopersona.value; // "Natural" o "Juridico"


    const labelNrodoc = document.querySelector('label[for="nrodoc"]');

    if (selectedValue === 'Juridico') {
        // Cambiar ID y clase del input nombres
        const inputNombres = document.getElementById('nombres');
        const contenedorNombres = inputNombres.closest('.input-field');
        if (inputNombres) inputNombres.id = 'razonsocial';
        if (contenedorNombres && contenedorNombres.classList.contains('col')) {
            contenedorNombres.classList.remove('m4');
            contenedorNombres.classList.add('m5');
        }
        const buscarRucBtn = document.getElementById('buscarDNI');
        if (buscarRucBtn) buscarRucBtn.id = 'buscarRuc';
        // Ocultar/mostrar campos
        document.getElementById('apellidos-div').style.display = 'none';
        document.getElementById('divGenero').style.display = 'none';
        document.getElementById('divFechaNac').style.display = 'none';
        document.getElementById('divFechaIns').style.display = 'block';

        // Cambiar label del número de documento
        const labelNrodoc = document.querySelector("label[for='nrodoc']");
        if (labelNrodoc) labelNrodoc.textContent = 'RUC';

        // Mover direccion y email al principio de #divOtrosDatos
        const direccion = document.getElementById('direccion')?.closest('.input-field');
        const email = document.getElementById('email')?.closest('.input-field');
        const divOtrosDatos = document.getElementById('divOtrosDatos');
        if (direccion && email && divOtrosDatos) {
            divOtrosDatos.prepend(email);
            divOtrosDatos.prepend(direccion);
        }

        // Cambiar m2 a m3 en departamento, provincia y distrito
        ['departamento', 'provincia', 'distrito'].forEach(id => {
            const select = document.getElementById(id);
            const container = select?.closest('.input-field');
            if (container?.classList.contains('m2')) {
                container.classList.remove('m2');
                container.classList.add('m3');
            }
        });
    } else {
        // Revertir ID y clase del input razon social a nombres
        const inputRazonSocial = document.getElementById('razonsocial');
        const contenedorNombres = inputRazonSocial?.closest('.input-field');
        if (inputRazonSocial) inputRazonSocial.id = 'nombres';
        if (contenedorNombres && contenedorNombres.classList.contains('m5')) {
            contenedorNombres.classList.remove('m5');
            contenedorNombres.classList.add('m4');
        }

        // Mostrar campos de persona natural
        document.getElementById('apellidos-div').style.display = 'block';
        document.getElementById('divGenero').style.display = 'block';
        document.getElementById('divFechaNac').style.display = 'block';
        document.getElementById('divFechaIns').style.display = 'none';

        // Restaurar label del número de documento
        const labelNrodoc = document.querySelector("label[for='nrodoc']");
        if (selectedValue === 'Natural') {
            if (labelNrodoc) labelNrodoc.textContent = 'DNI';
            const buscarRucBtn = document.getElementById('buscarRuc');
            if (buscarRucBtn) buscarRucBtn.id = 'buscarDNI';
        } else if (selectedValue === 'Pasaporte') {
            if (labelNrodoc) labelNrodoc.textContent = 'Pasaporte';
        } else {
            if (labelNrodoc) labelNrodoc.textContent = 'Carnet de Extranjería';
        }

        // Restaurar m3 a m2 en departamento, provincia y distrito
        ['departamento', 'provincia', 'distrito'].forEach(id => {
            const select = document.getElementById(id);
            const container = select?.closest('.input-field');
            if (container?.classList.contains('m3')) {
                container.classList.remove('m3');
                container.classList.add('m2');
            }
        });

        // Mover direccion y email de vuelta a divDatosAdicionales
        const direccion = document.getElementById('direccion')?.closest('.input-field');
        const email = document.getElementById('email')?.closest('.input-field');
        const divDatosAdicionales = document.getElementById('divDatosAdicionales');
        if (direccion && email && divDatosAdicionales) {
            divDatosAdicionales.appendChild(direccion);
            divDatosAdicionales.appendChild(email);
        }
    }

}

// Función para consultar el RUC
function consultaRUC(ruc) {
    return fetch(`/api/document-lookup/ruc?ruc=${encodeURIComponent(ruc)}`)
        .then(response => response.json())
        .then(data => {
            if (data?.mensaje && data?.code) {
                M.toast({
                    html: data.mensaje,
                    classes: 'red darken-1 rounded'
                });
                return;
            }

            const razonSocial = data.RazonSocial || '';
            const direccion = data.Direccion || '';

            document.getElementById('razonsocial').value = razonSocial;
            document.getElementById('direccion').value = direccion;

            M.updateTextFields();
        })
        .catch(error => {
            console.error("Error al obtener datos: ", error);
            mostrarMensajeError('Error al buscar RUC');

        });
}

function togglePisoBloques() {
    const switchState = document.getElementById('switchState');
    const unitarioForm = document.getElementById('unitarioForm');
    const bloquesForm = document.getElementById('bloquesForm');
    const switchHoras = document.getElementById('switchHoras');

    if (switchHoras.checked) {
        switchState.value = "bloques";
        unitarioForm.style.display = "none";
        bloquesForm.style.display = "block";
    } else {
        switchState.value = "unitario";
        unitarioForm.style.display = "block";
        bloquesForm.style.display = "none";
    }
}

function toggleHabitacionBloques() {
    const switchState = document.getElementById('switchState');
    const unitarioForm = document.getElementById('unitarioForm');
    const bloquesForm = document.getElementById('bloquesForm');
    const switchHoras = document.getElementById('switchHoras');

    if (switchHoras.checked) {
        switchState.value = "bloques";
        unitarioForm.style.display = "none";
        bloquesForm.style.display = "block";
    } else {
        switchState.value = "unitario";
        unitarioForm.style.display = "block";
        bloquesForm.style.display = "none";
    }
}

function inicializartooltipped() {
    var elems = document.querySelectorAll('.tooltipped');
    var options = {
        enterDelay: 500,  // Tiempo en milisegundos para el retraso antes de que el tooltip aparezca (500 ms)
        exitDelay: 200,   // Retraso antes de que el tooltip desaparezca (opcional)
        inDuration: 300,  // Duración de la transición de entrada
        outDuration: 250, // Duración de la transición de salida
        margin: 5,
        position: 'bottom' // Dirección del tooltip (puedes cambiar a 'top', 'left' o 'right')
    };
    M.Tooltip.init(elems, options);
}


// Función para agregar un producto al detalle
function agregarProductoAlDetalle() {
    // Vaciar `detallesProductos` para evitar duplicados
    detallesProductos = [];

    // Recorrer cada fila existente en la tabla de detalles para actualizar el array
    const rows = document.querySelectorAll("#detalle-productos tr");

    rows.forEach(row => {
        const cells = row.children;

        const productoId = row.getAttribute("data-producto-id");
        const productoNombre = cells[0]?.textContent.trim();
        const unidadNombre = cells[1]?.textContent.trim();
        const cantidad = parseFloat(cells[2]?.textContent.trim());
        const notas = cells[3]?.textContent.trim();


        if (productoId && productoNombre && unidadNombre && !isNaN(cantidad)) {
            detallesProductos.push({
                producto_id: productoId,
                producto_nombre: productoNombre,
                unidad_id: row.querySelector(".unidad-id")?.getAttribute("data-unidad-id") || '',
                unidad_nombre: unidadNombre,
                cantidad: cantidad,
                notas: notas || '',
                lote: '',  // Puedes ajustar según sea necesario
                fecha_vencimiento: ''  // Puedes ajustar según sea necesario
            });
        }
    });

    // Ahora agrega el nuevo producto del modal al array `detallesProductos`
    const productoId = document.getElementById('producto_id').value;
    const productoNombre = document.getElementById('producto-nombre').textContent;
    const unidadSeleccionada = document.getElementById('unidad_producto').options[document.getElementById('unidad_producto').selectedIndex];
    const unidadId = unidadSeleccionada.value;
    const unidadNombre = unidadSeleccionada.text;
    const cantidad = parseFloat(document.getElementById('cantidad_producto').value);
    const notas = document.getElementById('notas_producto').value;
    const lote = document.getElementById('lote_producto').value;
    const fechaVencimiento = document.getElementById('fecha_vencimiento_producto').value;

    // Añadir el nuevo producto
    detallesProductos.push({
        producto_id: productoId,
        producto_nombre: productoNombre,
        unidad_id: unidadId,
        unidad_nombre: unidadNombre,
        cantidad: cantidad,
        notas: notas,
        lote: lote,
        fecha_vencimiento: fechaVencimiento
    });

    // Actualizar el campo oculto con el array completo
    document.getElementById('detalles_productos_json').value = JSON.stringify(detallesProductos);

    // Agregar la fila a la tabla en el frontend
    const table = document.getElementById('detalle-productos');
    const emptyRow = table.querySelector("tr td[colspan='5']");
    if (emptyRow) {
        emptyRow.parentNode.removeChild(emptyRow);
    }
    const row = table.insertRow();
    row.setAttribute("data-producto-id", productoId);
    row.setAttribute("onclick", "editarDetalle(this, event)"); // ← Aquí se añade el evento de edición

    row.innerHTML = `
        <td class="producto-nombre">${productoNombre}</td>
        <td class="unidad-id" data-unidad-id="${unidadId}">${unidadNombre}</td>
        <td class="cantidad">${cantidad}</td>
        <td class="notas">${notas}</td>
        <td>
            <button type="button" class="btn red btn-small" onclick="eliminarProducto(this, '${productoId}')">
                <i class="material-icons">delete</i>
            </button>
        </td>
    `;

    // Limpiar los campos del modal para el próximo uso (opcional)
    document.getElementById('cantidad_producto').value = 1;
    document.getElementById('unidad_producto').selectedIndex = 0;
    document.getElementById('notas_producto').value = '';
    document.getElementById('lote_producto').value = '';
    document.getElementById('fecha_vencimiento_producto').value = '';
}

function back() {
    // Obtener el contenedor de las breadcrumbs
    const breadcrumbContainer = document.getElementById("breadcrumb-container");

    // Obtener todas las breadcrumbs
    const breadcrumbs = breadcrumbContainer.querySelectorAll('.breadcrumb');

    // Encontrar el breadcrumb activo
    const activeBreadcrumb = breadcrumbContainer.querySelector('.breadcrumb.active');

    if (activeBreadcrumb) {
        // Encontrar el anterior al breadcrumb activo
        const previousBreadcrumb = activeBreadcrumb.previousElementSibling;

        // Si existe un breadcrumb anterior, simula un clic en él
        if (previousBreadcrumb) {
            previousBreadcrumb.click();
        } else {

        }
    }
}
function seleccionarTipoReporte(formId, tipo) {
    const form = document.getElementById(formId);
    if (!form) {
        return;
    }
    const tipoInput = form.querySelector('input[name="tipo_documento"]');
    if (tipoInput) {
        tipoInput.value = tipo;
    }
    form.submit();
}

function generatePDFReportVentas() {
    let tipo = document.getElementById('tipo-filter').value;
    let turno = document.getElementById('rel_turnocaja_id').value;
    let fecha_inicio = document.getElementById('txtFechaInicio').value;
    let fecha_fin = document.getElementById('txtFechaFin').value;

    let url = `/movventa/generarPDFreporte/${tipo}/${turno}/${fecha_inicio}/${fecha_fin}`;

    // Abre la URL en una nueva pestaña
    window.open(url, '_blank');
}

function generateResumenPDF() {
    let fecha_inicio = document.getElementById('fecha_inicio').value;
    let hora_inicio = document.getElementById('hora_inicio').value;
    let fecha_fin = document.getElementById('fecha_fin').value;
    let hora_fin = document.getElementById('hora_fin').value;

    let url = `/kardex-resumen-pdf/${fecha_inicio}/${hora_inicio}/${fecha_fin}/${hora_fin}`;

    window.open(url, '_blank');
}

function generatePDFReportStock() {
    let categoria = document.getElementById('categoria-filter').value || 0;
    let tipo = document.getElementById('tipo-filter').value || 0;
    let situacion = document.getElementById('situacion-filter').value || 0;

    let url = `/stockproducto/generarPDFreporte/${categoria}/${tipo}/${situacion}`;
    window.open(url, '_blank');
}

function generatePDFReport() {
    let tipo = document.getElementById('tipo-filter').value;
    let situacion = document.getElementById('situacion-filter').value;
    let fecha_inicio = document.getElementById('fecha_inicio').value;
    let fecha_fin = document.getElementById('fecha_fin').value;

    let url = `/solicitud/generarPDFreporte/${tipo}/${situacion}/${fecha_inicio}/${fecha_fin}`;

    // Abre la URL en una nueva pestaña
    window.open(url, '_blank');
}


function generateExcelReport() {
    let tipo = document.getElementById('tipo-filter').value;
    let situacion = document.getElementById('situacion-filter').value;
    let fecha_inicio = document.getElementById('fecha_inicio').value;
    let fecha_fin = document.getElementById('fecha_fin').value;

    let url = `/solicitud/generarExcelReporte/${tipo}/${situacion}/${fecha_inicio}/${fecha_fin}`;

    // Abrir la URL en una nueva pestaña para descargar el archivo Excel
    window.open(url, '_blank');
}

function generarExcelAlojamientos() {
    const turno = document.getElementById('rel_turnocaja_id')?.value || '0';
    const search = document.getElementById('search-input')?.value?.trim() || '';
    const fechaInicio = document.getElementById('fecha_inicio')?.value || '';
    const fechaFin = document.getElementById('fecha_fin')?.value || '';
    const modalidad = document.getElementById('modalidad-filter')?.value || '';
    const situacion = document.getElementById('situacion-filter')?.value || '';

    const url = `/movhabitacion/exportarExcel/null/null/null/null/null?turno=${encodeURIComponent(turno)}&search=${encodeURIComponent(search)}&fechaInicio=${encodeURIComponent(fechaInicio)}&fechaFin=${encodeURIComponent(fechaFin)}&modalidad=${encodeURIComponent(modalidad)}&situacion=${encodeURIComponent(situacion)}`;

    window.open(url, '_blank');
}


function generateExcelReportVentas() {
    let tipo = document.getElementById('tipo-filter').value;
    let turno = document.getElementById('rel_turnocaja_id').value;
    let fecha_inicio = document.getElementById('txtFechaInicio').value;
    let fecha_fin = document.getElementById('txtFechaFin').value;

    let url = `/ventas/generarExcelReporte/${tipo}/${turno}/${fecha_inicio}/${fecha_fin}`;
    window.open(url, '_blank');
}

function getReservaReportQuery() {
    const searchInput = document.getElementById('search-input');
    const fechaInicioInput = document.getElementById('txtFechaInicio');
    const fechaFinInput = document.getElementById('txtFechaFin');
    const estadoSelect = document.getElementById('situacion-filter');
    const origenSelect = document.getElementById('origen-filter');
    const habitacionSelect = document.getElementById('habitacion-filter');
    const movimientoSituacionSelect = document.getElementById('movimiento-situacion-filter');
    const perPageSelect = document.getElementById('records-per-page');

    if (!searchInput || !fechaInicioInput || !fechaFinInput || !estadoSelect || !perPageSelect) {
        return null;
    }

    const params = new URLSearchParams();
    const search = searchInput.value.trim();
    const fechaInicio = fechaInicioInput.value || '';
    const fechaFin = fechaFinInput.value || '';
    const estado = estadoSelect.value || 'TODAS';
    const origen = origenSelect ? origenSelect.value : '';
    const habitacion = habitacionSelect ? habitacionSelect.value : '';
    const movimientoSituacion = movimientoSituacionSelect ? movimientoSituacionSelect.value : 'TODAS';
    const perPage = perPageSelect.value || '10';

    if (search) params.set('search', search);
    if (fechaInicio) params.set('fecha_inicio', fechaInicio);
    if (fechaFin) params.set('fecha_fin', fechaFin);
    if (estado) params.set('estado', estado);
    if (origen) params.set('origen', origen);
    if (habitacion) params.set('habitacion', habitacion);
    if (movimientoSituacion) params.set('movimiento_situacion', movimientoSituacion);
    if (perPage) params.set('perPage', perPage);

    return params.toString();
}

function filterReservasReport() {
    const query = getReservaReportQuery();
    if (query === null) {
        return;
    }

    const url = `/movreservareport${query ? `?${query}` : ''}`;

    mostrarPantallaDeCarga();

    fetch(url, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('Error al obtener datos.');
            }
            return response.text();
        })
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newTableContainer = doc.querySelector('#tb-container');

            if (!newTableContainer) {
                throw new Error('No se pudo actualizar la tabla.');
            }

            document.querySelector('#tb-container').innerHTML = newTableContainer.innerHTML;

            M.AutoInit();
            reinicializarDataTables();
            ocultarPantallaDeCarga();
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarMensajeError('Error al filtrar reservas');
            ocultarPantallaDeCarga();
        });
}

function generatePDFReportReservas() {
    const query = getReservaReportQuery();
    if (query === null) {
        return;
    }

    const url = `/movreserva/reporte/pdf${query ? `?${query}` : ''}`;
    window.open(url, '_blank');
}

function generateExcelReportReservas() {
    const query = getReservaReportQuery();
    if (query === null) {
        return;
    }

    const url = `/movreserva/reporte/excel${query ? `?${query}` : ''}`;
    window.open(url, '_blank');
}

function copiarUrlQr(url) {
    navigator.clipboard.writeText(url).then(() => {
        mostrarMensajeExito("Encale copiado");
    }).catch(err => {
        console.error("Error al copiar:", err);
    });
}

function mostrarMensajeExito(mensaje) {
    Swal.fire({
        icon: 'success',
        title: 'Éxito',
        text: mensaje,
        position: 'bottom-left',
        showConfirmButton: false,
        timer: 4000,
        toast: true,
        timerProgressBar: true,
        customClass: {
            popup: 'rounded-popup',
            icon: 'icono-exito-ajustado', // Clase para ajustar el icono
        },
        didOpen: (swalInstance) => {
            // Eliminar selects extraños por seguridad visual
            const selects = swalInstance.querySelectorAll('select');
            selects.forEach(select => select.remove());

            // Pausar y reanudar el temporizador
            swalInstance.addEventListener('mouseenter', Swal.stopTimer);
            swalInstance.addEventListener('mouseleave', Swal.resumeTimer);
        }
    });
}

function mostrarMensajeInterrogante(mensaje) {
    Swal.fire({
        icon: 'info',
        title: mensaje,
        text: '',
        position: 'bottom-left',
        showConfirmButton: false,
        timer: 4000,
        toast: true,
        timerProgressBar: true,
        customClass: {
            popup: 'rounded-popup',
            icon: 'swal2-icon-info-ajustado',
            title: 'swal2-title-ajustado',
        },
        didOpen: (swalInstance) => {
            // Eliminar selects extraños por seguridad visual
            const selects = swalInstance.querySelectorAll('select');
            selects.forEach(select => select.remove());

            // Ajustar el icono y el texto
            const icon = swalInstance.querySelector('.swal2-icon');
            if (icon) {
                icon.style.marginTop = '25px';
            }
            const title = swalInstance.querySelector('.swal2-title');
            if (title) {
                title.style.marginTop = '10px';
            }

            // Pausar y reanudar el temporizador
            swalInstance.addEventListener('mouseenter', Swal.stopTimer);
            swalInstance.addEventListener('mouseleave', Swal.resumeTimer);
        }
    });
}

function mostrarMensajeError(mensaje = 'Ha ocurrido un error inesperado. Por favor, inténtelo nuevamente.') {
    Swal.fire({
        icon: 'error',
        title: 'Error',
        text: mensaje,
        position: 'bottom-left',
        showConfirmButton: false,
        timer: 6000,
        toast: true,
        timerProgressBar: true,
        background: '#fff3f3',
        customClass: {
            popup: 'rounded-popup',
            title: 'swal2-title-error',
            icon: 'swal2-icon-error'
        },
        didOpen: (swalInstance) => {
            // Asegura que ningún select indeseado esté presente
            const selects = swalInstance.querySelectorAll('select');
            selects.forEach(select => select.remove());

            // Control del temporizador con el mouse
            swalInstance.addEventListener('mouseenter', Swal.stopTimer);
            swalInstance.addEventListener('mouseleave', Swal.resumeTimer);
        }
    });
}



// Función para manejar la selección de habitaciones al seleccionar un piso
function togglePisoSelection(pisoCheckbox) {
    let pisoId = pisoCheckbox.dataset.piso;
    let habitaciones = document.querySelectorAll(`.habitacion-checkbox[data-piso="${pisoId}"]`);

    habitaciones.forEach(habitacion => {
        habitacion.checked = pisoCheckbox.checked;
    });

    actualizarHabitacionesSeleccionadas();
}

function toggleHabitacionSelectionHouse(habitacionCheckbox) {
    let pisoId = habitacionCheckbox.dataset.piso;
    let pisoCheckbox = document.querySelector(`.piso-checkbox[data-piso="${pisoId}"]`);
    let habitaciones = document.querySelectorAll(`.habitacion-checkbox[data-piso="${pisoId}"]`);

    let todasMarcadas = [...habitaciones].every(h => h.checked);
    pisoCheckbox.checked = todasMarcadas;


    actualizarHabitacionesSeleccionadas();
}

// Función para actualizar el campo oculto con las habitaciones seleccionadas
function actualizarHabitacionesSeleccionadas() {
    let habitacionesSeleccionadas = [];

    document.querySelectorAll('.habitacion-checkbox:checked').forEach(checkbox => {
        habitacionesSeleccionadas.push(checkbox.dataset.id);
    });

    let inputHabitaciones = document.getElementById('habitaciones_seleccionadas');
    inputHabitaciones.value = JSON.stringify(habitacionesSeleccionadas);


}

let housekeepingId = null;

function abrirModalCompletarLimpieza(id) {
    housekeepingId = id;
    document.getElementById('modal-container').style.display = 'flex';

    document.getElementById('modal-finalizar').style.display = 'block';
}

function cerrarModalCompletarLimpieza() {
    document.getElementById('modal-finalizar').style.display = 'none';
    document.getElementById('modal-container').style.display = 'none';

    document.getElementById('observaciones').value = ''; // Limpiar el campo
}
function EnviaHousekeepingFinish() {
    let observaciones = document.getElementById("observaciones").value.trim();
    let btnConfirmar = document.querySelector("#modal-finalizar button.bg-green-500"); // Asegurar el selector correcto

    if (!housekeepingId) {
        alert("Error: No se ha seleccionado una limpieza.");
        return;
    }

    // Si el botón no se encuentra, evitar el error
    if (btnConfirmar) {
        btnConfirmar.disabled = true;
        btnConfirmar.textContent = "Procesando...";
    }

    fetch(`/housekeeping/finish/${housekeepingId}`, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
        },
        body: JSON.stringify({ observaciones: observaciones })
    })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => { throw err; });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert("Error: " + (data.errors || "No se pudo finalizar la limpieza."));
            }
        })
        .catch(error => {
            console.error("Error:", error);
            alert("Ocurrió un problema al finalizar la limpieza.");
        })
        .finally(() => {
            cerrarModalCompletarLimpieza();
            if (btnConfirmar) {
                btnConfirmar.disabled = false;
                btnConfirmar.textContent = "Finalizar";
            }
        });
}



let habitacionesSeleccionadas = new Map(); // Usamos Map para evitar duplicados

async function abrirModalHabitaciones() {
    mostrarPantallaDeCarga();
    let inputDetalles = document.getElementById("detalles_habitaciones_json");
    let fecha = document.getElementById("fechavencimiento")?.value;
    if (!fecha) {
        const fechaIngresoValor = document.getElementById("fechaingreso")?.value;
        fecha = fechaIngresoValor ? fechaIngresoValor.split('T')[0] : new Date().toISOString().split('T')[0];
    }


    habitacionesSeleccionadas.clear();

    if (inputDetalles.value && inputDetalles.value !== "[]") {
        try {
            let habitaciones = JSON.parse(inputDetalles.value);
            habitaciones.forEach(habitacion => {
                habitacionesSeleccionadas.set(parseInt(habitacion.id), habitacion);
            });
        } catch (error) {
            console.error("Error al parsear detalles_habitaciones_json:", error);
        }
    }

    // Hacer fetch para obtener las habitaciones disponibles
    try {
        const res = await fetch(`/reservas/habitaciones-disponibles?fecha=${fecha}`);
        const data = await res.json();

        const modalContent = document.querySelector("#modalHabitaciones .modal-content");
        let html = `<h5>Seleccionar Habitaciones Disponibles para la fecha ${fecha}</h5>`;

        Object.entries(data.disponibles || {}).forEach(([pisoId, habitaciones]) => {

            if (Array.isArray(habitaciones) && habitaciones.length > 0) {
                const pisoNombre = habitaciones[0]?.piso?.nombre || `Piso ${pisoId}`;
                html += `<h6 class="blue-text">${pisoNombre}</h6>
             <div class="card-action row" style="margin: 20px;">`;

                habitaciones.forEach(habitacion => {
                    // Evitar mostrar habitaciones ocupadas
                    if (habitacion.situacion?.toLowerCase() === 'ocupada') {
                        return; // salta esta iteración
                    }

                    const tipoNombre = habitacion.tipohabitacion?.nombre || 'Sin tipo';
                    const situacion = habitacion.situacion?.toLowerCase() || 'desconocido';
                    const precio = habitacion.precio || '0.00';
                    const numero = habitacion.numero || 'N/A';
                    const checked = habitacionesSeleccionadas.has(habitacion.id) ? "checked" : "";

                    html += `
            <div class="col s12 m4 l2 habitacion-item">
                <div class="card custom-card card-habitacion disponible" 
                     data-habitacion-id="${habitacion.id}">
                    <div class="card-body" 
                         style="display: flex; flex-direction: column; justify-content: center; align-items: center;">
                        <div class="card-item-title">
                            <span>${tipoNombre}</span>
                        </div>
                        <div class="card-item-body">
                            <div class="habitacion-detalles d-flex flex-column">
                                <h4 class="font-weight-bold text-dark text-light numerohabitacion">${numero}</h4>
                            </div>
                        </div>
                        <div class="card-item-footer">
                            <label>
                                <input type="checkbox" class="filled-in habitacion-checkbox"
                                    data-id="${habitacion.id}"
                                    data-piso="${habitacion.piso_id}"
                                    data-tipo="${tipoNombre}"
                                    data-situacion="${situacion}"
                                    data-precio="${precio}"
                                    onclick="toggleHabitacionSelection(this)"
                                    ${checked}>
                                <span style="color: white">Asignar</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>`;
                });

                html += `</div>`;
            } else {
                console.warn(`⚠️ Piso ${pisoId} no tiene habitaciones.`);
            }

        });


        modalContent.innerHTML = html;
        M.Modal.getInstance(document.getElementById("modalHabitaciones")).open();
        setTimeout(ocultarPantallaDeCarga, 500);

    } catch (error) {
        console.error("Error al obtener habitaciones disponibles:", error);
        mostrarMensajeError("No se pudieron cargar las habitaciones disponibles.");
    }
}

async function cambiarHabitacion(habitacionId) {
    mostrarPantallaDeCarga();
    const inputDetalles = document.getElementById("detalles_habitaciones_json");
    let fecha = document.getElementById("fechavencimiento")?.value;
    if (!fecha) {
        const fechaIngresoValor = document.getElementById("fechaingreso")?.value;
        fecha = fechaIngresoValor ? fechaIngresoValor.split('T')[0] : new Date().toISOString().split('T')[0];
    }

    habitacionesSeleccionadas.clear();

    let habitacionesOriginales = [];
    if (inputDetalles.value && inputDetalles.value !== "[]") {
        try {
            habitacionesOriginales = JSON.parse(inputDetalles.value).filter(h => parseInt(h.id) !== parseInt(habitacionId));
        } catch (error) {
            console.error("Error al parsear detalles_habitaciones_json:", error);
        }
    }

    try {
        const res = await fetch(`/reservas/habitaciones-disponibles?fecha=${fecha}`);
        const data = await res.json();

        const modalContent = document.querySelector("#modalHabitaciones .modal-content");
        const numero = $(`[data-habitacion-id="${habitacionId}"]`).find('.numerohabitacion').text().trim();

        let html = `
        <div class="card-content">
            <p class="grey-text text-darken-3 without-tb center card-title" id="modal-title">
                <i class="fa-solid fa-right-left left"></i> Reemplazar habitación N° ${numero}
            </p>
        </div>
    `;

        // ✅ Solo iteramos las habitaciones DISPONIBLES y en LIMPIEZA
        Object.entries(data.disponibles || {}).forEach(([pisoId, habitaciones]) => {
            if (!Array.isArray(habitaciones)) return; // seguridad
            const filtradas = habitaciones.filter(h =>
                parseInt(h.id) !== parseInt(habitacionId) &&
                h.situacion?.toLowerCase() !== 'ocupada'
            );

            if (filtradas.length > 0) {
                const pisoNombre = filtradas[0]?.piso?.nombre || `Piso ${pisoId}`;
                html += `<h6 class="blue-text">${pisoNombre}</h6><div class="card-action row" style="margin: 20px">`;

                filtradas.forEach(habitacion => {
                    const esLimpieza = habitacion.situacion?.toLowerCase() === 'limpieza';
                    const badgeLimpieza = esLimpieza
                        ? `<span style="position:absolute;top:4px;right:4px;background:#ff9800;color:white;font-size:9px;padding:2px 5px;border-radius:8px;">Limpieza</span>`
                        : '';
                    html += `
                    <div class="col s12 m4 l2 habitacion-item">
                        <div class="card custom-card card-habitacion disponible" data-habitacion-id="${habitacion.id}" style="position:relative;">
                            ${badgeLimpieza}
                            <div class="card-body" style="display: flex; flex-direction: column; justify-content: center; align-items: center;">
                                <div class="card-item-title">
                                    <span>${habitacion.tipohabitacion?.nombre || 'Sin tipo'}</span>
                                </div>
                                <div class="card-item-body">
                                    <div class="habitacion-detalles d-flex flex-column">
                                        <h4 class="font-weight-bold text-dark text-light numerohabitacion">${habitacion.numero}</h4>
                                    </div>
                                </div>
                                <div class="card-item-footer">
                                    <label>
                                        <input type="checkbox" class="filled-in habitacion-checkbox"
                                            data-id="${habitacion.id}"
                                            data-piso="${habitacion.piso_id}"
                                            data-tipo="${habitacion.tipohabitacion?.nombre || ''}"
                                            data-situacion="${habitacion.situacion?.toLowerCase() || 'disponible'}"
                                            data-precio="${habitacion.precio || 0}"
                                            onclick="toggleHabitacionSelection(this)">
                                        <span style="color: white">Asignar</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                });

                html += `</div>`;
            }
        });

        modalContent.innerHTML = html;
        inputDetalles.value = JSON.stringify(habitacionesOriginales);
        habitacionesOriginales.forEach(h => habitacionesSeleccionadas.set(parseInt(h.id), h));

        M.Modal.getInstance(document.getElementById("modalHabitaciones")).open();
    } catch (error) {
        console.error("Error al obtener habitaciones disponibles:", error);
        mostrarMensajeError("No se pudieron cargar las habitaciones disponibles.");
    }


    setTimeout(ocultarPantallaDeCarga, 500);
}

function toggleHabitacionSelection(checkbox) {
    const id = checkbox.getAttribute("data-id");
    const numero = checkbox.closest('.habitacion-item').querySelector('.numerohabitacion').textContent;
    const tipo = checkbox.getAttribute("data-tipo");
    const situacion = checkbox.getAttribute("data-situacion");
    const precio = parseFloat(checkbox.getAttribute("data-precio")) || 0; // Obtener precio

    if (checkbox.checked) {
        habitacionesSeleccionadas.set(id, { id, numero, tipo, situacion, precio });
    } else {
        habitacionesSeleccionadas.delete(id);
    }
} function confirmarHabitaciones() {
    mostrarPantallaDeCarga();

    const contenedor = document.getElementById("habitacionesSeleccionadas");
    contenedor.innerHTML = ""; // Limpiar antes de agregar nuevas

    const botonAgregar = document.getElementById("botonAgregarHabitacion");
    const switchState = document.getElementById("switchState")?.value || "unico";
    let habitacionesArray = [];

    const tarifaReadonlyAttr = tarifaCheckinEsSoloLectura() ? 'readonly' : '';

    // 🔹 Generar tarjetas de habitaciones seleccionadas
    habitacionesSeleccionadas.forEach(habitacion => {
        habitacionesArray.push(habitacion);

        const card = `
            <div class="col s12 m4 l5 habitacion-item" 
                 id="habitacion-${habitacion.id}" 
                 style="max-width: 148px; min-width: 140px; position: relative;">
                 
                <div class="card custom-card card-habitacion disponible"
                     data-habitacion-id="${habitacion.id}">

                    <!-- Botón eliminar -->
                    <a class="btn-remove-habitacion tooltipped"
                       onclick="eliminarHabitacion(${habitacion.id})"
                       data-tooltip="Eliminar habitación">
                        <i class="fa-solid fa-times"></i>
                    </a>

                    <!-- Botón cambiar -->
                    <a class="btn-change-habitacion tooltipped"
                       onclick="cambiarHabitacion(${habitacion.id})"
                       data-tooltip="Cambiar habitación">
                        <i class="fa-solid fa-right-left"></i>
                    </a>

                    <div class="card-body"
                         style="min-height: 90px; display: flex; flex-direction: column; justify-content: center; align-items: center;">
                         
                        <div class="card-item-title"
                             style="text-align: center; display: flex; justify-content: center; align-items: center;">
                            <span>${habitacion.tipo}</span>
                        </div>

                        <div class="card-item-body">
                            <p style="font-size:30px; text-align: center; display: flex; justify-content: center; align-items: center; margin: 10px;"
                               class="font-weight-bold text-dark text-light numerohabitacion">
                                ${habitacion.numero}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Input de Tarifa -->
                <div class="input-field col s12" style="min-width: 137px;" id="tarifa-grupo">
                    <input type="number" 
                           id="tarifa-${habitacion.id}" 
                           name="tarifa_habitacion[]" 
                           value="${parseFloat(habitacion.precio || 0).toFixed(2)}"
                           class="tarifa-input-grupal" 
                           step="0.01"
                           ${tarifaReadonlyAttr}>
                    <label for="tarifa-${habitacion.id}" class="active">Tarifa</label>
                </div>
            </div>
        `;

        contenedor.insertAdjacentHTML("beforeend", card);
    });

    // 🔹 Agregar el botón “+” al final de las habitaciones
    const botonMas = `
        <div class="col s12 m4 l5 habitacion-item" style="max-width: 148px; min-width: 140px;">
            <div class="card custom-card card-habitacion disponible"
                onclick="abrirModalHabitaciones()">
                <div class="card-body" 
                    style="min-height: 90px; display: flex; flex-direction: column; justify-content: center; align-items: center;">
                    <i class="fa-solid fa-plus fa-2x"></i>
                </div>
            </div>
        </div>
    `;
    contenedor.insertAdjacentHTML("beforeend", botonMas);

    // 🔹 Actualizar input oculto con las habitaciones seleccionadas
    document.getElementById("detalles_habitaciones_json").value = JSON.stringify(habitacionesArray);

    // 🔹 Actualizar el total de la reserva
    actualizarTotalReserva(habitacionesArray);

    // 🔹 Ocultar o mostrar botón externo si aplica
    if (botonAgregar) {
        botonAgregar.style.display = (switchState === "unico") ? "none" : "block";
    }

    // 🔹 Cerrar modal y reinicializar Materialize
    const modal = M.Modal.getInstance(document.getElementById('modalHabitaciones'));
    if (modal) modal.close();

    setTimeout(() => {
        M.AutoInit();
        inicializartooltipped?.(); // Reinicia tooltips si está definida
        ocultarPantallaDeCarga();
    }, 800);
}



function eliminarHabitacion(id) {
    // Convertir ID a string para evitar problemas de tipo
    id = String(id);

    // Eliminar del mapa de habitaciones seleccionadas
    if (habitacionesSeleccionadas.has(id)) {
        habitacionesSeleccionadas.delete(id);
    }

    // Obtener el input hidden y su valor actual
    const inputHidden = document.getElementById("detalles_habitaciones_json");
    let habitacionesArray = JSON.parse(inputHidden.value || "[]");

    // Filtrar el array eliminando la habitación con el ID dado
    habitacionesArray = habitacionesArray.filter(habitacion => String(habitacion.id) !== id);

    // Actualizar el input hidden con el nuevo JSON
    inputHidden.value = JSON.stringify(habitacionesArray);

    // Eliminar la tarjeta de la habitación visualmente si existe
    const habitacionElement = document.getElementById(`habitacion-${id}`);
    if (habitacionElement) {
        habitacionElement.remove();
    }

    // 🔹 **Desmarcar el checkbox de la habitación eliminada**
    let checkbox = document.querySelector(`.habitacion-checkbox[data-id="${id}"]`);
    if (checkbox) {
        checkbox.checked = false;
    }

    // Recalcular el total y actualizar el input hidden
    actualizarTotalReserva(habitacionesArray);

}

function actualizarTotalReserva(habitacionesArray = null) {
    // Si no se pasa el array, obtenerlo del input hidden
    if (!habitacionesArray) {
        const inputHidden = document.getElementById("detalles_habitaciones_json");
        habitacionesArray = JSON.parse(inputHidden?.value || "[]");
    }

    // Calcular el total sumando los precios de todas las habitaciones
    let totalReserva = habitacionesArray.reduce((total, habitacion) => total + parseFloat(habitacion.precio || 0), 0);

    // Obtener el valor del adelanto (si existe)
    let adelanto = parseFloat(document.getElementById("adelanto")?.value || 0);

    // Calcular la deuda
    let deuda = totalReserva - adelanto;

    // Actualizar los campos en el formulario
    const totalInput = document.getElementById("total") || document.getElementById("preciototal");
    if (totalInput) {
        totalInput.value = totalReserva.toFixed(2);
    }
    const deudaInput = document.getElementById("deuda");
    if (deudaInput) {
        deudaInput.value = deuda.toFixed(2);
    }
}

function calcularTotalDiasReserva() {
    let switchState = document.getElementById('switchState').value;
    let dias = parseInt(document.getElementById('dias').value) || 0;
    let total = 0;

    if (switchState === "unico") {
        // Modo Único: tarifa de la habitación principal
        let tarifaUnica = parseFloat(document.getElementById('tarifa-habitacion').value) || 0;
        total = tarifaUnica * dias;
    } else {
        // Modo Grupal: sumar todas las tarifas de habitaciones
        document.querySelectorAll('.tarifa-input-grupal').forEach(input => {
            let tarifa = parseFloat(input.value) || 0;
            total += tarifa;
        });
        total *= dias;
    }

    // Agregar Early y Late
    let early = parseFloat(document.getElementById('early')?.value) || 0;
    let late = parseFloat(document.getElementById('late')?.value) || 0;

    total += early + late;

    // Mostrar total
    document.getElementById('total').value = total.toFixed(2);
    document.getElementById('btn-save-MovReserva').disabled = false;

    actualizarDeuda(); // Si tienes una función para recalcular la deuda
}

function activarPago() {
    const checkbox = document.getElementById("pagarCheckbox");
    const contenedorPago = document.getElementById("tipopago");

    if (checkbox.checked) {
        contenedorPago.style.display = "block"; // Mostrar la sección
    } else {
        contenedorPago.style.display = "none"; // Ocultar la sección
    }
}
function seleccionarMedioPago(medioId, button) {
    let inputMedioPago = document.getElementById('medio_pago');
    if (inputMedioPago) {
        inputMedioPago.value = medioId;
    }

    // Recorrer todos los botones para actualizar estilos
    document.querySelectorAll('.btn-todo-det_movcaja').forEach(btn => {
        if (btn === button) {
            btn.classList.add('selected-payment');  // Resalta el seleccionado
            btn.classList.remove('unselected-payment'); // Quita el gris
        } else {
            btn.classList.remove('selected-payment');
            btn.classList.add('unselected-payment'); // Aplica gris a los no seleccionados
        }
    });
}



function actualizarDeuda() {
    const total = parseFloat(document.getElementById("total").value) || 0;
    const adelanto = parseFloat(document.getElementById("adelanto").value) || 0;
    document.getElementById("deuda").value = (total - adelanto).toFixed(2);
}

let tipoRegistro = "principal"; // Por defecto, se asume que es cliente principal

// Función para definir qué tipo de persona se está registrando
function abrirModalRegistro(tipo) {
    tipoRegistro = tipo; // Guarda si es principal o acompañante
}

function recargarContenidoModal(url, mensaje) {
    return fetch(url, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
        .then(response => {
            if (!response.ok) {
                throw new Error(`Error HTTP: ${response.status}`);
            }
            return response.text();
        })
        .then(html => {
            document.getElementById('modal-body-content').innerHTML = html;

            const modalEl = document.getElementById('modal');
            let modalInstance = M.Modal.getInstance(modalEl);
            if (!modalInstance) {
                modalInstance = M.Modal.init(modalEl);
            }
            modalInstance.open();

            M.FormSelect.init(document.querySelectorAll('select'));
            initializeAutocomplete();

            const form = document.getElementById('form');
            if (form) {
                form.addEventListener('submit', enviarFormularioAjax);
            }

            if (typeof M !== 'undefined' && M.Tooltip) {
                M.Tooltip.init(document.querySelectorAll('.tooltipped'));
            }

            if (mensaje) {
                mostrarMensajeExito(mensaje);
            }
        })
        .catch(error => {
            console.error('Error al recargar el modal:', error);
            mostrarMensajeError('No se pudo recargar el formulario.');
        })
        .finally(() => {
            forzarOcultarPantallaDeCarga();
        });
}

// Función para registrar la persona
function registrarPersona() {
    let form = document.getElementById("form");
    let formData = new FormData(form);
    let url = document.getElementById("form-url").value; // Obtener la URL correcta
    mostrarPantallaDeCarga();
    fetch(url, {
        method: "POST",
        body: formData,
        headers: {
            "X-Requested-With": "XMLHttpRequest" // Indica que es una petición AJAX
        }
    })
        .then(response => {
            if (!response.ok) {
                throw new Error(`Error HTTP: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            const accion = formData.get('accion') || '';

            if (data.success && data.redirect_url && accion === 'CONVERTIR') {
                recargarContenidoModal(data.redirect_url, data.message || 'Persona registrada correctamente');
                return;
            }

            if (data.success && data.persona) {
                let persona = data.persona;
                let personamaestro = data.personamaestro;
                let nrodoc = personamaestro?.nrodoc || "Sin Doc";
                let nombreCompleto = `${persona.apellidos} ${persona.nombres}`;
                let telefonoMovil = persona.telefonomovil || ""; // Si no existe, asigna una cadena vacía
                let email = persona.email || ""; // Si no existe, asigna una cadena vacía
                let tipo = data.tipo || "CLIENTE"; // Asignar tipo de registro
                if (tipoRegistro === "acompanante") {
                    agregarAcompanante(persona.id, nrodoc, nombreCompleto);


                } else {
                    const personaIdInput = document.getElementById("persona_id");
                    const personaAutocomplete = document.getElementById("persona_autocomplete");

                    if (!personaIdInput || !personaAutocomplete) {
                        ocultarPantallaDeCarga();
                        mostrarMensajeError('No se pudo asignar la persona al formulario actual.');
                        return;
                    }

                    if (tipo === "ARRENDADOR") {
                        document.getElementById("arrendador_id").value = persona.id;
                        document.getElementById("arrendador_autocomplete").value = `${nrodoc} - ${nombreCompleto}`;
                        document.getElementById('btnNuevoArrendador').style.display = 'none';
                        document.getElementById('btnQuitarArrendador').style.display = 'block';
                        document.getElementById('arrendador_autocomplete').disabled = true;
                    } else {
                        personaIdInput.value = persona.id;
                        personaAutocomplete.value = `${nrodoc} - ${nombreCompleto}`;
                        const btnNuevaPersona = document.getElementById('btnNuevaPersona');
                        const btnQuitarCliente = document.getElementById('btnQuitarCliente');
                        if (btnNuevaPersona) btnNuevaPersona.style.display = 'none';
                        if (btnQuitarCliente) btnQuitarCliente.style.display = 'block';
                        personaAutocomplete.disabled = true;
                    }


                    // Asignar teléfono móvil si el campo existe en el DOM
                    let celularInput = document.getElementById("celular");
                    let celularLabel = document.querySelector("label[for='celular']");
                    if (celularInput) {
                        celularInput.value = telefonoMovil;
                        if (celularLabel) celularLabel.classList.add("active");
                    }

                    // Asignar email si el campo existe en el DOM
                    let emailInput = document.getElementById("email");
                    let emailLabel = document.querySelector("label[for='email']");
                    if (emailInput) {
                        emailInput.value = email;
                        if (emailLabel) emailLabel.classList.add("active");
                    }
                }
                document.querySelector(".modal-close").click();
                mostrarMensajeExito("Persona registrada correctamente");


                ocultarPantallaDeCarga();
                //M.AutoInit();
            } else {
                console.warn("La clave 'persona' no está en la respuesta del servidor.");
                ocultarPantallaDeCarga();
            }
        })
        .catch(error => {
            console.error("Error:", error);
            mostrarMensajeError("Error al registrar la persona. Por favor, inténtelo nuevamente.");
            forzarOcultarPantallaDeCarga();
        }).finally(() => {
            tipoRegistro = "principal";
        });
}
function agregarAcompanante(id, nrodoc, nombreCompleto) {
    const tablaDT = $('#acompanantes-table').DataTable();

    // Verificar si el acompañante ya existe en el DataTable
    const existe = tablaDT
        .rows()
        .nodes()
        .to$()
        .filter(`tr[data-id="${id}"]`).length > 0;

    if (existe) {
        mostrarMensajeError("El acompañante ya está agregado.");
        return; // ✅ Salimos sin agregar
    }

    const modoReserva = document.getElementById("switchState")?.value || "unico";
    let habitacionId = null;
    let numero = null;
    let tipo = null;

    const habitacionesContenedor = document.getElementById("habitacionesSeleccionadas");
    const primeraHabitacion = habitacionesContenedor?.querySelector(".habitacion-item");

    if (primeraHabitacion) {
        habitacionId = primeraHabitacion.getAttribute("id").replace("habitacion-", "");
        tipo = primeraHabitacion.querySelector(".card-item-title span")?.innerText.trim();
        numero = primeraHabitacion.querySelector(".numerohabitacion")?.innerText.trim();
    }

    let habitacionColumna = '';
    if (habitacionId) {
        if (modoReserva === "unico") {
            habitacionColumna = `
                ${numero} - ${tipo}
                <input type="hidden" name="habitacion_acompanantes[${id}]" value="${habitacionId}">
            `;
        } else {
            const habitaciones = JSON.parse(document.getElementById("detalles_habitaciones_json")?.value || "[]");

            habitacionColumna = `
                <div class="td-center">
                    <select class="browser-default habitacion-select"
                            name="habitacion_acompanantes[${id}]">
                        ${habitaciones.map(h => `
                            <option value="${h.id}" ${h.id == habitacionId ? 'selected' : ''}>
                                ${h.numero} - ${h.tipo}
                            </option>
                        `).join('')}
                    </select>
                </div>
            `;

        }
    }

    // Construir la fila como array de columnas (para DataTables)
    let filaDT;

    if (habitacionColumna) {
        filaDT = [
            nrodoc,
            nombreCompleto,
            habitacionColumna,
            `<button class="btn red btn-small" onclick="eliminarAcompanante(this,${id})">
            <i class="material-icons">delete</i>
        </button>`
        ];
    } else {
        filaDT = [
            nrodoc,
            nombreCompleto,
            `<button class="btn red btn-small" onclick="eliminarAcompanante(this,${id})">
            <i class="material-icons">delete</i>
        </button>`
        ];
    }

    // Insertar fila en DataTable y agregar atributo data-id
    const nuevaFila = tablaDT.row.add(filaDT).draw(false).node();
    $(nuevaFila).attr('data-id', id);

    // ✅ Evitar duplicado en el array también
    const inputAcompanantes = document.getElementById("acompanantes");
    let acompanantesArray = JSON.parse(inputAcompanantes.value || "[]");

    let yaExiste = false;
    if (acompanantesArray.length && typeof acompanantesArray[0] === 'object') {
        yaExiste = acompanantesArray.some(a => a.id === id);
    } else {
        yaExiste = acompanantesArray.includes(id);
    }
    if (yaExiste) return;

    if (habitacionId) {
        let habitacionAsignadaId = habitacionId;
        if (modoReserva === "grupal") {
            const selectElement = $(nuevaFila).find("select")[0];
            habitacionAsignadaId = selectElement?.value || habitacionId;

            selectElement?.addEventListener("change", () => {
                actualizarInputAcompanantes(id, selectElement.value);
            });
        }
        acompanantesArray.push({
            id: id,
            habitacion_id: parseInt(habitacionAsignadaId),
        });
    } else {
        acompanantesArray.push(id);
    }

    inputAcompanantes.value = JSON.stringify(acompanantesArray);
}




function actualizarInputAcompanantes(personaId, nuevaHabitacionId) {
    const input = document.getElementById("acompanantes");
    let data = JSON.parse(input.value || "[]");

    const index = data.findIndex(a => a.id == personaId);
    if (index !== -1) {
        data[index].habitacion_id = parseInt(nuevaHabitacionId);
        input.value = JSON.stringify(data);
    }
}

function eliminarAcompanante(btn, id) {
    // 1) Obtener la fila (si DataTables muestra fila "child", usar la parent real)
    let tr = btn.closest('tr');
    if (tr && tr.classList.contains('child') && tr.previousElementSibling) {
        tr = tr.previousElementSibling; // la fila "parent" real en DataTables
    }

    // 2) Si usas DataTables, elimina con su API; si no, elimina el TR del DOM
    const dt = window.jQuery && jQuery.fn && jQuery.fn.dataTable
        ? jQuery('#acompanantes-table').DataTable()
        : null;

    if (dt) {
        dt.row(tr).remove().draw(false);
    } else if (tr) {
        tr.remove();
    }

    // 3) Actualizar el hidden #acompanantes
    const inputAcompanantes = document.getElementById('acompanantes');
    let acompanantesArray = [];
    try { acompanantesArray = JSON.parse(inputAcompanantes.value || '[]'); } catch (e) { }

    if (Array.isArray(acompanantesArray) && acompanantesArray.length) {
        if (typeof acompanantesArray[0] === 'number') {
            acompanantesArray = acompanantesArray.filter(acompId => Number(acompId) !== Number(id));
        } else if (typeof acompanantesArray[0] === 'object' && acompanantesArray[0] !== null) {
            acompanantesArray = acompanantesArray.filter(acomp => Number(acomp.id) !== Number(id));
        }
    }

    inputAcompanantes.value = JSON.stringify(acompanantesArray);
}
function deshabilitarBotonGuardar() {
    document.getElementById('btn-save-MovReserva').disabled = true;
}

// Función para mostrar el modal de mantenimiento
function mostrarModalMantenimiento(numeroHabitacion) {
    const habitacionLabel = `Habitacion ${numeroHabitacion}`;

    Swal.fire({
        title: 'Habitacion en mantenimiento',
        text: `La ${habitacionLabel} esta en mantenimiento y no se pueden hacer reservas en este momento.`,
        icon: 'info',
        confirmButtonText: 'Entendido',
        confirmButtonColor: '#1e3d8a',
        customClass: {
            popup: 'rounded-swal'
        }
    });
}

function activarModoGrupal() {
    let switchElement = document.getElementById("switchHoras");
    let switchStateInput = document.getElementById("switchState");
    let contenedorAdvertencia = document.getElementById("contenedorAdvertenciaMonto"); // Contenedor del modal

    if (switchElement && !switchElement.checked) {
        switchElement.checked = true; // Activa el switch a "Grupal"
        switchStateInput.value = "grupo";
        toggleModoCheckout(); // Ejecuta la función que actualiza la vista
    }

    // Ocultar el modal de advertencia
    if (contenedorAdvertencia) {
        contenedorAdvertencia.style.display = "none";
    }

    // 🔒 Deshabilitar el switch para evitar cambios manuales
    if (switchElement) {
        switchElement.disabled = true;
    }
}


function generarPDFAlojamientos() {
    const turno = document.getElementById('rel_turnocaja_id')?.value || '0';
    const search = document.getElementById('search-input')?.value?.trim() || '';
    const fechaInicio = document.getElementById('fecha_inicio')?.value || '';
    const fechaFin = document.getElementById('fecha_fin')?.value || '';
    const modalidad = document.getElementById('modalidad-filter')?.value || '';
    const situacion = document.getElementById('situacion-filter')?.value || '';

    let url = `/checkouts/exportar-pdf/null/null/null/null/null?turno=${encodeURIComponent(turno)}&search=${encodeURIComponent(search)}&fechaInicio=${encodeURIComponent(fechaInicio)}&fechaFin=${encodeURIComponent(fechaFin)}&modalidad=${encodeURIComponent(modalidad)}&situacion=${encodeURIComponent(situacion)}`;
    window.open(url, '_blank');
}
function cargarEvolucionHabitacion(id) {
    fetch(`/habitaciones/${id}/evolucion`)
        .then(res => res.json())
        .then(data => {
            const fechas = data.fechas.map((f, i) => ({
                date: new Date(f).getTime(),
                value: data.valores[i]
            }));

            renderEvolucionChart(fechas);
        })
        .catch(err => console.error("Error al cargar evolución:", err));
}
function generatePDFReportHuesped() {
    // Obtener los valores de los filtros
    var tipopersona = document.getElementById('tipopersona').value;
    var genero = document.getElementById('genero').value;
    var noGrato = document.getElementById('no_grato') ? document.getElementById('no_grato').value : 'Todos';
    var search = document.getElementById('search-input').value;
    var perPage = document.getElementById('records-per-page').value;

    // Construir la URL del PDF
    var url = '/generarPDF/huesped?tipopersona=' + encodeURIComponent(tipopersona)
        + '&genero=' + encodeURIComponent(genero)
        + '&no_grato=' + encodeURIComponent(noGrato)
        + '&search=' + encodeURIComponent(search)
        + '&perPage=' + encodeURIComponent(perPage);

    // Abrir en nueva pestaña
    window.open(url, '_blank');
}
function renderEvolucionChart(data) {
    // Eliminar completamente el div y recrearlo
    const wrapper = document.getElementById("evolucion-chart-container");
    wrapper.innerHTML = '<div id="chartdiv-evolucion" style="width: 100%; height: 500px;"></div>';

    // Crear nuevo root limpio
    am5.ready(function () {
        let root = am5.Root.new("chartdiv-evolucion");

        const myTheme = am5.Theme.new(root);
        myTheme.rule("AxisLabel", ["minor"]).setAll({ dy: 1 });
        myTheme.rule("Grid", ["minor"]).setAll({ strokeOpacity: 0.08 });

        root.setThemes([
            am5themes_Animated.new(root),
            myTheme
        ]);

        let chart = root.container.children.push(am5xy.XYChart.new(root, {
            panX: false,
            panY: false,
            wheelX: "panX",
            wheelY: "zoomX",
            paddingLeft: 0
        }));

        let cursor = chart.set("cursor", am5xy.XYCursor.new(root, {
            behavior: "zoomX"
        }));
        cursor.lineY.set("visible", false);

        let xAxis = chart.xAxes.push(am5xy.DateAxis.new(root, {
            maxDeviation: 0,
            baseInterval: { timeUnit: "day", count: 1 },
            renderer: am5xy.AxisRendererX.new(root, {
                minorGridEnabled: true,
                minGridDistance: 200,
                minorLabelsEnabled: true
            }),
            tooltip: am5.Tooltip.new(root, {})
        }));

        xAxis.set("minorDateFormats", {
            day: "dd",
            month: "MM"
        });

        let yAxis = chart.yAxes.push(am5xy.ValueAxis.new(root, {
            renderer: am5xy.AxisRendererY.new(root, {})
        }));

        let series = chart.series.push(am5xy.LineSeries.new(root, {
            name: "Alojamientos",
            xAxis: xAxis,
            yAxis: yAxis,
            valueYField: "value",
            valueXField: "date",
            tooltip: am5.Tooltip.new(root, {
                labelText: "{valueY}"
            })
        }));

        series.bullets.push(function () {
            return am5.Bullet.new(root, {
                sprite: am5.Circle.new(root, {
                    radius: 5,
                    fill: series.get("fill")
                })
            });
        });

        chart.set("scrollbarX", am5.Scrollbar.new(root, {
            orientation: "horizontal"
        }));

        series.data.setAll(data);

        series.appear(1000);
        chart.appear(1000, 100);
    });
}
function sumarCantidad() {
    const input = document.getElementById('cantidad');
    let valor = parseInt(input.value) || 0;
    valor++;
    input.value = valor;
}

function restarCantidad() {
    const input = document.getElementById('cantidad');
    let valor = parseInt(input.value) || 0;
    if (valor > 1) {
        valor--;
        input.value = valor;
    }
}


function openMaintenanceModal() {
    mostrarPantallaDeCarga();

    const modal = document.getElementById('maintenance-modal');
    const overlay = document.getElementById('modal-overlay');
    const totalPagarInput = document.getElementById('total');
    const totalActualInput = document.getElementById('total_actual');

    // Setear el valor del campo total_actual con el valor de totalapagar
    if (totalPagarInput && totalActualInput) {
        totalActualInput.value = totalPagarInput.value;
    }


    setTimeout(() => {
        modal.style.display = 'block';
        modal.style.opacity = '1';
        modal.classList.add('open');
        modal.style.top = '10%';
        modal.style.transform = 'scaleX(1) scaleY(1)';

        overlay.style.display = 'block';
        ocultarPantallaDeCarga();
    }, 1000);
}

function openSplitBillModal() {
    mostrarPantallaDeCarga();

    const modal = document.getElementById('split-bill-modal');
    const overlay = document.getElementById('modal-overlay');
    const montoPagarInput = document.getElementById('monto_a_pagar');
    const montoActualInput = document.getElementById('monto_a_pagar');

    // Setear el valor del campo monto_a_pagar con un valor predeterminado
    if (montoPagarInput && montoActualInput) {
        montoActualInput.value = montoPagarInput.value;
    }

    setTimeout(() => {
        modal.style.display = 'block';
        modal.style.opacity = '1';
        modal.classList.add('open');
        modal.style.top = '10%';
        modal.style.transform = 'scaleX(1) scaleY(1)';

        overlay.style.display = 'block';
        ocultarPantallaDeCarga();
    }, 1000);
}


function openSplitBillArrModal() {
    mostrarPantallaDeCarga();

    const modal = document.getElementById('split-bill-modal');
    const overlay = document.getElementById('modal-overlay');
    const montoPagarInput = document.getElementById('monto_a_pagar');
    const montoActualInput = document.getElementById('monto_a_pagar');

    // Setear el valor del campo monto_a_pagar con un valor predeterminado
    if (montoPagarInput && montoActualInput) {
        montoActualInput.value = montoPagarInput.value;
    }

    setTimeout(() => {
        modal.style.display = 'block';
        modal.style.opacity = '1';
        modal.classList.add('open');
        modal.style.top = '10%';
        modal.style.transform = 'scaleX(1) scaleY(1)';

        overlay.style.display = 'block';
        ocultarPantallaDeCarga();
    }, 1000);
}

// Función para abrir el modal de historial de ventas
function openVentasModal(movhabitacionId) {
    // Lógica para cargar el historial de ventas
    loadVentas(movhabitacionId);

    // Abrir el modal de ventas
    const modal = M.Modal.getInstance(document.getElementById('modal-ventas'));
    modal.open();
}

// Función para cargar las ventas
function loadVentas(movhabitacionId) {
    // Cambia esta URL por la URL de tu API con el ID correcto
    const url = `/movhabitacion/historial-ventas/${movhabitacionId}`;

    fetch(url)
        .then(response => response.json())
        .then(data => {
            const ventasList = document.getElementById('ventas-list');
            ventasList.innerHTML = ''; // Limpiar el contenido anterior

            if (data.message) {
                // Si el mensaje está presente en la respuesta, mostrarlo
                ventasList.innerHTML = `<p>${data.message}</p>`;
            } else if (Array.isArray(data) && data.length === 0) {
                // Si no hay ventas, mostrar mensaje
                ventasList.innerHTML = '<p>No se encontraron ventas.</p>';
            } else {
                // Si hay ventas, mostrar cada una
                data.forEach(venta => {
                    const ventaItem = document.createElement('div');
                    ventaItem.classList.add('venta-item');
                    ventaItem.innerHTML = `
                        <p><strong>Venta ID:</strong> ${venta.id}</p>
                        <p><strong>Total:</strong> S/${venta.total}</p>
                        <p><strong>Fecha:</strong> ${venta.fecha}</p>
                        <hr>
                    `;
                    ventasList.appendChild(ventaItem);
                });
            }
        })
        .catch(error => {
            console.error('Error al cargar ventas:', error);
            const ventasList = document.getElementById('ventas-list');
            ventasList.innerHTML = '<p>Error al cargar las ventas.</p>';
        });
}
function cancelSplitBillModal() {
    const modal = document.getElementById('split-bill-modal');
    const overlay = document.getElementById('modal-overlay');

    // Restablecer los valores de los campos si es necesario
    const montoPagarInput = document.getElementById('monto_a_pagar');
    if (montoPagarInput) {
        montoPagarInput.value = '';  // O puedes dejar el valor por defecto si lo prefieres
    }

    // Cerrar el modal
    modal.style.opacity = '0';
    modal.classList.remove('open');
    modal.style.top = '-10%'; // Puedes cambiar esto para ajustar el efecto
    modal.style.transform = 'scaleX(1) scaleY(0)';

    // Ocultar el overlay
    overlay.style.display = 'none';
}

function acceptSplitBillModal() {
    // Obtener el valor ingresado en el modal
    const montoPagar = parseFloat(document.getElementById('monto_a_pagar').value) || 0;

    // Actualizar el campo de "Total a Pagar" con el monto ingresado
    const totalPagarInput = document.getElementById('total');
    const totalPagarAlojamientoInput = document.getElementById('totalapagaralojamiento');

    // Reemplazar el valor en el campo de total a pagar con el nuevo monto
    totalPagarInput.value = montoPagar.toFixed(2);  // Redondeamos a 2 decimales

    // Reemplazar el valor en el campo de total a pagar con el nuevo monto
    if (totalPagarAlojamientoInput) {
        totalPagarAlojamientoInput.value = montoPagar.toFixed(2);
    }


    // Actualizar el valor en la tabla (detalles_movimientos_json)
    const detallesJsonInput = document.getElementById('detalles_movimientos_json');
    const detallesMovimientos = JSON.parse(detallesJsonInput.value);

    // Crear un nuevo movimiento con el monto y medio de pago
    const nuevoMovimiento = {
        tipo: 'Pagado',
        monto: montoPagar,
        medio_pago: 'Efectivo',  // Cambiar según el medio de pago
        mediopago_id: 1,  // ID del medio de pago, cambiar si es necesario
        notas: 'Pago pendiente'
    };

    // Reemplazar el array de movimientos con el nuevo movimiento
    detallesMovimientos.length = 0;  // Limpiar el array existente
    detallesMovimientos.push(nuevoMovimiento);  // Agregar el nuevo movimiento
    detallesJsonInput.value = JSON.stringify(detallesMovimientos);  // Actualizar el JSON

    // Actualizar la tabla con el nuevo movimiento (reemplazando la fila existente)
    const tablaMediosPago = document.querySelector('#tabla-medios-pago tbody');
    const filas = tablaMediosPago.querySelectorAll('tr');

    // Verificar si hay filas en la tabla
    if (filas.length > 0) {
        filas.forEach(fila => {
            const tipoElement = fila.querySelector('.dtr-control');
            const montoElement = fila.querySelector('.monto');
            const medioPagoElement = fila.querySelector('.medio-pago');
            const detallesElement = fila.querySelector('.detalles');
            const notasElement = fila.querySelector('.notas');

            // Actualizar el contenido de la fila con el nuevo valor
            if (tipoElement) {
                tipoElement.innerHTML = `<span class="new badge green white-text" data-badge-caption="Pagado"></span>`;
            }

            if (montoElement) {
                montoElement.textContent = montoPagar.toFixed(2);  // Actualizamos el monto
            }

            if (medioPagoElement) {
                medioPagoElement.textContent = 'Efectivo';  // Actualizamos el medio de pago
            }

            if (detallesElement) {
                detallesElement.textContent = '';  // Puedes agregar detalles si es necesario
            }

            if (notasElement) {
                notasElement.textContent = 'Pago pendiente';  // Actualizamos las notas
            }
        });
    }

    // Cerrar el modal
    cancelSplitBillModal();  // Llamamos a la función para cerrar el modal
    // Buscar el botón con la clase 'cobrar_checkout'
    const button = document.querySelector('.cobrar_checkout');

    // Verificar si el botón existe antes de intentar deshabilitarlo
    if (button) {
        // Deshabilitar el botón
        button.disabled = true;
    }
}


function acceptSplitBillArrModal() {
    // Obtener el valor ingresado en el modal
    const montoPagar = parseFloat(document.getElementById('monto_a_pagar').value) || 0;

    // Actualizar el campo de "Total a Pagar" con el monto ingresado
    const totalPagarInput = document.getElementById('totalapagar');
    totalPagarInput.value = montoPagar.toFixed(2);

    /** -------------------------------
     *  ACTUALIZAR detalles_movimientos_json
     * ------------------------------- */
    const detallesJsonInputMov = document.getElementById('detalles_movimientos_json');
    const detallesMovimientos = JSON.parse(detallesJsonInputMov.value);

    const nuevoMovimiento = {
        tipo: 'Pagado',
        monto: montoPagar,
        medio_pago: 'Efectivo',
        mediopago_id: 1,
        notas: 'Pago pendiente'
    };

    detallesMovimientos.length = 0;
    detallesMovimientos.push(nuevoMovimiento);
    detallesJsonInputMov.value = JSON.stringify(detallesMovimientos);

    // Actualizar la tabla de medios de pago
    const tablaMediosPago = document.querySelector('#tabla-medios-pago tbody');
    const filas = tablaMediosPago.querySelectorAll('tr');
    if (filas.length > 0) {
        filas.forEach(fila => {
            fila.querySelector('.monto').textContent = montoPagar.toFixed(2);
            fila.querySelector('.medio-pago').textContent = 'Efectivo';
            fila.querySelector('.notas').textContent = 'Pago pendiente';
            fila.querySelector('.dtr-control').innerHTML = `<span class="new badge green white-text" data-badge-caption="Pagado"></span>`;
        });
    }

    /** -------------------------------
     *  ACTUALIZAR detalles_json (productos/servicios)
     * ------------------------------- */
    const detallesJsonInput = document.getElementById('detalles_json');
    let detallesVenta = JSON.parse(detallesJsonInput.value);

    if (detallesVenta.length > 0) {
        const montoPorItem = montoPagar / detallesVenta.length;

        detallesVenta = detallesVenta.map(detalle => {
            detalle.precio_unitario = montoPorItem.toFixed(2);
            detalle.total = montoPorItem.toFixed(2);
            detalle.monto = montoPorItem.toFixed(2);

            // Si el producto tiene json_producto, lo actualizamos también
            if (detalle.json_producto) {
                try {
                    const jsonProd = typeof detalle.json_producto === "string"
                        ? JSON.parse(detalle.json_producto)
                        : detalle.json_producto;

                    jsonProd.precio_venta = montoPorItem.toFixed(2);
                    detalle.json_producto = JSON.stringify(jsonProd);
                } catch (e) {
                    console.warn("Error parseando json_producto", e);
                }
            }

            return detalle;
        });

        detallesJsonInput.value = JSON.stringify(detallesVenta);

        // Actualizar tabla en el DOM
        const filasTabla = document.querySelectorAll('#data-table tbody tr');
        filasTabla.forEach((fila, index) => {
            if (detallesVenta[index]) {
                const detalle = detallesVenta[index];
                const precioUnitEl = fila.querySelector('.precio-unitario');
                const precioTotalEl = fila.querySelector('.precio-total');

                if (precioUnitEl) precioUnitEl.textContent = detalle.precio_unitario;
                if (precioTotalEl) precioTotalEl.textContent = detalle.total;
            }
        });
    }

    // Cerrar el modal
    cancelSplitBillModal();

    // Deshabilitar botón de cobro
    const button = document.querySelector('.cobrar_checkout');
    if (button) button.disabled = true;
}


function cancelMaintenanceModal() {
    const modal = document.getElementById('maintenance-modal');
    const overlay = document.getElementById('modal-overlay');

    modal.style.display = 'none';
    modal.style.opacity = '0';
    modal.classList.remove('open');
    modal.style.top = '';
    modal.style.transform = '';

    overlay.style.display = 'none';
}


function enablesendEmailCodeForProcess() {
    document.getElementById('btn-codigo-aprobacion-movimiento-email').disabled = false;
    document.getElementById('btn-codigo-aprobacion-movimiento-whatsapp').disabled = false;
    document.getElementById('codigo_aprobacion_movimiento_spn').innerHTML = "Ingrese el código o envíe uno temporal";
}

function disablesendEmailCodeForProcess(transcurrido) {
    document.getElementById('btn-codigo-aprobacion-movimiento-email').disabled = true;
    document.getElementById('btn-codigo-aprobacion-movimiento-whatsapp').disabled = true;
    document.getElementById('codigo_aprobacion_movimiento_spn').innerHTML = `Para generar un código nuevo espere a que el actual expire (<font class="cod-anulacion">` + transcurrido + `</font>)`;
}

function sendEmailCodeForProcess() {
    const comentario = document.getElementById('motivo').value;
    const dctoPorcentaje = Number(document.getElementById('dcto_total_porcentaje').value);
    const dctoMonto = Number(document.getElementById('dcto_total_monto').value) || 0;
    const habitacionNumero = document.getElementById('habitacion_numero')?.value || '';
    const correoAdminRaw = document.getElementById('correoadmin')?.value;
    let correoAdmin = '';
    if (correoAdminRaw) {
        const sanitized = correoAdminRaw.replace(/\\u0022/g, '"').replace(/&quot;/g, '"').trim();
        try {
            const parsed = JSON.parse(sanitized);
            if (Array.isArray(parsed) && parsed.length) {
                correoAdmin = parsed[0];
            } else if (typeof parsed === 'string') {
                correoAdmin = parsed;
            }
        } catch (err) {
            const fallback = sanitized.replace(/^[\\s"']+|[\\s"']+$/g, '');
            correoAdmin = fallback.split(',')[0].trim();
        }
    }

    $.ajax({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        url: "/generate-approval-code",
        type: 'POST',
        dataType: 'JSON',
        data: {
            send_type: 'email',
            dcto_porcentaje: dctoPorcentaje,
            dcto_total_monto: dctoMonto,
            comentario,
            habitacion_numero: habitacionNumero,
            correo: correoAdmin,
            _token: $('meta[name="csrf-token"]').attr("content"),
        },
    }).done(function (respuesta) {
        if (respuesta.success) {
            codigoGenerado = respuesta.codigo || codigoGenerado;
            $('#codigo_aprobacion_movimiento_spn')
                .text('Código enviado por email')
                .removeClass('red-text');
            startCountdown();
        } else if (respuesta.status == 200 && respuesta.codigo) {
            codigoGenerado = respuesta.codigo;
            $('#codigo_aprobacion_movimiento_spn')
                .text('Código enviado por email')
                .removeClass('red-text');
            startCountdown();
        } else {
            Toast.fire({
                icon: respuesta.icon || 'error',
                title: respuesta.message || 'No se pudo enviar el código por email',
            });
        }
    })
        .fail(function (xhr, textStatus, error) {
            console.error('Error al enviar el código por email:', xhr, textStatus, error);
        }).always(function (xhr, textStatus, error) {

        });
}


function sendWhatsAppCodeForProcess() {
    var telefonosJson = $("#telefonoadmin").val(); // string JSON
    var telefonos = [];
    try {
        telefonos = JSON.parse(telefonosJson);
    } catch (e) {
        console.error("Error parsing telefonoadmin JSON:", e);
    }

    var dctoPorcentaje = Number($("#dcto_total_porcentaje").val()) || 0;
    var comentario = $("#motivo").val() || '';
    var sendType = 'whatsapp';
    var dctoMonto = Number($("#dcto_total_monto").val()) || 0;
    var habitacionNumero = $("#habitacion_numero").val() || '';

    $.ajax({
        url: '/generate-approval-code',
        type: 'POST',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            telefonos: telefonos,
            send_type: sendType,
            dcto_porcentaje: dctoPorcentaje,
            dcto_total_monto: dctoMonto,
            habitacion_numero: habitacionNumero,
            comentario: comentario,
        },
        success: function (response) {
            if (response.success) {
                codigoGenerado = response.codigo;

                $('#codigo_aprobacion_anulacion').val('');
                $('#codigo_aprobacion_movimiento_spn').text('Ingrese el código o envíe uno temporal').removeClass('red-text');
                startCountdown();
            } else {
                alert(response.message);
            }
        },
        error: function (error) {
            console.error('Error al enviar el código:', error);
            alert('Error al enviar el código');
        }
    });
}

function getMovventaIdForAnulacion() {
    if (typeof formToAnular === 'undefined' || !formToAnular) {
        return '';
    }
    const action = formToAnular.attr('action') || '';
    const match = action.match(/(\d+)(?:\D*)$/);
    return match ? match[1] : '';
}

function sendWhatsAppCodeForAnulacionVenta() {
    var telefonosJson = $("#telefonoadmin").val();
    var telefonos = [];
    try {
        telefonos = JSON.parse(telefonosJson);
    } catch (e) {
        console.error("Error parsing telefonoadmin JSON:", e);
    }

    var comentario = $("#motivo_anulacion").val() || '';
    var movventaId = getMovventaIdForAnulacion();

    $.ajax({
        url: '/generate-approval-code',
        type: 'POST',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            telefonos: telefonos,
            send_type: 'whatsapp',
            operation_type: 'anulacion_venta',
            movventa_id: movventaId,
            comentario: comentario,
        },
        success: function (response) {
            if (response.success) {
                codigoGenerado = response.codigo;
                $('#codigo_aprobacion_anulacion').val('');
                $('#codigo_aprobacion_movimiento_spn').text('Ingrese el código o envíe uno temporal').removeClass('red-text');
                startCountdown();
            } else {
                alert(response.message);
            }
        },
        error: function (error) {
            console.error('Error al enviar el código:', error);
            alert('Error al enviar el código');
        }
    });
}

function sendEmailCodeForAnulacionVenta() {
    const comentario = document.getElementById('motivo_anulacion')?.value || '';
    const movventaId = getMovventaIdForAnulacion();
    const correoAdminRaw = document.getElementById('correoadmin')?.value;
    let correoAdmin = '';

    if (correoAdminRaw) {
        const sanitized = correoAdminRaw.replace(/\\u0022/g, '"').replace(/&quot;/g, '"').trim();
        try {
            const parsed = JSON.parse(sanitized);
            if (Array.isArray(parsed) && parsed.length) {
                correoAdmin = parsed[0];
            } else if (typeof parsed === 'string') {
                correoAdmin = parsed;
            }
        } catch (err) {
            const fallback = sanitized.replace(/^[\\s"']+|[\\s"']+$/g, '');
            correoAdmin = fallback.split(',')[0].trim();
        }
    }

    $.ajax({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        url: "/generate-approval-code",
        type: 'POST',
        dataType: 'JSON',
        data: {
            send_type: 'email',
            operation_type: 'anulacion_venta',
            movventa_id: movventaId,
            comentario: comentario,
            correo: correoAdmin,
            _token: $('meta[name="csrf-token"]').attr("content"),
        },
    }).done(function (respuesta) {
        if (respuesta.success || (respuesta.status == 200 && respuesta.codigo)) {
            codigoGenerado = respuesta.codigo || codigoGenerado;
            $('#codigo_aprobacion_movimiento_spn')
                .text('Código enviado por email')
                .removeClass('red-text');
            startCountdown();
        } else {
            Toast.fire({
                icon: respuesta.icon || 'error',
                title: respuesta.message || 'No se pudo enviar el código por email',
            });
        }
    })
        .fail(function (xhr, textStatus, error) {
            console.error('Error al enviar el código por email:', xhr, textStatus, error);
        });
}

function getMovhabitacionIdForAnulacion() {
    if (typeof formToAnular === 'undefined' || !formToAnular) {
        return '';
    }
    const action = formToAnular.attr('action') || '';
    const match = action.match(/(\d+)(?:\D*)$/);
    return match ? match[1] : '';
}

function sendWhatsAppCodeForAnulacionAlojamiento() {
    var telefonosJson = $("#telefonoadmin").val(); // string JSON
    var telefonos = [];
    try {
        telefonos = JSON.parse(telefonosJson);
    } catch (e) {
        console.error("Error parsing telefonoadmin JSON:", e);
    }

    var comentario = $("#motivo_anulacion").val() || '';
    var sendType = 'whatsapp';
    var movhabitacionId = getMovhabitacionIdForAnulacion();

    $.ajax({
        url: '/movhabitacion/generate-approval-code',
        type: 'POST',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            telefonos: telefonos,
            send_type: sendType,
            comentario: comentario,
            movhabitacion_id: movhabitacionId,
        },
        success: function (response) {
            if (response.success) {
                codigoGenerado = response.codigo;

                $('#codigo_aprobacion_movimiento').val('');
                $('#codigo_aprobacion_movimiento_spn').text('Ingrese el código o envíe uno temporal').removeClass('red-text');
                startCountdown();
            } else {
                alert(response.message);
            }
        },
        error: function (error) {
            console.error('Error al enviar el código:', error);
            alert('Error al enviar el código');
        }
    });
}

function sendEmailCodeForAnulacionAlojamiento() {
    const comentario = document.getElementById('motivo_anulacion')?.value || '';
    const movhabitacionId = getMovhabitacionIdForAnulacion();
    const correoAdminRaw = document.getElementById('correoadmin')?.value;
    let correoAdmin = '';
    if (correoAdminRaw) {
        const sanitized = correoAdminRaw.replace(/\\u0022/g, '"').replace(/&quot;/g, '"').trim();
        try {
            const parsed = JSON.parse(sanitized);
            if (Array.isArray(parsed) && parsed.length) {
                correoAdmin = parsed[0];
            } else if (typeof parsed === 'string') {
                correoAdmin = parsed;
            }
        } catch (err) {
            const fallback = sanitized.replace(/^[\\s"']+|[\\s"']+$/g, '');
            correoAdmin = fallback.split(',')[0].trim();
        }
    }

    $.ajax({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        url: "/movhabitacion/generate-approval-code",
        type: 'POST',
        dataType: 'JSON',
        data: {
            send_type: 'email',
            comentario,
            movhabitacion_id: movhabitacionId,
            correo: correoAdmin,
            _token: $('meta[name="csrf-token"]').attr("content"),
        },
    }).done(function (respuesta) {
        if (respuesta.success) {
            codigoGenerado = respuesta.codigo || codigoGenerado;
            $('#codigo_aprobacion_movimiento_spn')
                .text('Código enviado por email')
                .removeClass('red-text');
            startCountdown();
        } else if (respuesta.status == 200 && respuesta.codigo) {
            codigoGenerado = respuesta.codigo;
            $('#codigo_aprobacion_movimiento_spn')
                .text('Código enviado por email')
                .removeClass('red-text');
            startCountdown();
        } else {
            Toast.fire({
                icon: respuesta.icon || 'error',
                title: respuesta.message || 'No se pudo enviar el código por email',
            });
        }
    })
        .fail(function (xhr, textStatus, error) {
            console.error('Error al enviar el código por email:', xhr, textStatus, error);
        }).always(function (xhr, textStatus, error) {

        });
}


// Función para rellenar con ceros (ejemplo para formato de tiempo)
function pad(num) {
    return num < 10 ? '0' + num : num;
}

// Función para iniciar el contador regresivo y manejar habilitación/deshabilitación de botones
function startCountdown() {
    var countdownTime = 300; // 5 minutos en segundos
    var countdownElement = $('#codigo_aprobacion_movimiento_spn'); // Span donde se muestra el mensaje

    // Botones a deshabilitar y habilitar
    var btnWhatsApp = $('#btn-codigo-aprobacion-movimiento-whatsapp');
    var btnEmail = $('#btn-codigo-aprobacion-movimiento-email');

    // Deshabilitar botones al iniciar el countdown
    btnWhatsApp.prop('disabled', true);
    btnEmail.prop('disabled', true);

    var interval = setInterval(function () {
        var minutes = Math.floor(countdownTime / 60);
        var seconds = countdownTime % 60;

        countdownElement.text('Para generar un código nuevo espere a que el actual expire (' + pad(minutes) + ':' + pad(seconds) + ')');

        if (countdownTime <= 0) {
            clearInterval(interval);
            countdownElement.text('Código expirado.');
            // Volver a habilitar botones cuando termine el timer
            btnWhatsApp.prop('disabled', false);
            btnEmail.prop('disabled', false);
        }

        countdownTime--;
    }, 1000);
}



function actualizarDescuento(tipo) {
    // Obtener el total actual como número decimal
    const totalActual = parseFloat(document.getElementById('total_actual').value) || 0;

    // Obtener los valores actuales de porcentaje y monto (como float)
    const porcentajeInput = document.getElementById('dcto_total_porcentaje');
    const montoInput = document.getElementById('dcto_total_monto');

    let porcentaje = parseFloat(porcentajeInput.value.replace(',', '.')) || 0;
    let monto = parseFloat(montoInput.value.replace(',', '.')) || 0;

    if (tipo === 'PORCENTAJE') {
        // Limitar porcentaje entre 0 y 100
        if (porcentaje > 100) porcentaje = 100;
        if (porcentaje < 0) porcentaje = 0;

        // Calcular el monto que representa ese porcentaje sobre el total
        monto = (totalActual * porcentaje) / 100;

        // Actualizar el campo monto con 2 decimales
        montoInput.value = monto.toFixed(2);
    } else if (tipo === 'MONTO') {
        // Limitar monto entre 0 y totalActual
        if (monto > totalActual) monto = totalActual;
        if (monto < 0) monto = 0;

        // Calcular el porcentaje que representa ese monto respecto al total
        porcentaje = (monto / totalActual) * 100;

        // Actualizar el campo porcentaje con 2 decimales
        porcentajeInput.value = porcentaje.toFixed(3);
    }
}
function aceptarMaintenanceModal() {


    let codigoIngresado = $('#codigo_aprobacion_movimiento').val().trim();


    // Verificar si codigoGenerado existe y no es null ni undefined
    let codigoGeneradoExiste = typeof codigoGenerado !== 'undefined' && codigoGenerado !== null;


    // Obtener código de contraseña descuento oculto
    let codigoDescuento = $('#contraseñadescuento').val();

    let codigoValido = false;

    if (!codigoGeneradoExiste) {

        if (codigoIngresado === codigoDescuento) {
            codigoValido = true;
        }
    } else {
        // Validar contra el código generado
        if (codigoIngresado === codigoGenerado.toString()) {
            codigoValido = true;
        }
    }

    if (!codigoValido) {
        $('#codigo_aprobacion_movimiento_spn')
            .text('Código inválido, inténtelo nuevamente.')
            .addClass('red-text');
        return;
    }


    // Código válido: Limpiar mensaje de error
    $('#codigo_aprobacion_movimiento_spn').text('').removeClass('red-text');

    let dctoMonto = parseFloat($('#dcto_total_monto').val()) || 0;


    $('#descuento').val(dctoMonto.toFixed(2));

    if (typeof calcularEarlylate === 'function') {

        calcularEarlylate();
    }


    cancelMaintenanceModal();

    // Opcional: Resetear código para evitar reuso
    if (codigoGeneradoExiste) {
        codigoGenerado = null;
    }
}


function openAnularVentaModal() {
    const modal = document.getElementById('anular-venta-modal');
    const overlay = document.getElementById('modal-overlay-anular');

    if (!modal) return;

    // Limpiar campos y advertencias previas
    $('#motivo_anulacion').val('');
    $('#codigo_aprobacion_anulacion').val('');
    $('#motivo_anulacion_spn').text('Ingrese el motivo de anulación').removeClass('red-text');
    $('#codigo_aprobacion_movimiento_spn').text('Ingrese el código o envíe uno temporal').removeClass('red-text');

    // Remover overlays residuales de Materialize que bloquean la pantalla
    $('.modal-overlay').remove();

    modal.style.display = 'block';
    modal.style.opacity = '1';
    modal.style.zIndex = '100001';
    modal.classList.add('open');
    modal.style.top = '10%';
    modal.style.transform = 'scaleX(1) scaleY(1)';

    if (overlay) {
        overlay.style.display = 'block';
        overlay.style.zIndex = '100000';
    }
}

function cancelAnularVentaModal() {
    const modal = document.getElementById('anular-venta-modal');
    const overlay = document.getElementById('modal-overlay-anular');

    if (modal) {
        modal.style.display = 'none';
        modal.style.opacity = '0';
        modal.classList.remove('open');
        modal.style.top = '';
        modal.style.transform = '';
    }

    if (overlay) {
        overlay.style.display = 'none';
    }

    $('.modal-overlay').remove();
}

function aceptarAnularVentaModal() {
    let codigoIngresado = $('#codigo_aprobacion_anulacion').val().trim();

    // Verificar si codigoGenerado existe y no es null ni undefined
    let codigoGeneradoExiste = typeof codigoGenerado !== 'undefined' && codigoGenerado !== null;

    // Obtener código oculto de anulación
    let codigoOculto = $('#contraseñaanulacion').val();



    let codigoValido = false;

    if (!codigoGeneradoExiste) {

        if (codigoIngresado === codigoOculto) {
            codigoValido = true;
        }
    } else {
        if (codigoIngresado === codigoGenerado.toString()) {
            codigoValido = true;
        } else if (codigoIngresado === codigoOculto) {
            codigoValido = true;
        }
    }
    if (!codigoValido) {
        $('#codigo_aprobacion_movimiento_spn')
            .text('Código inválido, inténtelo nuevamente.')
            .addClass('red-text');
        return; // No hace nada más
    }

    // Código válido: limpiar mensaje de error
    $('#codigo_aprobacion_movimiento_spn').text('').removeClass('red-text');

    // Ejecutar acción AJAX para anular la venta
    if (typeof formToAnular !== 'undefined' && formToAnular) {
        let url = formToAnular.attr('action');
        let method = formToAnular.find('input[name="_method"]').val() || 'POST';


        $.ajax({
            url: url,
            type: method,
            data: formToAnular.serialize() + '&motivo=' + encodeURIComponent($('#motivo_anulacion').val()),

            success: function (response) {


                if (response.success) {

                    // Actualizar tabla
                    if (response.redirect_url) {
                        cargarContenidoDinamicamente(response.redirect_url);
                    } else {
                        // Fallback opcional
                        const currentUrl = window.location.href;
                        cargarContenidoDinamicamente(currentUrl);
                    }

                    // Cerrar modal
                    cancelAnularVentaModal()
                    // Resetear código generado para evitar reuso
                    if (codigoGeneradoExiste) {
                        codigoGenerado = null;

                    }
                    mostrarMensajeExito(response.message);
                } else {
                    mostrarMensajeError('Error al anular: ' + response.message);
                }
            },
            error: function (xhr, status, error) {
                console.error('Error AJAX:', status, error);
                M.toast({
                    html: '<i class="material-icons left">error</i> Error al anular',
                    classes: 'red darken-1 rounded',
                    displayLength: 4000
                });
            }
        });
    } else {

        M.toast({
            html: 'No se encontró el formulario para anular',
            classes: 'red darken-1'
        });
    }
}

function cloneText() {
    const descripcion = document.getElementById('descripcion').value;
    document.getElementById('abreviatura').value = descripcion;

    // Opcional: activar la etiqueta flotante de abreviatura si usas MaterializeCSS
    const labelAbreviatura = document.querySelector('label[for="abreviatura"]');
    if (descripcion.trim() !== '') {
        labelAbreviatura.classList.add('active');
    } else {
        labelAbreviatura.classList.remove('active');
    }
}
let amenityIndex = 100000; // empezar en un número alto para no chocar con IDs existentes

function agregarAmenidad() {
    const nombre = document.getElementById('amenity_nombre').value.trim();
    const icono = document.getElementById('amenity_icon').value.trim();
    const tabla = document.getElementById('amenities-table').getElementsByTagName('tbody')[0];

    if (!nombre || !icono) {
        alert('Por favor, ingrese tanto el nombre como el icono de la amenidad.');
        return;
    }

    // Crear una nueva fila con un índice único
    const index = amenityIndex++;
    const row = tabla.insertRow();
    row.setAttribute('data-id', index);

    const cell1 = row.insertCell(0);
    const cell2 = row.insertCell(1);
    const cell3 = row.insertCell(2);

    cell1.innerHTML = `
        <input type="text" name="amenities[${index}][nombre]" value="${nombre}" class="validate" placeholder="Nombre de la amenidad">
    `;
    cell2.innerHTML = `
        <input type="text" name="amenities[${index}][icon]" value="${icono}" class="validate" placeholder="Icono">
    `;
    cell3.innerHTML = `
        <button type="button" class="btn red" onclick="removeAmenity(this)">Eliminar</button>
    `;

    // Limpiar los campos de entrada
    document.getElementById('amenity_nombre').value = '';
    document.getElementById('amenity_icon').value = '';
}

// Función para eliminar una amenidad
function removeAmenity(button) {
    button.closest('tr').remove();
}

function toggleMoney(tipo) {
    const tableId = tipo === 'moneda' ? '#dinero-monedaTable' : '#dinero-billeteTable';
    const buttonId = tipo === 'moneda' ? '#btn-monedas' : '#btn-billetes';

    const table = document.querySelector(tableId);
    const button = document.querySelector(buttonId);

    if (table.style.display === 'none' || table.classList.contains('d-none')) {
        table.style.display = 'table'; // o usa classList.remove('d-none');
        button.classList.remove('grey');
        button.classList.add('bg-primary');
    } else {
        table.style.display = 'none'; // o usa classList.add('d-none');
        button.classList.remove('bg-primary');
        button.classList.add('darken-2');
    }
}

function validateDecimalNumbers(input) {
    input.value = input.value.replace(/[^0-9]/g, '');
}

function validateEmptyNumber(input) {
    if (input.value === '' || isNaN(input.value)) {
        input.value = 0;
    }
}

function calcularDinero() {
    let total = 0;
    const inputs = document.querySelectorAll('.dinero');

    inputs.forEach(input => {
        const cantidad = parseFloat(input.value) || 0;
        const valor = parseFloat(input.getAttribute('money-value')) || 0;
        total += cantidad * valor;
    });

    document.getElementById('dinero').value = total.toFixed(2);
}
// Función para mostrar la imagen seleccionada en una vista previa
function imageChanged(input, imageId) {
    const file = input.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = function (e) {
        const container = document.querySelector(`#file-input-${imageId}`).closest('.imagen-container');
        const imageElement = container.querySelector('.card-image img');
        const placeholder = container.querySelector('.card-image span');

        imageElement.src = e.target.result;
        imageElement.style.display = 'block';
        if (placeholder) placeholder.style.display = 'none';
    };
    reader.readAsDataURL(file);
}

let newImageIndex = 0;

function agregarNuevaImagen() {
    const container = document.getElementById('imagenes-container'); // CAMBIADO

    const newImageHTML = `
        <div class="col s12 m6 l3 imagen-container">
            <div class="card hoverable">
                <div class="card-image">
                    <img id="preview-img-${newImageIndex}" src="#" alt="Imagen nueva" class="responsive-img" style="display: none;">
                    <span id="preview-placeholder-${newImageIndex}" style="color: #999;">Sin vista previa</span>
                </div>
                <div class="card-content">
                    <div class="input-field">
                        <input type="text" name="nuevas_imagenes[${newImageIndex}][descripcion]" placeholder="Descripción" class="validate">
                    </div>

                    <div class="center-align" style="margin-bottom: 10px;">
                        <button type="button" class="btn-small teal darken-1 waves-effect"
                            onclick="document.getElementById('new-file-input-${newImageIndex}').click();">
                            <i class="material-icons left">upload</i> Seleccionar Imagen
                        </button>
                        <input type="file" id="new-file-input-${newImageIndex}"
                            name="nuevas_imagenes[${newImageIndex}][imagen]" accept="image/*"
                            style="display: none;" onchange="previewNuevaImagen(this, ${newImageIndex})">
                        
                    </div>
                    <p class="center-align">
                            <label>
                                <input type="checkbox" class="filled-in red-text" name="eliminar_imagenes[]"
                                    value="{{ $imagen->id }}">
                                <span>Eliminar esta imagen</span>
                            </label>
                        </p>
                </div>
            </div>
        </div>
    `;

    container.insertAdjacentHTML('beforeend', newImageHTML);
    newImageIndex++;
}


function previewNuevaImagen(input, index) {
    const file = input.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function (e) {
            const img = document.getElementById(`preview-img-${index}`);
            const placeholder = document.getElementById(`preview-placeholder-${index}`);
            img.src = e.target.result;
            img.style.display = "block";
            placeholder.style.display = "none";
        };
        reader.readAsDataURL(file);
    }
}

function previewImagen(input) {
    const file = input.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function (e) {
            document.getElementById('preview-imagen').src = e.target.result;
            // Opcional: guardar temporalmente la imagen en base64 (si deseas enviar esto en lugar de la ruta)
        };
        reader.readAsDataURL(file);
    }
}

function generarCompromisos() {
    const fechaFinInput = document.getElementById('fecha_fin');
    const montoInput = document.getElementById('monto');
    const garantiaInput = document.getElementById('garantia');
    const pagoAnticipadoInput = document.getElementById('pagoanticipado');

    if (!fechaFinInput || !montoInput || !garantiaInput || !pagoAnticipadoInput) {
        mostrarMensajeError('Por favor, completa todos los campos requeridos.');
        return;
    }

    const fechaFinOriginal = new Date(fechaFinInput.value);
    const monto = parseFloat(montoInput.value);
    const garantia = parseFloat(garantiaInput.value);
    const pagoAnticipado = pagoAnticipadoInput.value.trim().toLowerCase() === 'si';

    if (isNaN(monto) || isNaN(garantia) || !fechaFinOriginal) {
        mostrarMensajeError('Por favor, ingresa valores válidos para monto, garantía y fecha de fin.');
        return;
    }

    // Fecha actual en horario peruano (UTC-5)
    const ahora = new Date();
    const utc = ahora.getTime() + ahora.getTimezoneOffset() * 60000;
    const hoyPeru = new Date(utc - 5 * 60 * 60000);
    const diaFijo = hoyPeru.getDate();

    let cuotas = [];
    let cuotaNro = 1;

    // Si el pago NO es anticipado, se debe correr la fechaFin 1 mes más
    const fechaFin = new Date(fechaFinOriginal);
    if (!pagoAnticipado) {
        fechaFin.setMonth(fechaFin.getMonth() + 1);
    }

    let currentDate;
    if (pagoAnticipado) {
        // Primer pago en el mes actual con garantía
        const año = hoyPeru.getFullYear();
        const mes = hoyPeru.getMonth();
        const diasMes = new Date(año, mes + 1, 0).getDate();
        const diaValido = Math.min(diaFijo, diasMes);
        const fechaCuota = new Date(año, mes, diaValido);

        cuotas.push({
            numero_cuota: cuotaNro++,
            fecha_vencimiento: fechaCuota.toISOString().split('T')[0],
            monto: monto + garantia
        });

        // Comenzar a contar desde el siguiente mes
        currentDate = new Date(año, mes + 1, 1);
    } else {
        // Primer pago en el próximo mes con garantía
        const año = hoyPeru.getFullYear();
        const mes = hoyPeru.getMonth() + 1;
        const diasMes = new Date(año, mes + 1, 0).getDate();
        const diaValido = Math.min(diaFijo, diasMes);
        const fechaCuota = new Date(año, mes, diaValido);

        cuotas.push({
            numero_cuota: cuotaNro++,
            fecha_vencimiento: fechaCuota.toISOString().split('T')[0],
            monto: monto + garantia
        });

        // Comenzar a contar desde el mes siguiente al primer pago
        currentDate = new Date(año, mes + 1, 1);
    }

    // Cuotas normales hasta fechaFin (ya ajustada si era no anticipado)
    while (currentDate <= fechaFin) {
        const año = currentDate.getFullYear();
        const mes = currentDate.getMonth();
        const diasMes = new Date(año, mes + 1, 0).getDate();
        const diaValido = Math.min(diaFijo, diasMes);
        const fechaVencimiento = new Date(año, mes, diaValido);

        if (fechaVencimiento > fechaFin) break;

        cuotas.push({
            numero_cuota: cuotaNro++,
            fecha_vencimiento: fechaVencimiento.toISOString().split('T')[0],
            monto: monto
        });

        currentDate.setMonth(currentDate.getMonth() + 1);
    }

    document.getElementById('compromisos_pago').value = JSON.stringify(cuotas);
    renderizarTablaCompromisos(cuotas);
}


function renderizarTablaCompromisos(cuotas) {
    const contenedor = document.getElementById('tabla-compromisos-container');

    if (!contenedor) return;

    // Estructura completa de tabla
    contenedor.innerHTML = `
        <div id="tb-container" class="table-responsive">
            <table id="data-table" class="highlight striped centered nowrap" style="width:100%; margin-top:20px">
                <thead class="thead-custom">
                    <tr>
                        <th>N° Cuota</th>
                        <th>Fecha Vencimiento</th>
                        <th>Monto</th>
                    </tr>
                </thead>
                <tbody id="tabla-compromisos-body">
                    ${cuotas.map(c => `
                        <tr>
                            <td>${c.numero_cuota}</td>
                            <td>${c.fecha_vencimiento}</td>
                            <td>${c.monto.toFixed(2)}</td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        </div>
    `;

    contenedor.style.display = 'block';
}


function verificarCamposCompromisos() {
    const fechaInicio = document.getElementById('fecha_inicio')?.value;
    const fechaFin = document.getElementById('fecha_fin')?.value;
    const monto = document.getElementById('monto')?.value;
    const garantia = document.getElementById('garantia')?.value;
    const diaPago = document.getElementById('dia_pago')?.value;

    const boton = document.getElementById('btn-generar-compromisos');

    if (fechaInicio && fechaFin && monto && diaPago && garantia) {
        boton.disabled = false;
    } else {
        boton.disabled = true;
    }
}

function filtrarAmbientesPorEdificio() {
    const edificioId = document.getElementById('edificio_id').value;
    const ambienteSelect = document.getElementById('ambiente_id');

    // Limpia y bloquea mientras se carga
    ambienteSelect.innerHTML = '<option value="" disabled selected>Cargando...</option>';
    ambienteSelect.disabled = true;

    fetch(`/contratos/ambientes-por-edificio/${edificioId}`)
        .then(response => response.json())
        .then(data => {
            let opciones = '<option value="" disabled selected>Selecciona un ambiente</option>';
            data.forEach(ambiente => {
                opciones += `<option value="${ambiente.id}">${ambiente.numero} -  ${ambiente.piso_nombre}</option>`;
            });
            ambienteSelect.innerHTML = opciones;
            ambienteSelect.disabled = false;
            M.FormSelect.init(ambienteSelect); // Re-inicializa Materialize select
        })
        .catch(error => {
            console.error('Error al cargar ambientes:', error);
            ambienteSelect.innerHTML = '<option value="" disabled selected>Error al cargar</option>';
            ambienteSelect.disabled = false;
            M.FormSelect.init(ambienteSelect);
        });
}

function obtenerDatosAmbiente() {
    const ambienteId = document.getElementById('ambiente_id').value;

    if (!ambienteId) return;

    fetch(`/ambientes/${ambienteId}/datos`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Asignar los valores al formulario
                document.getElementById('monto').value = data.monto;
                document.getElementById('garantia').value = data.garantia;

                // Activar el label si usas MaterializeCSS
                M.updateTextFields();
                verificarCamposCompromisos();
            } else {
                alert('No se pudo obtener los datos del ambiente.');
            }
        })
        .catch(error => {
            console.error('Error al obtener datos del ambiente:', error);
            alert('Ocurrió un error al consultar el ambiente.');
        });
}

function reinicializarTiny() {
    const textarea = document.getElementById('contenido_html');
    const fileInput = document.getElementById('file-upload');

    if (!textarea) return;

    if (tinymce.get('contenido_html')) {
        tinymce.get('contenido_html').remove();
    }

    tinymce.init({
        selector: 'textarea#contenido_html',
        height: 500,
        menubar: true,
        plugins: [
            'advlist', 'anchor', 'autolink', 'autosave',
            'charmap', 'code', 'codesample', 'directionality', 'emoticons', 'fullscreen',
            'help', 'image', 'importcss', 'insertdatetime', 'link', 'lists',
            'media', 'nonbreaking', 'pagebreak', 'preview', 'quickbars',
            'save', 'searchreplace', 'table', 'template', 'visualblocks', 'visualchars', 'wordcount'
        ],
        toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | ' +
            'forecolor backcolor removeformat | alignleft aligncenter alignright alignjustify | ' +
            'outdent indent | numlist bullist checklist | image media link codesample | ' +
            'table pagebreak anchor charmap emoticons | variablesMenu | fullscreen preview save print | ltr rtl | code help',
        toolbar_mode: 'sliding',
        contextmenu: 'link image table spellchecker',
        branding: false,
        language: 'es',
        relative_urls: false,
        remove_script_host: false,
        convert_urls: true,

        setup: function (editor) {
            // Leer las variables desde el input hidden
            let variablesInput = document.getElementById('variables');
            let variables = [];
            if (variablesInput && variablesInput.value) {
                try {
                    variables = JSON.parse(variablesInput.value);
                } catch (e) {
                    console.error("Error al parsear variables:", e);
                }
            }

            // Crear botón desplegable dinámico
            editor.ui.registry.addMenuButton('variablesMenu', {
                text: 'Variables',
                fetch: function (callback) {
                    const items = variables.map(v => {
                        return {
                            type: 'menuitem',
                            text: v.codigo,
                            onAction: function () {
                                const formatted = `<strong><code>${v.codigo}</code></strong>`;
                                editor.insertContent(formatted);
                            }
                        };
                    });
                    callback(items);
                }
            });

            editor.on('init', function () {
                if (fileInput) {
                    fileInput.addEventListener('change', handleFileUpload);
                }
            });
        }
    });

    // --- resto de tu función de subida de archivos ---
    function handleFileUpload(event) {
        const file = event.target.files[0];
        const reader = new FileReader();
        if (!file) return;

        const extension = file.name.split('.').pop().toLowerCase();

        if (extension === 'docx') {
            reader.onload = function () {
                mammoth.convertToHtml({ arrayBuffer: reader.result })
                    .then(result => tinymce.get('contenido_html').setContent(result.value))
                    .catch(err => alert("Error al procesar .docx"));
            };
            reader.readAsArrayBuffer(file);

        } else if (extension === 'txt') {
            reader.onload = function () {
                tinymce.get('contenido_html').setContent('<pre>' + reader.result + '</pre>');
            };
            reader.readAsText(file);

        } else if (extension === 'pdf') {
            reader.onload = function () {
                const typedarray = new Uint8Array(reader.result);
                pdfjsLib.getDocument({ data: typedarray }).promise.then(pdf => {
                    let pages = [];
                    for (let i = 1; i <= pdf.numPages; i++) {
                        pages.push(pdf.getPage(i).then(page =>
                            page.getTextContent().then(content =>
                                content.items.map(item => item.str).join(' ')
                            )
                        ));
                    }
                    Promise.all(pages).then(texts => {
                        const fullText = texts.join('<br><br>');
                        tinymce.get('contenido_html').setContent('<p>' + fullText + '</p>');
                    });
                }).catch(err => {
                    alert("Error al procesar PDF.");
                    console.error(err);
                });
            };
            reader.readAsArrayBuffer(file);

        } else {
            alert("Formato no soportado. Usa .docx, .pdf o .txt");
        }
    }
}

function mostrarVistaPreviaPDF() {
    const ambienteId = document.getElementById('ambiente_id')?.value;

    if (!ambienteId) {
        mostrarMensajeError('Por favor, selecciona un ambiente antes de generar la vista previa del contrato.');
        return;
    }

    const formData = new FormData();

    // Campos que se van al backend
    formData.append('ambiente_id', ambienteId);
    formData.append('fecha_inicio', document.getElementById('fecha_inicio')?.value || '');
    formData.append('fecha_fin', document.getElementById('fecha_fin')?.value || '');
    formData.append('dia_pago', document.getElementById('dia_pago')?.value || '');
    formData.append('monto', document.getElementById('monto')?.value || '');
    formData.append('garantia', document.getElementById('garantia')?.value || '');
    formData.append('persona_id', document.getElementById('persona_id')?.value || '');
    formData.append('arrendador_id', document.getElementById('arrendador_id')?.value || '');

    // Opcional: puedes añadir más campos como ciudad, dirección, duración, etc.

    fetch(`/plantilla-contrato/vista-previa`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
        .then(response => {
            if (!response.ok) throw new Error('Error al generar PDF');
            return response.blob();
        })
        .then(blob => {
            const url = URL.createObjectURL(blob);
            document.getElementById('iframePDF').src = url;

            const modal = M.Modal.getInstance(document.getElementById('modalVistaPreviaPDF'));
            modal.open();
        })
        .catch(error => {
            console.error('Error al generar vista previa del PDF:', error);
            mostrarMensajeError('Error al generar vista previa del PDF. Por favor, inténtelo de nuevo más tarde.');
        });
}

function cargarConceptosPago(tipoId) {
    mostrarPantallaDeCarga();
    fetch(`/conceptos-pago-por-tipo/${tipoId}`)
        .then(response => response.json())
        .then(data => {
            let selectConceptos = document.getElementById('conceptopago_id'); // id correcto
            selectConceptos.innerHTML = '';

            data.forEach(concepto => {
                let option = document.createElement('option');
                option.value = concepto.id;
                option.textContent = concepto.descripcion; // tu API devuelve 'descripcion'
                selectConceptos.appendChild(option);
            });

            // Reinicializar Materialize
            if (M && M.FormSelect) {
                M.FormSelect.init(selectConceptos);
            }
        })
        .catch(error => console.error('Error:', error))
        .finally(() => {
            ocultarPantallaDeCarga();
        });
}

function cambiarreemplazar() {
    let botonreemplazar = document.getElementById('btn-reemplazar');
    let botonagregar = document.getElementById('boton-agregar');
    let inputmode = document.getElementById('modalidad-det_movcaja');
    if (botonreemplazar && botonagregar && inputmode) {
        botonreemplazar.style.display = 'none';
        botonagregar.style.display = '';
        inputmode.value = 'agregar';
    }
    mostrarMensajeInterrogante('Ahora se agregará a los métodos indicados el nuevo que seleccione');
}

function cambiaragregar() {
    let botonreemplazar = document.getElementById('btn-reemplazar');
    let botonagregar = document.getElementById('boton-agregar');
    let inputmode = document.getElementById('modalidad-det_movcaja');

    if (botonreemplazar && botonagregar && inputmode) {
        botonagregar.style.display = 'none';
        botonreemplazar.style.display = '';
        inputmode.value = 'reemplazar';
    }
    mostrarMensajeInterrogante('Ahora se reemplazarán todos los métodos de pago por el nuevo que seleccione');
}

function eliminarHabitacionSeleccionada() {
    // Limpiar el input de búsqueda y el hidden
    $('#evolucion-habitacion').val('');
    $('#evolucion-habitacion_id').val('');
    // Ocultar el botón de quitar habitación
    $('#evolucion-btn-quitar-habitacion').hide();
    // Opcional: puedes recargar el gráfico o limpiar resultados relacionados aquí
}

function actualizarGraficosDeAlojamientos() {
    const url = construirUrlDashboardAlojamientos();

    cargarContenidoDinamicamente(url)
        .then(() => {
            reinicializarDataTables();
        })
        .catch(error => {
            console.error('Error al actualizar dashboard de alojamientos:', error);
        });
}

window.filtrarCuentas = function (page = 1) {
    const cuentaRoot = document.querySelector('[data-cuentas-url]');
    const baseUrl = cuentaRoot?.dataset.cuentasUrl || window.location.pathname;
    const getValue = (id) => document.getElementById(id)?.value || '';

    const params = new URLSearchParams({
        page,
        caja_id: getValue('cuenta-caja'),
        sucursal_id: getValue('cuenta-sucursal'),
        search: getValue('cuenta-search'),
        estado_cuotas: getValue('cuenta-estado-cuotas'),
        estado: getValue('cuenta-estado'),
        situacion: getValue('cuenta-situacion'),
        persona: getValue('cuenta-persona'),
        conceptopago_id: getValue('cuenta-concepto'),
        fechaInicio: getValue('cuenta-fecha-inicio'),
        fechaFin: getValue('cuenta-fecha-fin'),
        fechaProgramada: getValue('cuenta-fecha-programada'),
        salon: getValue('cuenta-salon'),
        perPage: getValue('records-per-page') || '10',
    });

    cargarContenidoDinamicamente(`${baseUrl}?${params.toString()}`);
};

window.toggleCuentasChecks = function (source) {
    document.querySelectorAll('.cuenta-check').forEach((check) => {
        check.checked = source.checked;
    });
};

window.abrirPagoCuentaSeleccionada = function () {
    const check = document.querySelector('.cuenta-check:checked');
    if (!check) {
        mostrarMensajeError('Seleccione una cuenta para continuar.');
        return;
    }

    const row = check.closest('tr');
    const link = row?.querySelector('.btn-cuenta-pago, .btn-crear');
    if (!link) {
        mostrarMensajeError('No se encontró la acción para la cuenta seleccionada.');
        return;
    }

    link.click();
};

function obtenerValoresSelect(selectId) {
    const select = document.getElementById(selectId);
    if (!select) {
        return [];
    }

    return Array.from(select.selectedOptions || [])
        .map(option => option.value)
        .filter(Boolean);
}

function construirUrlDashboardAlojamientos() {
    const params = new URLSearchParams();
    const fechaInicio = document.getElementById("fecha_inicio")?.value || '';
    const fechaFin = document.getElementById("fecha_fin")?.value || '';

    if (fechaInicio) {
        params.set("fecha_inicio", fechaInicio);
    }

    if (fechaFin) {
        params.set("fecha_fin", fechaFin);
    }

    params.set("limit_top_habitaciones", document.getElementById("records_displayed_top_productos")?.value || '10');
    params.set("orden_top_habitaciones", document.getElementById("asc_desc_top_productos")?.value || 'desc');

    const categoriasSelect = document.getElementById("categorias_top_habitaciones");
    if (categoriasSelect) {
        let categoriasSeleccionadas = obtenerValoresSelect("categorias_top_habitaciones");

        if (!categoriasSeleccionadas.length) {
            categoriasSeleccionadas = Array.from(categoriasSelect.options)
                .map(option => option.value)
                .filter(Boolean);
        }

        categoriasSeleccionadas.forEach(categoriaId => {
            params.append("categorias_top_habitaciones[]", categoriaId);
        });
    }

    params.set("limit_top_categorias", document.getElementById("records_displayed_top_categorias")?.value || '10');
    params.set("orden_top_categorias", document.getElementById("asc_desc_top_categorias")?.value || 'desc');

    return `/dashboard/movhabitacion?${params.toString()}`;
}

function abrirModalAlojamientosHabitacion(habitacionId, habitacionNumero = '') {
    const params = new URLSearchParams();
    const fechaInicio = document.getElementById("fecha_inicio")?.value || '';
    const fechaFin = document.getElementById("fecha_fin")?.value || '';

    if (fechaInicio) {
        params.set("fecha_inicio", fechaInicio);
    }

    if (fechaFin) {
        params.set("fecha_fin", fechaFin);
    }

    const queryString = params.toString();
    const url = `/habitaciones/${habitacionId}/alojamientos${queryString ? `?${queryString}` : ''}`;
    const modalElement = document.getElementById('modal');

    if (!modalElement) {
        return;
    }

    mostrarPantallaDeCarga();

    fetch(url)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            return response.text();
        })
        .then(html => {
            document.getElementById('modal-body-content').innerHTML = html;

            modalElement.style.width = '95%';
            modalElement.style.maxHeight = '88%';

            let modalInstance = M.Modal.getInstance(modalElement);
            if (!modalInstance) {
                modalInstance = M.Modal.init(modalElement);
            }

            modalInstance.open();
            inicializarDataTablesPorSelector('#modal-body-content .data-table, #modal-body-content .data-table-2, #modal-body-content #data-table', false);
            ocultarPantallaDeCarga();

            modalInstance.options.onCloseEnd = function () {
                document.getElementById('modal-body-content').innerHTML = '';
                modalElement.style.width = '';
                modalElement.style.maxHeight = '';
            };
        })
        .catch(error => {
            console.error(`Error al cargar alojamientos de la habitación ${habitacionNumero || habitacionId}:`, error);
            ocultarPantallaDeCarga();
            mostrarMensajeError('No se pudo cargar el historial de alojamientos.');
        });
}

function topProductos() {
    actualizarGraficosDeAlojamientos();
}

function topCategorias() {
    actualizarGraficosDeAlojamientos();
}

function pedidosPorSalon() {
    actualizarGraficosDeAlojamientos();
}

function alojamientosPorResponsable() {
    actualizarGraficosDeAlojamientos();
}

function evolucionAlojamientos() {
    actualizarGraficosDeAlojamientos();
}

function recargarEvolucionHabitacion() {
    const habitacionId = document.getElementById('evolucion-habitacion_id')?.value;

    if (!habitacionId) {
        mostrarMensajeError('Seleccione una habitacion');
        return;
    }

    cargarEvolucionHabitacion(habitacionId);
}

function actualizarGraficosDeventas() {
    const fechaInicio = document.getElementById("fecha_inicio").value;
    const fechaFin = document.getElementById("fecha_fin").value;

    // Construir la URL con los parámetros
    const url = `/dashboard/movventa?fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}`;

    // Usar la función que ya tienes
    cargarContenidoDinamicamente(url);
    reinicializarDataTables();
}

function actualizarGraficosDeMovCaja() {
    const fechaInicio = document.getElementById("fecha_inicio").value;
    const fechaFin = document.getElementById("fecha_fin").value;

    // Construir la URL con los parámetros
    const url = `/dashboard/movcaja?fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}`;

    // Usar la función que ya tienes
    cargarContenidoDinamicamente(url);
    reinicializarDataTables();
}


function mostrarFiltro() {
    const filtro = document.getElementById('filtros');
    const icono = document.querySelector('#btn-filtro i');

    if (filtro) {
        const currentDisplay = window.getComputedStyle(filtro).display;

        if (currentDisplay === 'none') {
            // 🔹 Mostrar (restaurar al valor por defecto del CSS)
            filtro.style.display = '';
            if (icono) {
                icono.classList.remove('fa-magnifying-glass-plus');
                icono.classList.add('fa-magnifying-glass-minus');
            }
        } else {
            // 🔹 Ocultar
            filtro.style.display = 'none';
            if (icono) {
                icono.classList.remove('fa-magnifying-glass-minus');
                icono.classList.add('fa-magnifying-glass-plus');
            }
        }
    }
}

function normalizarFiltroControlHabitaciones(valor) {
    return String(valor || '')
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLocaleLowerCase('es')
        .trim();
}

function reinicializarSelectsControlHabitaciones() {
    if (!window.M?.FormSelect) return;

    document
        .querySelectorAll('#estado-control-habitaciones, #piso-control-habitaciones, #tipo-control-habitaciones')
        .forEach(function (select) {
            const instancia = M.FormSelect.getInstance(select);
            if (instancia) instancia.destroy();
            M.FormSelect.init(select);
        });
}

function mostrarFiltrosControlHabitaciones(boton) {
    const filtros = document.getElementById('filtros-control-habitaciones');
    if (!filtros) return;

    const mostrar = window.getComputedStyle(filtros).display === 'none';
    filtros.style.display = mostrar ? '' : 'none';
    boton?.setAttribute('aria-expanded', mostrar ? 'true' : 'false');
    boton?.querySelector('i')?.classList.toggle('fa-magnifying-glass-plus', !mostrar);
    boton?.querySelector('i')?.classList.toggle('fa-magnifying-glass-minus', mostrar);

    if (mostrar) {
        reinicializarSelectsControlHabitaciones();
        window.M?.updateTextFields?.();
        document.getElementById('buscar-control-habitaciones')?.focus();
    }
}

function filtrarControlHabitaciones() {
    const termino = normalizarFiltroControlHabitaciones(
        document.getElementById('buscar-control-habitaciones')?.value
    );
    const estado = document.getElementById('estado-control-habitaciones')?.value || '';
    const piso = document.getElementById('piso-control-habitaciones')?.value || '';
    const tipo = document.getElementById('tipo-control-habitaciones')?.value || '';
    const tarjetas = document.querySelectorAll('.habitacion-column > .habitacion-item');
    let visibles = 0;

    tarjetas.forEach(tarjeta => {
        const coincide = (!termino || normalizarFiltroControlHabitaciones(tarjeta.dataset.busqueda).includes(termino))
            && (!estado || tarjeta.dataset.estado === estado)
            && (!piso || tarjeta.dataset.piso === piso)
            && (!tipo || tarjeta.dataset.tipo === tipo);

        tarjeta.style.display = coincide ? '' : 'none';
        if (coincide) visibles++;
    });

    const resultado = document.getElementById('resultado-control-habitaciones');
    if (resultado) {
        const hayFiltros = termino || estado || piso || tipo;
        resultado.textContent = hayFiltros
            ? (visibles === 1 ? '1 habitación encontrada' : `${visibles} habitaciones encontradas`)
            : '';
    }
}

function limpiarFiltrosControlHabitaciones() {
    const busqueda = document.getElementById('buscar-control-habitaciones');
    const estado = document.getElementById('estado-control-habitaciones');
    const piso = document.getElementById('piso-control-habitaciones');
    const tipo = document.getElementById('tipo-control-habitaciones');

    if (busqueda) busqueda.value = '';
    if (estado) estado.value = '';
    if (piso) piso.value = '';
    if (tipo) tipo.value = '';
    reinicializarSelectsControlHabitaciones();
    filtrarControlHabitaciones();
    busqueda?.focus();
}

function toggleResumenMovcaja() {
    const wrapper = document.getElementById('movcaja-resumen-row');
    const resumen = document.getElementById('resumen-dia');
    const toggleButton = document.getElementById('btn-toggle-resumen');
    const resumenUrl = toggleButton ? toggleButton.getAttribute('data-resumen-url') : '';

    if (!wrapper) {
        return;
    }
    if (resumen) {
        resumen.style.display = '';
    }
    wrapper.classList.toggle('resumen-oculto');

    if (resumenUrl) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const headers = {
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        };
        if (csrfToken) {
            headers['X-CSRF-TOKEN'] = csrfToken;
        }

        fetch(resumenUrl, {
            method: 'POST',
            headers: headers,
            body: JSON.stringify({
                hidden: wrapper.classList.contains('resumen-oculto')
            })
        }).catch(error => {
            console.error('Error al guardar estado del resumen:', error);
        });
    }
}

function expandOrCompressChildRows() {
    const controles = document.querySelectorAll('#data-table td.dtr-control');
    const btn = document.getElementById('btn-expand-compress-MovCaja');
    const icono = btn ? btn.querySelector('i') : null;

    controles.forEach(ctrl => {
        ctrl.click(); // alterna la fila
    });

    // Cambiar el ícono del botón según el estado
    if (icono) {
        // Revisar si alguna fila sigue expandida
        const hayFilasExpand = document.querySelectorAll('#data-table tr.dt-hasChild.parent').length > 0;

        if (hayFilasExpand) {
            icono.classList.remove('fa-expand');
            icono.classList.add('fa-compress');
        } else {
            icono.classList.remove('fa-compress');
            icono.classList.add('fa-expand');
        }
    }
}

// Función para ejecutar la búsqueda
function realizarBusqueda() {
    const searchInput = document.getElementById('search-input');
    const searchTerm = searchInput ? searchInput.value.trim() : '';
    const urlBase = searchInput ? searchInput.getAttribute('data-url-base') : null;

    if (!urlBase) {
        console.error("URL base no definida en el atributo data-url-base del input de búsqueda.");
        return;
    }

    const params = new URLSearchParams();
    params.set('search', searchTerm);
    const perPageSelect = document.getElementById('records-per-page');
    const perPage = perPageSelect ? perPageSelect.value : 10;
    params.set('perPage', perPage);

    const noGratoSelect = document.getElementById('no_grato');
    if (noGratoSelect) {
        params.set('no_grato', noGratoSelect.value);
    }

    agregarFiltrosHabitacionAParams(params);
    agregarFiltrosMovcompraAParams(params);

    const url = `${urlBase}?${params.toString()}`;

    cargarContenidoDinamicamente(url);
}

function generarXML(solicitudId) {
    window.open('/solicitud/' + solicitudId + '/download-xml', '_blank');
}

function generarCDR(solicitudId) {
    window.open('/solicitud/' + solicitudId + '/download-cdr', '_blank');
}

/* ===================== IMPORTAR PRODUCTOS ENTRE SUCURSALES ===================== */

function inicializarFormularioTransferenciaProductos() {
    const form = document.querySelector('[data-form-transferencia-productos]');
    if (!form) {
        return;
    }

    const select = document.getElementById('sucursal_origen_id');
    if (select) {
        const instancia = M.FormSelect.getInstance(select);
        if (instancia) {
            instancia.destroy();
        }
        M.FormSelect.init(select);
        select.removeEventListener('change', cargarProductosOrigenTransferencia);
        select.addEventListener('change', cargarProductosOrigenTransferencia);
    }

    const buscarInput = document.getElementById('buscar-producto-transferencia');
    if (buscarInput) {
        buscarInput.removeEventListener('keyup', filtrarProductosTransferencia);
        buscarInput.addEventListener('keyup', filtrarProductosTransferencia);
    }

    const transferirTodos = document.getElementById('transferir_todos');
    if (transferirTodos) {
        transferirTodos.removeEventListener('change', alternarTodosProductosTransferencia);
        transferirTodos.addEventListener('change', alternarTodosProductosTransferencia);
    }

    const btnMarcar = document.getElementById('btn-marcar-productos-transferencia');
    if (btnMarcar) {
        btnMarcar.onclick = () => seleccionarProductosVisiblesTransferencia(true);
    }

    const btnLimpiar = document.getElementById('btn-limpiar-productos-transferencia');
    if (btnLimpiar) {
        btnLimpiar.onclick = () => seleccionarProductosVisiblesTransferencia(false);
    }

    M.updateTextFields();
    actualizarBotonGuardarTransferencia();
}

function alternarTodosProductosTransferencia() {
    const todos = document.getElementById('transferir_todos')?.checked;
    document.querySelectorAll('.producto-transferencia-check').forEach((checkbox) => {
        checkbox.disabled = !!todos;
        if (todos) {
            checkbox.checked = false;
        }
    });
    actualizarBotonGuardarTransferencia();
}

function seleccionarProductosVisiblesTransferencia(marcar) {
    if (document.getElementById('transferir_todos')?.checked) {
        return;
    }

    document.querySelectorAll('.producto-transferencia').forEach((item) => {
        if (item.style.display !== 'none') {
            const checkbox = item.querySelector('.producto-transferencia-check');
            if (checkbox) {
                checkbox.checked = marcar;
            }
        }
    });
    actualizarBotonGuardarTransferencia();
}

function filtrarProductosTransferencia() {
    const filtro = (document.getElementById('buscar-producto-transferencia')?.value || '').toLowerCase();
    document.querySelectorAll('.producto-transferencia').forEach((item) => {
        item.style.display = item.dataset.texto.includes(filtro) ? '' : 'none';
    });
}

function actualizarBotonGuardarTransferencia() {
    const btn = document.getElementById('btn-guardar-transferencia');
    if (!btn) {
        return;
    }

    const todos = document.getElementById('transferir_todos')?.checked;
    const hayProductos = document.querySelectorAll('.producto-transferencia-check').length > 0;
    const haySeleccionados = todos || document.querySelectorAll('.producto-transferencia-check:checked').length > 0;

    btn.disabled = !(hayProductos && haySeleccionados);
}

function escapeHtmlTransferencia(texto) {
    return String(texto ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function cargarProductosOrigenTransferencia() {
    const select = document.getElementById('sucursal_origen_id');
    const contenedor = document.getElementById('lista-productos-transferencia');
    const resumen = document.getElementById('resumen-origen-transferencia');
    const nombreOrigen = document.getElementById('nombre-origen-transferencia');
    const origenId = select?.value;

    if (!origenId || !contenedor) {
        return;
    }

    const opcionSeleccionada = select.options[select.selectedIndex];
    const textoOrigen = opcionSeleccionada?.text?.trim() || '';
    if (resumen && nombreOrigen) {
        nombreOrigen.textContent = textoOrigen;
        resumen.style.display = '';
    }

    contenedor.innerHTML = '<p>Cargando productos...</p>';
    actualizarBotonGuardarTransferencia();

    const baseUrl = document.getElementById('productos-transferencia-url')?.value || '/sucursal/productosTransferencia';

    fetch(`${baseUrl}/${origenId}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
        .then((response) => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }
            return response.json();
        })
        .then((productos) => {
            if (!Array.isArray(productos) || !productos.length) {
                contenedor.innerHTML = '<p>No hay productos asociados a esta sucursal.</p>';
                actualizarBotonGuardarTransferencia();
                return;
            }

            contenedor.innerHTML = productos.map((producto) => {
                const codigo = escapeHtmlTransferencia(producto.codigo);
                const descripcion = escapeHtmlTransferencia(producto.descripcion);
                const categoria = escapeHtmlTransferencia(producto.categoria);
                const texto = `${producto.codigo} ${producto.descripcion} ${producto.categoria}`.toLowerCase();
                const categoriaHtml = categoria ? `<small>(${categoria})</small>` : '';

                return `
                    <p class="producto-transferencia" data-texto="${escapeHtmlTransferencia(texto)}">
                        <label>
                            <input type="checkbox" name="productos[]" value="${producto.id}"
                                class="producto-transferencia-check filled-in" />
                            <span>${codigo} - ${descripcion} ${categoriaHtml}</span>
                        </label>
                    </p>`;
            }).join('');

            contenedor.querySelectorAll('.producto-transferencia-check').forEach((checkbox) => {
                checkbox.addEventListener('change', actualizarBotonGuardarTransferencia);
            });

            const transferirTodos = document.getElementById('transferir_todos');
            if (transferirTodos) {
                transferirTodos.checked = false;
            }

            actualizarBotonGuardarTransferencia();
        })
        .catch((error) => {
            console.error('Error al cargar productos de transferencia:', error);
            contenedor.innerHTML = '<p>Error al cargar productos.</p>';
            actualizarBotonGuardarTransferencia();
        });
}

/* ===================== MÓDULO COMPRAS ===================== */

function initFormularioCompra() {
    const form = document.querySelector('.form-movcompra');
    if (!form) return;

    M.Tabs.init(document.querySelectorAll('.tabs-compra'));
    M.FormSelect.init(form.querySelectorAll('select'));

    const productsContainer = document.querySelector('.form-movcompra .products-container');
    const firstCategoryBtn = document.querySelector('#slider-categoria-1 .btn-categoria');
    if (firstCategoryBtn && productsContainer && productsContainer.children.length === 0) {
        firstCategoryBtn.click();
    }

    toggleBotonAgregarCompra();
    toggleSeccionCajaCompra();
    sincronizarDetallesCompraDesdeTabla();
    actualizarTotalesCompra();

    const submitHandler = function (e) {
        if (!prepararEnvioCompra(e)) {
            e.preventDefault();
        }
    };

    form.removeEventListener('submit', submitHandler);
    form.addEventListener('submit', submitHandler);
}

function toggleBotonAgregarCompra() {
    const tipo = document.getElementById('tipo_detalle_compra')?.value;
    const btn = document.getElementById('det_movcompra-btn-agregar');
    if (btn) btn.style.display = tipo === 'GLOSA' ? 'inline-block' : 'none';
}

function toggleSeccionCajaCompra() {
    const checkbox = document.getElementById('afecta_caja');
    const seccion = document.getElementById('seccion-caja-compra');
    if (!seccion) return;
    seccion.style.display = checkbox?.checked ? 'block' : 'none';
}

function obtenerPorcentajeIgvCompra() {
    const valor = parseFloat(document.getElementById('porcentaje_igv')?.value || '18');
    return isNaN(valor) ? 18 : valor;
}

function actualizarTotalesCompra() {
    const tabla = document.querySelector('#data-table tbody');
    if (!tabla) return;

    let sumaImportes = 0;
    tabla.querySelectorAll('tr').forEach((fila) => {
        const importe = parseFloat(fila.querySelector('.precio-total')?.textContent?.trim() || '0') || 0;
        sumaImportes += importe;
    });

    const incluyeIgv = document.getElementById('incluye_igv')?.checked === true;
    const porcentaje = obtenerPorcentajeIgvCompra() / 100;
    let subtotal = 0;
    let igv = 0;
    let total = 0;

    if (incluyeIgv) {
        total = sumaImportes;
        subtotal = total / (1 + porcentaje);
        igv = total - subtotal;
    } else {
        subtotal = sumaImportes;
        igv = subtotal * porcentaje;
        total = subtotal + igv;
    }

    const subtotalInput = document.getElementById('subtotal_compra');
    const igvInput = document.getElementById('igv_compra');
    const totalInput = document.getElementById('total_compra');

    if (subtotalInput) subtotalInput.value = subtotal.toFixed(2);
    if (igvInput) igvInput.value = igv.toFixed(2);
    if (totalInput) totalInput.value = total.toFixed(2);

    const filaPago = document.querySelector('#detalle-movcaja tr .monto span');
    if (filaPago && document.querySelectorAll('#detalle-movcaja tr').length === 1) {
        filaPago.textContent = total.toFixed(2);
        if (typeof actualizarDetallesMovimientos === 'function') {
            actualizarDetallesMovimientos();
        }
    }
}

function sincronizarDetallesCompraDesdeTabla() {
    const campo = document.getElementById('detalles_json');
    const tabla = document.querySelector('#data-table tbody');
    if (!campo || !tabla) return;

    const detalles = [];
    tabla.querySelectorAll('tr').forEach((fila) => {
        const celdas = fila.querySelectorAll('td');
        if (celdas.length < 7) return;

        const cantidad = parseFloat(fila.querySelector('.cantidad')?.textContent?.trim() || '0') || 0;
        const precioUnitario = parseFloat(fila.querySelector('.precio-unitario')?.textContent?.trim() || '0') || 0;

        detalles.push({
            producto_id: fila.getAttribute('data-producto-id') || null,
            codigo: celdas[0]?.textContent?.trim() || '',
            descripcion: celdas[1]?.textContent?.trim() || '',
            unidad: celdas[2]?.textContent?.trim() || '',
            cantidad: cantidad,
            precio_unitario: precioUnitario,
            precio_compra: precioUnitario,
            total: cantidad * precioUnitario,
            fecha_vencimiento: fila.querySelector('.fecha-vencimiento')?.value || '',
            notas: celdas[8]?.textContent?.trim() || '',
        });
    });

    campo.value = JSON.stringify(detalles);
}

function actualizarRapidoDetCompra(productId, cantidad) {
    const tbody = document.querySelector('#data-table tbody');
    if (!tbody) return;

    let filaExistente = null;
    tbody.querySelectorAll('tr').forEach((fila) => {
        if (String(fila.getAttribute('data-producto-id')) === String(productId)) {
            filaExistente = fila;
        }
    });

    const cantidadDelta = parseFloat(cantidad || 1);

    if (filaExistente) {
        const cantidadActual = parseFloat(filaExistente.querySelector('.cantidad')?.textContent || '0') || 0;
        const nuevaCantidad = cantidadActual + cantidadDelta;

        if (nuevaCantidad <= 0) {
            quitarProductoDeTablaCompra(productId, filaExistente.querySelector('button'));
            return;
        }

        const precioUnitario = parseFloat(filaExistente.querySelector('.precio-unitario')?.textContent || '0') || 0;
        const porcentaje = obtenerPorcentajeIgvCompra();
        const importe = nuevaCantidad * precioUnitario;

        filaExistente.querySelector('.cantidad').textContent = nuevaCantidad.toFixed(2);
        filaExistente.querySelector('.precio-total').textContent = importe.toFixed(2);
        filaExistente.querySelector('.igv').textContent = (importe * (porcentaje / 100)).toFixed(2);

        sincronizarDetallesCompraDesdeTabla();
        actualizarTotalesCompra();
        return;
    }

    fetch(`/productos/detalle/${productId}`)
        .then(response => response.json())
        .then(producto => {
            producto.cantidad = cantidadDelta;
            producto.precio_unitario = parseFloat(producto.precio_compra || producto.precio_unitario || 0);
            agregarProductoATablaCompra(producto);
        })
        .catch(error => {
            console.error('Error al obtener el producto:', error);
        });
}

function agregarProductoATablaCompra(producto) {
    const tbody = document.querySelector('#data-table tbody');
    if (!tbody) return;

    let filaExistente = null;
    tbody.querySelectorAll('tr').forEach((fila) => {
        if (String(fila.getAttribute('data-producto-id')) === String(producto.id)) {
            filaExistente = fila;
        }
    });

    const cantidadNueva = parseFloat(producto.cantidad || 1);
    const precioUnitario = parseFloat(producto.precio_compra || producto.precio_unitario || 0);
    const porcentaje = obtenerPorcentajeIgvCompra();
    const igvLinea = (cantidadNueva * precioUnitario) * (porcentaje / 100);

    if (filaExistente) {
        const cantidadActual = parseFloat(filaExistente.querySelector('.cantidad')?.textContent || '0') || 0;
        const nuevaCantidad = cantidadActual + cantidadNueva;
        const importe = nuevaCantidad * precioUnitario;
        filaExistente.querySelector('.cantidad').textContent = nuevaCantidad.toFixed(2);
        filaExistente.querySelector('.precio-total').textContent = importe.toFixed(2);
        filaExistente.querySelector('.igv').textContent = (importe * (porcentaje / 100)).toFixed(2);
    } else {
        const importe = cantidadNueva * precioUnitario;
        const fila = document.createElement('tr');
        fila.setAttribute('data-producto-id', producto.id);
        fila.setAttribute('onclick', 'editarDetalleCompra(this, event)');
        fila.innerHTML = `
            <td>${producto.codigo || ''}</td>
            <td>${producto.descripcion || ''}</td>
            <td>${producto.unidad || 'Unidad'}</td>
            <td><span class="inpt-cantidad-mc cantidad">${cantidadNueva.toFixed(2)}</span></td>
            <td><span class="inpt-cantidad-mc igv">${igvLinea.toFixed(2)}</span></td>
            <td><span class="inpt-cantidad-mc precio-unitario">${precioUnitario.toFixed(2)}</span></td>
            <td><span class="inpt-cantidad-mc precio-total">${importe.toFixed(2)}</span></td>
            <td><input type="date" class="fecha-vencimiento" value="${producto.fecha_vencimiento || ''}" onclick="event.stopPropagation();" onchange="sincronizarDetallesCompraDesdeTabla();"></td>
            <td></td>
            <td>
                <button type="button" class="btn btn-floating btn-small red white-text tooltipped" data-tooltip="Quitar"
                    onclick="quitarProductoDeTablaCompra(${producto.id}, this); event.stopPropagation();">
                    <i class="fa-solid fa-trash-can"></i>
                </button>
            </td>`;
        tbody.appendChild(fila);
    }

    sincronizarDetallesCompraDesdeTabla();
    actualizarTotalesCompra();
    reinicializarDataTables();
}

function agregarProductoDesdeAutocompleteCompra() {
    const productoId = document.getElementById('producto_id')?.value;
    const texto = document.getElementById('producto_autocomplete')?.value;
    if (!productoId || !texto) return;

    const partes = texto.split(' - ');
    agregarProductoATablaCompra({
        id: productoId,
        codigo: partes[0] || '',
        descripcion: partes.slice(1).join(' - ') || texto,
        unidad: 'Unidad',
        cantidad: 1,
        precio_unitario: 0,
        precio_compra: 0,
    });
}

function quitarProductoDeTablaCompra(productoId, boton) {
    const fila = boton?.closest('tr');
    if (fila) fila.remove();
    sincronizarDetallesCompraDesdeTabla();
    actualizarTotalesCompra();
    reinicializarDataTables();
}

function editarDetalleCompra(fila, event) {
    if (event?.target?.closest('button')) return;
    // Reservado para edición modal futura
}

function agregarGlosaCompra() {
    const descripcion = prompt('Descripción de la glosa:');
    if (!descripcion) return;

    agregarProductoATablaCompra({
        id: 'glosa-' + Date.now(),
        codigo: 'GLOSA',
        descripcion: descripcion,
        unidad: 'Unidad',
        cantidad: 1,
        precio_unitario: 0,
        precio_compra: 0,
    });
}

function cambiarTipoDocCompra(tipoDocumentoId) {
    const select = document.getElementById('tipodocumento_id');
    const abreviatura = select?.selectedOptions?.[0]?.dataset?.abreviatura || 'B';
    const serie = document.getElementById('serie_compra')?.value || '000';
    console.log('Tipo documento compra:', tipoDocumentoId, abreviatura + serie);
}

function setHiddenCheckboxValue(form, name, checkboxId) {
    let hidden = form.querySelector(`input[type="hidden"][data-sync="${name}"]`);
    if (!hidden) {
        hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.name = name;
        hidden.dataset.sync = name;
        form.appendChild(hidden);
    }
    const checkbox = document.getElementById(checkboxId);
    hidden.value = checkbox?.checked ? 'S' : 'N';
}

function prepararEnvioCompra(event) {
    const form = document.querySelector('.form-movcompra');
    if (!form) return true;

    sincronizarDetallesCompraDesdeTabla();
    actualizarTotalesCompra();

    setHiddenCheckboxValue(form, 'incluye_igv', 'incluye_igv');
    setHiddenCheckboxValue(form, 'afecta_kardex', 'afecta_kardex');
    setHiddenCheckboxValue(form, 'afecta_caja', 'afecta_caja');

    const detalles = JSON.parse(document.getElementById('detalles_json')?.value || '[]');
    if (!detalles.length) {
        Swal.fire('Validación', 'Debe agregar al menos un producto o ítem a la compra.', 'warning');
        if (event) event.preventDefault();
        return false;
    }

    const afectaCaja = document.getElementById('afecta_caja')?.checked;
    if (afectaCaja && typeof actualizarDetallesMovimientos === 'function') {
        actualizarDetallesMovimientos();
    }

    return true;
}
