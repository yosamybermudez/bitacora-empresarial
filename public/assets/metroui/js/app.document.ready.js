$(document).ready(function () {
/////
    var notification_count = $('#notifications-count').html();
    var notification_all_leidas = $('#notifications-all-leidas');
    if(notification_count === '0'){
        notification_all_leidas.addClass('disabled');
    } else {
        notification_all_leidas.removeClass('disabled');
    }

    $('.table-component').each(function (index) {
        var elem = $(this).parent().find('.table-bulk-actions');
        if(elem.length > 0){
            $(this).parent().wrap('<form id="accion-grupal-' + index + '" method="post"></form>');
            $(this).after('<input name="accion" type="hidden">');
        }

    });

    $('.table-bulk-actions').each(function () {
        let elem = $(this);
        elem.closest('form').attr('action', elem.attr('data-action'));
    });

    $('#notifications-container [data-role="materialtabs"]').parent().css('top', '0px');

    $('#notifications-container .tab-marker').remove();
    $('.table-bulk-actions').remove();

////
});