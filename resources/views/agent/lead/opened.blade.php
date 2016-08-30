@extends('layouts.master')

@section('content')
        <!-- Page Content -->
                <div class="row">
                    <div id="main_table" class="col-md-12">

                        {{--<table class="table table-bordered table-striped table-hover openLeadsTable">--}}
                        <table class="table table-bordered table-striped table-hover openLeadsTable">
                            <thead>
                                <tr>
                                    {{-- todo {!! trans("site/lead.opened.icon") !!} --}}
                                    <th>icon </th>
                                    <th>status </th>
                                    <th>date </th>
                                    <th>name </th>
                                    <th>phone </th>
                                    <th>email </th>
                                    <th>mask name </th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach ($dataArray as $data)
                                <tr lead_id="{{ $data->id }}">
                                    <td><div></div></td>
                                    <td class="select_cell">
                                        {{ Form::select('status', $data->sphereStatuses->statuses->lists('stepname', 'id'), $data->openLeadStatus->status, [ 'class'=>'form', 'disabled_opt'=>$data->blockOptions ]) }}
                                    </td>
                                    <td><div>{{ $data->date }}</div></td>
                                    <td><div>{{ $data->name }}</div></td>
                                    <td><div>{{ $data->phone->phone }}</div></td>
                                    <td><div>{{ $data->email }}</div></td>
                                    <td><div> Имя маски </div></td>
                                    <td class="edit">
                                        <div>
                                            <a href="#">
                                                <img src="/assets/web/icons/list-edit.png" class="_icon pull-left flip">
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>

                    </div>

                    <div id="info_table_block" class="col-md-3 hidden">

                        <table id="info_table" class="table table-bordered table-striped table-hover"  cellspacing="0" width="100%">

                            <tr class="organizer_tr">
                                <td id="organizer_title" colspan="2" rowspan="1" >
                                    Organizer
                                </td>
                                <td class="organizer_time_title">
                                    Time
                                </td>
                                <td class="organizer_comments_title">
                                    <div>
                                        Comments
                                    </div>
                                    <span class="dropdown">
                                        <a class="dropdown-toggle" aria-expanded="true" role="button" data-toggle="dropdown" href="#">
                                            <i class="glyphicon glyphicon-plus"></i>
                                        </a>

                                        <ul class="dropdown-menu myDropDown" role="menu">
                                            <li> <a id="commentHref" class="dialog" href="http://lmcrm.cos/en/agent/lead/addReminder/3"> Comments </a> </li>
                                            <li> <a id="reminderHref" class="dialog" href="http://lmcrm.cos/en/agent/lead/addReminder/3"> Reminder </a> </li>
                                        </ul>

                                    </span>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <!-- /.col-lg-10 -->
                </div>
                <!-- /.row -->
            <!-- /.container -->


<div id="statusModal" class="modal fade bs-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="exampleModalLabel">
                    {{-- todo {!! trans("site/lead.opened.modal.head") !!} --}}
                    Change status
                </h4>
            </div>

            <div class="modal-body">

                {{-- todo {!! trans("site/lead.opened.modal.body") !!} --}}
                Are you sure?

            </div>

            <div class="modal-footer">

                <button id="statusModalCancel" type="button" class="btn btn-default" data-dismiss="modal">
                    {{-- todo {!! trans("site/lead.opened.modal.button.Cancel") !!} --}}
                    Cancel
                </button>

                <button id="statusModalChange" type="button" class="btn btn-danger">
                    {{-- todo {!! trans("site/lead.opened.modal.button.OK") !!} --}}
                    Change status
                </button>
            </div>


        </div>
    </div>
</div>

@endsection

@section('styles')
    <style>

        #main_table table tr td{
            cursor: help;
        }

        table.table.openLeadsTable > tbody > tr > td.select_cell{
            padding: 0 !important;
            margin: 0;
        }



        td.select_cell .form{
            width: 100% !important;
            height: 100% !important;
            border-radius: 0 !important;
            border: none;
        }



        /*длина контейнера*/
        td.select_cell .selectboxit-container.selectboxit-container{
            width: 100%;
            /*height: 100% !important;*/
            border: solid 1px #ED5056;

        }

        td.select_cell .selectboxit-container.selectboxit-container .selectboxit-text{
            color: #ED5056;
            margin: 4px;
        }


        td.select_cell .selectboxit-container.selectboxit-container .selectboxit-arrow-container{
            width: 16px;
            height: 100%;
            background: #ED5056;
        }

        td.select_cell .selectboxit-container.selectboxit-container .selectboxit-arrow-container i{
            font-size: 11px !important;
        }


        td.select_cell .selectboxit-container.selectboxit-container .selectboxit-option-icon-container{
            margin: 0;
        }


        td.select_cell .selectboxit-container.selectboxit-container .selectboxit-option-icon-container i{
            border: none;
            background: none;
        }


        td.select_cell .selectboxit-container.selectboxit-container ul{
            min-width: 150px !important;
        }

        table tbody tr.selected_row{
            background: lightblue !important;
        }


        td.select_cell ul li.disabled{
            background: lightgray;
            cursor: default;
        }

        td.select_cell ul li.disabled a{
            color: grey;
        }

        td.select_cell ul li.selectboxit-focus a {
            background: #ED5056 !important;
        }

        #info_table{
            font-size: 12px;
            min-width: 267px;
        }


        #info_table tr td:first-child{
            background: #63A4B8;
            color: white;
            font-weight: bold;
        }

        #info_table tr td.organizerTime{
            background: white;
            color: black;
            font-weight: normal;
        }

        #info_table .organizer_time_title{
            font-weight: bold;
            color: white;
            background: #63A4B8;
            height: 40px;
            font-size: 12px;

            padding-top: 10px;
            padding-left: 8px;

        }

        #info_table .organizer_comments_title{
            font-weight: bold;
            color: white;
            background: #63A4B8;
            height: 40px;
            padding: 0;
            font-size: 12px;
        }

        #info_table .organizer_comments_title div{
            display: inline-block;

            padding-top: 10px;
            padding-left: 8px;
        }


        #info_table .organizer_comments_title span,
        #info_table .organizer_comments_title span > a{
            float: right;
            color: white;
            background: #5593A7;
            height: 100%;
            width: 20px;
            padding-top: 6px;
            padding-left: 4px;
        }


        #info_table .organizer_comments_title span > a:hover{
            color: yellow;
        }


        ul.myDropDown{
            min-width: 10px;
        }

        /* выравнивание выпадающего меню по правому краю */
        .organizer_comments_title .dropdown .dropdown-menu{
            left: auto;
            right: 0;
        }

        i.bell_icon{
            display: block;
            color: #5593A7;
            float: right;
        }

    </style>
@endsection


@section('scripts')
    <script>

        $.extend( true, $.fn.dataTable.defaults, {
            "language": {
                "url": '{!! asset('components/datatables-plugins/i18n/'.LaravelLocalization::getCurrentLocaleName().'.lang') !!}'
            }

        });


        // путь к методу получения итема органайзера
        var getOrganizerRoute = '{{ route('agent.lead.OrganizerItem')  }}';


        /** загрузка дополнительной таблицы с подробной информацией лида */

        // id - это id лида
        function reloadTable(id){

            var tableBlock = $('#info_table_block');
            var table = $('#info_table');

            // возвращаем всем строкам дефолтный цвет
            $('tr[lead_id]').removeClass('selected_row');

            // если таблица с таким id уже существует
            if(tableBlock.attr('lead_id')==id){

                // увеличиваем основную таблицу на полную ширину
                $('#main_table').attr('class', 'col-md-12');
                // выставляем индекс дополнительной таблицы в 0
                tableBlock.attr('lead_id', 0);

                // делаем таблицу невидимой
                tableBlock.addClass('hidden');

                // очищаем ссылку на комментарии в меню органайзера
                $('#commentHref').attr( 'href', '');

                // очищаем ссылку на напоминаний в меню органайзера
                $('#reminderHref').attr( 'href', '');

                // rowspan таблицы с заголовком органайзера выставляется в дефолтное значение
                $('#organizer_title').attr( 'rowspan', 1);

                // удаляются все строки органайзера кроме шапки органайзера
                $(table).find('tr').not('.organizer_tr').remove();


                // если таблицы нет или индекс таблицы другой
            }else {
                // создание таблицы

                // выделяем активную строку цветом
                $('tr[lead_id='+id+']').addClass('selected_row');

                // выставляем rowspan в дефолтное положение
                $('#organizer_title').attr( 'rowspan',1);

                // путь к странице комментариев
                var commentHref = '{{ route('agent.lead.addСomment', '') }}' + '/' + id;

                // путь к странице напоминаний
                var reminderHref = '{{ route('agent.lead.addReminder', '') }}' + '/' + id;

                // выставляем ссылку на комментарии в меню органайзера
                $('#commentHref').attr( 'href', commentHref);

                // выставляем ссылку на напоминания в меню органайзера
                $('#reminderHref').attr( 'href', reminderHref);

                // очищаем старые данные таблицы
                $(table).find('tr').not('.organizer_tr').remove();

                // делаем блок таблицы видимым
                tableBlock.removeClass('hidden');

                // уменьшаем размер основной таблицы
                $('#main_table').attr('class', 'col-md-8');

                // получение токена
                var token = $('meta[name=csrf-token]').attr('content');

                // получаем поднобные данные о лиде с сервера
                $.post('{{ route('agent.openedLeadsAjax')  }}', { 'id': id, '_token': token }, function( data ){

                    // парсим ответ в json
                    var tableData = $.parseJSON(data);

                    // выбираем таблицу
                    var infoTable = $('#info_table');

                    // заполняем таблицу полученными данными
                    $.each( tableData['data'], function( k, data ){

                        var tr = $('<tr />');
                        var tdName = $('<td />');
                        var tdData = $('<td />');

                        tdName.attr('colspan', 2);
                        tdData.attr('colspan', 2);


                        tdName.text(data[0]);
                        tdData.text(data[1]);

                        tr.append(tdName);
                        tr.append(tdData);



                        $('.organizer_tr').before(tr);

                    } );



                    /** данные и таблица органайзера */

                    // перебираем все данные органайзера и выбираем нужные данные
                    $.each( tableData['organizer'], function(  k, data ){

                        addOrganizerRow( data[0], data[1], data[2], data[3] );

                    } );

                });


                // присваиваем таблице индекс лида
                $('#info_table_block').attr('lead_id', id);
            }
        }

        // добавление строки органайзера
        function addOrganizerRow( organizerId, time, comment, type ){

            // иконка колокольчика
            var bell = $('<i />');
            $(bell).attr( 'class', 'glyphicon glyphicon-bell bell_icon');

            // строка органайзера с данными
            var tr = $('<tr />');

            // столбцы таблицы
            var td1 = $('<td />');
            var td2 = $('<td />');

            // добавляем класс к ячейке органайзера
            td1.addClass('organizerTime');

            // добавляем id органайзера к строке таблицы
            $(tr).attr( 'organizer_id', organizerId );
            $(tr).addClass('organizedRow');


            // кнопка удаления  итема
            var dellItem = $('<button />');

            // оформление кнопки удаления итема
            dellItem.attr( 'type', 'button');
            dellItem.attr( 'class', 'btn btn-danger btn-xs');
            dellItem.css( 'float', 'left' );
            dellItem.css( 'margin-top', '5px' );

            dellItem.css( 'display', 'block' );
            dellItem.text('dell');

            // событие на нажатие кнопки
            $(dellItem).bind('click', function(){

                // путь к странице удаления итема
                var deleteReminder = '{{ route('agent.lead.deleteReminder', '') }}' + '/' + organizerId;

                // запрос на удаление
                $.get( deleteReminder, function( data ){

                    // при успешном запросе, строка удаляется из таблицы, на странце
                    if( data == true ){
                        tr.remove();
                    }
                });
            });


            // кнопка редактирования итема
            var editItem = $('<a />');

            // оформление кнопки редактирования итема
            editItem.attr( 'type', 'button');
            editItem.attr( 'class', 'btn btn-primary btn-xs dialog');
            editItem.css( 'float', 'left' );
            editItem.css( 'margin-top', '5px' );
            editItem.css( 'margin-left', '4px' );

            editItem.css( 'display', 'block' );
            editItem.text('edit');
            editItem.attr('href', '{{ route('agent.lead.editOrganizer', '') }}' + '/' + organizerId);


            // кнопка завершения
            var doneItem = $('<button />');

            // оформление кнопки завершения
            doneItem.attr( 'type', 'button');
            doneItem.attr( 'class', 'btn btn-success btn-xs');
            doneItem.css( 'float', 'left' );
            doneItem.css( 'margin-top', '5px' );
            doneItem.css( 'margin-left', '4px' );

            doneItem.css( 'display', 'block' );
            doneItem.text('done');

            // событие на нажатие кнопки
            $(doneItem).bind('click', function(){

            });


            td1.text( time );
            td2.text( comment );

            if( type == 2){
                td1.append(bell);
            }

            td2.append('<div class="button-wrap" style="display: none;"></div>');
            td2.find('.button-wrap').append(dellItem);
            td2.find('.button-wrap').append(editItem);

            if( type == 2 ) {
                td2.find('.button-wrap').append(doneItem);
            }

            var rowspan = $('#organizer_title').attr( 'rowspan' );

            $('#organizer_title').attr( 'rowspan', Number(rowspan)+1);

            tr.append(td1);
            tr.append(td2);

            $('#info_table .organizer_tr').after(tr);

        }

        // обновление строки органайзера
        function updateOrganizerRow( organizerId, time, comment, type ){

            // иконка колокольчика
            var bell = $('<i />');
            $(bell).attr( 'class', 'glyphicon glyphicon-bell bell_icon');

            // строка органайзера с данными
            var tr = $('tr[organizer_id='+organizerId+']');
            tr.empty();

            // столбцы таблицы
            var td1 = $('<td />');
            var td2 = $('<td />');

            // добавляем класс к ячейке органайзера
            td1.addClass('organizerTime');



            // кнопка удаления  итема
            var dellItem = $('<button />');

            // оформление кнопки удаления итема
            dellItem.attr( 'type', 'button');
            dellItem.attr( 'class', 'btn btn-danger btn-xs');
            dellItem.css( 'float', 'left' );
            dellItem.css( 'margin-top', '5px' );

            dellItem.css( 'display', 'block' );
            dellItem.text('dell');

            // событие на нажатие кнопки
            $(dellItem).bind('click', function(){

                // путь к странице удаления итема
                var deleteReminder = '{{ route('agent.lead.deleteReminder', '') }}' + '/' + organizerId;

                // запрос на удаление
                $.get( deleteReminder, function( data ){

                    // при успешном запросе, строка удаляется из таблицы, на странце
                    if( data == true ){
                        tr.remove();
                    }
                });
            });


            // кнопка редактирования итема
            var editItem = $('<a />');

            // оформление кнопки редактирования итема
            editItem.attr( 'type', 'button');
            editItem.attr( 'class', 'btn btn-primary btn-xs dialog');
            editItem.css( 'float', 'left' );
            editItem.css( 'margin-top', '5px' );
            editItem.css( 'margin-left', '4px' );

            editItem.css( 'display', 'block' );
            editItem.text('edit');
            editItem.attr('href', '{{ route('agent.lead.editOrganizer', '') }}' + '/' + organizerId);

            // кнопка завершения
            var doneItem = $('<button />');

            // оформление кнопки завершения
            doneItem.attr( 'type', 'button');
            doneItem.attr( 'class', 'btn btn-success btn-xs');
            doneItem.css( 'float', 'left' );
            doneItem.css( 'margin-top', '5px' );
            doneItem.css( 'margin-left', '4px' );

            doneItem.css( 'display', 'block' );
            doneItem.text('done');

            // событие на нажатие кнопки
            $(doneItem).bind('click', function(){

            });

            td1.text( time );
            td2.text( comment );

            if( type == 2){
                td1.append(bell);
            }

            td2.append('<div class="button-wrap" style="display: none;"></div>');
            td2.find('.button-wrap').append(dellItem);
            td2.find('.button-wrap').append(editItem);

            if( type == 2 ) {
                td2.find('.button-wrap').append(doneItem);
            }

            tr.append(td1);
            tr.append(td2);

        }

        /*
        * Собитие наведения на строку органайзера
        */
        var organizedRow = '#info_table .organizedRow';
        $(document).on('mouseover', organizedRow,function () {
            $(this).find('.button-wrap').show();
        });
        $(document).on('mouseout', organizedRow, function () {
            $(this).find('.button-wrap').hide();
        });




        /**
         * Событие на клик на строку таблицы
         *
         * выводит таблицу с боку с подробными данными о лиде
         *
         * событие привязывается к каждой ячейке отдельно, а не ко всей строке
         * чтобы таблица не выпрыгивала по каждому нажатию на выпадающий список (к примеру)
         *
         */

        // выбираем все ячейки таблицы кроме выпадающего меню и кнопки редактирования
        var openLeadsTable = $('table.openLeadsTable tbody tr td').not( ".select_cell,.edit " );

        // привязываем функцию на клик, которая будет прорисовывать таблицу
        openLeadsTable.bind( 'click', function(){

            // id лида, данные которого нужно ввести в таблицу
            var id = $(this).parent().attr('lead_id');

            // отрисовываем таблицу
            reloadTable(id);
        });


        $(function(){


            /**
             * Отключаем опции в селекте (используется код с сервера)
             *
             * данные приходят с сервера, может когда то и понадобятся
             *
             */

            function disabledOptionFromServer(){

                $.each( $('.select_cell'), function( k, cell ){

                    // номера опций которые нужно заблокировать
                    var disabled_data = $(cell).find('select').attr('disabled_opt').split(',');


                    $.each( disabled_data, function( k, disabled ){

                        $(cell).find('li[data-val="' + disabled + '"]')
                                .attr( 'data-disabled', 'true')
                                .addClass('disabled');
                    });
                });
            }



            /**
             * Делает опции выпадающего меню на странице openLeads недоступными (отрабатывает только на фронтенде)
             *
             * перебирает все опции в селекте и останавливается только дойдя до активной опции
             *
             */

            function disabledSelectOption(){

                // выбираем все ячейки с селектом в таблице
                $.each( $('.select_cell'), function( k, cell ){

                    // перебираем все опции в ячейке
                    $.each( $(cell).find('li'), function( k, li ){

                        // если доходим до активного класса - останавливаемся
                        if( $(li).hasClass( 'selectboxit-selected' ) ){
                            return false;

                        // если опция находится до активного класса - делаем ее недоступной
                        }else{
                            $(li).attr( 'data-disabled', 'true').addClass('disabled');
                        }
                    });
                });
            }


            // делаем опции, которые находятся до активной опции - недоступными
            disabledSelectOption();


            /** реакция на изменение выпадающего списка на openLeads */
            $('.select_cell').change(function(){


                // получаем выбранное значение из списка
                var selectData = $(this).find('.selectboxit-text').attr('data-val');

                // получение id лида
                var lead_id = $(this).parent().attr('lead_id');

                // получение токена
                var token = $('meta[name=csrf-token]').attr('content');

                // получение selectboxit
                var selectBox = $(this).find('select').data("selectBox-selectBoxIt");

                var self = $(this);


                // событие на клик, по кнопке "Chenge status" (изменение статуса)
                $( '#statusModalChange' ).bind( 'click', function(){

                    // спрятать модальное окно
                    $('#statusModal').modal('hide');


                    // изменяем статусы на сервере
                    $.post('{{  route('agent.lead.setOpenLeadStatus') }}', { 'status': selectData, 'lead_id': lead_id, '_token': token}, function( data ){

                        // если статус изменен нормально
                        if( data == 'statusChanged'){

                            // делаем статусы неактивными до выбранного
                            $.each( self.find('li'), function( k, li ){

                                // если доходим до активного класса - останавливаемся
                                if( $(li).hasClass( 'selectboxit-focus' ) ){
                                    return false;

                                    // если опция находится до активного класса - делаем ее недоступной
                                }else{
                                    $(li).attr( 'data-disabled', 'true' ).addClass('disabled');
                                }
                            });

                        }else{

                            // todo вывести какое то сообщение об ошибке на сервере
                            alert( 'ошибки на сервере' );
                        }

                    });


                });

                // событие на нажатие кнопки Cancel на модальном окне
                $( '#statusModalCancel').bind( 'click', function(){

                    // выбераем первый активный статус
                    $.each( self.find('li'), function (k, li) {

                        // если обьект selectboxit не равен NULL
                        if( selectBox != null ) {

                            // если текущий элемент активный - выбираем его и останавливаемся
                            if( !$(li).hasClass('disabled') && $(li).attr('data-disabled') == 'false' ) {
                                selectBox.selectOption(k);
                                selectBox = null;
                                return false;
                            }

                        }

                    });

                    // сбрасываем значения переменных к NULL
                    // чтоб не подхватились другим селектом
                    selectData = lead_id = token = selectBox = self = null;

                    // отключаем события клика по кнопкам отмены и сабмита
                    $('#statusModalChange').unbind('click');
                    $('#statusModalCancel').unbind('click');

                });

                // появление модального окна
                $('#statusModal').modal();

            });

        });

    </script>
@endsection

