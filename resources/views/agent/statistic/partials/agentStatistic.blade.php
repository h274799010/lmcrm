@foreach($spheres as $sphere)
    <h4>{{ $sphere->name }}</h4>

    <div class="row table-statuses-row">
        <div class="table-statuses table-statuses-small">
            <table class="table table-bordered table-striped table-hover process-statuses">
                <thead>
                <tr>
                    <th colspan="4">@lang('statistic.process_statuses')</th>
                </tr>
                <tr>
                    <th>@lang('statistic.status')</th>
                    <th>@lang('statistic.count_leads')</th>
                    <th>@lang('statistic.percentage_total')</th>
                    <th>@lang('statistic.percentage_period')</th>
                </tr>
                </thead>
                <tbody>
                @foreach($sphere->statuses as $status)
                    @if($status->type == 1)
                        <tr>
                            <td>{{ $status->stepname }}</td>
                            <td class="percent-col">{{ $status->countOpenLeads }}</td>
                            <td class="percent-col">{{ $status->percentOpenLeads }}%</td>
                            <td class="percent-col">{{ $status->percentPeriodOpenLeads }}%</td>
                        </tr>
                    @endif
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="table-statuses table-statuses-small">
            <table class="table table-bordered table-striped table-hover undefined-statuses">
                <thead>
                <tr>
                    <th colspan="4">@lang('statistic.uncertain ')</th>
                </tr>
                <tr>
                    <th>@lang('statistic.status')</th>
                    <th>@lang('statistic.count_leads')</th>
                    <th>@lang('statistic.percentage_total')</th>
                    <th>@lang('statistic.percentage_period')</th>
                </tr>
                </thead>
                <tbody>
                @foreach($sphere->statuses as $status)
                    @if($status->type == 2)
                        <tr>
                            <td>{{ $status->stepname }}</td>
                            <td class="percent-col">{{ $status->countOpenLeads }}</td>
                            <td class="percent-col">{{ $status->percentOpenLeads }}%</td>
                            <td class="percent-col">{{ $status->percentPeriodOpenLeads }}%</td>
                        </tr>
                    @endif
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="table-statuses table-statuses-small">
            <table class="table table-bordered table-striped table-hover fail-statuses">
                <thead>
                <tr>
                    <th colspan="4">@lang('statistic.refuseniks')</th>
                </tr>
                <tr>
                    <th>@lang('statistic.status')</th>
                    <th>@lang('statistic.count_leads')</th>
                    <th>@lang('statistic.percentage_total')</th>
                    <th>@lang('statistic.percentage_period')</th>
                </tr>
                </thead>
                <tbody>
                @foreach($sphere->statuses as $status)
                    @if($status->type == 3)
                        <tr>
                            <td>{{ $status->stepname }}</td>
                            <td class="percent-col">{{ $status->countOpenLeads }}</td>
                            <td class="percent-col">{{ $status->percentOpenLeads }}%</td>
                            <td class="percent-col">{{ $status->percentPeriodOpenLeads }}%</td>
                        </tr>
                    @endif
                @endforeach
                </tbody>
            </table>
        </div>

        <div class="table-statuses table-statuses-small">
            <table class="table table-bordered table-striped table-hover bad-statuses">
                <thead>
                <tr>
                    <th colspan="4">@lang('statistic.bad')</th>
                </tr>
                <tr>
                    <th>@lang('statistic.status')</th>
                    <th>@lang('statistic.count_leads')</th>
                    <th>@lang('statistic.percentage_total')</th>
                    <th>@lang('statistic.percentage_period')</th>
                </tr>
                </thead>
                <tbody>
                @foreach($sphere->statuses as $status)
                    @if($status->type == 4)
                        <tr>
                            <td>{{ $status->stepname }}</td>
                            <td class="percent-col">{{ $status->countOpenLeads }}</td>
                            <td class="percent-col">{{ $status->percentOpenLeads }}%</td>
                            <td class="percent-col">{{ $status->percentPeriodOpenLeads }}%</td>
                        </tr>
                    @endif
                @endforeach
                </tbody>
            </table>
        </div>
        @if(isset($sphere->statusTransitions) && count($sphere->statusTransitions) > 0)
            <div class="table-statuses table-statuses-large">
                <table class="table table-bordered table-striped table-hover performance-table">
                    <thead>
                    <tr>
                        <th colspan="5">@lang('statistic.level_performance')</th>
                    </tr>
                    <tr>
                        <th>@lang('statistic.status_1')</th>
                        <th>@lang('statistic.status_2')</th>
                        <th>@lang('statistic.percentage_total')</th>
                        <th>@lang('statistic.percentage_period')</th>
                        <th>@lang('statistic.rating')</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($sphere->statusTransitions as $status)
                        <tr>
                            <td>
                                @if(isset($status->previewStatus->stepname))
                                    {{ $status->previewStatus->stepname }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if(isset($status->status->stepname))
                                    {{ $status->status->stepname }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="percent-col">{{ $status->percent }}%</td>
                            <td class="percent-col">{{ $status->percentPeriod }}%</td>
                            <td class="status_{{ $status->rating }}">{{ $status->rating }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endforeach