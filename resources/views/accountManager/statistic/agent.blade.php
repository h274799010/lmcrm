@extends('layouts.accountManagerDefault')

{{-- Web site Title --}}
@section('title') {!! trans("admin/agent.agents") !!} :: @parent
@stop

{{-- Content --}}
@section('content')
    <div class="page-header">
        <h3>
            Statistic: {{ $agent->email }}
        </h3>
    </div>
    <div class="row">
        <div class="col-md-12 col-xs-12" id="leadsListFilter">
            <div class="row">
                <div class="col-xs-2">
                    <div class="form-group">
                        <label class="control-label _col-sm-2">{{ trans('admin/openLeads.filter.period') }}</label><br>
                        <input type="text" name="reportrange" data-name="period"
                               class="mdl-textfield__input dataTables_filter" value="" id="reportrange"/>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <h4>Sphere name</h4>

    <div class="row">
        <div class="col-md-4">
            <table class="table table-striped table-hover process-statuses">
                <thead>
                <tr>
                    <th colspan="4">Процессные статусы</th>
                </tr>
                <tr>
                    <th>Статус</th>
                    <th>Кол-во лидов</th>
                    <th>Процент от общего числа</th>
                    <th>Процент за выбранный период</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>Step 1</td>
                    <td>50</td>
                    <td>18%</td>
                    <td>10%</td>
                </tr>
                <tr>
                    <td>Step 2</td>
                    <td>30</td>
                    <td>20%</td>
                    <td>18%</td>
                </tr>
                </tbody>
            </table>
        </div>
        <div class="col-md-4">
            <table class="table table-striped table-hover undefined-statuses">
                <thead>
                <tr>
                    <th colspan="4">Не определенные</th>
                </tr>
                <tr>
                    <th>Статус</th>
                    <th>Кол-во лидов</th>
                    <th>Процент от общего числа</th>
                    <th>Процент за выбранный период</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>Step 1</td>
                    <td>50</td>
                    <td>18%</td>
                    <td>10%</td>
                </tr>
                <tr>
                    <td>Step 2</td>
                    <td>30</td>
                    <td>20%</td>
                    <td>18%</td>
                </tr>
                </tbody>
            </table>
        </div>
        <div class="col-md-4">
            <table class="table table-striped table-hover fail-statuses">
                <thead>
                <tr>
                    <th colspan="4">Отказники</th>
                </tr>
                <tr>
                    <th>Статус</th>
                    <th>Кол-во лидов</th>
                    <th>Процент от общего числа</th>
                    <th>Процент за выбранный период</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>Step 1</td>
                    <td>50</td>
                    <td>18%</td>
                    <td>10%</td>
                </tr>
                <tr>
                    <td>Step 2</td>
                    <td>30</td>
                    <td>20%</td>
                    <td>18%</td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <table class="table table-striped table-hover bad-statuses">
                <thead>
                <tr>
                    <th colspan="4">Плохие</th>
                </tr>
                <tr>
                    <th>Статус</th>
                    <th>Кол-во лидов</th>
                    <th>Процент от общего числа</th>
                    <th>Процент за выбранный период</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>Step 1</td>
                    <td>50</td>
                    <td>18%</td>
                    <td>10%</td>
                </tr>
                <tr>
                    <td>Step 2</td>
                    <td>30</td>
                    <td>20%</td>
                    <td>18%</td>
                </tr>
                </tbody>
            </table>
        </div>
        <div class="col-md-8">
            <table class="table table-striped table-hover performance-table">
                <thead>
                <tr>
                    <th colspan="5">уровень производительности</th>
                </tr>
                <tr>
                    <th>Статус 1</th>
                    <th>Статус 2</th>
                    <th>Процент от общего числа</th>
                    <th>Процент за выбранный период</th>
                    <th>Оценка</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>Step 1</td>
                    <td>Step 2</td>
                    <td>18%</td>
                    <td>10%</td>
                    <td class="rating_very_good">Очень хорошо</td>
                </tr>
                <tr>
                    <td>Step 3</td>
                    <td>Step 4</td>
                    <td>20%</td>
                    <td>18%</td>
                    <td class="rating_good">Хорошо</td>
                </tr>
                <tr>
                    <td>Step 5</td>
                    <td>Step 6</td>
                    <td>20%</td>
                    <td>18%</td>
                    <td class="rating_needs_improvements">Требуется улучшение</td>
                </tr>
                <tr>
                    <td>Step 7</td>
                    <td>Step 8</td>
                    <td>20%</td>
                    <td>18%</td>
                    <td class="rating_takes_significant_improvements">Требуется значительное улучшение</td>
                </tr>
                <tr>
                    <td>Step 9</td>
                    <td>Step 10</td>
                    <td>20%</td>
                    <td>18%</td>
                    <td class="rating_bad">Плохо</td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
@stop

@section('styles')
    <style>
        span.red {
            color: red;
        }

        span.green {
            color: green;
        }
        .process-statuses thead tr:first-child th {
            text-align: center;
            color: green;
        }
        .undefined-statuses  thead tr:first-child th {
            text-align: center;
            color: #00ca00;
        }
        .fail-statuses  thead tr:first-child th {
            text-align: center;
            color: #e1d12a;
        }
        .bad-statuses  thead tr:first-child th {
            text-align: center;
            color: red;
        }
        .performance-table  thead tr:first-child th {
            text-align: center;
        }

        .rating_very_good {
            color: rgb(0, 176, 80);
        }
        .rating_good {
            color: rgb(146, 208, 80);
        }
        .rating_needs_improvements {
            color: rgb(191, 143, 0);
        }
        .rating_takes_significant_improvements {
            color: rgb(237, 125, 49);
        }
        .rating_bad {
            color: red;
        }
    </style>
@stop

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
            /*$(document).on('change', '.connectedFilter', function () {
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
            });*/
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