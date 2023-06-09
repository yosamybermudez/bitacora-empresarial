(function($){
    $.fn.moveTo = function(selector){
        return this.each(function(){
            let cl = $(this).clone();
            $(cl).prependTo(selector);
            $(this).remove();
        });
    };
})(jQuery);

$('ul#ad-tree-selectable').delegate('li','click', function (e) {
    e.stopPropagation();
    $('ul#ad-tree-selectable li > .caption').removeClass('bg-gray');
    $(this).find('.caption:first').addClass('bg-gray');
});

$('div.toolbar  a[data-role="button-eliminar"]').each(function () {
    var elem = $(this);
    var href = $(this).attr('href');
    elem.addClass('tool-button alert fg-white index-eliminar-modal-show');
    elem.append('<span class="mif mif-bin"></span>');
    elem.attr('data-action', href);
    elem.attr('title', 'Eliminar');
    elem.removeAttr('href');
});

$('div.toolbar a[data-role="button-mostrar"]').each(function () {
    var elem = $(this);
    elem.addClass('tool-button fg-white primary');
    elem.attr('title', 'Mostrar');
    elem.append('<span class="mif-eye"></span>');
});

$('div.toolbar a[data-role="button-editar"]').each(function () {
    var elem = $(this);
    elem.addClass('tool-button fg-white secondary');
    elem.attr('title', 'Editar');
    elem.append('<span class="mif-pencil"></span>');
});

$('div.toolbar a[data-role="button-exportar-pdf"]').each(function () {
    var elem = $(this);
    elem.addClass('tool-button fg-white info');
    elem.attr('title', 'Exportar a PDF');
    elem.append('<span class="mif-file-pdf"></span>');
});

$('div.toolbar a[data-role="button-seguir"]').each(function () {
    var elem = $(this);
    elem.addClass('tool-button fg-white warning');
    if(elem.attr('data-id') === 'unfollow'){
        elem.append('<span class="mif-star-empty"></span>');
        elem.attr('title', 'Seguir');
    } else {
        elem.append('<span class="mif-star-full"></span>');
        elem.attr('title', 'Dejar de seguir');
    }
});

$('div.toolbar a[data-role="button-descargar"]').each(function () {
    var elem = $(this);
    elem.addClass('tool-button fg-white warning');
    elem.attr('title', 'Descargar');
    elem.append('<span class="mif-download"></span>');
});


$(document).on('click', '.index-eliminar-modal-show', function(event){
    event.stopPropagation();
    event.stopImmediatePropagation();
    Metro.dialog.open('#index-eliminar-modal');
    $('#index-eliminar-form').attr('action', $(this).attr('data-action'));
});

Metro.makePlugin('input[type="text"]', "input", {});
Metro.makePlugin('input[type="email"]', "input", {});
Metro.makePlugin('input[type="date"]', "calendarpicker", {});
Metro.makePlugin('input[type="file"]', "file", {});
Metro.makePlugin('input[type="checkbox"]', "switch", {'material' : 'true' });
Metro.makePlugin('input.tag-input', "taginput", {'random-color' : 'true' });
Metro.makePlugin('textarea', "textarea", {});
Metro.makePlugin('select', "select", {});
Metro.makePlugin('table thead tr:first', "row", {});



$('form .bg-white:last').parent().after('<div class="cell-md-12"><div class="bg-white p-4 mt-2 h-100"><button type="submit" class="button bg-darkBlue fg-white">Procesar</button></div></div>');

$('table.table').each(function () {
    $(this).addClass('table compact row-hover');
    $(this).attr('data-role', 'table');
    $(this).attr('data-check', 'true');
    $(this).attr('data-cell-wrapper', 'true');
    $(this).attr('data-table-info-title', 'Mostrando desde $1 hasta $2 de $3 filas');
    $(this).attr('data-table-search-title', 'Buscar');
    $(this).attr('data-pagination-prev-title', 'Anterior');
    $(this).attr('data-pagination-next-title', 'Siguiente');
    $(this).attr('data-all-records-title', 'Todas los resultados');
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

$('[title]').each(function () {
    var elem = $(this);
    var title = elem.attr('title');
    elem.attr('data-role', 'hint');
    elem.attr('data-hint-position', 'bottom');
    elem.addClass('c-help');
    elem.attr('data-hint-text', title);
    elem.attr('data-cls-hint','bg-cyan fg-white drop-shadow');
    elem.removeAttr('title');
});

$('div.panel strong').each(function () {
    var elem = $(this);
    var panel = elem.closest('div.panel');
    panel.attr('data-role', 'panel');
    panel.attr('data-title-caption', elem.text());
    panel.attr('data-title-icon', '<span class=\'mif-arrow-right\'>');
    panel.attr('data-collapsible', 'true');
    elem.remove();
});

$(document).on('click', '#notifications-container [data-role="materialtabs"] > .active', function (e) {
    e.preventDefault();
});

$('body').on('click', function (e) {
    var notification = Metro.getPlugin('#notifications-container','collapse');
    var user = Metro.getPlugin('#logged-user-panel-container','collapse');

    if (!$('#notifications-container').is(e.target)
        && $('#notifications-container').has(e.target).length === 0
    ) {
        notification['collapse']();
    }

    if (!$('#logged-user-panel-container').is(e.target)
        && $('#logged-user-panel-container').has(e.target).length === 0
    ) {
        user['collapse']();
    }
});

$('#logged-user-panel-toggle').click(function () {
    var notification = Metro.getPlugin('#notifications-container','collapse');
    notification['collapse']();
});

$('#notifications-toogle').click(function () {
    var user = Metro.getPlugin('#logged-user-panel-container','collapse');
    user['collapse']();
});

// Marcar una notificacion como no leida
$(document).on('click', '#notifications-container .feed-list > li:not(#no-leidas-empty) .mark-as-unread', function (e) {
    var elem = $(this);
    ajaxToggleNotifications(elem.parent());
    e.stopPropagation();
});

//
$("#notifications-container .feed-list > li.bg-lightGray:not(#no-leidas-empty)")
    .mouseover(function() {
       var elem = $(this);
        elem.find('.mark-as-unread').addClass('hover');
    })
    .mouseout(function() {
        var elem = $(this);
        elem.find('.mark-as-unread').removeClass('hover');
    });

// Acceder al contenido de la notificacion seleccionada
$(document).on('click', '#notifications-container .feed-list > li:not(#no-leidas-empty)', function () {
    var elem = $(this);
    window.location.href = elem.attr('data-location');
});

//Marcar todos como no leidos
$(document).on('click', '#notifications-all-leidas', function (e) {
    var elem = $(this);
    ajaxAllReadNotifications(elem);
});

// Si no hay checkboxes marcados en las paginas de listar, no se pueden ejecutar las cciones grupales.
$(document).on('click', 'a[data-type="bulk-action-submit"]', function (e) {
    e.preventDefault();
    var elem = $(this);
    var input = $('input[name="accion"]:first');
    var form = elem.closest('form');
    input.val(elem.attr('data-name'));
    if($('input[name="table_row_check[]"]:checked').length > 0){
        form.submit();
    } else {
        Metro.toast.create("Debe seleccionar al menos un elemento", null, 2000, "alert");
    }
});

//Cambiar Leida / No Leida en el listado de todas las notificaciones (tabla)
$(document).on('click', '.index-button-read-unread', function (e) {
    e.preventDefault();
    var elem = $(this);
    ajaxToggleNotificationsIndexTable(elem);
});