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
                                        {{ Form::select('status', $data->sphereStatuses->statuses->lists('stepname', 'id'), $data->openLeadStatus->status, [ 'class'=>'form', 'disabled_opt'=>'4,5,6' ]) }}
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

                    <div id="info_table" class="col-md-3">


                        <table id="table" class="display table table-bordered table-hover info_table" cellspacing="0" width="100%" style="display: none;">
                                <thead style="display: none">
                                    <tr>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                    </tr>
                                </thead>
                            </table>
                            <tbody>
                            </tbody>
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
            /*border: solid 1px #ED5056;*/
        }



        .form{
            width: 100% !important;
            height: 100% !important;
            border-radius: 0 !important;
            border: none;
        }



        /*длина контейнера*/
        .selectboxit-container.selectboxit-container{
            width: 100%;
            /*height: 100% !important;*/
            border: solid 1px #ED5056;

        }

        .selectboxit-container.selectboxit-container .selectboxit-text{
            color: #ED5056;
            margin: 4px;
        }



        .selectboxit-container.selectboxit-container .selectboxit-arrow-container{
            width: 16px;
            height: 100%;
            background: #ED5056;
        }

        .selectboxit-container.selectboxit-container .selectboxit-arrow-container i{
            font-size: 11px !important;
        }


        .selectboxit-container.selectboxit-container .selectboxit-option-icon-container{
            margin: 0;
        }


        .selectboxit-container.selectboxit-container .selectboxit-option-icon-container i{
            border: none;
            background: none;
        }


        .selectboxit-container.selectboxit-container ul{
            min-width: 150px !important;
        }

        table tbody tr.selected_row{
            background: lightblue !important;
        }


        td.select_cell ul li.disabled{
            background: lightgray;
        }

        td.select_cell ul li.disabled a{
            color: grey;
        }

        /*td.select_cell ul li.selectboxit-selected{*/
            /*background: red;*/
        /*}*/

        /*td.select_cell ul li.selectboxit-selected a{*/
            /*color: white;*/
            /*background: red !important;*/
        /*}*/

        td.select_cell ul li.selectboxit-focus a {
            background: #ED5056 !important;
        }

        /*td.select_cell ul li.selectboxit-selected a:hover{*/
            /*background: red !important;*/
        /*}*/

        /*td.select_cell ul li.selectboxit-selected:hover{*/
            /*background: red;*/
        /*}*/


    </style>
@endsection


@section('scripts')
    <script>


        /** загрузка дополнительной таблицы с подробной информацией лида */

        var table;
        function reloadTable(id){

            // возвращаем всем строкам дефолтный цвет
            $('tr[lead_id]').removeClass('selected_row');

            if($('#info_table').attr('lead_id')==id){

                $('#main_table').attr('class', 'col-md-10');
                $('#info_table').attr('lead_id', 0);
                $('#table tbody').remove();

            }else {

                // выделяем активную строку цветом
                $('tr[lead_id='+id+']').addClass('selected_row');


                $('#table').show();
                if (typeof(table) == 'object') {
                    table.destroy();
                }

                $('#main_table').attr('class', 'col-md-8');


                // todo сделать обычной таблицей

                table = $('#table').DataTable({
                    bInfo: false,
                    bFilter: false,
                    "iDisplayLength": 200,
                    "dom": '<"top"i>rt<"clear">',
                    "ajax": '{{ route('agent.openedLeadsAjax')  }}?id=' + id,
                    "fnRowCallback": function (nRow, aData, iDisplayIndex, iDisplayIndexFull) {

                        $('td', nRow).first().attr('class', 'hidden');

                        $('td+td', nRow).first().css('background-color', '#63A4B8');
                        $('td+td', nRow).first().css('color', 'white');
                        $('td+td', nRow).first().css('font-weight', 'bold');
                    }

                });

                $('#info_table').attr('lead_id', id);
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
             * Отключаем опции в селекте
             *
             *
             */

            var select_cell = $('.select_cell');


            $.each( select_cell, function( k, cell ){

                // номера опций которые нужно заблокировать
                var disabled_data = $(cell).find('select').attr('disabled_opt').split(',');


                $.each( disabled_data, function( k, disabled ){

                    $(cell).find('li[data-val="' + disabled + '"]')
                            .attr( 'data-disabled', 'true')
                            .addClass('disabled');

//                    $('tr[lead_id]').removeClass('selected_row');
//                    $('tr[lead_id]').addClass('selected_row');

                });

            });

        });



    </script>
@endsection

