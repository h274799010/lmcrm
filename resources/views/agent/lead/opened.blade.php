@extends('layouts.master')

@section('content')
        <!-- Page Content -->
            <div class="container">
                <div class="row">
                    <div class="col-md-10">
                        {{--<h1 class="page-header">@lang('site/sidebar.lead_opened')</h1>--}}
                        <table class="table table-bordered table-striped table-hover dataTable">
                            <thead>
                                <tr>
                                    <th>icon </th>
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
                                    <td>{{ $data->date }}</td>
                                    <td>{{ $data->name }}</td>
                                    <td>{{ $data->phone->phone }}</td>
                                    <td>{{ $data->email }}</td>
                                    <td>
                                        <a href="{{ route('agent.lead.showOpenedLead',$data->id) }}">
                                            <img src="/public/icons/list-edit.png" class="_icon pull-left flip">
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        <table id="table" class="display table table-bordered" cellspacing="0" width="100%" style="display: none;">
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th></th>
                                    </tr>
                                </thead>
                            </table>
                            <tbody>
                                <tr>
                                    <th data-field="first"></th>
                                    <td data-field="second"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <!-- /.col-lg-10 -->
                </div>
                <!-- /.row -->
            </div>
            <!-- /.container -->
    </div>
    <script>
    var table;
    function reloadTable(id){
        $('#table').show();
        if (typeof(table)=='object')
            table.destroy();
        table = $('#table').DataTable( {
            "ajax": '{{ route('agent.openedLeadsAjax')  }}?id='+id
        } );
    }
    </script>
@endsection