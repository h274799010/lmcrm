@extends('admin.layouts.default')

{{-- Web site Title --}}
@section('title') Spheres :: @parent
@stop

{{-- Content --}}
@section('main')
    <div class="page-header">
        <h3>
            Spheres
        </h3>
    </div>
    <table id="statisticSphereTable" class="table table-striped table-hover">
        <thead>
        <tr>
            <th class="center">Name</th>
            <th class="center">Leads</th>
            <th class="center">Agents</th>
            <th class="center">Active agents</th>
            <th class="center">Date</th>
            <th class="center">{!! trans("admin/admin.action") !!}</th>
        </tr>
        </thead>
        <tbody></tbody>
    </table>
@stop

{{-- Scripts --}}
@section('scripts')
    <script type="text/javascript">
        $(document).ready(function () {
            var oTable;

            oTable = $('#statisticSphereTable').DataTable({
                "sDom": "<'row'<'col-md-6'l><'col-md-6'f>r>t<'row'<'col-md-6'i><'col-md-6'p>>",
                "sPaginationType": "bootstrap",
                "oLanguage": {
                    "sProcessing": "{{ trans('table.processing') }}",
                    "sLengthMenu": "{{ trans('table.showmenu') }}",
                    "sZeroRecords": "{{ trans('table.noresult') }}",
                    "sInfo": "{{ trans('table.show') }}",
                    "sEmptyTable": "{{ trans('table.emptytable') }}",
                    "sInfoEmpty": "{{ trans('table.view') }}",
                    "sInfoFiltered": "{{ trans('table.filter') }}",
                    "sInfoPostFix": "",
                    "sSearch": "{{ trans('table.search') }}:",
                    "sUrl": "",
                    "oPaginate": {
                        "sFirst": "{{ trans('table.start') }}",
                        "sPrevious": "{{ trans('table.prev') }}",
                        "sNext": "{{ trans('table.next') }}",
                        "sLast": "{{ trans('table.last') }}"
                    }
                },
                "processing": true,
                "serverSide": true,
                "ajax": {
                    "url": "/admin/statistic/spheresData"
                }
            });
        });
    </script>
@stop
