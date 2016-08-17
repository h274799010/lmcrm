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
                                <tr onclick="reloadTable({{ $data->id }})">
                                    <td><div></div></td>
                                    <td class="select_cell"> {{ Form::select('status', $data->sphereStatuses->statuses->lists('stepname', 'id'), $data->openLeadStatus->status, ['class'=>'form']) }} </td>
                                    <td><div>{{ $data->date }}</div></td>
                                    <td><div>{{ $data->name }}</div></td>
                                    <td><div>{{ $data->phone->phone }}</div></td>
                                    <td><div>{{ $data->email }}</div></td>
                                    <td>
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
            margin: 0;
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


    </style>
@endsection


@section('scripts')
    <script>

        var table;
        function reloadTable(id){


            if($('#info_table').attr('lead_id')==id){

                $('#main_table').attr('class', 'col-md-10');
                $('#info_table').attr('lead_id', 0);
                $('#table tbody').remove();

            }else {

                $('#table').show();
                if (typeof(table) == 'object') {
                    table.destroy();
                }

                $('#main_table').attr('class', 'col-md-8');

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

//        $('document').find('span.selectboxit-container.selectboxit-container').css('height', '300px');

//        $('span.selectboxit-container.selectboxit-container').css('height', '300px');

//           var a = $('span.selectboxit-container.selectboxit-container').css('width');
//
//        alert(a);


    </script>
@endsection

