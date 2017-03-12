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
    <div class="col-md-6 col-xs-12" id="accManagerListFilter">
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
    </div>
    <table id="statisticAccManagerTable" class="table table-striped table-hover">
        <thead>
        <tr>
            <th class="center">Name</th>
            <th class="center">Spheres</th>
            <th class="center">Agents</th>
            <th class="center">Date</th>
            <th class="center">{!! trans("admin/admin.action") !!}</th>
        </tr>
        </thead>
        <tbody>
        {{--@forelse($accountManagers as $manager)
            <tr>
                <td>{{ $manager['email'] }}</td>
                <td class="center">
                    @forelse( $manager['spheres'] as $sphere )
                        {{ $sphere['sphere']['name'] }},
                    @empty
                    @endforelse
                </td>
                <td class="center">{{ $manager['agents'] }}</td>
                <td class="center">{{ $manager['created_at'] }}</td>
                <td>
                    <a class="btn btn-sm btn-success" title="Statistic" href="{{ route('admin.statistic.accManager', ['id'=>$manager['id']]) }}">
                        Statistic
                    </a>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="2"></td>
            </tr>
        @endforelse--}}
        </tbody>
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

            oTable = $('#statisticAccManagerTable').DataTable({
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
                    "url": "/admin/statistic/accManagerData",
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
