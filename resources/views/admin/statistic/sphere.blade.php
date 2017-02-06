@extends('admin.layouts.default')

{{-- Web site Title --}}
@section('title') {!! trans("admin/agent.agents") !!} :: @parent
@stop

{{-- Content --}}
@section('main')
    {{--<div class="page-header">--}}
        {{--<h3>--}}
            {{--@lang('statistic.page_title') {{ $sphere->name }}--}}
        {{--</h3>--}}
    {{--</div>--}}

        {{-- строка с селектами --}}
        <div class="row">

            {{-- селект с периодом --}}
            <div class="col-md-2">
                <div class="form-group">
                    <label class="control-label _col-sm-2">{{ trans('admin/openLeads.filter.period') }}</label><br>
                    <input type="text" name="reportrange" data-name="period"
                           class="mdl-textfield__input dataTables_filter statistics_input" value="" id="reportrange"/>
                </div>
            </div>
        </div>

        <div id="statisticWrapper">

            <div class="row sphere_status_block">

                <h4 class="statistic-sphere-name"> <span class="sphere-name">{{ $statistic['sphereName'] }}</span>
                    {{--<span class="badge statistics_head_badge"> {{ $statistic['data']['periodOpenLeads'] }} / {{ $statistic['data']['allOpenLeads'] }} </span> --}}
                    {{--<span class="badge statistics_head_badge_auction"> auction {{ $statistic['data']['PeriodAuction'] }}/{{ $statistic['data']['allAuctionWithTrash'] }}/{{ $statistic['data']['allAuctionWithTrash'] }}  </span>--}}
                </h4>

                <table class="summary_table">
                    <tr>
                        <td>Added</td>
                        <td>
                            <span class="summary_table_addition">all: </span>
                            <span class="summary_table_added_all">{{ $statistic['data']['allLeads'] }}</span>
                        </td>
                        <td>
                            <span class="summary_table_addition">period: </span>
                            <span class="summary_table_added_period">{{ $statistic['data']['periodLeads'] }}</span>
                        </td>
                    </tr>                    <tr>
                        <td>Seen</td>
                        <td>
                            <span class="summary_table_addition">all: </span>
                            <span class="summary_table_seen_all">{{ $statistic['data']['allAuctionWithTrash'] }}</span>
                        </td>
                        <td>
                            <span class="summary_table_addition">period: </span>
                            <span class="summary_table_seen_period">{{ $statistic['data']['allAuctionWithTrash'] }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td>Open</td>
                        <td>
                            <span class="summary_table_addition">all: </span>
                            <span class="summary_table_open_all">{{ $statistic['data']['allOpenLeads'] }}</span>
                        </td>
                        <td>
                            <span class="summary_table_addition">period: </span>
                            <span class="summary_table_open_period">{{ $statistic['data']['periodOpenLeads'] }}</span>
                        </td>
                    </tr>
                </table>




                {{-- Проверяем достаточно ли у агента открытых лидов по сфере для статистики --}}
                @if( $statistic['status'] )
                    {{-- по сфере достаточно открытых лидов для статистики --}}

                    <div class="col-md-12 table_status_block sphere_no_data center  hidden ">
                        Not enough open leads for statistics. <span class="leads_needed"></span> open leads needed
                    </div>

                    {{-- Общие данные - no status и close Deal --}}
                    <div class="table-statuses table-statuses-large table_status_block">
                        <table class="table topStatusTable">

                            <thead>
                            <tr>
                                <th class="center middle">status</th>
                                <th class="center middle">amount all</th>
                                <th class="center middle">percent all</th>
                                <th class="center middle">amount period</th>
                                <th class="center middle">percent period</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr class="status_no_status">
                                <td class="center middle"> No status </td>
                                <td class="percent-col center middle countAll">{{ $statistic['data']['statuses']['nostatus']['countAll'] }}</td>
                                <td class="percent-col center middle allPercent">{{ $statistic['data']['statuses']['nostatus']['allPercent'] }}%</td>
                                <td class="percent-col center middle countPeriod">{{ $statistic['data']['statuses']['nostatus']['countPeriod'] }}</td>
                                <td class="percent-col center middle periodPercent">{{ $statistic['data']['statuses']['nostatus']['periodPercent'] }}%</td>
                            </tr>
                            <tr class="status_close_deal">
                                <td class="center middle"> Close deal </td>
                                <td class="percent-col center middle countAll">{{ $statistic['data']['statuses']['close_deal']['countAll'] }}</td>
                                <td class="percent-col center middle allPercent">{{ $statistic['data']['statuses']['close_deal']['allPercent'] }}%</td>
                                <td class="percent-col center middle countPeriod">{{ $statistic['data']['statuses']['close_deal']['countPeriod'] }}</td>
                                <td class="percent-col center middle periodPercent">{{ $statistic['data']['statuses']['close_deal']['periodPercent'] }}%</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>

                    {{-- Процессные статусы --}}
                    <div class="table-statuses table-statuses-small table_status_block">
                        <table class="table process-statuses">
                            <thead>
                            <tr class="statistics_process_statuses">
                                <th colspan="5">Process</th>
                            </tr>
                            <tr>
                                <th class="center middle">status</th>
                                <th class="center middle">amount all</th>
                                <th class="center middle">percent all</th>
                                <th class="center middle">amount period</th>
                                <th class="center middle">percent period</th>
                            </tr>
                            </thead>
                            <tbody>

                            @forelse( $statistic['data']['statuses'][1] as $status)
                                <tr>
                                    <td class="center middle name">{{ $status['name'] }}</td>
                                    <td class="percent-col center middle countAll">{{ $status['countAll'] }}</td>
                                    <td class="percent-col center middle allPercent">{{ $status['allPercent'] }}%</td>
                                    <td class="percent-col center middle countPeriod">{{ $status['countPeriod'] }}</td>
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
                        <table class="table undefined-statuses">
                            <thead>
                            <tr class="statistics_uncertain_statuses">
                                <th colspan="5">Uncertain</th>
                            </tr>
                            <tr>
                                <th class="center middle">status</th>
                                <th class="center middle">amount all</th>
                                <th class="center middle">percent all</th>
                                <th class="center middle">amount period</th>
                                <th class="center middle">percent period</th>
                            </tr>
                            </thead>
                            <tbody>

                            @forelse( $statistic['data']['statuses'][2] as $status)
                                <tr>
                                    <td class="center middle name">{{ $status['name'] }}</td>
                                    <td class="percent-col center middle countAll">{{ $status['countAll'] }}</td>
                                    <td class="percent-col center middle allPercent">{{ $status['allPercent'] }}%</td>
                                    <td class="percent-col center middle countPeriod">{{ $status['countPeriod'] }}</td>
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

                    {{-- Отказные статусы --}}
                    <div class="table-statuses table-statuses-small table_status_block">
                        <table class="table fail-statuses">
                            <thead>
                            <tr class="statistics_refuseniks_statuses">
                                <th colspan="5">Refuseniks</th>
                            </tr>
                            <tr>
                                <th class="center middle">status</th>
                                <th class="center middle">amount all</th>
                                <th class="center middle">percent all</th>
                                <th class="center middle">amount period</th>
                                <th class="center middle">percent period</th>
                            </tr>
                            </thead>
                            <tbody>

                            @forelse( $statistic['data']['statuses'][3] as $status)
                                <tr>
                                    <td class="center middle name">{{ $status['name'] }}</td>
                                    <td class="percent-col center middle countAll">{{ $status['countAll'] }}</td>
                                    <td class="percent-col center middle allPercent">{{ $status['allPercent'] }}%</td>
                                    <td class="percent-col center middle countPeriod">{{ $status['countPeriod'] }}</td>
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

                    {{-- Плохие статусы --}}
                    <div class="table-statuses table-statuses-small table_status_block">
                        <table class="table bad-statuses">
                            <thead>
                            <tr class="statistics_bad_statuses">
                                <th colspan="5">Bad</th>
                            </tr>
                            <tr>
                                <th class="center middle">status</th>
                                <th class="center middle">amount all</th>
                                <th class="center middle">percent all</th>
                                <th class="center middle">amount period</th>
                                <th class="center middle">percent period</th>
                            </tr>
                            </thead>
                            <tbody>

                            @forelse( $statistic['data']['statuses'][4] as $status)
                                <tr>
                                    <td class="center middle name">{{ $status['name'] }}</td>
                                    <td class="percent-col center middle countAll">{{ $status['countAll'] }}</td>
                                    <td class="percent-col center middle allPercent">{{ $status['allPercent'] }}%</td>
                                    <td class="percent-col center middle countPeriod">{{ $status['countPeriod'] }}</td>
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

                    {{-- Статистика по транзитам --}}
                    <div class="table-statuses table-statuses-large table_status_block">
                        <table class="table performance-table">
                            <thead>
                            <tr class="statistics_transitions_statuses">
                                <th colspan="8">Transitions</th>
                            </tr>
                            <tr>
                                <th class="center middle">from</th>
                                <th class="center middle"></th>
                                <th class="center middle">to</th>
                                <th class="center middle">all</th>
                                <th class="center middle">all rating</th>
                                <th class="center middle"></th>
                                <th class="center middle">period</th>
                                <th class="center middle">period rating</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($statistic['data']['transitions'] as $transit)
                                <tr>
                                    <td class="center middle fromStatus"> {{ $transit['fromStatus'] }} </td>
                                    <td class="center middle statistics_transitions_statuses_arrow"> <i class="glyphicon glyphicon-arrow-right"></i> </td>
                                    <td class="center middle toStatus"> {{ $transit['toStatus'] }} </td>
                                    <td class="percent-col center middle allPercent">{{ $transit['allPercent'] }}%</td>
                                    <td class="center middle status_{{ $transit['allRating'] }} rating">{{ $transit['allRating'] }}</td>
                                    <td class="center middle statistics_transitions_emptyCall"></td>
                                    <td class="percent-col center middle periodPercent">{{ $transit['periodPercent'] }}%</td>
                                    <td class="center middle status_{{ $transit['periodRating'] }} rating">{{ $transit['periodRating'] }}</td>
                                </tr>
                            @empty
                                <tr >
                                    <td class="center statistics_no_data" colspan="8">No data</td>
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

            // выбираем блок со сферой
            var sphere = $('.sphere_status_block');

            // обновление имени сферы
            sphere.find('.sphere-name').text( statistic['statistic']['sphereName'] );

            // проверка на статус статистики
//            if( !statistic['statistic']['status'] ){
//                // если статистики по какой то причине нет
//
//                sphere.find('.topStatusTable').addClass('hidden');
//                sphere.find('.process-statuses').addClass('hidden');
//                sphere.find('.undefined-statuses').addClass('hidden');
//                sphere.find('.fail-statuses').addClass('hidden');
//                sphere.find('.bad-statuses').addClass('hidden');
//                sphere.find('.performance-table').addClass('hidden');
//
//                sphere.find('.sphere_no_data').removeClass('hidden');
//
//            }else {
//
//                sphere.find('.topStatusTable').removeClass('hidden');
//                sphere.find('.process-statuses').removeClass('hidden');
//                sphere.find('.undefined-statuses').removeClass('hidden');
//                sphere.find('.fail-statuses').removeClass('hidden');
//                sphere.find('.bad-statuses').removeClass('hidden');
//                sphere.find('.performance-table').removeClass('hidden');
//
//                sphere.find('.sphere_no_data').addClass('hidden');
//            }

            // минимальное количество лидов которое нужно для вывода статистики
            sphere.find('.leads_needed').text( statistic['statistic']['minLead'] );


            // обновляем данные по количеству открытых лидов
            sphere.find('.summary_table_open_all').text( statistic['statistic']['data']['allOpenLeads'] );
            sphere.find('.summary_table_open_period').text( statistic['statistic']['data']['periodOpenLeads'] );

            // данные по количеству увиденных лидов
            sphere.find('.summary_table_seen_all').text( statistic['statistic']['data']['allAuctionWithTrash'] );
            sphere.find('.summary_table_seen_period').text( statistic['statistic']['data']['allAuction'] );

            // данные по количеству добавленных лидов
            sphere.find('.summary_table_added_all').text( statistic['statistic']['data']['allLeads'] );
            sphere.find('.summary_table_added_period').text( statistic['statistic']['data']['periodLeads'] );


            // обновление данных по открытым лидам с отсутствующим статусом
            sphere.find('.status_no_status .countAll').text( statistic['statistic']['data']['statuses']['nostatus']['countAll'] );
            sphere.find('.status_no_status .allPercent').text( statistic['statistic']['data']['statuses']['nostatus']['allPercent']+'%' );
            sphere.find('.status_no_status .countPeriod').text( statistic['statistic']['data']['statuses']['nostatus']['countPeriod'] );
            sphere.find('.status_no_status .periodPercent').text( statistic['statistic']['data']['statuses']['nostatus']['periodPercent']+'%' );

            // обновление данных по открытым лидам с закрытыми сделками
            sphere.find('.status_close_deal .countAll').text( statistic['statistic']['data']['statuses']['close_deal']['countAll'] );
            sphere.find('.status_close_deal .allPercent').text( statistic['statistic']['data']['statuses']['close_deal']['allPercent']+'%' );
            sphere.find('.status_close_deal .countPeriod').text( statistic['statistic']['data']['statuses']['close_deal']['countPeriod'] );
            sphere.find('.status_close_deal .periodPercent').text( statistic['statistic']['data']['statuses']['close_deal']['periodPercent']+'%' );

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
                if( statistic['statistic']['data']['statuses'][typeIndex].length == 0){
                    // выводим что статусов нет

                    // создание строки таблицы
                    var tr = $('<tr />');

                    // создание ячейки таблицы
                    var noStatusRow = $('<td />');

                    // добавление классов
                    noStatusRow.addClass('center statistics_no_data');

                    // добавляем атрибут объединения ячеек
                    noStatusRow.attr('colspan', 6);

                    // добавление данных в ячейки
                    noStatusRow.text( 'No statuses' );

                    // добавление ячеек в строку
                    tr.append(noStatusRow);

                    // добавление строки в таблицу
                    table.append( tr );
                }

                // перебираем все статусы и наполняем таблицу
                $.each( statistic['statistic']['data']['statuses'][typeIndex], function( statusKey, statusData ){

                    // создание строки таблицы
                    var tr = $('<tr />');

                    // создание ячеек таблицы
                    var name = $('<td />');
                    var countAll = $('<td />');
                    var allPercent = $('<td />');
                    var countPeriod = $('<td />');
                    var periodPercent = $('<td />');

                    // добавление классов
                    name.addClass('center middle name');
                    countAll.addClass('percent-col center middle countAll');
                    allPercent.addClass('percent-col center middle allPercent');
                    countPeriod.addClass('percent-col center middle countPeriod');
                    periodPercent.addClass('percent-col center middle periodPercent');

                    // добавление данных в ячейки
                    name.text( statusData['name'] );
                    countAll.text( statusData['countAll'] );
                    allPercent.text( statusData['allPercent'] + '%' );
                    countPeriod.text( statusData['countPeriod'] );
                    periodPercent.text( statusData['periodPercent'] + '%' );

                    // добавление ячеек в строку
                    tr.append(name);
                    tr.append(countAll);
                    tr.append(allPercent);
                    tr.append(countPeriod);
                    tr.append(periodPercent);

                    // добавление строки в таблицу
                    table.append( tr );
                });
            } );

            // выбираем таблицу транзитов
            var transitionTable = sphere.find('.performance-table tbody');

            // очищаем таблицу
            transitionTable.empty();

            // если нет транзитов
            if( statistic['statistic']['data']['transitions'].length == 0){
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
            $.each( statistic['statistic']['data']['transitions'], function( transitionKey, transitionData ){

                // создание строки таблицы
                var tr = $('<tr />');

                // создание ячеек таблицы
                var fromStatus = $('<td />');
                var arrow = $('<td />');
                var toStatus = $('<td />');
                var allPercent = $('<td />');
                var allRating = $('<td />');
                var emptyCol = $('<td />');
                var periodPercent = $('<td />');
                var periodRating = $('<td />');

                // добавление классов
                fromStatus.addClass('center middle fromStatus');
                arrow.addClass('center middle statistics_transitions_statuses_arrow');
                toStatus.addClass('center middle toStatus');
                allPercent.addClass('percent-col center middle allPercent');
                allRating.addClass('center middle rating status_' + transitionData['allRating']);
                emptyCol.addClass('center middle statistics_transitions_emptyCall');
                periodPercent.addClass('percent-col center middle periodPercent');
                periodRating.addClass('center middle rating status_' + transitionData['periodRating']);

                // добавление данных в ячейки
                fromStatus.text( transitionData['fromStatus'] );
                arrow.html('<i class="glyphicon glyphicon-arrow-right"></i>');
                toStatus.text( transitionData['toStatus'] );
                allPercent.text( transitionData['allPercent'] );
                allRating.text( transitionData['allRating'] );
                emptyCol.text( '' );
                periodPercent.text( transitionData['periodPercent'] + '%' );
                periodRating.text( transitionData['periodRating'] );

                // добавление ячеек в строку
                tr.append(fromStatus);
                tr.append(arrow);
                tr.append(toStatus);
                tr.append(allPercent);
                tr.append(allRating);
                tr.append(emptyCol);
                tr.append(periodPercent);
                tr.append(periodRating);

                // добавление строки в таблицу
                transitionTable.append( tr );
            });
        }

        // функция отправки данных на сервер для обновления периода и прочего
        function loadStatistic() {

            // выбираем данные селекта сфер (id сферы)
            var sphereSelect = $('#sphere_select').val();

            // заносим параметры
            var params ={ _token: '{{ csrf_token() }}', timeFrom: dataStart, timeTo: dataEnd, sphere_id: '{{ $sphere['id'] }}' } ;

            // отправка запроса на сервер
            $.post(
                '{{ route('admin.statistic.sphereData') }}',
                params,
                function (data)
                {

                    // обновляем статистику на странице
                    statisticUpdate( data );
                }
            );
        }


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

            // todo дефолтное значение календаря, при кнопке отмена
            $('#reportrange').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('').trigger('change');
            });

        });
    </script>
@stop