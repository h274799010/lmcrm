/**
 * Notifications
 * Оповещение пользователей о новых лидах и пр.
 *
 * Server Side Events
 *
 */



$(function(){

    /**
     * Подключение SSE
     *
     */
    var source = new EventSource("/notice");


    /**
     * При получении данных сервера
     *
     */
    source.onmessage = function(event) {

        // парсим данные
        var a = $.parseJSON(event.data);

        // перебираем все данные и оповещаем пользователя
        $.each( a, function( k, notice ){

            // проверяем тип оповещения
            if( notice == 'note' ){
                // если тип данных 'note'

                // выбираем блок с уведомлениями
                var noteBlock = $('#notice .notice_newLead');

                // делаем блок уведомлений видимым
                noteBlock.css('display', 'block');

                // выключение оповещений
                noticeOff('note');
            }

        });
    };


    /**
     * Функция отключения оповещения
     *
     */
    function noticeOff ( event ){

        // выбираем токен
        var token = $('meta[name=csrf-token]').attr('content');

        // отправляем пост на отключение сообщения
        $.post('/notified', {'event': event, '_token': token});
    }


    /**
     * Действие по клику на кнопку "закрыть уведомление"
     *
     */
    $('.removeNoticeIcon').bind('click', function(){
        $('#notice .notice_newLead').css('display', 'none');
    });

});


