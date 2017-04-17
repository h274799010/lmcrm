@extends('layouts.master')

{{-- Content --}}
@section('content')
    <div class="_page-header" xmlns="http://www.w3.org/1999/html">
    </div>

    <ol class="breadcrumb">
        <li><a href="/">LM CRM</a></li>
        <li  class="active">Deposited leads</li>
    </ol>

        <div class="panel panel-default">
            <div class="row">
                <div class="col-md-12 filter-wrapper" id="openedLeadsFilters">
                    <label class="obtain-label-period" for="reportrange">
                        <span class="filter-label">Period:</span>
                        <input type="text" name="date" data-name="date" class="mdl-textfield__input dataTables_filter" value="" id="reportrange" />
                    </label>
                    @if( isset($spheres) && count($spheres) > 0 )
                        <label class="obtain-label-period">
                            <span class="filter-label">Sphere:</span>
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
                            <span class="filter-label">Status:</span>
                            <select data-name="status" class="selectbox dataTables_filter" id="statusesFilter">
                                <option selected="selected" value=""></option>
                                @foreach($statuses as $status => $name)
                                    <option value="{{ $status }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </label>
                    @endif
                    <label>
                        <span class="filter-label">Show</span>
                        <select data-name="pageLength" class="selectbox dataTables_filter" data-js="1">
                            <option></option>
                            <option value="10">10</option>
                            <option value="20">20</option>
                            <option value="50">50</option>
                        </select> <span class="filter-label filter-label-last">entries</span>
                    </label>
                    <button class="btn btn-sm btn-danger" id="resetFilters" style="margin-bottom: 0;">{{ trans('admin/admin.button.filter_reset') }}</button>
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
        </div>

@stop

@section('script')
    <script type="text/javascript">
        $(document).ready(function () {
            $(document).on('click', '#resetFilters', function (e) {
                e.preventDefault();

                $('.filter-wrapper').find('select').each(function (i, el) {
                    $(el).prop('selectedIndex', 0);
                    var selectBox = $(el).data("selectBox-selectBoxIt");
                    selectBox.refresh();
                });

                $('.filter-wrapper input').val('').trigger('change');
            });
        });

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

@section('styles')
    <style type="text/css">
        .filter-wrapper {
            margin: 16px 0;
        }
        .filter-label {
            margin-right: 6px;
        }
        .filter-label.filter-label-last {
            margin-right: 0;
            margin-left: 6px;
        }
        .filter-wrapper label {
            margin-right: 15px;
        }
    </style>
@endsection