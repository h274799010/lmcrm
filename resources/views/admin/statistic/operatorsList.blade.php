@extends('admin.layouts.default')

{{-- Web site Title --}}
@section('title') Operators :: @parent
@stop

{{-- Content --}}
@section('main')


    <div class="breadcrumb-wrapper">
        <ul class="breadcrumb" style="margin-bottom: 5px;">
            <li><a href="/">LM CRM</a></li>
            <li class="active">Operator statistic</li>
            {{--<li class="active">Operator: {{ $statistic['user']['email'] }}</li>--}}
        </ul>
    </div>


    {{--<div class="page-header">--}}
        {{--<h3>--}}
            {{--Operators--}}
        {{--</h3>--}}
    {{--</div>--}}
    <div class="row">
        <div class="col-md-6 col-xs-12" id="operatorsListFilter">
            <div class="col-xs-4">
                <div class="form-group">
                    <label class="control-label _col-sm-3">@lang('statistic.spheres')</label>
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
                    <label class="control-label _col-sm-3">@lang('statistic.account_manager')</label>
                    <select data-name="accountManager" class="selectbox dataTables_filter form-control connectedFilter" data-type="accountManager" data-target="#sphereFilter" id="accountManagerFilter" data-placeholder="-">
                        <option value=""></option>
                        @foreach($accountManagers as $accountManager)
                            <option value="{{ $accountManager->id }}">{{ $accountManager->email }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>
    <table id="statisticOperatorTable" class="table table-striped table-hover">
        <thead>
        <tr>
            <th class="center">Name</th>
            <th class="center">Leads added</th>
            <th class="center">Leads for processing</th>
            <th class="center">Marked bad</th>
            <th class="center">Processed leads</th>

            <th class="center">Spheres</th>

            <th class="center">created date</th>
            <th class="center">{!! trans("admin/admin.action") !!}</th>
        </tr>
        </thead>
        <tbody>
            {{--@forelse($operators as $operator)
                <tr>
                    <td class="middle">{{ $operator['email'] }}</td>
                    <td class="center middle">{{ $operator['leadsAddedCount'] }}</td>
                    <td class="center middle">{{ $operator['leadsToEdit'] }}</td>
                    <td class="center middle">{{ $operator['marked_bad'] }}</td>
                    <td class="center middle">{{ $operator['leadsEdited'] }}</td>

                    <td class="middle">
                        @forelse($operator['sphere'] as $index=>$sphere )

                            @if( $index == 0)
                                {{ $sphere['name'] }}
                            @else
                                , {{ $sphere['name'] }}
                            @endif

                        @empty
                            No sphere
                        @endforelse
                    </td>

                    <td class="center middle">{{ $operator['created_at']->format('d/m/Y') }}</td>
                    <td>
                        <a class="btn btn-sm btn-success" title="Statistic" href="{{ route('admin.statistic.operator', ['id'=>$operator['id']]) }}">
                            Statistic
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7"></td>
                </tr>
            @endforelse--}}
        </tbody>
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

                $.post('{{ route('admin.statistic.getFilterOperator') }}', params, function (data) {
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
            var $container = $('#operatorsListFilter');

            oTable = $('#statisticOperatorTable').DataTable({
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
                    "url": "/admin/statistic/operatorsData",
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
