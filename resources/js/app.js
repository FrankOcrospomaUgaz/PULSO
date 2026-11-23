$(document).ready(function() {
    var table = $('#data-table').DataTable({
        responsive: true,
        searching: false,
        lengthChange: false,
        dom: 'rtip',
        buttons: [
            {
                extend: 'copyHtml5',
                exportOptions: {
                    columns: ':not(:last-child)' // Excluir la última columna
                }
            },
            {
                extend: 'excelHtml5',
                exportOptions: {
                    columns: ':not(:last-child)' // Excluir la última columna
                }
            },
            {
                extend: 'csvHtml5',
                exportOptions: {
                    columns: ':not(:last-child)' // Excluir la última columna
                }
            },
            {
                extend: 'pdfHtml5',
                exportOptions: {
                    columns: ':not(:last-child)' // Excluir la última columna
                }
            }
        ],
        language: {
            "url": "//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json",
            "paginate": {
                "first": '<i class="material-icons">first_page</i>',
                "last": '<i class="material-icons">last_page</i>',
                "next": '<i class="material-icons">chevron_right</i>',
                "previous": '<i class="material-icons">chevron_left</i>'
            },
            "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
            "infoEmpty": "Mostrando 0 a 0 de 0 entradas",
            "emptyTable": "No hay datos disponibles en la tabla",
            "lengthMenu": "Mostrar _MENU_ registros",
            "search": "Buscar:",
            "zeroRecords": "No se encontraron coincidencias",
            "infoFiltered": "(filtrado de _MAX_ registros totales)"
        },
        pagingType: 'full_numbers',
        rowGroup: {
            dataSrc: 2
        },
    });

    $('.tooltipped').tooltip();
    M.AutoInit();

    $('#search-input').on('keyup', function() {
        var value = $(this).val().toLowerCase();
        $('#data-table tbody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });

    $('#records-per-page').on('change', function() {
        var val = $(this).val();
        table.page.len(val).draw();
    });

    $('#data-table tbody').on('click', '.toggle-container', function () {
        var tr = $(this).closest('tr');
        var row = table.row(tr);
        var icon = $(this).find('.rotate-icon');

        if (row.child.isShown()) {
            row.child.hide();
            tr.removeClass('shown');
            icon.removeClass('rotate');
        } else {
            row.child(row.data()[3]).show();
            tr.addClass('shown');
            icon.addClass('rotate');
        }
    });

    $('#export-copy').on('click', function() {
        table.button('.buttons-copy').trigger();
    });
    $('#export-excel').on('click', function() {
        table.button('.buttons-excel').trigger();
    });
    $('#export-csv').on('click', function() {
        table.button('.buttons-csv').trigger();
    });
    $('#export-pdf').on('click', function() {
        table.button('.buttons-pdf').trigger();
    });
});
