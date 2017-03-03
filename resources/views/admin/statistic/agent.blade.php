@extends('admin.layouts.default')

{{-- Web site Title --}}
@section('title') {!! trans("admin/agent.agents") !!} :: @parent
@stop

{{-- Content --}}
@section('main')

    <div class="breadcrumb-wrapper">
        <ul class="breadcrumb">
            <li><a href="/">LM CRM</a></li>
            <li><a href="{{ route('admin.statistic.agents') }}">Agents statistic</a></li>
            <li class="active">Agent: {{ $statistic['user']['email'] }}</li>
        </ul>
    </div>

    {{--таблица с данными по агенту--}}
    <div class="row">

        <div class="col-md-5">
            <table class="agent_data_table">
                <thead>
                <tr>
                    <th colspan="2">Agent data</th>

                </tr>
                </thead>
                <tbody>
                <tr>
                    <th>first name:</th>
                    <td>{{ $statistic['user']['first_name'] }}</td>
                </tr>
                <tr>
                    <th>last name:</th>
                    <td>{{ $statistic['user']['last_name'] }}</td>
                </tr>
                <tr>
                    <th>email:</th>
                    <td>{{ $statistic['user']['email'] }}</td>
                </tr>
                <tr>
                    <th>role:</th>
                    <td>{{ $statistic['user']['subRole'] }}</td>
                </tr>
                <tr>
                    <th>salesmen's count:</th>
                    <td>{{ $statistic['user']['salesmanCount'] }} ({{ $statistic['user']['salesmanBannedCount'] }} banned)</td>
                </tr>
                <tr>
                    <th>registration date:</th>
                    <td>{{ $statistic['user']['created_at'] }}</td>
                </tr>
                <tr>
                    <th>add to sphere date:</th>
                    <td>{{ $statistic['user']['addToSphere'] }}</td>
                </tr>
                </tbody>
            </table>
            <a class="" href="{{ route('admin.user.edit', ['id'=>$statistic['user']['id']]) }}">
                edit
            </a>
        </div>

        <div class="col-md-4">

            <h5>Permissions</h5>

            <ul class="list-group permissions_group">
                @foreach( $statistic['user']['permissions'] as $permission=>$status )
                    <li data-status="{{ $status ? 'true':'false' }}" data-permission="{{ $permission }}" class="list-group-item">
                        <i data-permission="{{ $permission }}" class="glyphicon @if($status) glyphicon-ok icon_green user_permission @else glyphicon-ban-circle icon_red user_permission @endif "></i>
                        {{ trans('admin/users.permissions.' .$permission) }}
                    </li>
                @endforeach
            </ul>
            <div class="permissions_change">change</div>

        </div>

    </div>



    {{-- Проверка есть ли у пользователя сферы --}}
    @if($spheres->count() == 0)
        {{-- Если сфер нету --}}

        {{--todo дооформить, поставить по средине, цвет сделать серый--}}
        No spheres
    @else
        {{-- Если есть хоть одна сфера --}}

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
                    <span class="sphere-name">{{ $statistic['sphere']['name'] }}</span>
                </h4>

               {{-- Сводная таблица данных по лидам пользователя --}}
                <div class="row user_manager_block">
                    <div class="col-md-12">
                        <table class="table table-striped table-bordered user_leads_info">

                            <thead>
                            <tr class="user_leads_info_head">
                                <th colspan="10">Leads</th>
                            </tr>
                            <tr>
                                <th colspan="2" class="center middle">added</th>

                                <th colspan="2" class="center middle">operator banned</th>

                                <th colspan="2" class="center middle">users banned</th>

                                <th colspan="2" class="center middle">seen</th>
                                <th colspan="2" class="center middle">open</th>
                            </tr>
                            <tr>
                                <th class="center middle">all</th>
                                <th class="center middle">period</th>

                                <th class="center middle">all</th>
                                <th class="center middle">period</th>

                                <th class="center middle">all</th>
                                <th class="center middle">period</th>

                                <th class="center middle">all</th>
                                <th class="center middle">period</th>
                                <th class="center middle">all</th>
                                <th class="center middle">period</th>
                            </tr>
                            </thead>
                            <tbody>

                                <tr class="">

                                    <td class="center middle summary_table_added_all"> {{ $statistic['added']['all'] }} </td>
                                    <td class="center middle summary_table_added_period"> {{ $statistic['added']['period'] }} </td>

                                    <td class="center middle summary_table_operator_bad_all"> {{ $statistic['operatorBad']['all'] }}</td>
                                    <td class="center middle summary_table_operator_bad_period"> {{ $statistic['operatorBad']['period'] }}</td>

                                    <td class="center middle summary_table_users_bad_all"> {{ $statistic['agentBad']['all'] }}</td>
                                    <td class="center middle summary_table_users_bad_period"> {{ $statistic['agentBad']['period'] }}</td>

                                    <td class="center middle summary_table_seen_all"> {{ $statistic['auction']['all'] }} </td>
                                    <td class="center middle summary_table_seen_period"> {{ $statistic['auction']['period'] }} </td>

                                    <td class="center middle summary_table_open_all"> {{ $statistic['openLeads']['all'] }} </td>
                                    <td class="center middle summary_table_open_period"> {{ $statistic['openLeads']['period'] }} </td>

                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>


                {{-- если пользователь агент, выводим таблицу его продавцов --}}
                @if( $statistic['user']['role'] == 'agent' )

                    {{-- Таблица чистых данных по текущему агенту (данные только агента, без салесманов) --}}
                    <div class="row user_manager_block">
                        <div class="col-md-12">
                            <table class="table table-striped table-bordered just_agent_table">

                                <thead>
                                <tr class="just_agent_table_head">
                                    <th colspan="10">Just a agent data</th>
                                </tr>
                                <tr>
                                    <th colspan="2" class="center middle">leads added</th>

                                    <th colspan="2" class="center middle">operator banned</th>

                                    <th colspan="2" class="center middle">users banned</th>

                                    <th colspan="2" class="center middle">leads seen</th>

                                    <th colspan="2" class="center middle">leads open</th>
                                </tr>
                                <tr>
                                    <th class="center middle just_agent_count_data">all</th>
                                    <th class="center middle just_agent_count_data">period</th>

                                    <th class="center middle just_agent_count_data">all</th>
                                    <th class="center middle just_agent_count_data">period</th>

                                    <th class="center middle just_agent_count_data">all</th>
                                    <th class="center middle just_agent_count_data">period</th>


                                    <th class="center middle just_agent_count_data">all</th>
                                    <th class="center middle just_agent_count_data">period</th>

                                    <th class="center middle just_agent_count_data">all</th>
                                    <th class="center middle just_agent_count_data">period</th>
                                </tr>
                                </thead>
                                <tbody>

                                    <tr>

                                        <td class="center middle just_agent_added_all"> {{ $statistic['user']['justAgent']['added']['all'] }} </td>
                                        <td class="center middle just_agent_added_period"> {{ $statistic['user']['justAgent']['added']['period'] }} </td>

                                        <td class="center middle just_agent_operatorBad_all"> {{ $statistic['user']['justAgent']['operatorBad']['all'] }}</td>
                                        <td class="center middle just_agent_operatorBad_period"> {{ $statistic['user']['justAgent']['operatorBad']['period'] }}</td>

                                        <td class="center middle just_agent_agentBad_all"> {{ $statistic['user']['justAgent']['agentBad']['all'] }}</td>
                                        <td class="center middle just_agent_agentBad_period"> {{ $statistic['user']['justAgent']['agentBad']['period'] }}</td>

                                        <td class="center middle just_agent_auction_all"> {{ $statistic['user']['justAgent']['auction']['all'] }} </td>
                                        <td class="center middle just_agent_auction_period"> {{ $statistic['user']['justAgent']['auction']['period'] }} </td>

                                        <td class="center middle just_agent_openLeads_all"> {{ $statistic['user']['justAgent']['openLeads']['all'] }} </td>
                                        <td class="center middle just_agent_openLeads_period"> {{ $statistic['user']['justAgent']['openLeads']['period'] }} </td>

                                    </tr>

                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Таблица салесманов --}}
                    <div class="row user_manager_block">
                        <div class="col-md-12">
                            <table class="table table-striped table-bordered salesmen_table">

                                <thead>
                                <tr class="salesmen_table_head">
                                    <th colspan="14">Salesmen</th>
                                </tr>
                                <tr>
                                    <th rowspan="2" class="center middle">name</th>
                                    <th colspan="2" class="center middle">leads added</th>

                                    <th colspan="2" class="center middle">operator banned</th>
                                    <th colspan="2" class="center middle">users banned</th>

                                    <th colspan="2" class="center middle">leads seen</th>
                                    <th colspan="2" class="center middle">leads open</th>
                                    <th rowspan="2" class="center middle">presence</th>
                                    <th rowspan="2" class="center middle">status</th>
                                    <th rowspan="2" class="center middle">action</th>
                                </tr>
                                <tr>
                                    <th class="center middle salesman_count_data">all</th>
                                    <th class="center middle salesman_count_data">period</th>


                                    <th class="center middle salesman_count_data">all</th>
                                    <th class="center middle salesman_count_data">period</th>

                                    <th class="center middle salesman_count_data">all</th>
                                    <th class="center middle salesman_count_data">period</th>


                                    <th class="center middle salesman_count_data">all</th>
                                    <th class="center middle salesman_count_data">period</th>
                                    <th class="center middle salesman_count_data">all</th>
                                    <th class="center middle salesman_count_data">period</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse( $statistic['user']['salesmenData'] as $salesman )
                                    <tr class="">
                                        <td class="center middle"> {{ $salesman['user']['email'] }} </td>

                                        <td class="center middle"> {{ $salesman['added']['all'] }} </td>
                                        <td class="center middle"> {{ $salesman['added']['period'] }} </td>

                                        <td class="center middle"> {{ $salesman['operatorBad']['all'] }}</td>
                                        <td class="center middle"> {{ $salesman['operatorBad']['period'] }}</td>

                                        <td class="center middle"> {{ $salesman['agentBad']['all'] }}</td>
                                        <td class="center middle"> {{ $salesman['agentBad']['period'] }}</td>

                                        <td class="center middle"> {{ $salesman['auction']['all'] }} </td>
                                        <td class="center middle"> {{ $salesman['auction']['period'] }} </td>

                                        <td class="center middle"> {{ $salesman['openLeads']['all'] }} </td>
                                        <td class="center middle"> {{ $salesman['openLeads']['period'] }} </td>

                                        <td class="center middle"> @if($salesman['sphere']['presence']) yes @else no @endif </td>

                                        <td class="center middle">@if($salesman['user']['active']) Active @else Banned @endif </td>

                                        <td class="center middle">
                                            <a class="btn btn-sm btn-success" href="{{ route('admin.statistic.agent', ['id'=>$salesman['user']['id']]) }}">
                                                detail
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr class="status_no_status">
                                        <td colspan="10" class="center middle statistics_no_data"> No salesmen's </td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                @endif

                {{-- Проверяем достаточно ли у агента открытых лидов по сфере для статистики --}}
                @if( $statistic['user']['statisticStatus'] )
                    {{-- по сфере достаточно открытых лидов для статистики --}}

                    <div class="col-md-12 table_status_block sphere_no_data center  hidden ">
                        Not enough open leads for statistics. <span class="leads_needed"></span> open leads needed
                    </div>

                    {{-- Общие данные - no status --}}
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

                    {{-- Сводная таблица по всем типам статусов --}}
                    <div class="table-statuses table-statuses-large table_status_block">
                        <table class="table table-bordered groups-statuses">
                            <thead>
                                <tr class="statistics_groups_statuses">
                                    <th colspan="13" class="middle center">Statuses by groups</th>
                                </tr>
                                <tr>
                                    <th rowspan="2" class="center middle">time period</th>

                                    <th colspan="2" class="center middle">No status</th>
                                    <th colspan="2" class="center middle">Process</th>
                                    <th colspan="2" class="center middle">Uncertain</th>
                                    <th colspan="2" class="center middle">Refuseniks</th>
                                    <th colspan="2" class="center middle">Bad</th>
                                    <th colspan="2" class="center middle">Close Deal</th>
                                </tr>

                                <tr>

                                    <th class="center middle">amount</th>
                                    <th class="center middle">percent</th>

                                    <th class="center middle">amount</th>
                                    <th class="center middle">percent</th>

                                    <th class="center middle">amount</th>
                                    <th class="center middle">percent</th>

                                    <th class="center middle">amount</th>
                                    <th class="center middle">percent</th>

                                    <th class="center middle">amount</th>
                                    <th class="center middle">percent</th>

                                    <th class="center middle">amount</th>
                                    <th class="center middle">percent</th>

                                </tr>

                            </thead>
                            <tbody>

                                <tr>
                                    <th class="center middle name"> all </th>

                                    <td class="percent-col center middle group_no_status_countAll">{{ $statistic['statuses']['noStatus']['countAll'] }}</td>
                                    <td class="percent-col center middle group_no_status_allPercent">{{ $statistic['statuses']['noStatus']['percentAll'] }}%</td>

                                    <td class="percent-col center middle group_process_countAll"></td>
                                    <td class="percent-col center middle group_process_allPercent"></td>

                                    <td class="percent-col center middle group_uncertain_countAll"></td>
                                    <td class="percent-col center middle group_uncertain_allPercent"></td>

                                    <td class="percent-col center middle group_refuseniks_countAll"></td>
                                    <td class="percent-col center middle group_refuseniks_allPercent"></td>

                                    <td class="percent-col center middle group_bad_countAll"></td>
                                    <td class="percent-col center middle group_bad_allPercent"></td>

                                    <td class="percent-col center middle group_close_deal_countAll"></td>
                                    <td class="percent-col center middle group_close_deal_allPercent"></td>
                                </tr>

                                <tr>
                                    <th class="center middle name"> period </th>

                                    <td class="percent-col center middle group_no_status_countPeriod">{{ $statistic['statuses']['noStatus']['countPeriod'] }}</td>
                                    <td class="percent-col center middle group_no_status_periodPercent">{{ $statistic['statuses']['noStatus']['percentPeriod'] }}%</td>

                                    <td class="percent-col center middle group_process_countPeriod"></td>
                                    <td class="percent-col center middle group_process_periodPercent"></td>

                                    <td class="percent-col center middle group_uncertain_countPeriod"></td>
                                    <td class="percent-col center middle group_uncertain_periodPercent"></td>

                                    <td class="percent-col center middle group_refuseniks_countPeriod"></td>
                                    <td class="percent-col center middle group_refuseniks_periodPercent"></td>

                                    <td class="percent-col center middle group_bad_countPeriod"></td>
                                    <td class="percent-col center middle group_bad_periodPercent"></td>

                                    <td class="percent-col center middle group_close_deal_countPeriod"></td>
                                    <td class="percent-col center middle group_close_deal_periodPercent"></td>
                                </tr>

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
                                <tr data-transitId="{{ $transit['transitionId'] }}">
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


                @else
                    {{-- по сфере недостаточно откртых лидов для статистики --}}

                        <div class="col-md-12 table_status_block sphere_no_data center">
                            Not enough open leads for statistics. <span class="leads_needed"> {{ $statistic['sphere']['minOpen'] }} </span> open leads needed                        </div>
                @endif

            </div>

        </div>

    @endif



    <!-- Transition modal -->
    <div id="transitionDetailModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">Transition</h4>
                </div>
                <div class="modal-body">

                    <div class="row modal_transition_head">
                        <div class="center col-md-4 modal_status_from">
                        </div>
                        <div class="center col-md-4">
                            <i class="glyphicon glyphicon-arrow-right"></i>
                        </div>
                        <div class="center col-md-4 modal_status_to">
                        </div>
                    </div>

                    <table class="modal_current_user_table">
                        <thead>
                        </thead>
                        <tbody>
                            <tr>
                                <th>agent:</th><td class="modal_current_user_name"></td>
                            </tr>
                            <tr>
                                <th>agent percent:</th><td class="modal_current_user_percent"></td>
                            </tr>
                            <tr>
                                <th>overall percent:</th><td class="modal_overall_percent"></td>
                            </tr>
                        </tbody>
                    </table>


                    <table class="table modal_leads_table">
                        <thead>
                            <tr>
                                <th>name</th>
                                <th>phone</th>
                                <th>email</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Permission modal -->
    <div id="permissionModal" class="modal fade">
        <div class="modal-dialog permission-modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h4 class="modal-title">Change permissions</h4>
                </div>
                <div class="modal-body">

                    <ul class="list-group modal_permissions_group">
                    </ul>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary modal_permissions_update">update</button>
                </div>
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

        /**
         * Дата начала периода
         *
         */
        var dataStart = 0;

        /**
         * Дата окончания периода
         *
         */
        var dataEnd = 0;

        /**
         * Переменная с разрешениями агента
         *
         * сами разрешения агента отображаются в двух местах:
         * на странице и в модальном окне переключения
         * Эта переменная нужна чтобы облегчить синхронизацию данных
         *
         */
        var agentPermissions = [];

        /**
         * Обновления данных статистики на странице
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

            // проверка на статус статистики
            if( !statistic['statistic']['user']['statisticStatus'] ){
                // если статистики по какой то причине нет

                sphere.find('.topStatusTable').addClass('hidden');
                sphere.find('.process-statuses').addClass('hidden');
                sphere.find('.undefined-statuses').addClass('hidden');
                sphere.find('.fail-statuses').addClass('hidden');
                sphere.find('.bad-statuses').addClass('hidden');
                sphere.find('.performance-table').addClass('hidden');
                sphere.find('.closeDeal-statuses').addClass('hidden');
                sphere.find('.groups-statuses').addClass('hidden');

                sphere.find('.sphere_no_data').removeClass('hidden');

            }else {
                // если статистика есть

                sphere.find('.topStatusTable').removeClass('hidden');
                sphere.find('.process-statuses').removeClass('hidden');
                sphere.find('.undefined-statuses').removeClass('hidden');
                sphere.find('.fail-statuses').removeClass('hidden');
                sphere.find('.bad-statuses').removeClass('hidden');
                sphere.find('.performance-table').removeClass('hidden');
                sphere.find('.closeDeal-statuses').removeClass('hidden');
                sphere.find('.groups-statuses').removeClass('hidden');

                sphere.find('.sphere_no_data').addClass('hidden');
            }

            // минимальное количество лидов которое нужно для вывода статистики
            sphere.find('.leads_needed').text( statistic['statistic']['sphere']['minOpen'] );

            // обновляем данные по количеству добавленных лидов
            sphere.find('.summary_table_added_all').text( statistic['statistic']['added']['all'] );
            sphere.find('.summary_table_added_period').text( statistic['statistic']['added']['period'] );

            // обновляем данные по количеству лидов забаненных оператором
            sphere.find('.summary_table_operator_bad_all').text( statistic['statistic']['operatorBad']['all'] );
            sphere.find('.summary_table_operator_bad_period').text( statistic['statistic']['operatorBad']['period'] );

            // обновляем данные по количеству забаненных агентами
            sphere.find('.summary_table_users_bad_all').text( statistic['statistic']['agentBad']['all'] );
            sphere.find('.summary_table_users_bad_period').text( statistic['statistic']['agentBad']['period'] );

            // обновляем данные по количеству открытых лидов
            sphere.find('.summary_table_open_all').text( statistic['statistic']['openLeads']['all'] );
            sphere.find('.summary_table_open_period').text( statistic['statistic']['openLeads']['period'] );

            // данные по количеству увиденных лидов
            sphere.find('.summary_table_seen_all').text( statistic['statistic']['auction']['all'] );
            sphere.find('.summary_table_seen_period').text( statistic['statistic']['auction']['period'] );

            // обновление даты регистрации и добавления агента в сферу
            sphere.find('.user_created_at').text( statistic['statistic']['user']['created_at'] );
            sphere.find('.user_addToSphere').text( statistic['statistic']['user']['addToSphere'] );

            // обновление данных по открытым лидам с отсутствующим статусом
            // в таблице отсутствующих статусов
            sphere.find('.status_no_status .countAll').text( statistic['statistic']['statuses']['noStatus']['countAll'] );
            sphere.find('.status_no_status .allPercent').text( statistic['statistic']['statuses']['noStatus']['percentAll']+'%' );
            sphere.find('.status_no_status .countPeriod').text( statistic['statistic']['statuses']['noStatus']['countPeriod'] );
            sphere.find('.status_no_status .periodPercent').text( statistic['statistic']['statuses']['noStatus']['percentPeriod']+'%' );
            // в таблице статусов по типам
            sphere.find('.groups-statuses .group_no_status_countAll').text( statistic['statistic']['statuses']['noStatus']['countAll'] );
            sphere.find('.groups-statuses .group_no_status_allPercent').text( statistic['statistic']['statuses']['noStatus']['percentAll']+'%' );
            sphere.find('.groups-statuses .group_no_status_countPeriod').text( statistic['statistic']['statuses']['noStatus']['countPeriod'] );
            sphere.find('.groups-statuses .group_no_status_periodPercent').text( statistic['statistic']['statuses']['noStatus']['percentPeriod']+'%' );

            // обновление "чистых" данных по лиду
            sphere.find('.just_agent_table .just_agent_added_all').text( statistic['statistic']['user']['justAgent']['added']['all'] );
            sphere.find('.just_agent_table .just_agent_added_period').text( statistic['statistic']['user']['justAgent']['added']['period'] );
            sphere.find('.just_agent_table .just_agent_operatorBad_all').text( statistic['statistic']['user']['justAgent']['operatorBad']['all'] );
            sphere.find('.just_agent_table .just_agent_operatorBad_period').text( statistic['statistic']['user']['justAgent']['operatorBad']['period'] );
            sphere.find('.just_agent_table .just_agent_agentBad_all').text( statistic['statistic']['user']['justAgent']['agentBad']['all'] );
            sphere.find('.just_agent_table .just_agent_agentBad_period').text( statistic['statistic']['user']['justAgent']['agentBad']['period'] );
            sphere.find('.just_agent_table .just_agent_auction_all').text( statistic['statistic']['user']['justAgent']['auction']['all'] );
            sphere.find('.just_agent_table .just_agent_auction_period').text( statistic['statistic']['user']['justAgent']['auction']['period'] );
            sphere.find('.just_agent_table .just_agent_openLeads_all').text( statistic['statistic']['user']['justAgent']['openLeads']['all'] );
            sphere.find('.just_agent_table .just_agent_openLeads_period').text( statistic['statistic']['user']['justAgent']['openLeads']['period'] );

            if( $('.salesmen_table tbody tr').length != 0){

                // выбираем таблицу салесманов
                var salesmanTable = sphere.find('.salesmen_table tbody');

                // очищаем таблицу
                salesmanTable.empty();

                // если салесманов нет
                if( statistic['statistic']['user']['salesmenData'].length == 0){
                    // выводим что таблица пустая

                    // создание строки таблицы
                    var tr = $('<tr />');

                    // создание ячейки таблицы
                    var noStatusRow = $('<td />');

                    // добавление классов
                    noStatusRow.addClass('center statistics_no_data');

                    // добавляем атрибут объединения ячеек
                    noStatusRow.attr('colspan', 14);

                    // добавление данных в ячейки
                    noStatusRow.text( 'No salesmen\'s' );

                    // добавление ячеек в строку
                    tr.append(noStatusRow);

                    // добавление строки в таблицу
                    salesmanTable.append( tr );
                }


                // перебираем салесманов и наполняем таблицу
                $.each( statistic['statistic']['user']['salesmenData'], function( statusKey, salesmenData ){

                    // создание строки таблицы
                    var tr = $('<tr />');

                    // создание ячеек таблицы
                    var name = $('<td />');

                    var allAdded = $('<td />');
                    var periodAdded = $('<td />');

                    var allOperatorBad = $('<td />');
                    var periodOperatorBad = $('<td />');

                    var allAgentBad = $('<td />');
                    var periodAgentBad = $('<td />');

                    var allSeen = $('<td />');
                    var periodSeen = $('<td />');

                    var allOpen = $('<td />');
                    var periodOpen = $('<td />');

                    var presence = $('<td />');

                    var status = $('<td />');

                    var action = $('<td />');

                    var link = $('<a />');

                    var presenceData = salesmenData['sphere']['presence'] ? 'yes' : 'no';



                    // добавление классов
                    name.addClass('middle');

                    allAdded.addClass('center middle');
                    periodAdded.addClass('center middle');

                    allOperatorBad.addClass('center middle');
                    periodOperatorBad.addClass('center middle');

                    allAgentBad.addClass('center middle');
                    periodAgentBad.addClass('center middle');

                    allSeen.addClass('center middle');
                    periodSeen.addClass('center middle');

                    allOpen.addClass('center middle');
                    periodOpen.addClass('center middle');

                    presence.addClass('center middle');

                    status.addClass('center middle');

                    action.addClass('center middle');

                    link.addClass('btn btn-sm btn-success');

                    link.attr('href', '{{ route('admin.statistic.agent', ['id'=>'']) }}/' + salesmenData['user']['id'] );




                    // определение права пользователя (активный или забаненный
                    var active = salesmenData['user']['active'] ? 'Active' : 'Banned';




                    // добавление данных в ячейки
                    name.text( salesmenData['user']['email'] );

                    allAdded.text( salesmenData['added']['all'] );
                    periodAdded.text( salesmenData['added']['period'] );



                    allOperatorBad.text( salesmenData['operatorBad']['all'] );
                    periodOperatorBad.text( salesmenData['operatorBad']['period'] );

                    allAgentBad.text( salesmenData['agentBad']['all'] );
                    periodAgentBad.text( salesmenData['agentBad']['period'] );



                    allSeen.text( salesmenData['auction']['all'] );
                    periodSeen.text( salesmenData['auction']['period'] );

                    allOpen.text( salesmenData['openLeads']['all'] );
                    periodOpen.text( salesmenData['openLeads']['period'] );

                    presence.text( presenceData );

                    link.text('detail');

                    status.text( active );

                    action.append( link );


                    // добавление ячеек в строку
                    tr.append(name);

                    tr.append(allAdded);
                    tr.append(periodAdded);




                    tr.append(allOperatorBad);
                    tr.append(periodOperatorBad);

                    tr.append(allAgentBad);
                    tr.append(periodAgentBad);




                    tr.append(allSeen);
                    tr.append(periodSeen);

                    tr.append(allOpen);
                    tr.append(periodOpen);

                    tr.append(presence);

                    tr.append(status);

                    tr.append(action);


                    // добавление строки в таблицу
                    salesmanTable.append( tr );
                });


            }

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
                if( statistic['statistic']['statuses']['type'][typeIndex].length == 0){
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

                    // добавляем данные в таблицу групп статусов в зависимости от типа статуса

                    // стили статусов
                    var styleCountAll;
                    var styleAllPercent;
                    var styleCountPeriod;
                    var stylePeriodPercent;

                    switch(typeIndex){

                        case 1:
                            styleCountAll = ' .group_process_countAll';
                            styleAllPercent = ' .group_process_allPercent';
                            styleCountPeriod = ' .group_process_countPeriod';
                            stylePeriodPercent = ' .group_process_periodPercent';
                            break;

                        case 2:
                            styleCountAll = ' .group_uncertain_countAll';
                            styleAllPercent = ' .group_uncertain_allPercent';
                            styleCountPeriod = ' .group_uncertain_countPeriod';
                            stylePeriodPercent = ' .group_uncertain_periodPercent';
                            break;

                        case 3:
                            styleCountAll = ' .group_refuseniks_countAll';
                            styleAllPercent = ' .group_refuseniks_allPercent';
                            styleCountPeriod = ' .group_refuseniks_countPeriod';
                            stylePeriodPercent = ' .group_refuseniks_periodPercent';
                            break;

                        case 4:
                            styleCountAll = ' .group_bad_countAll';
                            styleAllPercent = ' .group_bad_allPercent';
                            styleCountPeriod = ' .group_bad_countPeriod';
                            stylePeriodPercent = ' .group_bad_periodPercent';
                            break;

                        case 5:
                            styleCountAll = ' .group_close_deal_countAll';
                            styleAllPercent = ' .group_close_deal_allPercent';
                            styleCountPeriod = ' .group_close_deal_countPeriod';
                            stylePeriodPercent = ' .group_close_deal_periodPercent';
                            break;
                    }

                    sphere.find('.groups-statuses' + styleCountAll).text( '0' );
                    sphere.find('.groups-statuses' + styleAllPercent).text( '0%' );
                    sphere.find('.groups-statuses' + styleCountPeriod).text( '0' );
                    sphere.find('.groups-statuses' + stylePeriodPercent).text( '0%' );
                }

                // перебираем все статусы и наполняем таблицу
                $.each( statistic['statistic']['statuses']['type'][typeIndex], function( statusKey, statusData ){

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

                    // если присутствует тип - добавляем класс к строке
                    if( statusData['type'] ){

                        // добавляем строке класс count_summary
                        tr.addClass('count_summary');

                        // добавляем данные в таблицу групп статусов в зависимости от типа статуса

                        // стили статусов
                        var styleCountAll;
                        var styleAllPercent;
                        var styleCountPeriod;
                        var stylePeriodPercent;

                        switch(typeIndex){

                            case 1:
                                styleCountAll = ' .group_process_countAll';
                                styleAllPercent = ' .group_process_allPercent';
                                styleCountPeriod = ' .group_process_countPeriod';
                                stylePeriodPercent = ' .group_process_periodPercent';
                                break;

                            case 2:
                                styleCountAll = ' .group_uncertain_countAll';
                                styleAllPercent = ' .group_uncertain_allPercent';
                                styleCountPeriod = ' .group_uncertain_countPeriod';
                                stylePeriodPercent = ' .group_uncertain_periodPercent';
                                break;

                            case 3:
                                styleCountAll = ' .group_refuseniks_countAll';
                                styleAllPercent = ' .group_refuseniks_allPercent';
                                styleCountPeriod = ' .group_refuseniks_countPeriod';
                                stylePeriodPercent = ' .group_refuseniks_periodPercent';
                                break;

                            case 4:
                                styleCountAll = ' .group_bad_countAll';
                                styleAllPercent = ' .group_bad_allPercent';
                                styleCountPeriod = ' .group_bad_countPeriod';
                                stylePeriodPercent = ' .group_bad_periodPercent';
                                break;

                            case 5:
                                styleCountAll = ' .group_close_deal_countAll';
                                styleAllPercent = ' .group_close_deal_allPercent';
                                styleCountPeriod = ' .group_close_deal_countPeriod';
                                stylePeriodPercent = ' .group_close_deal_periodPercent';
                                break;
                        }

                        sphere.find('.groups-statuses' + styleCountAll).text( statusData['countAll'] );
                        sphere.find('.groups-statuses' + styleAllPercent).text( statusData['percentAll']+'%' );
                        sphere.find('.groups-statuses' + styleCountPeriod).text( statusData['countPeriod'] );
                        sphere.find('.groups-statuses' + stylePeriodPercent).text( statusData['percentPeriod']+'%' );

                    }

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
            if( statistic['statistic']['transitions'].length == 0){
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
            $.each( statistic['statistic']['transitions'], function( transitionKey, transitionData ){

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


                tr.bind('click', transitionDetailAction);

                // добавление классов
                tr.attr('data-transitId', transitionData.transitionId);
                tr.addClass('transitionDetail');

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
                allPercent.text( transitionData['percentAll'] + '%' );
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


        /**
         * Загрузка данных по статистике с сервера (по заданным параметрам)
         *
         */
        function loadStatistic() {

            // выбираем данные селекта сфер (id сферы)
            var sphereSelect = $('#sphere_select').val();

            // заносим параметры
            var params ={ _token: '{{ csrf_token() }}', agent_id: '{{ $user->id }}', timeFrom: dataStart, timeTo: dataEnd, sphere_id: sphereSelect } ;

            // отправка запроса на сервер
            $.post(
                '{{ route('admin.statistic.agentData') }}',
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


        /**
         * Данные по транзиту
         *
         */
        function transitionDetailAction(){

            var self = this;

            var transitId = $(this).data('transitid');

            // отправка запроса на сервер
            $.post(
                '{{ route('admin.statistic.transition.details') }}',
                { _token: '{{ csrf_token() }}', transitId: transitId, userId: '{{ $statistic['user']['id'] }}' },
                function (data)
                {

                    $('.modal_status_from').text( $(self).find('.fromStatus').text() );
                    $('.modal_status_to').text( $(self).find('.toStatus').text() );
                    $('.modal_current_user_name').text('{{ $statistic['user']['email'] }}');
                    $('.modal_current_user_percent').text( $(self).find('.allPercent').text() );
                    $('.modal_overall_percent').text( data['overallPercent'] + '%' );

                    var tbody = $('.modal_leads_table tbody');

                    tbody.empty();

                    if( data.leads.length == 0 ){

                        var tr = $('<tr/>');

                        var row = $('<td/>');

                        row.attr('colspan', 3);

                        row.addClass('center');

                        row.text('No leads');

                        tr.append(row);

                        tbody.append(tr);


                    }else{

                        $.each(data.leads, function(key, lead){
//                            console.log(user);

                            var tr = $('<tr/>');

                            var name = $('<td/>');
                            var email = $('<td/>');
                            var phone = $('<td/>');

                            name.addClass('');
                            email.addClass('center');
                            phone.addClass('center');

                            name.text( lead['name'] );
                            email.text( lead['email'] );
                            phone.text( lead['phone']['phone'] );

                            tr.append(name);
                            tr.append(phone);
                            tr.append(email);

                            tbody.append(tr);
                        });

                    }

                    $('#transitionDetailModal').modal('show');
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

            // вызов модального окна по смене прав пользователя
            $('.permissions_change').bind('click', function(){

                // блок с данными прав на странице
                var permissions_group = $('ul.permissions_group li');

                // очищаем переменную с правами
                agentPermissions = [];

                // блок с данными прав в модальном окне
                var modalPermissions = $('ul.modal_permissions_group');

                // очищаем блок с правами в модальном окне
                modalPermissions.empty();

                // перебираем все права из блока прав на фронтенде
                $.each(permissions_group, function( key, permission){

                    // сохраняем разрешение в переменной с разрешениями
                    agentPermissions.push(
                        {
                            status: $(permission).data('status'),
                            name: $(permission).data('permission'),
                            title: $(permission).text().trim()
                        }
                    );


                    /** Отрисовываем разрешения в блоке прав модального окна */

                    // создание основного узла
                    var li = $('<li />');
                    // создание узла лейбла
                    var label = $('<label />');
                    // создание узла инпута
                    var input = $('<input />');

                    // добавление узлу li основных классов
                    li.addClass('list-group-item togglebutton');

                    li.attr('data-name', $(permission).data('permission'));

                    // добавление узлу input тип
                    input.attr('type', 'checkbox');

                    // проверка на статус у права
                    if( $(permission).data('status')){
                        // если статус положительный добавляем атрибут checked
                        input.attr('checked', 'checked');
                    }

                    // добавляем input в лейбл
                    label.append(input);
                    // добавляем в дейбыл имя права
                    label.html( label.html() + $(permission).text().trim() );

                    // добавляем лейбл в узел li
                    li.append(label);
                    // добавляем li в блок прав модального окна
                    modalPermissions.append(li);
                });

                // инициализация материалайз (чтобы прорисовались инпуты)
                $.material.init();

                // показываем модальное окно
                $('#permissionModal').modal('show');
            });


            /**
             * Обработка нажатия на кнопку сохранения пермиссий
             *
             *
             * получение данных по правам из блока прав модального окна
             * оформление данных в json
             * отправка на сервер
             * при успешном ответе - обновить данные в блоке прав пользователя
             *
             */
            $('.modal_permissions_update').bind('click', function(){

                // скрытие модального окна
                $('#permissionModal').modal('hide');


                /** Собираем данные с блока прав модального окна и оформляем в json */

                // блок с данными прав в модальном окне
                var modalPermissions = $('ul.modal_permissions_group li');

                // переменная с правами пользователя
                var permissions = {};

                // наполняем переменную с правами
                $.each(modalPermissions, function( key, data ){

                    // переменная с данными
                    var permission = $(data);

                    // собираем данные в объект с данными
                    permissions[key] =
                    {
                        status: permission.find('input:checked').length == 1 ? 'true':'false',
                        name: permission.data('name')
                    };

                });


                /** Отправка данных на сервер */

                // получаем параметры
                var params ={ _token: '{{ csrf_token() }}', agent_id: '{{ $user->id }}', permissions: permissions };

                // отправка запроса на сервер
                $.post(
                    '{{ route('admin.agent.permissions.update') }}',
                    params,
                    function(data){
                        // проверяем статус
                        if( data.status == true ){
                            // если статусы изменены нормально

                            /** Обновляем права на странице */

                            // выбираем блок с правами пользователя на странице
                            var permissionBlock = $('.permissions_group');

                            // очищаем блок
                            permissionBlock.empty();

                            // перебираем все права которые пришли с сервера
                            $.each(data.permissions, function( key, permission ){

                                /** Создание узлов */

                                // узел li
                                var li = $('<li />');
                                // узел иконки i
                                var i = $('<i />');


                                /** Приведение нужных данных */

                                // преобразовываем bulean статус в строку
                                var status = permission.status ? 'true':'false';


                                /** Добавление атрибутов data-* */

                                // атрибут со статусом в li
                                li.attr('data-status', status);
                                // атрибут с разрешением в li
                                li.attr('data-permission', permission.name);
                                // атрибут с разрешением в i
                                i.attr('data-permission', permission.name);


                                /** Добавление классов */

                                // класс li
                                li.addClass('list-group-item');

                                // класс иконки
                                if( permission.status ){
                                    // если статус true
                                    i.addClass('glyphicon glyphicon-ok icon_green user_permission');

                                }else{
                                    // если статус false
                                    i.addClass('glyphicon glyphicon-ban-circle icon_red user_permission');
                                }


                                /** Добавление узлов и данных */

                                // добавление i K li
                                li.append(i);
                                // добавление названия к узлу
                                li.html( li.html()+' ' + permission.title );
                                // добавляем li в конец блока с правами
                                permissionBlock.append(li);
                            });


                            /** Сообщение об успешном выполнении */

                            // сообщение пользователю что права успешно изменились
                            $.snackbar(
                                {
                                    content: "Permissions changed", // text of the snackbar
                                    style: "toast", // add a custom class to your snackbar
                                    timeout: 4000 // time in milliseconds after the snackbar autohides, 0 is disabled
                                }
                            );

                        }else{
                            // если изменить права не удалось

                            // ошибка при попытке изменить права
                            $.snackbar(
                                {
                                    content: "Permissions change error", // text of the snackbar
                                    style: "toast", // add a custom class to your snackbar
                                    timeout: 4000 // time in milliseconds after the snackbar autohides, 0 is disabled
                                }
                            );
                        }
                    }
                );
            });

            // инициализациям материалайз
            $.material.init();
        });

    </script>
@stop