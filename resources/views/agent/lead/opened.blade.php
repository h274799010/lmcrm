@extends('layouts.master')

@section('content')
        <!-- Page Content -->
                <div class="row">
                    <div id="main_table" class="col-md-10">
                        {{--<h1 class="page-header">@lang('site/sidebar.lead_opened')</h1>--}}
                        <table class="table table-bordered table-striped table-hover dataTable">
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
                                    <td></td>
                                    <td> в разработке </td>
                                    <td>{{ $data->date }}</td>
                                    <td>{{ $data->name }}</td>
                                    <td>{{ $data->phone->phone }}</td>
                                    <td>{{ $data->email }}</td>
                                    <td>
                                        <a href="{{ route('agent.lead.showOpenedLead',$data->id) }}">
                                            <img src="/assets/web/icons/list-edit.png" class="_icon pull-left flip">
                                        </a>
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

    </script>
@endsection