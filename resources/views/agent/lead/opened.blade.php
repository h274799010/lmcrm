@extends('layouts.master')

@section('content')
<!-- Page Content -->
<div class="row">
    <div id="main_table" class="col-md-12">

        <table class="table table-bordered table-striped table-hover openLeadsTable">
            <thead>
                <tr>
                    <th>{{ trans("site/lead.opened.icon") }}</th>
                    <th>{{ trans('site/lead.opened.status') }}</th>
                    <th>{{ trans('site/lead.opened.name') }}</th>
                    <th>{{ trans('site/lead.opened.phone') }}</th>
                    <th>{{ trans('site/lead.opened.email') }}</th>
                    <th>{{ trans('site/lead.opened.maskname') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            @foreach ($openLeads as $openLead)
                <tr lead_id="{{ $openLead['lead']['id'] }}"  opened_Lead_Id="{{ $openLead['id'] }}">
                    <td><div></div></td>
                    <td class="select_cell">

                        {{-- Проверка на наличие статусов у сферы --}}

                        {{--Если у сферы нет статусов, значить сфера удаленна--}}
                        @if($openLead['lead']['sphereStatuses'])
                            {{-- если статусы есть --}}

                            {{-- Если лид был отмечен как плохой --}}
                            @if( $openLead->state == 1 || ($openLead['lead']['status'] == 5) )
                                @lang('agent/openLeads.bad_lead')
                            {{-- впротивном случае вывод select со статусами --}}
                            @elseif( $openLead->state == 2 )
                                @lang('site/lead.deal_closed')
                            @else

                                <select name="status" class="form">
                                    @if( $openLead->status == 0 )
                                        <option selected="selected" class="emptyOption"></option>
                                    @endif
                                    @if( (time() < strtotime($openLead['expiration_time'])) && ($openLead->status == 0) )
                                        <option value="bad" class="badOption">bad lead</option>
                                    @endif
                                    @foreach($openLead['lead']->sphereStatuses->statuses as $status)
                                        <option value="{{ $status->id }}" @if($openLead->status == $status->id) selected="selected"@endif>{{ $status->stepname }}</option>
                                    @endforeach
                                        <option value="closing_deal">{{ trans('site/lead.closing_deal') }}</option>
                                </select>
                            @endif
                        @else
                            {{-- если статусов нет --}}

                            <div class="sphere_deleted">@lang('agent/openLeads.sphere_deleted')</div>

                        @endif
                        {{-- Конец проверки на наличие статусов у сферы --}}

                    </td>
                    <td><div>{{ $openLead['lead']['name'] }}</div></td>
                    <td><div>{{ $openLead['lead']['phone']->phone }}</div></td>
                    <td><div>{{ $openLead['lead']['email'] }}</div></td>
                    <td>
                        @if($openLead['mask_id']==0)
                            <div class="from_agent">from agent</div>
                        @elseif($openLead->maskName2)
                            <div> {{ $openLead->maskName2->name }}</div>
                        @else
                            <div class="mask_deleted">@lang('agent/openLeads.mask_deleted')</div>
                        @endif
                    </td>
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
                    {{ trans("site/lead.opened.organizer.title") }}
                </td>
                <td class="organizer_time_title">
                    {{ trans("site/lead.opened.organizer.time") }}
                </td>
                <td class="organizer_comments_title">
                    <div>
                        {{ trans("site/lead.opened.organizer.comments") }}
                    </div>
                    <span class="dropdown">
                        <a class="dropdown-toggle" aria-expanded="true" role="button" data-toggle="dropdown" href="#">
                            <i class="glyphicon glyphicon-plus"></i>
                        </a>

                        <ul class="dropdown-menu myDropDown" role="menu">
                            <li> <a id="commentHref" class="dialog" href="">{{ trans("site/lead.opened.organizer.button.comment") }}</a> </li>
                            <li> <a id="reminderHref" class="dialog" href="">{{ trans("site/lead.opened.organizer.button.reminder") }}</a> </li>
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
                     {{ trans("site/lead.opened.modal.head") }}
                </h4>
            </div>

            <div class="modal-body">

                {{ trans("site/lead.opened.modal.body") }}

            </div>

            <div class="modal-footer">

                <button id="statusModalCancel" type="button" class="btn btn-default" data-dismiss="modal">
                    {{ trans("site/lead.opened.modal.button.Cancel") }}
                </button>

                <button id="statusModalChange" type="button" class="btn btn-danger">
                    {{ trans("site/lead.opened.modal.button.OK") }}
                </button>
            </div>


        </div>
    </div>
</div>

<div id="checkModal" class="modal fade bs-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="exampleModalLabel">
                     {{ trans("site/lead.opened.modal.head") }}
                </h4>
            </div>

            <div class="modal-body">

                {{--{{ trans("site/lead.opened.modal.body") }}--}}

                {{ Form::open(array('route' => ['agent.lead.setOpenLeadStatus'], 'method' => 'post', 'class'=>'ajax-form validate pick-check-form', 'files'=> true)) }}
                <input type="hidden" name="open_lead_id" value="">
                <input type="hidden" name="status" value="">
                <input type="hidden" name="lead_id" value="">
                <div class="form-group  {{ $errors->has('price') ? 'has-error' : '' }}">
                    <div class="controls">
                        {{ Form::text('price', null, array('class' => 'form-control','placeholder'=>'price','required'=>'required','data-rule-minLength'=>'2')) }}
                    </div>
                </div>

                <div class="form-group  {{ $errors->has('comment') ? 'has-error' : '' }}">
                    <div id="uploadProgress"></div>
                </div>
                <div class="form-group  {{ $errors->has('comment') ? 'has-error' : '' }}">
                    <div class="controls">
                        <div id="addCheckBtn" class="btn btn-success">Add file</div>
                    </div>
                </div>
                {{ Form::close() }}

            </div>

            <div class="modal-footer">

                <button id="checkModalCancel" type="button" class="btn btn-default" data-dismiss="modal">
                    {{ trans("site/lead.opened.modal.button.Cancel") }}
                </button>

                <button id="checkModalChange" type="button" class="btn btn-danger disabled" disabled="disabled">
                    {{ trans("site/lead.opened.modal.button.OK") }}
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
            vertical-align: middle;
            text-align: center;
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
        .pick-check-form:after, .pick-check-form .form-group:after {
            content: " ";
            display: block;
            clear: both;
        }
        .file-name {}
        .upload-progress {
            width: 100%;
            margin-top: 6px;
            background-color: #777777;
            padding: 3px 0;
            position: relative;
        }
        .upload-progress .upload-status {
            display: block;
            width: 0;
            background-color: #5cb85c;
            border: 1px solid #4cae4c;
            height: 100%;
            position: absolute;
            left: 0;
            top: 0;
            z-index: 1;
        }
        .upload-progress.danger .upload-status {
            background-color: #d9534f;
            border: 1px solid #d43f3a;
        }
        .upload-progress .upload-status-percent {
            color: #ffffff;
            text-align: center;
            width: 100%;
            font-weight: bold;
            position: relative;
            z-index: 2;
        }
        .file-container {
            margin-top: 16px;
        }
        .file-container:first-child {
            margin-top: 0;
        }

        table.dataTable.dtr-inline.collapsed > tbody > tr > td:first-child:before, table.dataTable.dtr-inline.collapsed > tbody > tr > th:first-child:before {
            display: none;
        }

        .from_agent{
            color: blue;
        }

    </style>
@endsection


@section('scripts')
    <script>

        $.extend( true, $.fn.dataTable.defaults, {
            "language": {
                "url": '{{ asset('components/datatables-plugins/i18n/'.LaravelLocalization::getCurrentLocaleName().'.lang') }}'
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
                $.post('{{ route('agent.lead.openedAjax')  }}', { 'id': id, '_token': token }, function( data ){

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
         * Событие наведения на строку органайзера
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


        $(function() {


            /**
             * Отключаем опции в селекте (используется код с сервера)
             *
             * данные приходят с сервера, может когда то и понадобятся
             *
             */

            function disabledOptionFromServer() {

                $.each($('.select_cell'), function (k, cell) {

                    // номера опций которые нужно заблокировать
                    var disabled_data = $(cell).find('select').attr('disabled_opt').split(',');


                    $.each(disabled_data, function (k, disabled) {

                        $(cell).find('li[data-val="' + disabled + '"]')
                            .attr('data-disabled', 'true')
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

            function disabledSelectOption() {

                // выбираем все ячейки с селектом в таблице
                $.each($('.select_cell'), function (k, cell) {

                    // перебираем все опции в ячейке
                    $.each($(cell).find('li'), function (k, li) {

                        // если доходим до активного класса - останавливаемся
                        if ($(li).hasClass('selectboxit-selected')) {
                            return false;

                            // если опция находится до активного класса - делаем ее недоступной
                        } else {
                            $(li).attr('data-disabled', 'true').addClass('disabled');
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

                // получение id лида
                var openedLeadId = $(this).parent().attr('opened_Lead_Id');

                // получение токена
                var token = $('meta[name=csrf-token]').attr('content');

                // получение selectboxit
                var selectBox = $(this).find('select').data("selectBox-selectBoxIt");

                var self = $(this);

                var status = $(this).find('option:selected').val();

                if(status == 'closing_deal') {
                    $('#checkModal').find('input[name=open_lead_id]').val(openedLeadId);

                    // событие на нажатие кнопки Cancel на модальном окне
                    $( '#checkModalCancel').bind( 'click', function(){
                        $('#checkModalChange').addClass('disabled').prop('disabled', true);

                        $('#checkModal form').find('input').val('');

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
                        selectData = lead_id = token = selectBox = self = status = null;

                        $('#uploadProgress').empty();
                        // отключаем события клика по кнопкам отмены и сабмита
                        $('#statusModalChange').unbind('click');
                        $('#checkModalCancel').unbind('click');

                    });

                    $( '#checkModalChange' ).bind( 'click', function(){

                        var price = $('#checkModal').find('input[name=price]').val();

                        $('#checkModal').find('input[name=price]').on('change', function () {
                            $(this).closest('.form-group').removeClass('has-error');
                        });

                        if(price == '' || price == undefined) {
                            $('#checkModal').find('input[name=price]').focus().closest('.form-group').addClass('has-error');
                        } else {
                            // спрятать модальное окно
                            $('#checkModal').modal('hide');

                            // изменяем статусы на сервере
                            $.post('{{  route('agent.lead.setOpenLeadStatus') }}', { 'status': selectData, 'openedLeadId': openedLeadId, 'lead_id': lead_id, 'price': price, '_token': token}, function( data ){

                                if(data == 'setClosingDealStatus') {
                                    self.closest('td').html('{{ trans('site/lead.deal_closed') }}');
                                }else{

                                    // todo вывести какое то сообщение об ошибке на сервере
                                    alert( 'ошибки на сервере' );
                                }

                                // сбрасываем значения переменных к NULL
                                // чтоб не подхватились другим селектом
                                selectData = lead_id = token = selectBox = self = null;

                                // отключаем события клика по кнопкам отмены и сабмита
                                $('#checkModalChange').unbind('click');
                                $('#checkModalCancel').unbind('click');

                            });
                        }
                    });

                    $('#checkModal').modal();
                }
                else if(status != '') {
                    // событие на клик, по кнопке "Change status" (изменение статуса)
                    $( '#statusModalChange' ).bind( 'click', function(){

                        // спрятать модальное окно
                        $('#statusModal').modal('hide');


                        // изменяем статусы на сервере
                        $.post('{{  route('agent.lead.setOpenLeadStatus') }}', { 'status': selectData, 'openedLeadId': openedLeadId, 'lead_id': lead_id, '_token': token}, function( data ){

                            // если статус изменен нормально
                            if( data == 'statusChanged'){


                                // удаление пустого поля
                                var emptyOption = self.find('option.emptyOption');
                                // если путое поле найдено
                                if(emptyOption.length > 0) {
                                    // удаляем его
                                    emptyOption.remove();

                                    // обновляем select
                                    selectBox.refresh();
                                }

                                // делаем статусы неактивными до выбранного
                                $.each( self.find('li'), function( k, li ){
                                    //console.log(li);
                                    // если доходим до активного класса - останавливаемся
                                    if( $(li).hasClass( 'selectboxit-focus' ) || ($(li).hasClass('selectboxit-selected') && emptyOption.length > 0) ){
                                        return false;

                                        // если опция находится до активного класса - делаем ее недоступной
                                    }else{
                                        $(li).attr( 'data-disabled', 'true' ).addClass('disabled');
                                    }
                                });

                                // если лид отмечен как плохой, убираем select
                            } else if(data == 'setBadStatus') {
                                self.closest('td').html('bad lead');
                            } else if(data == 'pendingTimeExpire') {
                                // Если время pending_time истекло - выводим сообщение об ошибке
                                bootbox.dialog({
                                    message: '{{ trans('site/lead.opened.pending_time_expired') }}',
                                    show: true
                                });

                                // и удаляем статус bad_lead из списка
                                var badOption = self.find('option.badOption');
                                // если путое поле найдено
                                if(badOption.length > 0) {
                                    // удаляем его
                                    badOption.remove();

                                    // обновляем select
                                    selectBox.refresh();
                                }
                            } else if(data == 'setClosingDealStatus') {
                                self.closest('td').html('{{ trans('site/lead.deal_closed') }}');
                            }else{

                                // todo вывести какое то сообщение об ошибке на сервере
                                alert( 'ошибки на сервере' );
                            }

                            // сбрасываем значения переменных к NULL
                            // чтоб не подхватились другим селектом
                            selectData = lead_id = token = selectBox = self = null;

                            // отключаем события клика по кнопкам отмены и сабмита
                            $('#statusModalChange').unbind('click');
                            $('#statusModalCancel').unbind('click');

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
                }

            });

        });

        var uploaderImages = new plupload.Uploader({
            runtimes : 'html5',

            browse_button : 'addCheckBtn',
            multi_selection: true,
            url : "{{ route('agent.lead.checkUpload') }}",

            multipart_params: {
                _token: $('meta[name=csrf-token]').attr('content'),
                open_lead_id: $('#checkModal').find('input[name=open_lead_id]').val()
            },

            filters : {
                max_file_size : '15mb',
                mime_types: [
                    {title : "Image files", extensions : "jpg,jpeg,png"}
                ]
            },

            init: {
                FilesAdded: function(up, files) {
                    $('#jsAjaxPreloader').show();

                    up.settings.multipart_params.open_lead_id = $('#checkModal').find('input[name=open_lead_id]').val();

                    $.each(files, function (i, file) {
                        var data = '';

                        data += '<div class="controls file-container">';
                        data += '<div id="checkName" class="file-name">'+file.name+'</div>';
                        data += '<div class="upload-progress">';
                        data += '<div id="uploadStatus_'+file.id+'" class="upload-status"></div>';
                        data += '<div id="uploadStatusPercent_'+file.id+'" class="upload-status-percent">Pleas wait...</div>';
                        data += '</div>';
                        data += '</div>';

                        $('#uploadProgress').append(data);

                        uploaderImages.start();
                    });
                },

                UploadProgress: function(up, file) {
                    $('#uploadStatus_'+file.id).css('width', file.percent + '%');
                    $('#uploadStatusPercent_'+file.id).html(file.percent + '%');
                },

                FileUploaded: function (up, file, res) {
                    $('#checkModalChange').removeClass('disabled').prop('disabled', false);

                    var data = $.parseJSON(res.response);
                    data = data.result;

                    if(data.success == false) {
                        $('#uploadStatusPercent_'+file.id).closest('.upload-progress').addClass('danger');
                    }

                    $('#uploadStatusPercent_'+file.id).html(data.message);

                },

                Error: function(up, err) {
                    alert("\nError #" + err.code + ": " + err.message);
                }
            }
        });

        uploaderImages.init();
    </script>
@endsection

