@extends('admin.layouts.default')

{{-- Web site Title --}}
@section('title') {!! trans("admin/agent.agents") !!} :: @parent
@stop

{{-- Content --}}
@section('main')
    <div class="page-header">
        <h3>
            {!! trans("admin/agent.agents") !!}
        </h3>
    </div>
    <div class="row">
        <div class="col-md-6 col-xs-12" id="agentsListFilter">
            <div class="col-xs-4">
                <div class="form-group">
                    <label class="control-label _col-sm-3">Spheres</label>
                    <select data-name="sphere" class="selectbox dataTables_filter form-control connectedFilter" data-type="sphere" data-target="#accountManagerFilter" id="sphereFilter" data-placeholder="-">
                        <option value=""></option>
                        @foreach($spheres as $sphere)
                            <option value="{{ $sphere->id }}">{{ $sphere->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-xs-4">
                <div class="form-group">
                    <label class="control-label _col-sm-3">Account manager</label>
                    <select data-name="accountManager" class="selectbox dataTables_filter form-control connectedFilter" data-type="accountManager" data-target="#sphereFilter" id="accountManagerFilter" data-placeholder="-">
                        <option value=""></option>
                        @foreach($accountManagers as $accountManager)
                            <option value="{{ $accountManager->id }}">{{ $accountManager->email }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-xs-4">
                <div class="form-group">
                    <label class="control-label _col-sm-2">Roles</label>
                    <select data-name="role" class="selectbox dataTables_filter form-control" data-placeholder="-">
                        <option value=""></option>
                        <option value="dealmaker">Dealmaker</option>
                        <option value="leadbayer">Leadbayer</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
    <table id="statisticAgentTable" class="table table-striped table-hover table-filter">
        <thead>
        <tr>
            <th>{!! trans("admin/users.name") !!}</th>
            {{--<th>{!! trans("admin/users.email") !!}</th>--}}
            {{--<th>{!! trans("admin/admin.created_at") !!}</th>--}}
            <th>{!! trans("admin/admin.role") !!}</th>
            <th>Sphere</th>
            <th>Account manager</th>
            <th>{!! trans("admin/admin.action") !!}</th>
        </tr>
        </thead>
        <tbody></tbody>
    </table>
@stop

{{-- Scripts --}}
@section('scripts')
    <script type="text/javascript">
        function prepareHTMLForFilter(data, selected) {
            var options = '<option value=""></option>';

            $.each(data, function (i, el) {
                if(el.id == selected) {
                    options += '<option value="'+el.id+'" selected="selected">'+el.name+'</option>';
                } else {
                    options += '<option value="'+el.id+'">'+el.name+'</option>';
                }
            });

            return options;
        }
        $(document).ready(function () {
            $('select').select2({
                allowClear: true
            });
            $(document).on('change', '.connectedFilter', function () {
                var $this = $(this);

                var $sphereFilter = $('#sphereFilter'),
                    $accountManagerFilter = $('#accountManagerFilter');

                var params = '_token={{ csrf_token() }}&type='+$this.data('name')+'&id='+$this.val();
                params += '&sphere_id='+$sphereFilter.val();
                params += '&accountManager_id='+$accountManagerFilter.val();

                $.post('{{ route('admin.statistic.getFilterAgent') }}', params, function (data) {
                    $.each(data, function (i, el) {
                        var tmpObj = null;
                        switch (i) {
                            case 'spheres':
                                tmpObj = $sphereFilter;
                                break;
                            case 'accountManagers':
                                tmpObj = $accountManagerFilter;
                                break;
                        }

                        var options = prepareHTMLForFilter(el, tmpObj.val());
                        tmpObj.html(options);
                    });
                })
            });

            var oTable;
            var $container = $('#agentsListFilter');

            oTable = $('#statisticAgentTable').DataTable({
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
                    "url": "/admin/statistic/agentsData",
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
