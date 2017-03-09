@extends('layouts.master')

{{-- Content --}}
@section('content')
    <div class="_page-header" xmlns="http://www.w3.org/1999/html">
    </div>

        <div class="panel panel-default">
            <div class="col-md-12" id="openedLeadsFilters">
                <label class="obtain-label-period" for="reportrange">
                    Period:
                    <input type="text" name="date" data-name="date" class="mdl-textfield__input dataTables_filter" value="" id="reportrange" />
                </label>
                @if( isset($spheres) && count($spheres) > 0 )
                    <label class="obtain-label-period">
                        Sphere:
                        <select data-name="sphere" class="selectbox dataTables_filter" id="spheresFilter">
                            <option selected="selected" value=""></option>
                            @foreach($spheres as $sphere)
                                <option value="{{ $sphere->id }}">{{ $sphere->name }}</option>
                            @endforeach
                        </select>
                    </label>
                @endif
                @if( isset($statuses) && count($statuses) > 0 )
                    <label class="obtain-label-period">
                        Status:
                        <select data-name="status" class="selectbox dataTables_filter" id="statusesFilter">
                            <option selected="selected" value=""></option>
                            @foreach($statuses as $status => $name)
                                <option value="{{ $status }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </label>
                @endif
                <label>
                    Show
                    <select data-name="pageLength" class="selectbox dataTables_filter" data-js="1">
                        <option></option>
                        <option value="10">10</option>
                        <option value="20">20</option>
                        <option value="50">50</option>
                    </select> entries
                </label>
            </div>
            <div class="col-md-12">
                <table class="table table-bordered table-striped table-hover" id="depositedLeadTable">
                    <thead>
                    <tr>
                        {{--<th>{{ trans("main.action") }}</th>--}}
                        <th>{{ trans("main.status") }}</th>
                        <th>{{ trans("site/lead.updated") }}</th>
                        <th>{{ trans("site/lead.sphere") }}</th>
                        <th>{{ trans("site/lead.name") }}</th>
                        <th>{{ trans("site/lead.phone") }}</th>
                        <th>{{ trans("site/lead.email") }}</th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

        </div>

@stop

@section('script')
    <script type="text/javascript">

        $(window).on('load', function () {
            var $table = $('#depositedLeadTable');
            var $container = $('#openedLeadsFilters');

            var dTable = $table.DataTable({
                "destroy": true,
                "searching": false,
                "lengthChange": false,
                "processing": true,
                "serverSide": true,
                "ajax": {
                    "url" : '{{ route('agent.lead.depostited.data') }}',
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

                "responsive": true
            });


            // обработка фильтров таблицы при изменении селекта
            $container.find(':input.dataTables_filter').change(function () {

                // проверяем параметр data-js
                if ($(this).data('js') == '1') {
                    // если js равен 1

                    // перечисляем имена
                    switch ($(this).data('name')) {

                        // если у селекта имя pageLength
                        case 'pageLength':
                            // перерисовываем таблицу с нужным количеством строк
                            if ($(this).val()) dTable.page.len($(this).val()).draw();
                            break;
                        default:
                            ;
                    }
                } else {
                    // если js НЕ равен 1
                    // просто перезагружаем таблицу
                    dTable.ajax.reload();
                }
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
                opens: "right",
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
