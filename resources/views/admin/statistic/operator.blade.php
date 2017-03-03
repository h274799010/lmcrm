@extends('admin.layouts.default')

{{-- Web site Title --}}
@section('title') {!! trans("admin/agent.agents") !!} :: @parent
@stop

{{-- Content --}}
@section('main')

    <div class="breadcrumb-wrapper">
        <ul class="breadcrumb">
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
                    <td>{{ $operator['created_at']->format('d/m/Y') }}</td>
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

                {{-- Сводная таблица данных по лидам оператора --}}
                <div class="row user_manager_block">
                    <div class="col-md-12">
                        <table class="table table-striped table-bordered operator_data_table">

                            <thead>
                            <tr class="user_leads_info_head">
                                <th colspan="9">Leads</th>
                            </tr>
                            <tr>
                                <th rowspan="2" class="center middle width-prc-20">for processing</th>
                                <th colspan="2" class="center middle">processed</th>
                                <th colspan="2" class="center middle">marked as bad</th>
                                <th colspan="2" class="center middle">added</th>
                                <th colspan="2" class="center middle">users banned</th>
                            </tr>
                            <tr>
                                <th class="center middle operator_count_col">all</th>
                                <th class="center middle operator_count_col">period</th>

                                <th class="center middle operator_count_col">all</th>
                                <th class="center middle operator_count_col">period</th>

                                <th class="center middle operator_count_col">all</th>
                                <th class="center middle operator_count_col">period</th>

                                <th class="center middle operator_count_col">all</th>
                                <th class="center middle operator_count_col">period</th>
                            </tr>
                            </thead>
                            <tbody>

                            <tr class="">

                                <td class="center middle operator_for_processing">{{ $statistic['leads']['for_processing'] }}</td>

                                <td class="center middle operator_processed_all">{{ $statistic['leads']['processed_all'] }}</td>
                                <td class="center middle operator_processed_period">{{ $statistic['leads']['processed_period'] }}</td>

                                <td class="center middle marked_bad_all">{{ $statistic['leads']['marked_bad_all'] }}</td>
                                <td class="center middle marked_bad_period">{{ $statistic['leads']['marked_bad_period'] }}</td>

                                <td class="center middle operator_added_all">{{ $statistic['leads']['added_all'] }}</td>
                                <td class="center middle operator_added_period">{{ $statistic['leads']['added_period'] }}</td>

                                <td class="center middle users_banned_all">{{ $statistic['leads']['users_banned_all'] }}</td>
                                <td class="center middle users_banned_period">{{ $statistic['leads']['users_banned_period'] }}</td>

                            </tr>
                            </tbody>
                        </table>




                        <table class="table table-striped operator_bad_leads_table">

                            <thead>
                            <tr class="operator_bad_leads_table_head center">
                                <th colspan="4">Leads marked as bad</th>
                            </tr>
                            <tr>
                                <th class="center middle">name</th>
                                <th class="center middle">phone</th>
                                <th class="center middle">email</th>
                                <th class="center middle">created_at</th>

                            </tr>
                            </thead>
                            <tbody>

                            @forelse($marked_bad as $bad)
                                <tr>

                                    <td class="center middle">{{ $bad['name'] }}</td>
                                    <td class="center middle">{{ $bad['phone']['phone'] }}</td>
                                    <td class="center middle">{{ $bad['email'] }}</td>
                                    <td class="center middle">{{ $bad['created_at'] }}</td>

                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="center middle">No bad leads</td>
                                </tr>
                            @endforelse

                            </tbody>
                        </table>



                    </div>
                </div>



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

        table.operator_data_table th{
            color: #80808A;
            font-size: 12px;
        }

        .operator_bad_leads_table{
            margin-top: 50px;
        }

        .operator_bad_leads_table_head th{
            color: white;
            background: #3F51B5;
            text-align: center;
        }

        .operator_bad_leads_table th{
            color: #80808A;
            font-size: 12px;
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
            sphere.find('.sphere-name').text( statistic['statistic']['sphere']['name'] );

            sphere.find('.operator_for_processing').text( statistic['statistic']['leads']['for_processing'] );
            sphere.find('.operator_processed_all').text( statistic['statistic']['leads']['processed_all'] );
            sphere.find('.operator_processed_period').text( statistic['statistic']['leads']['processed_period'] );


            sphere.find('.marked_bad_all').text( statistic['statistic']['leads']['marked_bad_all'] );
            sphere.find('.marked_bad_period').text( statistic['statistic']['leads']['marked_bad_period'] );


            sphere.find('.operator_added_all').text( statistic['statistic']['leads']['added_all'] );
            sphere.find('.operator_added_period').text( statistic['statistic']['leads']['added_period'] );


            sphere.find('.users_banned_all').text( statistic['statistic']['leads']['users_banned_all'] );
            sphere.find('.users_banned_period').text( statistic['statistic']['leads']['users_banned_period'] );


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