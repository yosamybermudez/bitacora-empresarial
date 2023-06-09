// MetroUI . Tablas

$('table.table:not(.no-metro-ui)').each(function () {
    $(this).addClass('table compact row-hover');
    $(this).attr('data-role', 'table');
    $(this).attr('data-check', 'true');
    $(this).attr('data-cell-wrapper', 'true');
    $(this).attr('data-table-info-title', 'Mostrando desde $1 hasta $2 de $3 filas');
    $(this).attr('data-table-search-title', 'Buscar');
    $(this).attr('data-pagination-prev-title', 'Anterior');
    $(this).attr('data-pagination-next-title', 'Siguiente');
    $(this).attr('data-all-records-title', 'Todos los resultados');
    $(this).attr('data-table-rows-count-title', 'Mostrar resultados');
    $(this).attr('data-check-col-index', '0');
    $(this).attr('data-all-records-title', 'Todos los resultados');
    $(this).attr('data-rows-steps', '-1, 5, 10, 30, 50, 100');
    $(this).attr('data-horizontal-scroll', 'true');
    $(this).attr('data-cell-wrapper', 'false');
});

$('table.table.no-check').each(function () {
    $(this).attr('data-check', 'false');
});
$('table.table.no-fields').each(function () {
    $(this).attr('data-check', 'false');
    $(this).attr('data-show-search', 'false');
    $(this).attr('data-show-rows-steps', 'false');
    $(this).attr('data-show-pagination', 'false');
    $(this).attr('data-show-table-info', 'false');
});

$('table.table thead th:not(:last)').attr('data-sortable', 'true');
$('table.table thead th.display-none').attr('data-show', 'false');