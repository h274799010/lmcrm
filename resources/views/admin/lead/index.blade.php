@extends('admin.layouts.default')

{{-- Web site Title --}}
@section('title') {!! trans("admin/agent.agents") !!} :: @parent
@stop

{{-- Content --}}
@section('main')
    <div class="page-header">
        <h3>
            {!! trans("admin/agent.agents") !!}
            <div class="pull-right flip">
                <a href="{!! route('admin.agent.create') !!}"
                   class="btn btn-sm  btn-primary"><span
                            class="glyphicon glyphicon-plus-sign"></span> {{
                                trans("admin/modal.new") }}</a>
            </div>
        </h3>
    </div>
    <div class="row">
        <div class="col-md-12 col-xs-12" id="leadsListFilter">
            <div class="col-xs-2">
                <div class="form-group">
                    <label class="control-label _col-sm-2">{{ trans('admin/openLeads.filter.sphere') }}</label>
                    <select data-name="sphere" class="dataTables_filter form-control connectedFilter" id="spheresFilter" data-placeholder="-">
                        <option value=""></option>
                        @foreach($spheres as $sphere)
                            <option value="{{ $sphere->id }}">{{ $sphere->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-xs-2">
                <div class="form-group">
                    <label class="control-label _col-sm-2">{{ trans('admin/openLeads.filter.accountManager') }}</label>
                    <select data-name="account_manager" class="dataTables_filter form-control connectedFilter" id="accountManagersFilter" data-placeholder="-">
                        <option value=""></option>
                        @foreach($accountManagers as $accountManager)
                            <option value="{{ $accountManager->id }}">{{ $accountManager->email }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-xs-2">
                <div class="form-group">
                    <label class="control-label _col-sm-2">{{ trans('admin/openLeads.filter.operator') }}</label>
                    <select data-name="operator" class="dataTables_filter form-control connectedFilter" id="operatorsFilter" data-placeholder="-">
                        <option value=""></option>
                        @foreach($operators as $operator)
                            <option value="{{ $operator->id }}">{{ $operator->email }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-xs-2">
                <div class="form-group">
                    <label class="control-label _col-sm-2">{{ trans('admin/openLeads.filter.agents') }}</label>
                    <select data-name="agent" class="dataTables_filter form-control connectedFilter" id="agentsFilter" data-placeholder="-">
                        <option value=""></option>
                        @foreach($agents as $agent)
                            <option value="{{ $agent->id }}">{{ $agent->email }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-xs-2">
                <div class="form-group">
                    <label class="control-label _col-sm-2">{{ trans('admin/openLeads.filter.agentType') }}</label>
                    <select data-name="role" class="dataTables_filter form-control" id="rolesFilter" data-placeholder="-">
                        <option value=""></option>
                        <option value="leadbayer">Lead bayer</option>
                        <option value="dealmaker">Deal maker</option>
                    </select>
                </div>
            </div>
            <div class="col-xs-2">
                <div class="form-group">
                    <label class="control-label _col-sm-2">{{ trans('admin/openLeads.filter.period') }}</label><br>
                    <input type="text" name="reportrange" data-name="period" class="mdl-textfield__input dataTables_filter" value="" id="reportrange" />
                </div>
            </div>
        </div>
    </div>
    <table id="leadsTable" class="table table-striped table-hover table-filter">
        <thead>
        <tr>
            <th>{{ trans('admin/openLeads.table.name') }}</th>
            <th>{{ trans('admin/openLeads.table.phone') }}</th>
            <th>{{ trans('admin/openLeads.table.email') }}</th>
            <th>{{ trans('admin/openLeads.table.status') }}</th>
            <th>{{ trans('admin/openLeads.table.opened') }}</th>
            <th>{{ trans('admin/openLeads.table.depositor') }}</th>
            <th>{{ trans('admin/openLeads.table.expire_time') }}</th>
        </tr>
        </thead>
        <tbody></tbody>
    </table>
@stop

@section('styles')
    <style type="text/css">
        .status-list span {
            font-weight: bold;
            color: gray;
        }
        .status-list {
            list-style-type: none;
            padding: 0;
        }
    </style>
@endsection

{{-- Scripts --}}
@section('scripts')
    <script type="text/javascript">
        // Генерация наполнения для select-ов фильтров
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

            // Изменение связанного фильтра
            $(document).on('change', '.connectedFilter', function () {
                var $this = $(this);

                // Связанные фильтры
                var $spheresFilter = $('#spheresFilter'),
                    $accountManagersFilter = $('#accountManagersFilter'),
                    $operatorsFilter = $('#operatorsFilter'),
                    $agentsFilter = $('#agentsFilter');

                // Строка параметров запроса
                var params = '_token={{ csrf_token() }}&type='+$this.data('name')+'&id='+$this.val();
                params += '&sphere_id='+$spheresFilter.val();
                params += '&accountManager_id='+$accountManagersFilter.val();
                params += '&operator_id='+$operatorsFilter.val();
                params += '&agent_id='+$agentsFilter.val();

                // Отправляем запрос на сервер для получение данных для связанных фильтров
                $.post('{{ route('admin.lead.getFilter') }}', params, function (data) {
                    // Пробегаемся по полученым данным
                    $.each(data, function (i, el) {
                        var tmpObj = null;
                        // Ищем фильтр
                        switch (i) {
                            case 'spheres':
                                tmpObj = $spheresFilter;
                                break;
                            case 'accountManagers':
                                tmpObj = $accountManagersFilter;
                                break;
                            case 'operators':
                                tmpObj = $operatorsFilter;
                                break;
                            case 'agents':
                                tmpObj = $agentsFilter;
                                break;
                        }

                        // Вставляем новые данные в фильтр
                        var options = prepareHTMLForFilter(el, tmpObj.val());
                        tmpObj.html(options);
                    });
                })
            });

            var leadsTable;
            var $container = $('#leadsListFilter');

            leadsTable = $('#leadsTable').DataTable({
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
                columns: [
                    {data: 'name', name: 'name'},
                    {data: 'phone', name: 'phone'},
                    {data: 'email', name: 'email'},
                    {data: 'status', name: 'status'},
                    {data: 'opened', name: 'opened'},
                    {data: 'depositor', name: 'depositor'},
                    {data: 'expiry_time', name: 'expiry_time'}
                ],
                "ajax": {
                    "url": "/admin/{{ $type }}/data",
                    "data": function (d) {

                        // переменная с данными по фильтру
                        var filter = {};

                        // перебираем фильтры и выбираем данные по ним
                        $container.find(':input.dataTables_filter').each(function () {

                            // если есть name и нет js
                            if ($(this).data('name') && $(this).data('js') != 1) {

                                // заносим в фильтр данные с именем name и значением опции
                                filter[$(this).data('name')] = $(this).val();
                            }
                        });

                        // данные фильтра
                        d['filter'] = filter;
                    }
                },
                "fnDrawCallback": function (oSettings) {
                    $(".iframe").colorbox({
                        iframe: true,
                        width: "80%",
                        height: "80%",
                        onClosed: function () {
                            leadsTable.ajax.reload();
                        }
                    });
                }
            });

            // обработка фильтров таблицы при изменении селекта
            $container.find(':input.dataTables_filter').change(function () {
                leadsTable.ajax.reload();
            });
        });



        $(function() {

            var start = moment().startOf('month');
            var end = moment().endOf('month');

            function cb(start, end) {
                $('#reportrange').val(start.format('YYYY-MM-DD') + ' / ' + end.format('YYYY-MM-DD')).trigger('change');
            }

            $('#reportrange').daterangepicker({
                autoUpdateInput: false,
                startDate: start,
                endDate: end,
                opens: "left",
                locale: {
                    cancelLabel: 'Clear'
                },
                ranges: {
                    'Today': [moment(), moment()],
                    'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'This week': [moment().startOf('week'), moment()],
                    'Previous week': [moment().subtract(1, 'weeks').startOf('week'), moment().subtract(1, 'weeks').endOf('week')],
                    'This month': [moment().startOf('month'), moment().endOf('month')],
                    'Previous month': [moment().subtract(1, 'months').startOf('month'), moment().subtract(1, 'months').endOf('month')]
                }
            }, cb);

            cb(start, end);

            $('#reportrange').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('').trigger('change');
            });

        });
    </script>
@stop
