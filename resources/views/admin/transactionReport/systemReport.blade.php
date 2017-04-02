@extends('admin.layouts.default')

{{-- Web site Title --}}
@section('title') {!! trans("admin/agent.agents") !!} :: @parent
@stop

{{-- Content --}}
@section('main')

    <div class="row">
        <div class="col-md-12">
            <div class="breadcrumb-wrapper">
                <ul class="breadcrumb">
                    <li><a href="/">LM CRM</a></li>
                    <li class="active">System</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="row">
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


            <div class="row user_manager_block">
                <div class="col-md-12">
                    <table class="table table-striped table-bordered user_leads_info">

                        <thead>
                        <tr class="user_leads_info_head">
                            <th colspan="10">Request payments</th>
                        </tr>
                        <tr>
                            <th colspan="2" class="center middle">Withdrawal</th>

                            <th colspan="2" class="center middle">Replenishment</th>

                            <th colspan="2" class="center middle">Confirmed</th>

                            <th colspan="2" class="center middle">Rejected</th>
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
                        </tr>
                        </thead>
                        <tbody>

                        <tr id="reportsWrapper">

                            <td class="center middle summary_table_added_all"> {{ $statistic['replenishment']['all'] }} </td>
                            <td class="center middle summary_table_added_period"> {{ $statistic['replenishment']['period'] }} </td>

                            <td class="center middle summary_table_operator_bad_all"> {{ $statistic['withdrawal']['all'] }}</td>
                            <td class="center middle summary_table_operator_bad_period"> {{ $statistic['withdrawal']['period'] }}</td>

                            <td class="center middle summary_table_users_bad_all"> {{ $statistic['confirmed']['all'] }}</td>
                            <td class="center middle summary_table_users_bad_period"> {{ $statistic['confirmed']['period'] }}</td>

                            <td class="center middle summary_table_seen_all"> {{ $statistic['rejected']['all'] }} </td>
                            <td class="center middle summary_table_seen_period"> {{ $statistic['rejected']['period'] }} </td>

                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <h4>Request payment list</h4>

                    <table class="table table-bordered table-striped table-hover table-requests" id="openLeadsTable">
                        <thead>
                        <tr>
                            <th>Amount</th>
                            <th>Initiator</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Created</th>
                            {{--<th>Actions</th>--}}
                        </tr>
                        </thead>
                        <tbody>
                        @if(count($reports) > 0)
                            @foreach($reports as $report)
                                <tr>
                                    <td>{{ $report->amount }}</td>
                                    <td>
                                        @if(isset($report->initiator))
                                            {{ $report->initiator->email }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td><span class="badge badge-type-{{ $report->type }}">{{ $types[ $report->type ] }}</span></td>
                                    <td>
                                        <span class="badge badge-status-{{ $report->status }}">
                                            {{ $statuses[ $report->status ] }}
                                        </span>
                                    </td>
                                    <td>{{ $report->created_at->format('d/m/Y H:i') }}</td>
                                    {{--<td>
                                        <a href="{{ route('admin.credits.detail', [ 'id'=>$report->id ]) }}" class="btn btn-default">Detail</a>
                                    </td>--}}
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="5">Empty agent request payment list</td>
                            </tr>
                        @endif
                        </tbody>
                    </table>
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
        .table-requests .badge-type-{{ \App\Models\RequestPayment::TYPE_REPLENISHMENT }} {background-color: #dff0d8;color: #3c763d;}
        .table-requests .badge-type-{{ \App\Models\RequestPayment::TYPE_WITHDRAWAL }} {background-color: #d9edf7;color: #31708f;}
        .table-requests .badge-status-{{ \App\Models\RequestPayment::STATUS_WAITING_PROCESSING }},
        .table-requests .badge-status-{{ \App\Models\RequestPayment::STATUS_WAITING_CONFIRMED }},
        .table-requests .badge-status-{{ \App\Models\RequestPayment::STATUS_WAITING_PAYMENT }} {background-color: #fcf8e3;color: #8a6d3b;}
        .table-requests .badge-status-{{ \App\Models\RequestPayment::STATUS_CONFIRMED }} {background-color: #dff0d8;color: #3c763d;}
        .table-requests .badge-status-{{ \App\Models\RequestPayment::STATUS_REJECTED }} {background-color: #f2dede;color: #a94442;}
        .table-requests td:first-child {font-weight: bold;}
    </style>
@stop

{{-- Scripts --}}
@section('scripts')
    <script type="text/javascript">
        function reportsUpdate(statistic) {
            var html = '';

            html += '<td class="center middle summary_table_added_all">'+statistic.replenishment.all+'</td>';
            html += '<td class="center middle summary_table_added_period">'+statistic.replenishment.period+'</td>';

            html += '<td class="center middle summary_table_operator_bad_all">'+statistic.withdrawal.all+'</td>';
            html += '<td class="center middle summary_table_operator_bad_period">'+statistic.withdrawal.period+'</td>';

            html += '<td class="center middle summary_table_users_bad_all">'+statistic.confirmed.all+'</td>';
            html += '<td class="center middle summary_table_users_bad_period">'+statistic.confirmed.period+'</td>';

            html += '<td class="center middle summary_table_seen_all">'+statistic.rejected.all+'</td>';
            html += '<td class="center middle summary_table_seen_period">'+statistic.rejected.period+'</td>';

            $('#reportsWrapper').html(html);
        }

        function loadReports() {
            // заносим параметры
            var params ={ _token: '{{ csrf_token() }}', timeFrom: dataStart, timeTo: dataEnd } ;

            // отправка запроса на сервер
            $.post(
                '{{ route('admin.report.system.data') }}',
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
                        reportsUpdate( data );

                        // todo удалить
//                        console.log( data );
                    }
                }
            );
        }

        $(function () {
            $(document).on('change', '#reportrange', function () {
                loadReports();
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