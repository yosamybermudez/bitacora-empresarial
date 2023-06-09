function ajaxToggleNotifications(elem){
    elem.after('<div data-role="progress" class="notification-progress" data-type="line" data-small="true"></div>');
    $.ajax({
        type: "GET",
        url: elem.attr('data-href'),
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        success: function(response)
        {
            var leido = response.estado;
            var read_notification = elem.find('.read-notification:first');
            var notification_progress = elem.siblings('.notification-progress:first');
            var notification_all_leidas = $('#notifications-all-leidas');
            if(read_notification)
            {
                read_notification.toggleClass('no-visible');
            }
            var elem_todas = $('#todas li[data-href="' + elem.attr('data-href') +'"]');
            var elem_no_leidas = $('#no-leidas li[data-href="' + elem.attr('data-href') +'"]');
            if(leido){
                elem_todas.addClass('bg-lightGray');
                elem_todas.find('.read-notification:first').addClass('no-visible');
                elem_no_leidas.addClass('bg-lightGray');
                elem_no_leidas.addClass('hide-notification');
                read_notification.addClass('')
            } else {
                elem_todas.removeClass('bg-lightGray');


                elem_no_leidas.removeClass('hide-notification');
                elem_no_leidas.removeClass('bg-lightGray');
                elem_no_leidas.find('.read-notification:first').removeClass('no-visible');
            }
            notification_progress.remove();
            var notif = $('#no-leidas .feed-list > li:not(.hide-notification)');
            $('#notifications-count').html(notif.length);
            if(notif.length === 0 ){
                notification_all_leidas.addClass('disabled');
                $('#no-leidas').find('.feed-list:first').prepend('<li id="no-leidas-empty" class="p-0 text-center">No hay notificaciones que mostrar</li>');

            } else {
                notification_all_leidas.removeClass('disabled');
                $('#no-leidas-empty').remove();
            }


        },
        failure: function (response) {
            location.reload();
        },
        error: function (response) {
            location.reload();
        }
    });
};

function ajaxToggleNotificationsIndexTable(elem){
    elem.after('<div class="bg-white notification-progress pos-absolute z-10" style="top: 0; left: 0;"><div data-role="activity" data-type="square" data-style="color"></div></div> ');
    var notification_progress = elem.siblings('.notification-progress:first');
    $.ajax({
        type: "GET",
        url: elem.attr('data-href'),
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        success: function(response)
        {
            var leido = response.estado;
            var fecha_visto = elem.parent().parent().prev();
            if(leido){
                elem.attr('title','Marcar como no leída');
                elem.html('<span class="mif-mail-read"></span>');
                Metro.toast.create("Elemento marcado como LEÍDO", null, 2000, "success");
                var date = $.format.date(new Date().getTime(), "dd-MM-yyyy hh:mm:ss a");
                fecha_visto.html(date);
                elem.addClass('secondary');
                elem.removeClass('primary');

            } else {
                elem.attr('title', 'Marcar como leída');
                Metro.toast.create("Elemento marcado como NO LEÍDO", null, 2000, "success");
                elem.html('<span class="mif-mail"></span>');
                fecha_visto.html('');
                elem.addClass('primary');
                elem.removeClass('secondary');
            }
            console.log(fecha_visto.html());
            notification_progress.remove();
        },
        failure: function (response) {
            Metro.toast.create("No se pudo realizar la acción", null, 2000, "alert");
        },
        error: function (response) {
            Metro.toast.create("No se pudo realizar la acción", null, 2000, "alert");
        }
    });
};

function ajaxAllReadNotifications(elem){
        var elem_todas = $('#todas');
        var elem_no_leidas = $('#no-leidas');
        $('ul[data-role="materialtabs"]').before('<div data-role="progress" class="notification-progress" data-type="line" data-small="true"></div>');
        $.ajax({
            type: "GET",
            url: elem.attr('data-href'),
            contentType: "application/json; charset=utf-8",
            dataType: "json",
            success: function(response)
            {
                var notification_all_leidas = $('#notifications-all-leidas');
                elem_todas.find('li').addClass('bg-lightGray');
                elem_todas.find('.read-notification').addClass('no-visible');
                console.log(elem_todas.find('.read-notification').length);

                elem_no_leidas.find('.feed-list:first').html('<li id="no-leidas-empty" class="p-0 text-center">No hay notificaciones que mostrar</li>');
                $('#notifications-container .notification-progress').each(function () {
                    $(this).remove();
                });
                $('#notifications-count').html(0);
                notification_all_leidas.addClass('disabled');
            },
            failure: function (response) {
                location.reload();
            },
            error: function (response) {
                location.reload();
            }
        });
};