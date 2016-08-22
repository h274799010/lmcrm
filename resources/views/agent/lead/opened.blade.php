@extends('layouts.master')

@section('content')
        <!-- Page Content -->
                <div class="row">
                    <div id="main_table" class="col-md-10">

                        {{--<table class="table table-bordered table-striped table-hover openLeadsTable">--}}
                        <table class="table table-bordered table-striped table-hover openLeadsTable">
                            <thead>
                                <tr>
                                    <th>icon </th>
                                    <th>status </th>
                                    <th>date </th>
                                    <th>name </th>
                                    <th>phone </th>
                                    <th>email </th>
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
                                    <td class="edit">
                                        <div>
                                            <a href="{{ route('agent.lead.showOpenedLead',$data->id) }}">
                                                <img src="/assets/web/icons/list-edit.png" class="_icon pull-left flip">
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>

                    </div>

                    <div id="info_table_block" class="col-md-3">

                        <table id="info_table" class="table table-bordered table-striped table-hover"  cellspacing="0" width="100%" style="display: none;">
                        </table>


                    </div>
                    <!-- /.col-lg-10 -->
                </div>
                <!-- /.row -->
            <!-- /.container -->
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

    </style>
@endsection


@section('scripts')
    <script>


        /** загрузка дополнительной таблицы с подробной информацией лида */

        function reloadTable(id){

            // возвращаем всем строкам дефолтный цвет
            $('tr[lead_id]').removeClass('selected_row');

            // если таблица с таким id уже существует
            if($('#info_table_block').attr('lead_id')==id){

                // увеличиваем основную таблицу на полную ширину
                $('#main_table').attr('class', 'col-md-10');
                // выставляем индекс дополнительной таблицы в 0
                $('#info_table_block').attr('lead_id', 0);
                // удаляем содержимое таблицы
                $('#info_table tbody').remove();

                // если таблицы нет или индекс таблицы другой
            }else {
                // создание таблицы

                // выделяем активную строку цветом
                $('tr[lead_id='+id+']').addClass('selected_row');

                // показываем таблицу
                $('#info_table').show();
//                if (typeof(info_table) == 'object') {
//                    info_table.destroy();
//                }
                $('#info_table tbody').remove();

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

                        infoTable.append(tr);

                    } );



                    var organizer = $('<tr />');

                    var organizerTitle = $('<td />');
                    var organizerTime = $('<td />');
                    var organizerComments = $('<td />');



                    organizerTitle.attr('colspan', 2);
                    organizerTitle.attr('rowspan', 8);

                    organizerTitle.text('Organizer');
                    organizerTime.text('Time');
                    organizerComments.text('Comments');

                    organizer.append(organizerTitle);

                    organizer.append(organizerTime);
                    organizer.append(organizerComments);


                    infoTable.append(organizer);


                    $.each( tableData['organizer'], function(  k, data ){

                        var tr = $('<tr />');


                        var td1 = $('<td />');
                        var td2 = $('<td />');

                        td1.addClass('organizerTime')

                        td1.text( data[0] );
                        td2.text( data[1] );


                        tr.append(td1);
                        tr.append(td2);

                        infoTable.append(tr);


                    } );

                });


                // присваиваем таблице индекс лида
                $('#info_table_block').attr('lead_id', id);
            }
        }


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
             * данные приходят с сервера, может когда то и понядобятся
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

                var lead_id = $(this).parent().attr('lead_id'); // todo тут должен быть id лида

                // получение токена
                var token = $('meta[name=csrf-token]').attr('content');

                // делаем статусы неактивными до выбранного
                $.each( $(this).find('li'), function( k, li ){

                    // если доходим до активного класса - останавливаемся
                    if( $(li).hasClass( 'selectboxit-focus' ) ){
                        return false;

                        // если опция находится до активного класса - делаем ее недоступной
                    }else{
                        $(li).attr( 'data-disabled', 'true').addClass('disabled');
                    }
                });


                // изменяем статусы на сервере
                $.post('{{  route('agent.lead.setOpenLeadStatus') }}', { 'status': selectData, 'lead_id': lead_id, '_token': token });


                // todo отмечать запросы на фронте, только если на сервере запрос будет успешным

            });




        });



    </script>
@endsection

