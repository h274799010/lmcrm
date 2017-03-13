@extends('admin.layouts.default')

{{-- Web site Title --}}
@section('title') {!! trans("admin/agent.agents") !!} :: @parent
@stop

{{-- Content --}}
@section('main')
    <div class="page-header">
        <h3>
            {!! trans("admin/accountManager.accountManagers") !!}
            <div class="pull-right flip">
                <a href="{!! route('admin.accountManager.create') !!}"
                   class="btn btn-sm  btn-primary"><span
                            class="glyphicon glyphicon-plus-sign"></span> {{
                                trans("admin/modal.new") }}</a>
            </div>
        </h3>
    </div>
    <div class="row">
        <div class="col-md-6 col-xs-12" id="accManagerListFilter">
            <div class="col-xs-4">
                <div class="form-group">
                    <label class="control-label _col-sm-3">@lang('statistic.spheres')</label><br>
                    <select data-name="sphere" class="selectbox dataTables_filter form-control connectedFilter" data-type="sphere" data-target="#accountManagerFilter" id="sphereFilter" data-placeholder="-">
                        <option value=""></option>
                        @foreach($spheres as $sphere)
                            <option value="{{ $sphere->id }}">{{ $sphere->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    <table id="accManagerList" class="table table-striped table-hover">
        <thead>
        <tr>
            <th>{!! trans("admin/users.name") !!}</th>
            <th>{!! trans("admin/users.email") !!}</th>
            <th>{!! trans("admin/admin.created_at") !!}</th>
            <th>{!! trans("admin/admin.action") !!}</th>
        </tr>
        </thead>
        <tbody></tbody>
    </table>
@stop

{{-- Scripts --}}
@section('scripts')
    <script type="text/javascript">
        $(document).ready(function () {
            $('select').select2({
                allowClear: true
            });

            var oTable;
            var $container = $('#accManagerListFilter');

            oTable = $('#accManagerList').DataTable({
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
                    "url": "/admin/accountManager/data",
                    "data": function (d) {

                        // переменная с данными по фильтру
                        var filter = {};

                        // перебираем фильтры и выбираем данные по ним
                        $container.find('select.dataTables_filter').each(function () {

                            // если есть name и нет js
                            if ($(this).data('name') && $(this).data('js') != 1) {

                                // заносим в фильтр данные с именем name и значением опции
                                filter[$(this).data('name')] = $(this).val();
                            }
                        });

                        // данные фильтра
                        d['filter'] = filter;
                    }
                }
            });

            // обработка фильтров таблицы при изменении селекта
            $container.find('select.dataTables_filter').change(function () {
                // просто перезагружаем таблицу
                oTable.ajax.reload();
            });
        });
    </script>
@stop
