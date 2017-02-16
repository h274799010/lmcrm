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

                <h4 class="statistic-sphere-name"> <span class="sphere-name">{{ $statistic['sphere']['name'] }}</span> </h4>


                <div class="row">
                    <div class="col-md-3">
                        <table class="summary_table">
                            <thead>
                            <tr>
                                <th></th>
                                <th class="summary_table_addition center">all</th>
                                <th class="summary_table_addition center">period</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>Leads added</td>
                                <td class="summary_table_added_all center">
                                    {{ $statistic['sphere']['leads']['all'] }}
                                </td>
                                <td class="summary_table_added_period center">
                                    {{ $statistic['sphere']['leads']['period'] }}
                                </td>
                            </tr>
                            <tr>
                                <td>Leads seen</td>
                                <td class="summary_table_seen_all center">
                                    {{ $statistic['auction']['all'] }}
                                </td>
                                <td class="summary_table_seen_period center">
                                    {{ $statistic['auction']['all'] }}
                                </td>
                            </tr>
                            <tr>
                                <td>Leads open</td>
                                <td class="summary_table_open_all center">
                                    {{ $statistic['openLeads']['all'] }}
                                </td>
                                <td class="summary_table_open_period center">
                                    {{ $statistic['openLeads']['all'] }}
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="col-md-2">
                        <table class="summary_table">
                            <thead>
                            <tr>
                                <th></th>
                                <th class="summary_table_addition">amount</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>Agents</td>
                                <td class="center sphere_agents_count">{{ $statistic['sphere']['users']['all'] }}</td>
                            </tr>
                            </tbody>
                        </table>

                    </div>
                    <div class="col-md-3">
                        <table class="summary_table">
                            <thead>
                            <tr>
                                <th></th>
                                <th class="summary_table_addition center">date</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>created</td>
                                <td class="user_created_at center">
                                    {{ $statistic['sphere']['created_at'] }}
                                </td>
                            </tr>

                            </tbody>

                        </table>
                    </div>

                </div>


                {{-- Таблица аккаунт менеджеров --}}
                <div class="row acc_manager_block">
                    <div class="col-md-8 col-md-offset-2">
                        <table class="table table-striped account_managers_table">

                            <thead>
                            <tr>
                                <th class="center middle">account manager</th>
                                <th class="center middle">agents added</th>
                                <th class="center middle">agents added during the period</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse( $statistic['sphere']['accManagers'] as $accManager )
                                <tr class="">
                                    <td class="center middle"> {{ $accManager['email'] }} </td>
                                    <td class="center middle">{{ $accManager['allAgents'] }}</td>
                                    <td class="center middle">{{ $accManager['periodAgents'] }}</td>
                                </tr>
                            @empty
                                <tr class="status_no_status">
                                    <td colspan="3" class="center middle statistics_no_data"> No account managers </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
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
                            <td class="percent-col center middle countAll">{{ $statistic['statuses']['noStatus']['countAll'] }}</td>
                            <td class="percent-col center middle allPercent">{{ $statistic['statuses']['noStatus']['percentAll'] }}%</td>
                            <td class="percent-col center middle countPeriod">{{ $statistic['statuses']['noStatus']['countPeriod'] }}</td>
                            <td class="percent-col center middle periodPercent">{{ $statistic['statuses']['noStatus']['percentPeriod'] }}%</td>
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

                        @forelse( $statistic['statuses']['type'][1] as $status)
                            <tr>
                                <td class="center middle name">{{ $status['name'] }}</td>
                                <td class="percent-col center middle countAll">{{ $status['countAll'] }}</td>
                                <td class="percent-col center middle allPercent">{{ $status['percentAll'] }}%</td>
                                <td class="percent-col center middle countPeriod">{{ $status['countPeriod'] }}</td>
                                <td class="percent-col center middle periodPercent">{{ $status['percentPeriod'] }}%</td>
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

                        @forelse( $statistic['statuses']['type'][2] as $status)
                            <tr>
                                <td class="center middle name">{{ $status['name'] }}</td>
                                <td class="percent-col center middle countAll">{{ $status['countAll'] }}</td>
                                <td class="percent-col center middle allPercent">{{ $status['percentAll'] }}%</td>
                                <td class="percent-col center middle countPeriod">{{ $status['countPeriod'] }}</td>
                                <td class="percent-col center middle periodPercent">{{ $status['percentPeriod'] }}%</td>
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

                        @forelse( $statistic['statuses']['type'][3] as $status)
                            <tr>
                                <td class="center middle name">{{ $status['name'] }}</td>
                                <td class="percent-col center middle countAll">{{ $status['countAll'] }}</td>
                                <td class="percent-col center middle allPercent">{{ $status['percentAll'] }}%</td>
                                <td class="percent-col center middle countPeriod">{{ $status['countPeriod'] }}</td>
                                <td class="percent-col center middle periodPercent">{{ $status['percentPeriod'] }}%</td>
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

                        @forelse( $statistic['statuses']['type'][4] as $status)
                            <tr>
                                <td class="center middle name">{{ $status['name'] }}</td>
                                <td class="percent-col center middle countAll">{{ $status['countAll'] }}</td>
                                <td class="percent-col center middle allPercent">{{ $status['percentAll'] }}%</td>
                                <td class="percent-col center middle countPeriod">{{ $status['countPeriod'] }}</td>
                                <td class="percent-col center middle periodPercent">{{ $status['percentPeriod'] }}%</td>
                            </tr>
                        @empty
                            <tr>
                                <td class="center statistics_no_data" colspan="5">No data</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Статусы по закрытым сделкам --}}
                <div class="table-statuses table-statuses-large table_status_block">
                    <table class="table closeDeal-statuses">
                        <thead>
                        <tr class="statistics_closeDeal_statuses">
                            <th colspan="5" class="middle center">Close Deal</th>
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

                        @forelse( $statistic['statuses']['type'][5] as $status)
                            <tr>
                                <td class="center middle name">{{ $status['name'] }}</td>
                                <td class="percent-col center middle countAll">{{ $status['countAll'] }}</td>
                                <td class="percent-col center middle allPercent">{{ $status['percentAll'] }}%</td>
                                <td class="percent-col center middle countPeriod">{{ $status['countPeriod'] }}</td>
                                <td class="percent-col center middle periodPercent">{{ $status['percentPeriod'] }}%</td>
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
                        @forelse($statistic['transitions'] as $transit)
                            <tr>
                                <td class="center middle fromStatus"> {{ $transit['fromStatus'] }} </td>
                                <td class="center middle statistics_transitions_statuses_arrow"> <i class="glyphicon glyphicon-arrow-right"></i> </td>
                                <td class="center middle toStatus"> {{ $transit['toStatus'] }} </td>
                                <td class="percent-col center middle allPercent">{{ $transit['percentAll'] }}%</td>
                                <td class="center middle status_{{ $transit['ratingAll'] }} rating">{{ $transit['ratingAll'] }}</td>
                                <td class="center middle statistics_transitions_emptyCall"></td>
                                <td class="percent-col center middle periodPercent">{{ $transit['percentPeriod'] }}%</td>
                                <td class="center middle status_{{ $transit['ratingPeriod'] }} rating">{{ $transit['ratingPeriod'] }}</td>
                            </tr>
                        @empty
                            <tr >
                                <td class="center statistics_no_data" colspan="8">No data</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

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
            sphere.find('.sphere-name').text( statistic['sphere']['name'] );

            // минимальное количество лидов которое нужно для вывода статистики
            sphere.find('.leads_needed').text( statistic['sphere']['minOpen'] );


            // обновляем данные по количеству открытых лидов
            sphere.find('.summary_table_open_all').text( statistic['openLeads']['all'] );
            sphere.find('.summary_table_open_period').text( statistic['openLeads']['period'] );

            // данные по количеству увиденных лидов
            sphere.find('.summary_table_seen_all').text( statistic['auction']['all'] );
            sphere.find('.summary_table_seen_period').text( statistic['auction']['period'] );

            // данные по количеству добавленных лидов
            sphere.find('.summary_table_added_all').text( statistic['sphere']['leads']['all'] );
            sphere.find('.summary_table_added_period').text( statistic['sphere']['leads']['period'] );

            // обновление количества агентов по сфере
            sphere.find('.sphere_agents_count').text( statistic['sphere']['users']['all'] );

            // обновление данных по открытым лидам с отсутствующим статусом
            sphere.find('.status_no_status .countAll').text( statistic['statuses']['noStatus']['countAll'] );
            sphere.find('.status_no_status .allPercent').text( statistic['statuses']['noStatus']['percentAll']+'%' );
            sphere.find('.status_no_status .countPeriod').text( statistic['statuses']['noStatus']['countPeriod'] );
            sphere.find('.status_no_status .periodPercent').text( statistic['statuses']['noStatus']['percentPeriod']+'%' );


            // выбираем таблицу аккаунт менеджеров
            var accManagersTable = sphere.find('.account_managers_table tbody');

            // очищаем таблицу
            accManagersTable.empty();

            // если нет транзитов
            if( statistic['sphere']['accManagers'].length == 0){
                // выводим что статусов нет

                // создание строки таблицы
                var accManagerTr = $('<tr />');

                // создание ячейки таблицы
                var noAccManagersRow = $('<td />');

                // добавление классов
                noAccManagersRow.addClass('center middle statistics_no_data');

                // добавляем атрибут объединения ячеек
                noAccManagersRow.attr('colspan', 3);

                // добавление данных в ячейки
                noAccManagersRow.text( 'No account managers' );

                // добавление ячеек в строку
                accManagerTr.append(noAccManagersRow);

                // добавление строки в таблицу
                accManagersTable.append( accManagerTr );
            }

            // перебираем все транзиты и наполняем таблицу
            $.each( statistic['sphere']['accManagers'], function( index, accManager ){

                // создание строки таблицы
                var tr = $('<tr />');

                // создание ячеек таблицы
                var email = $('<td />');
                var allAgents = $('<td />');
                var periodAgents = $('<td />');

                // добавление классов
                email.addClass('center middle');
                allAgents.addClass('center middle');
                periodAgents.addClass('center middle');

                // добавление данных в ячейки
                email.text( accManager['email'] );
                allAgents.html(accManager['allAgents'] );
                periodAgents.text( accManager['periodAgents'] );

                // добавление ячеек в строку
                tr.append( email );
                tr.append( allAgents );
                tr.append( periodAgents );

                // добавление строки в таблицу
                accManagersTable.append( tr );
            });



            // массив с типами статусов, по которым составляются таблицы
            var statusesType = [ 'process-statuses', 'undefined-statuses', 'fail-statuses', 'bad-statuses', 'closeDeal-statuses' ];

            // перебираем все типы статусов
            $.each( statusesType, function( key, type){

                // выбираем индекс типа
                var typeIndex = key + 1;

                // выбираем нужную таблицу
                var table = sphere.find('.' + type + ' tbody');

                // очищаем таблицу
                table.empty();

                // если нет статуса конкретного типа
                if( statistic['statuses']['type'][typeIndex].length == 0){
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
                $.each( statistic['statuses']['type'][typeIndex], function( statusKey, statusData ){

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
                    allPercent.text( statusData['percentAll'] + '%' );
                    countPeriod.text( statusData['countPeriod'] );
                    periodPercent.text( statusData['percentPeriod'] + '%' );

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
            if( statistic['transitions'].length == 0){
                // выводим что статусов нет

                // создание строки таблицы
                var tr = $('<tr />');

                // создание ячейки таблицы
                var noTransitionRow = $('<td />');

                // добавление классов
                noTransitionRow.addClass('center statistics_no_data');

                // добавляем атрибут объединения ячеек
                noTransitionRow.attr('colspan', 8);

                // добавление данных в ячейки
                noTransitionRow.text( 'No transitions' );

                // добавление ячеек в строку
                tr.append(noTransitionRow);

                // добавление строки в таблицу
                transitionTable.append( tr );
            }

            // перебираем все транзиты и наполняем таблицу
            $.each( statistic['transitions'], function( transitionKey, transitionData ){

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
                allRating.addClass('center middle rating status_' + transitionData['ratingAll']);
                emptyCol.addClass('center middle statistics_transitions_emptyCall');
                periodPercent.addClass('percent-col center middle periodPercent');
                periodRating.addClass('center middle rating status_' + transitionData['ratingPeriod']);

                // добавление данных в ячейки
                fromStatus.text( transitionData['fromStatus'] );
                arrow.html('<i class="glyphicon glyphicon-arrow-right"></i>');
                toStatus.text( transitionData['toStatus'] );
                allPercent.text( transitionData['percentAll'] );
                allRating.text( transitionData['ratingAll'] );
                emptyCol.text( '' );
                periodPercent.text( transitionData['percentPeriod'] + '%' );
                periodRating.text( transitionData['ratingPeriod'] );

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

            // заносим параметры
            var params ={ _token: '{{ csrf_token() }}', timeFrom: dataStart, timeTo: dataEnd, sphere_id: '{{ $statistic['sphere']['id'] }}' } ;

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