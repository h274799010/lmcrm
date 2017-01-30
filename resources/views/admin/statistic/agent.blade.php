@extends('admin.layouts.default')

{{-- Web site Title --}}
@section('title') {!! trans("admin/agent.agents") !!} :: @parent
@stop

{{-- Content --}}
@section('main')
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
                               class="mdl-textfield__input dataTables_filter statistics_input" value="" id="reportrange"/>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="statisticWrapper">


        {{-- Перебираем все статусы по сферам --}}
        @foreach($statistics as $statistic  )

            <div class="row sphere_status_block sphere_{{ $statistic['sphereId'] }}">

                <h4 class="statistic-sphere-name">{{ $statistic['sphereName'] }} <span class="badge statistics_head_badge"> {{ $statistic['data']['periodOpenLeads'] }} / {{ $statistic['data']['allOpenLeads'] }} </span></h4>

                {{-- Проверяем достаточно ли у агента открытых лидов по сфере для статистики --}}
                @if( $statistic['status'] )
                    {{-- по сфере достаточно открытых лидов для статистики --}}


                    {{-- Общие данные - no status и close Deal --}}
                    <div class="table-statuses table-statuses-large table_status_block">
                        <table class="table ">

                            <thead>
                                <tr>
                                    <th class="center middle"> </th>
                                    <th class="center middle">amount</th>
                                    <th class="center middle">all</th>
                                    <th class="center middle">period</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="status_no_status">
                                    <td class="center middle"> No status </td>
                                    <td class="percent-col center middle countAll">{{ $statistic['data']['statuses']['nostatus']['countAll'] }}</td>
                                    <td class="percent-col center middle allPercent">{{ $statistic['data']['statuses']['nostatus']['allPercent'] }}%</td>
                                    <td class="percent-col center middle periodPercent">{{ $statistic['data']['statuses']['nostatus']['periodPercent'] }}%</td>
                                </tr>
                                <tr class="status_close_deal">
                                    <td class="center middle"> Close deal </td>
                                    <td class="percent-col center middle countAll">{{ $statistic['data']['statuses']['close_deal']['countAll'] }}</td>
                                    <td class="percent-col center middle allPercent">{{ $statistic['data']['statuses']['close_deal']['allPercent'] }}%</td>
                                    <td class="percent-col center middle periodPercent">{{ $statistic['data']['statuses']['close_deal']['periodPercent'] }}%</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    {{-- Процессные статусы --}}
                    <div class="table-statuses table-statuses-small table_status_block">
                        <table class="table table-striped table-hover process-statuses">
                            <thead>
                                <tr class="statistics_process_statuses">
                                    <th colspan="4">Process</th>
                                </tr>
                                <tr>
                                    <th class="center middle">status</th>
                                    <th class="center middle">amount</th>
                                    <th class="center middle">all</th>
                                    <th class="center middle">period</th>
                                </tr>
                            </thead>
                            <tbody>

                            @forelse( $statistic['data']['statuses'][1] as $status)
                                <tr>
                                    <td class="center middle name">{{ $status['name'] }}</td>
                                    <td class="percent-col center middle countAll">{{ $status['countAll'] }}</td>
                                    <td class="percent-col center middle allPercent">{{ $status['allPercent'] }}%</td>
                                    <td class="percent-col center middle periodPercent">{{ $status['periodPercent'] }}%</td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="center statistics_no_data" colspan="5">No data</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Неопределенные статусы --}}
                    <div class="table-statuses table-statuses-small table_status_block">
                        <table class="table table-striped table-hover undefined-statuses">
                            <thead>
                            <tr class="statistics_uncertain_statuses">
                                <th colspan="4">Uncertain</th>
                            </tr>
                            <tr>
                                <th class="center middle">status</th>
                                <th class="center middle">amount</th>
                                <th class="center middle">all</th>
                                <th class="center middle">period</th>
                            </tr>
                            </thead>
                            <tbody>

                            @forelse( $statistic['data']['statuses'][2] as $status)
                                <tr>
                                    <td class="center middle">{{ $status['name'] }}</td>
                                    <td class="percent-col center middle">{{ $status['countAll'] }}</td>
                                    <td class="percent-col center middle">{{ $status['allPercent'] }}%</td>
                                    <td class="percent-col center middle">{{ $status['periodPercent'] }}%</td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="center statistics_no_data" colspan="5">No data</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Отказные статусы --}}
                    <div class="table-statuses table-statuses-small table_status_block">
                        <table class="table table-striped table-hover fail-statuses">
                            <thead>
                            <tr class="statistics_refuseniks_statuses">
                                <th colspan="4">Refuseniks</th>
                            </tr>
                            <tr>
                                <th class="center middle">status</th>
                                <th class="center middle">amount</th>
                                <th class="center middle">all</th>
                                <th class="center middle">period</th>
                            </tr>
                            </thead>
                            <tbody>

                            @forelse( $statistic['data']['statuses'][3] as $status)
                                <tr>
                                    <td class="center middle">{{ $status['name'] }}</td>
                                    <td class="percent-col center middle">{{ $status['countAll'] }}</td>
                                    <td class="percent-col center middle">{{ $status['allPercent'] }}%</td>
                                    <td class="percent-col center middle">{{ $status['periodPercent'] }}%</td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="center statistics_no_data" colspan="5">No data</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Плохие статусы --}}
                    <div class="table-statuses table-statuses-small table_status_block">
                        <table class="table table-striped table-hover bad-statuses">
                            <thead>
                            <tr class="statistics_bad_statuses">
                                <th colspan="4">Bad</th>
                            </tr>
                            <tr>
                                <th class="center middle">status</th>
                                <th class="center middle">amount</th>
                                <th class="center middle">all</th>
                                <th class="center middle">period</th>
                            </tr>
                            </thead>
                            <tbody>

                                @forelse( $statistic['data']['statuses'][4] as $status)
                                    <tr>
                                        <td class="center middle">{{ $status['name'] }}</td>
                                        <td class="percent-col center middle">{{ $status['countAll'] }}</td>
                                        <td class="percent-col center middle">{{ $status['allPercent'] }}%</td>
                                        <td class="percent-col center middle">{{ $status['periodPercent'] }}%</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="center statistics_no_data" colspan="5">No data</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Статистика по транзитам --}}
                    <div class="table-statuses table-statuses-large table_status_block">
                        <table class="table table-striped table-hover performance-table">
                            <thead>
                            <tr class="statistics_transitions_statuses">
                                <th colspan="5">Transitions</th>
                            </tr>
                            <tr>
                                <th class="center middle">from</th>
                                <th class="center middle">to</th>
                                <th class="center middle">all</th>
                                <th class="center middle">period</th>
                                <th class="center middle">rating</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($statistic['data']['transitions'] as $transit)
                                <tr>
                                    <td class="center middle fromStatus"> {{ $transit['fromStatus'] }} </td>
                                    <td class="center middle toStatus"> {{ $transit['toStatus'] }} </td>
                                    <td class="percent-col center middle allPercent">{{ $transit['allPercent'] }}%</td>
                                    <td class="percent-col center middle periodPercent">{{ $transit['periodPercent'] }}%</td>
                                    <td class="center middle status_{{ $transit['rating'] }} rating">{{ $transit['rating'] }}</td>
                                </tr>
                            @empty
                                <tr >
                                    <td class="center statistics_no_data" colspan="5">No data</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>

                @else
                    {{-- по сфере недостаточно откртых лидов для статистики --}}

                        <div class="col-md-12 table_status_block sphere_no_data center">
                            Not enough open leads for statistics
                        </div>
                @endif

            </div>

        @endforeach

       {{-- @include('admin.statistic.partials.agentStatistic')--}}
    </div>
@stop

@section('styles')
    <style>

        .sphere_status_block{
            margin-top: 10px;
            margin-bottom: 30px;
        }

        .table_status_block{
            margin-bottom: 25px;
        }

        span.red {
            color: red;
        }

        span.green {
            color: green;
        }
    </style>
@stop

{{-- Scripts --}}
@section('scripts')
    <script type="text/javascript">

        // дата начала периода
        var dataStart = 0;
        // дата окончания периода
        var dataEnd = 0;

        /**
         * Функция обновления данных статистики на странице
         *
         */
        function statisticUpdate( statistic ){

            // перебираем все сферы по статистике
            $.each( statistic, function( index, data ){

                // выбираем блок со сферой
                var sphere = $('.sphere_' + data['sphereId']);

                // обновляем данные по количеству открытых лидов
                sphere.find('.badge').text( data['data']['periodOpenLeads'] + '/' + data['data']['allOpenLeads'] );

                // обновление данных по открытым лидам с отсутствующим статусом
                sphere.find('.status_no_status .countAll').text( data['data']['statuses']['nostatus']['countAll'] );
                sphere.find('.status_no_status .allPercent').text( data['data']['statuses']['nostatus']['allPercent']+'%' );
                sphere.find('.status_no_status .periodPercent').text( data['data']['statuses']['nostatus']['periodPercent']+'%' );

                // обновление данных по открытым лидам с закрытыми сделками
                sphere.find('.status_close_deal .countAll').text( data['data']['statuses']['close_deal']['countAll'] );
                sphere.find('.status_close_deal .allPercent').text( data['data']['statuses']['close_deal']['allPercent']+'%' );
                sphere.find('.status_close_deal .periodPercent').text( data['data']['statuses']['close_deal']['periodPercent']+'%' );

                // массив с типами статусов, по которым составляются таблицы
                var statusesType = [ 'process-statuses', 'undefined-statuses', 'fail-statuses', 'bad-statuses' ];

                // перебираем все типы статусов
                $.each( statusesType, function( key, type){

                    // выбираем индекс типа
                    var typeIndex = key + 1;

                    // выбираем нужную таблицу
                    var table = sphere.find('.' + type + ' tbody');

                    // очищаем таблицу
                    table.empty();

                    // если нет статуса конкретного типа
                    if( data['data']['statuses'][typeIndex].length == 0){
                        // выводим что статусов нет

                        // создание строки таблицы
                        var tr = $('<tr />');

                        // создание ячейки таблицы
                        var noStatusRow = $('<td />');

                        // добавление классов
                        noStatusRow.addClass('center statistics_no_data');

                        // добавляем атрибут объединения ячеек
                        noStatusRow.attr('colspan', 5);

                        // добавление данных в ячейки
                        noStatusRow.text( 'No statuses' );

                        // добавление ячеек в строку
                        tr.append(noStatusRow);

                        // добавление строки в таблицу
                        table.append( tr );
                    }

                    // перебираем все статусы и наполняем таблицу
                    $.each( data['data']['statuses'][typeIndex], function( statusKey, statusData ){

                        // создание строки таблицы
                        var tr = $('<tr />');

                        // создание ячеек таблицы
                        var name = $('<td />');
                        var countAll = $('<td />');
                        var allPercent = $('<td />');
                        var periodPercent = $('<td />');

                        // добавление классов
                        name.addClass('center middle name');
                        countAll.addClass('percent-col center middle countAll');
                        allPercent.addClass('percent-col center middle allPercent');
                        periodPercent.addClass('percent-col center middle periodPercent');

                        // добавление данных в ячейки
                        name.text( statusData['name'] );
                        countAll.text( statusData['countAll'] );
                        allPercent.text( statusData['allPercent'] + '%' );
                        periodPercent.text( statusData['periodPercent'] + '%' );

                        // добавление ячеек в строку
                        tr.append(name);
                        tr.append(countAll);
                        tr.append(allPercent);
                        tr.append(periodPercent);

                        // добавление строки в таблицу
                        table.append( tr );

//                        console.log(statusData);
                    });
                } );

                // выбираем таблицу транзитов
                var transitionTable = sphere.find('.performance-table tbody');

                // очищаем таблицу
                transitionTable.empty();

                // если нет транзитов
                if( data['data']['transitions'].length == 0){
                    // выводим что статусов нет

                    // создание строки таблицы
                    var tr = $('<tr />');

                    // создание ячейки таблицы
                    var noTransitionRow = $('<td />');

                    // добавление классов
                    noTransitionRow.addClass('center statistics_no_data');

                    // добавляем атрибут объединения ячеек
                    noTransitionRow.attr('colspan', 5);

                    // добавление данных в ячейки
                    noTransitionRow.text( 'No transitions' );

                    // добавление ячеек в строку
                    tr.append(noTransitionRow);

                    // добавление строки в таблицу
                    transitionTable.append( tr );
                }

                // перебираем все транзиты и наполняем таблицу
                $.each( data['data']['transitions'], function( transitionKey, transitionData ){

                    // создание строки таблицы
                    var tr = $('<tr />');

                    // создание ячеек таблицы
                    var fromStatus = $('<td />');
                    var toStatus = $('<td />');
                    var allPercent = $('<td />');
                    var periodPercent = $('<td />');
                    var rating = $('<td />');

                    // добавление классов
                    fromStatus.addClass('center middle fromStatus');
                    toStatus.addClass('center middle toStatus');
                    allPercent.addClass('percent-col center middle allPercent');
                    periodPercent.addClass('percent-col center middle periodPercent');
                    rating.addClass('center middle rating status_' + transitionData['rating']);

                    // добавление данных в ячейки
                    fromStatus.text( transitionData['fromStatus'] );
                    toStatus.text( transitionData['toStatus'] );
                    allPercent.text( transitionData['allPercent'] );
                    periodPercent.text( transitionData['periodPercent'] + '%' );
                    rating.text( transitionData['rating'] );

                    // добавление ячеек в строку
                    tr.append(fromStatus);
                    tr.append(toStatus);
                    tr.append(allPercent);
                    tr.append(periodPercent);
                    tr.append(rating);

                    // добавление строки в таблицу
                    transitionTable.append( tr );
                });

//                performance-table


//                console.log( data );
//                console.log( sphere );

            } );

        }

        // функция отправки данных на сервер для обновления периода и прочего
        function loadStatistic() {

            // заносим параметры
            var params ={ _token: '{{ csrf_token() }}', agent_id: '{{ $agent->id }}', timeFrom: dataStart, timeTo: dataEnd } ;

            // отправка запроса на сервер
            $.post(
                    '{{ route('admin.statistic.agentData') }}',
                    params,
                    function (data)
                    {

                        statisticUpdate( data );
//                        $('#statisticWrapper').html(data);

//                        console.log(data);
                    });
        }


//        $(document).ready(function () {

//            // подулючение select2 к селекту
//            $('select').select2({
//                allowClear: true
//            });
//
//            // отправка запроса на обновление данных при изменении периода
//            $(document).on('change', '#reportrange', function () {
//                loadStatistic();
//            })
//        });
//        $(window).on('load', function () {
//            loadStatistic();
//        });



        $(function() {

            // подулючение select2 к селекту
            $('select').select2({
                allowClear: true
            });

            // отправка запроса на обновление данных при изменении периода
            $(document).on('change', '#reportrange', function () {
                loadStatistic();
            });

            var start = moment().startOf('month');
            var end = moment().endOf('month');

            function cb(start, end) {

                dataStart = start.format('YYYY-MM-DD');
                dataEnd = end.format('YYYY-MM-DD');

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