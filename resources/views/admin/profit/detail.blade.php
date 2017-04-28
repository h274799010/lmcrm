@extends('admin.layouts.default')

{{-- Web site Title --}}
@section('title') {!! trans("admin/agent.agents") !!} :: @parent
@stop

{{-- Content --}}
@section('main')
    <div class="breadcrumb-wrapper">
        <ul class="breadcrumb">
            <li><a href="/">LM CRM</a></li>
            <li><a href="{{ route('admin.profit.index') }}">Agents profit</a></li>
            <li class="active">Agent: {{ $agent->email }}</li>
        </ul>
    </div>

    <div class="row">
        <div class="col-md-8 col-xs-12" id="agentCreditReportsFilter">
            <div class="form-group">
                <label class="control-label _col-sm-2">{{ trans('admin/openLeads.filter.period') }}</label>
                <input type="text" name="reportrange" data-name="period" class="mdl-textfield__input filter" value="" id="reportrange" />
            </div>
            <div class="form-group form-group-select">
                <label class="control-label _col-sm-3">Spheres</label>
                <select data-name="sphere" class="selectbox form-control filter">
                    @foreach($spheres as $sphere)
                        <option value="{{ $sphere->id }}">{{ $sphere->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- Коеффициент агента --}}
    <div class="row">
        <div class="col-xs-12">
            <div class="coeff">
                Current coefficient: <span id="agentProfitability">{{ number_format($profit, 2) }}%</span><br>
                Period coefficient: <span id="agentProfitabilityPeriod">{{ number_format($profit_period, 2) }}%</span>
            </div>
        </div>
    </div>

    {{-- Таблица профитабильности по отданым лидам --}}
    <div class="row">
        <div class="col-xs-12">
            <table class="table table-striped table-hover dataTable1" id="profitDepositionTable">
                <thead>
                <tr>
                    <th class="center" rowspan="2">Deposition</th>
                    <th class="center" colspan="3">Share Revenue</th>
                    <th class="center" rowspan="2">Opened from auction</th>
                    <th class="center" colspan="2">Deals profit</th>
                    <th class="center" colspan="3">Profit From the auction</th>
                    <th class="center" rowspan="2">Operator Cost</th>
                    <th class="center" colspan="3">End Profit</th>
                </tr>
                <tr>
                    <th class="center">Profit From Deals</th>
                    <th class="center">Profit From Leads</th>
                    <th class="center">Profit From Dealmakers</th>

                    <th class="center">Total profit</th>
                    <th class="center">Our profit from the transaction</th>

                    <th class="center">Sum Lead Auction</th>
                    <th class="center">Deals</th>
                    <th class="center">Sum Profit</th>

                    <th class="center">Leads profit</th>
                    <th class="center">Deal profit</th>
                    <th class="center">Summary profit</th>
                </tr>
                </thead>
                <tbody>
                @foreach($result['deposition'] as $detail)
                    <tr>
                        <td>{{ $detail['type'] }}</td>
                        <td>{{ $detail['revenue_share']['from_deals'] }}%</td>
                        <td>{{ $detail['revenue_share']['from_leads'] }}%</td>
                        <td>{{ $detail['revenue_share']['from_dealmaker'] }}@if(intval($detail['revenue_share']['from_dealmaker']))%@endif</td>
                        <td>
                            <table class="opened-table">
                                @foreach($detail['opened'] as $key => $value)
                                    <tr>
                                        <td>{{ $key + 1 }}:</td>
                                        <td @if(intval($value))class="dirty" @endif >
                                            @if(intval($value))
                                                {{ number_format($value, 2) }}
                                            @else
                                                {{ $value }}
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </table>
                        </td>
                        <td class="dirty">{{ number_format($detail['deals']['total'], 2) }}</td>
                        <td class="profit">{{ number_format($detail['deals']['our'], 2) }}</td>
                        <td class="dirty">{{ number_format($detail['auction']['leads'], 2) }}</td>
                        <td class="profit">{{ number_format($detail['auction']['deals'], 2) }}</td>
                        <td class="dirty">{{ number_format($detail['auction']['total'], 2) }}</td>
                        <td class="loss">{{ number_format($detail['operator'], 2) }}</td>
                        <td class="profit">{{ number_format($detail['profit']['leads'], 2) }}</td>
                        <td class="profit">{{ number_format($detail['profit']['deals'], 2) }}</td>
                        <td class="profit">{{ number_format($detail['profit']['total'], 2) }}</td>
                    </tr>
                @endforeach
                </tbody>
                <tfoot>
                <tr>
                    <td colspan="4" class="total">Total:</td>
                    <td @if(intval($result['deposition_total']['opened']))class="dirty" @endif >
                        {{ number_format($result['deposition_total']['opened'], 2) }}
                    </td>
                    <td class="dirty">{{ number_format($result['deposition_total']['deals']['total'], 2) }}</td>
                    <td class="profit">{{ number_format($result['deposition_total']['deals']['our'], 2) }}</td>
                    <td class="dirty">{{ number_format($result['deposition_total']['auction']['leads'], 2) }}</td>
                    <td class="profit">{{ number_format($result['deposition_total']['auction']['deals'], 2) }}</td>
                    <td class="dirty">{{ number_format($result['deposition_total']['auction']['total'], 2) }}</td>
                    <td class="loss">{{ number_format($result['deposition_total']['operator'], 2) }}</td>
                    <td class="profit">{{ number_format($result['deposition_total']['profit']['leads'], 2) }}</td>
                    <td class="profit">{{ number_format($result['deposition_total']['profit']['deals'], 2) }}</td>
                    <td class="profit">{{ number_format($result['deposition_total']['profit']['total'], 2) }}</td>
                </tr>
                </tfoot>
            </table>
        </div>
    </div>
    {{-- Общая информация по профитабильности отданых лидов --}}
    <div class="row">
        <div class="col-xs-12">
            <div class="pull-right">
                <table class="table table-striped table-hover total-table" id="profitDepositionTableTotal">
                    <tbody>
                    <tr>
                        <th>Profit</th>
                        <td class="profit profit_field">{{ number_format($result['deposition_total']['profit']['total'], 2) }}</td>
                    </tr>
                    <tr>
                        <th>Deposited leads</th>
                        <td class="loss deposited_field">{{ $result['leads'] }}</td>
                    </tr>
                    <tr>
                        <th>Profitability</th>
                        <td class="profit profitability_field">{{ number_format($result['deposition_total']['total'], 2) }}</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Таблица профитабильности по купленым лидам --}}
    <div class="row">
        <div class="col-xs-12">
            <table class="table table-striped table-hover dataTable1" id="profitExpositionTable">
                <thead>
                <tr>
                    <th class="center" rowspan="2">Exposion</th>
                    <th class="center" colspan="3">Share Revenue</th>
                    <th class="center" rowspan="2">Opened from auction</th>
                    <th class="center" colspan="2">Deals profit</th>
                    <th class="center" colspan="3">Profit From the auction</th>
                    <th class="center" rowspan="2">Operator Cost</th>
                    <th class="center" colspan="3">End Profit</th>
                </tr>
                <tr>
                    <th class="center">Profit From Deals</th>
                    <th class="center">Profit From Leads</th>
                    <th class="center">Profit From Dealmakers</th>

                    <th class="center">Total profit</th>
                    <th class="center">Our profit from the transaction</th>

                    <th class="center">Sum Lead Auction</th>
                    <th class="center">Deals</th>
                    <th class="center">Sum Profit</th>

                    <th class="center">Leads profit</th>
                    <th class="center">Deal profit</th>
                    <th class="center">Summary profit</th>
                </tr>
                </thead>
                <tbody>
                @foreach($result['exposition'] as $detail)
                    <tr>
                        <td>{{ $detail['type'] }}</td>
                        <td>{{ $detail['revenue_share']['from_deals'] }}%</td>
                        <td>{{ $detail['revenue_share']['from_leads'] }}%</td>
                        <td>{{ $detail['revenue_share']['from_dealmaker'] }}@if(intval($detail['revenue_share']['from_dealmaker']))%@endif</td>
                        <td>
                            <table class="opened-table">
                                @foreach($detail['opened'] as $key => $value)
                                    <tr>
                                        <td>{{ $key + 1 }}:</td>
                                        <td @if(intval($value))class="dirty" @endif >
                                            @if(intval($value))
                                                {{ number_format($value, 2) }}
                                            @else
                                                {{ $value }}
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </table>
                        </td>
                        <td class="dirty">{{ number_format($detail['deals']['total'], 2) }}</td>
                        <td class="profit">{{ number_format($detail['deals']['our'], 2) }}</td>
                        <td class="dirty">{{ number_format($detail['auction']['leads'], 2) }}</td>
                        <td class="profit">{{ number_format($detail['auction']['deals'], 2) }}</td>
                        <td class="dirty">{{ number_format($detail['auction']['total'], 2) }}</td>
                        <td class="loss">{{ number_format($detail['operator'], 2) }}</td>
                        <td class="profit">{{ number_format($detail['profit']['leads'], 2) }}</td>
                        <td class="profit">{{ number_format($detail['profit']['deals'], 2) }}</td>
                        <td class="profit">{{ number_format($detail['profit']['total'], 2) }}</td>
                    </tr>
                @endforeach
                </tbody>
                <tfoot>
                <tr>
                    <td colspan="4" class="total">Total:</td>
                    <td @if(intval($result['exposition_total']['opened']))class="dirty" @endif >
                        {{ number_format($result['exposition_total']['opened'], 2) }}
                    </td>
                    <td class="dirty">{{ number_format($result['exposition_total']['deals']['total'], 2) }}</td>
                    <td class="profit">{{ number_format($result['exposition_total']['deals']['our'], 2) }}</td>
                    <td class="dirty">{{ number_format($result['exposition_total']['auction']['leads'], 2) }}</td>
                    <td class="profit">{{ number_format($result['exposition_total']['auction']['deals'], 2) }}</td>
                    <td class="dirty">{{ number_format($result['exposition_total']['auction']['total'], 2) }}</td>
                    <td class="loss">{{ number_format($result['exposition_total']['operator'], 2) }}</td>
                    <td class="profit">{{ number_format($result['exposition_total']['profit']['leads'], 2) }}</td>
                    <td class="profit">{{ number_format($result['exposition_total']['profit']['deals'], 2) }}</td>
                    <td class="profit">{{ number_format($result['exposition_total']['profit']['total'], 2) }}</td>
                </tr>
                </tfoot>
            </table>
        </div>
    </div>
    {{-- Общая информация по профитабильности купленых лидов --}}
    <div class="row">
        <div class="col-xs-12">
            <div class="pull-right">
                <table class="table table-striped table-hover total-table" id="profitExpositionTableTotal">
                    <tbody>
                    <tr>
                        <th>Profit</th>
                        <td class="profit profit_field">{{ number_format($result['exposition_total']['profit']['total'], 2) }}</td>
                    </tr>
                    <tr>
                        <th>Exposion leads</th>
                        <td class="loss deposited_field">{{ $result['openLeads'] }}</td>
                    </tr>
                    <tr>
                        <th>Profitability</th>
                        <td class="profit profitability_field">{{ number_format($result['exposition_total']['total'], 2) }}</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@stop

@section('styles')
    <style type="text/css">
        table thead tr th, table.total-table th {
            background: #63A4B8 !important;
            color: #ffffff;
            border: 1px solid #ddd !important;
            vertical-align: middle !important;
        }
        table tr td {
            vertical-align: middle !important;
        }
        table tr td {
            text-align: center;
        }
        table > tbody > tr > td:first-child {
            text-align: left;
        }
        table table tr td {
            text-align: left;
        }
        .loss, .profit, .dirty, .total {
            font-weight: bold;
        }
        .total {
            text-align: right;
        }
        .loss {
            color: red;
        }
        .profit {
            color: green;
        }
        .dirty {
            color: orange;
        }
        .opened-table {
            width: 100%;
        }
        .opened-table td:first-child {
            width: 20%;
        }
        .opened-table td:last-child {
            font-weight: bold;
        }
        tfoot {
            background: rgba(255, 236, 53, 0.53);
        }
        .coeff {
            padding: 20px 0;
        }
        .coeff span {
            font-weight: bold;
        }
        .form-group {
            display: inline-block;
            vertical-align: middle;
            max-width: 300px;
            margin: 0 8px 0 0;
        }
        .form-group:last-child {
            margin-right: 0;
        }
        .form-group label {
            display: block;
            width: 100%;
            margin: 0 0 6px !important;
        }
        .form-group-select {
            min-width: 200px;
        }
    </style>
@endsection

@section('scripts')
    <script type="text/javascript">
        function number_format( number, decimals, dec_point, thousands_sep ) {
            var i, j, kw, kd, km;

            if( isNaN(decimals = Math.abs(decimals)) ){
                decimals = 2;
            }
            if( dec_point == undefined ){
                dec_point = ".";
            }
            if( thousands_sep == undefined ){
                thousands_sep = ",";
            }

            i = parseInt(number = (+number || 0).toFixed(decimals)) + "";

            if( (j = i.length) > 3 ){
                j = j % 3;
            } else{
                j = 0;
            }

            km = (j ? i.substr(0, j) + thousands_sep : "");
            kw = i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + thousands_sep);
            kd = (decimals ? dec_point + Math.abs(number - i).toFixed(decimals).replace(/-/, 0).slice(2) : "");


            return km + kw + kd;
        }

        function prepareHtmlTotal(total) {
            var openedClass = '';
            if(parseInt(total.opened) > 0) {
                openedClass = 'dirty';
            }

            var html = '<tr>';
                html += '<td colspan="4" class="total">Total:</td>';
                html += '<td class="'+openedClass+'">';
                html += number_format(total.opened);
                html += '</td>';
                html += '<td class="dirty">'+number_format(total.deals.total)+'</td>';
                html += '<td class="profit">'+number_format(total.deals.our)+'</td>';
                html += '<td class="dirty">'+number_format(total.auction.leads)+'</td>';
                html += '<td class="profit">'+number_format(total.auction.deals)+'</td>';
                html += '<td class="dirty">'+number_format(total.auction.total)+'</td>';
                html += '<td class="loss">'+number_format(total.operator)+'</td>';
                html += '<td class="profit">'+number_format(total.profit.leads)+'</td>';
                html += '<td class="profit">'+number_format(total.profit.deals)+'</td>';
                html += '<td class="profit">'+number_format(total.profit.total)+'</td>';
            html += '</tr>';

            return html;
        }

        function prepareHtmlInfo(data) {
            var html = '';

            $.each(data, function (i, detail) {
                html += '<tr>';

                var revenueFromDealmaker = number_format(detail.revenue_share.from_dealmaker);
                if(parseInt(detail.revenue_share.from_dealmaker) > 0) {
                    revenueFromDealmaker = number_format(detail.revenue_share.from_dealmaker)+'%';
                }

                html += '<td>'+detail.type+'</td>';
                html += '<td>'+number_format(detail.revenue_share.from_deals)+'%</td>';
                html += '<td>'+number_format(detail.revenue_share.from_leads)+'%</td>';
                html += '<td>'+revenueFromDealmaker+'</td>';
                html += '<td>';
                html += '<table class="opened-table">';

                $.each(detail.opened, function (key, value) {
                    html += '<tr>';
                    html += '<td>'+ (parseInt(key) + 1) +':</td>';
                    var openedClass = '';
                    if(parseInt(value) > 0) {
                        openedClass = 'dirty';
                    }
                    html += '<td class="'+openedClass+'">';

                    if(parseInt(value) > 0) {
                        html += number_format(value);
                    }
                    else {
                        html += value;
                    }
                    html += '</td>';
                    html += '</tr>';
                });

                html += '</table>';
                html += '</td>';
                html += '<td class="dirty">'+number_format(detail.deals.total)+'</td>';
                html += '<td class="profit">'+number_format(detail.deals.our)+'</td>';
                html += '<td class="dirty">'+number_format(detail.auction.leads)+'</td>';
                html += '<td class="profit">'+number_format(detail.auction.deals)+'</td>';
                html += '<td class="dirty">'+number_format(detail.auction.total)+'</td>';
                html += '<td class="loss">'+number_format(detail.operator)+'</td>';
                html += '<td class="profit">'+number_format(detail.profit.leads)+'</td>';
                html += '<td class="profit">'+number_format(detail.profit.deals)+'</td>';
                html += '<td class="profit">'+number_format(detail.profit.total)+'</td>';

                html += '</tr>';
            });

            return html;
        }

        $(document).ready(function () {
            $('select').select2();
            $(document).on('change', '.filter', function (e) {
                e.preventDefault();

                var $filters = $(document).find('.filter');
                var params = {};
                $filters.each(function (i, el) {
                    params[$(el).data('name')] = $(el).val();
                });

                $.get('{{ route('admin.profit.detail', ['id'=>$agent->id]) }}', params, function (data) {
                    $('#profitDepositionTable tfoot').html( prepareHtmlTotal(data.result.deposition_total) );
                    $('#profitExpositionTable tfoot').html( prepareHtmlTotal(data.result.exposition_total) );

                    $('#profitDepositionTable tbody').html( prepareHtmlInfo(data.result.deposition) );
                    $('#profitExpositionTable tbody').html( prepareHtmlInfo(data.result.exposition) );

                    $('#profitDepositionTableTotal .profit_field').html(number_format(data.result.deposition_total.profit.total));
                    $('#profitDepositionTableTotal .deposited_field').html(data.result.leads);
                    $('#profitDepositionTableTotal .profitability_field').html(number_format(data.result.deposition_total.total));

                    $('#profitExpositionTableTotal .profit_field').html(number_format(data.result.exposition_total.profit.total));
                    $('#profitExpositionTableTotal .deposited_field').html(data.result.openLeads);
                    $('#profitExpositionTableTotal .profitability_field').html(number_format(data.result.exposition_total.total));

                    $('#agentProfitability').html(number_format(data.profit)+'%');
                    $('#agentProfitabilityPeriod').html(number_format(data.profit_period)+'%');
                });
            });
        });
        $(function() {

            var start = moment().subtract(1,'months').startOf('month');
            var end = moment().subtract(1,'months').endOf('month');

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
@endsection