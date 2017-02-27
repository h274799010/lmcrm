@extends('admin.layouts.default')

{{-- Web site Title --}}
@section('title') {!! trans("admin/agent.agents") !!} :: @parent
@stop

{{-- Content --}}
@section('main')

    <div class="breadcrumb-wrapper">
        <ul class="breadcrumb" style="margin-bottom: 5px;">
            <li><a href="/">LM CRM</a></li>
            <li><a href="{{ route('admin.statistic.operators') }}">Operator statistic</a></li>
            <li class="active">Operator: {{ $operator['email'] }}</li>
        </ul>
    </div>

    {{--таблица с данными по агенту--}}

    <div class="row">

        <div class="col-md-5">
            <table class="agent_data_table">
                <thead>
                <tr>
                    <th colspan="2">Operator data</th>

                </tr>
                </thead>
                <tbody>
                <tr>
                    <th>first name:</th>
                    <td>{{ $operator['first_name'] }}</td>
                </tr>
                <tr>
                    <th>last name:</th>
                    <td>{{ $operator['last_name'] }}</td>
                </tr>
                <tr>
                    <th>email:</th>
                    <td>{{ $operator['email'] }}</td>
                </tr>
                <tr>
                    <th>registration date:</th>
                    <td>{{ $operator['created_at'] }}</td>
                </tr>
                {{--<tr>--}}
                    {{--<th>add to sphere date:</th>--}}
                    {{--<td>{{ $statistic['user']['addToSphere'] }}</td>--}}
                {{--</tr>--}}
                </tbody>
            </table>
            <a class="" href="{{ route('admin.user.edit', ['id'=>$operator['id']]) }}">
                edit
            </a>
        </div>
    </div>



    {{-- Проверка есть ли у пользователя сферы --}}
    @if($spheres->count() == 0)
        {{-- Если сфер нету --}}

        {{--todo дооформить, поставить по средине, цвет сделать серый--}}
        No spheres
    @else
        {{-- Если сферы есть --}}

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

            {{-- селект с названием сферы --}}
            <div class="col-md-2 col-md-offset-7">
                <div class="form-group">
                    <label class="control-label _col-sm-2">Sphere</label><br>
                    <select name="sphere name" id="sphere_select" class="sphere_select">

                        @foreach( $spheres as $sphere)
                            <option value="{{ $sphere['id'] }}">{{ $sphere['name'] }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

        </div>

        <div id="statisticWrapper">

            <div class="row sphere_status_block">

                <h4 class="statistic-sphere-name">
                    <span class="sphere-name">{{ $currentSphere['name'] }}</span>
                </h4>

                {{-- Сводная таблица данных по лидам пользователя --}}
                <div class="row user_manager_block">
                    <div class="col-md-12">
                        <table class="table table-striped table-bordered">

                            <thead>
                            <tr class="user_leads_info_head">
                                <th colspan="5">Leads</th>
                            </tr>
                            <tr>
                                <th rowspan="2" class="center middle width-prc-20">for processing</th>
                                <th colspan="2" class="center middle">processed</th>
                                <th colspan="2" class="center middle">added</th>
                            </tr>
                            <tr>
                                <th class="center middle width-prc-20">all</th>
                                <th class="center middle width-prc-20">period</th>
                                <th class="center middle width-prc-20">all</th>
                                <th class="center middle width-prc-20">period</th>
                            </tr>
                            </thead>
                            <tbody>

                            <tr class="">

                                <td class="center middle operator_for_processing">{{ $statistic['leads']['for_processing'] }}</td>

                                <td class="center middle operator_processed_all">{{ $statistic['leads']['processed_all'] }}</td>
                                <td class="center middle operator_processed_period">{{ $statistic['leads']['processed_period'] }}</td>

                                <td class="center middle operator_added_all">{{ $statistic['leads']['added_all'] }}</td>
                                <td class="center middle operator_added_period">{{ $statistic['leads']['added_period'] }}</td>

                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>


                @if( false )

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

                @endif

            </div>

        </div>

    @endif

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

            // обновление данных в селекте сферы
            var sphereSelect = $('#sphere_select');

            // сохраняем значения селекта в переменную
            var selectVal = sphereSelect.val();

            // очищаем данные по селекта по сфере
            sphereSelect.empty();

            // перебираем все сферы и заносим их в селект
            $.each( statistic['spheres'], function( key, sphere ){

                // создаем новую опцию селекта
                var option = $('<option/>');

                // записываем в опцию id
                option.val( sphere.id );
                // записываем в опцию имя
                option.text( sphere.name );

                // добавление в опции в селект
                sphereSelect.append(option);
            });

            // восстанавливаем значение селекта
            sphereSelect.val(selectVal);

            // выбираем блок со сферой
            var sphere = $('.sphere_status_block');

            // обновление имени сферы
            sphere.find('.for_processing').text( statistic['statistic']['sphere']['name'] );

            sphere.find('.operator_for_processing').text( statistic['statistic']['leads']['for_processing'] );
            sphere.find('.operator_processed_all').text( statistic['statistic']['leads']['processed_all'] );
            sphere.find('.operator_processed_period').text( statistic['statistic']['leads']['processed_period'] );
            sphere.find('.operator_added_all').text( statistic['statistic']['leads']['added_all'] );
            sphere.find('.operator_added_period').text( statistic['statistic']['leads']['added_period'] );


            {{--<td class="center middle operator_for_processing">{{ $statistic['leads']['for_processing'] }}</td>--}}
        {{----}}
            {{--<td class="center middle operator_processed_all">{{ $statistic['leads']['processed_all'] }}</td>--}}
            {{--<td class="center middle operator_processed_period">{{ $statistic['leads']['processed_period'] }}</td>--}}
    {{----}}
            {{--<td class="center middle operator_added_all">{{ $statistic['leads']['added_all'] }}</td>--}}
            {{--<td class="center middle operator_added_period">{{ $statistic['leads']['added_period'] }}</td>--}}






        }

        // функция отправки данных на сервер для обновления периода и прочего
        function loadStatistic() {

            // выбираем данные селекта сфер (id сферы)
            var sphereSelect = $('#sphere_select').val();

            // заносим параметры
            var params ={ _token: '{{ csrf_token() }}', agent_id: '{{ $operator->id }}', timeFrom: dataStart, timeTo: dataEnd, sphere_id: sphereSelect } ;

            // отправка запроса на сервер
            $.post(
                '{{ route('admin.statistic.operatorData') }}',
                params,
                function (data)
                {
                    // обработка данных полученных с фронтенда
                    if(data == 'false'){
                        // у агента нет сфер

                        // просто обновляем страницу
                        document.location.reload();

                    }else{
                        // все данные в порядке

                        // обновляем статистику на странице
                        statisticUpdate( data );

                        // todo удалить
//                        console.log( data );
                    }
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

            // отправка запроса на обновление данных при изменении сферы
            $(document).on('change', '#sphere_select', function () {
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

            // переключение права пользователя (если true в false и на оборот)
            $('.user_permission').bind('click', function(){

                var self = this;

                // выбираем правило которое нужно изменить
                var permission = $(this).data('permission');

                // заносим параметры
                var params ={ _token: '{{ csrf_token() }}', agent_id: '{{ $operator->id }}', permission: permission };

                // отправка запроса на сервер
                $.post(
                    '{{ route('admin.agent.permission.switch') }}',
                    params,
                    function(data){
                        // проверяем статус
                        if( data.status == true ){
                            // если статус true

                            // меняем классы в арзрешениях на соответствующие
                            if(data.permissions[permission]){

                                $(self).removeClass('glyphicon-ban-circle icon_red');
                                $(self).addClass('glyphicon-ok icon_green');

                            }else{

                                $(self).removeClass('glyphicon-ok icon_green');
                                $(self).addClass('glyphicon-ban-circle icon_red');

                            }
                        }
                    }
                );
            });

        });
    </script>
@stop