@extends('admin.layouts.default')

{{-- Web site Title --}}
@section('title') {!! trans("admin/agent.agents") !!} :: @parent
@stop

{{-- Content --}}
@section('main')
    <div class="breadcrumb-wrapper">
        <ul class="breadcrumb">
            <li><a href="/">LM CRM</a></li>
            {{--            <li><a href="{{ route('admin.statistic.agents') }}">Agents statistic</a></li>--}}
            <li class="active">Agents profit</li>
        </ul>
    </div>

    <div class="row">
        <div class="col-md-8 col-xs-12" id="agentCreditReportsFilter">
            <div class="form-group">
                <label class="control-label _col-sm-3">Spheres</label>
                <select data-name="sphere" class="selectbox dataTables_filter form-control connectedFilter" data-type="sphere" data-target="#accountManagerFilter" id="sphereFilter" data-placeholder="-">
                    <option value=""></option>
                    @foreach($spheres as $sphere)
                        <option value="{{ $sphere->id }}" @if($selectedFilters['sphere'] && $selectedFilters['sphere'] == $sphere->id) selected="selected" @endif >{{ $sphere->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="control-label _col-sm-3">Account manager</label>
                <select data-name="accountManager" class="selectbox dataTables_filter form-control connectedFilter" data-type="accountManager" data-target="#sphereFilter" id="accountManagerFilter" data-placeholder="-">
                    <option value=""></option>
                    @foreach($accManagers as $accountManager)
                        <option value="{{ $accountManager->id }}" @if($selectedFilters['accountManager'] && $selectedFilters['accountManager'] == $accountManager->id) selected="selected" @endif >{{ $accountManager->email }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="padding-top: 23px;">
                <button class="btn btn-sm btn-danger" id="resetFilters" style="margin-bottom: 0;">{{ trans('admin/admin.button.filter_reset') }}</button>
            </div>
        </div>
    </div>

    <table class="table table-striped table-hover" id="agentCreditReportsTable">
        <thead>
        <tr>
            <th class="center">Name</th>
            <th class="center">Spheres</th>
            <th class="center">Account managers</th>
            <th class="center">{!! trans("admin/admin.action") !!}</th>
        </tr>
        </thead>
        <tbody></tbody>
    </table>

@stop

@section('styles')
    <style type="text/css">
        .form-group {
            display: inline-block;
            max-width: 220px;
            vertical-align: bottom;
            margin-right: 8px;
        }
        .form-group:last-child {
            margin-right: 0;
        }
        .form-group label.control-label, label.control-label {
            display: block;
            margin-bottom: 6px;
        }
    </style>
@endsection

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

            $(document).on('click', '#resetFilters', function (e) {
                e.preventDefault();

                $('#agentCreditReportsFilter select').each(function (i, el) {
                    $(el).prop('selectedIndex', 0);
                    $(el).select2("destroy");

                    $(el).select2();
                });

                $('#agentCreditReportsFilter select:eq(0)').trigger('change');
            });

            $(document).on('change', '.connectedFilter', function () {
                var $this = $(this);

                var $sphereFilter = $('#sphereFilter'),
                    $accountManagerFilter = $('#accountManagerFilter');

                var params = '_token={{ csrf_token() }}&type='+$this.data('name')+'&id='+$this.val();
                params += '&sphere_id='+$sphereFilter.val();
                params += '&accountManager_id='+$accountManagerFilter.val();

                $.post('{{ route('admin.profit.getFilter') }}', params, function (data) {
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
            var $container = $('#agentCreditReportsFilter');

            oTable = $('#agentCreditReportsTable').DataTable({
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
                    "url": "{{ route('admin.profit.data') }}",
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
@endsection