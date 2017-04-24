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
            <li class="active">Agent: {{ $accManager->email }}</li>
        </ul>
    </div>

    {{--<div class="row">
        <div class="col-xs-12">
            <div class="coeff">
                Coefficient: <span>{{ number_format($profit, 2) }}</span>
            </div>
        </div>
    </div>--}}

    @foreach($spheres as $sphere)
        <div class="row">
            <div class="col-xs-12">
                <table class="table table-striped table-hover dataTable1" id="agentCreditReportsTable">
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
                    @foreach($sphere->profit['details'] as $detail)
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
                        <td @if(intval($sphere->profit['profit']['opened']))class="dirty" @endif >
                            {{ number_format($sphere->profit['profit']['opened'], 2) }}
                        </td>
                        <td class="dirty">{{ number_format($sphere->profit['profit']['deals']['total'], 2) }}</td>
                        <td class="profit">{{ number_format($sphere->profit['profit']['deals']['our'], 2) }}</td>
                        <td class="dirty">{{ number_format($sphere->profit['profit']['auction']['leads'], 2) }}</td>
                        <td class="profit">{{ number_format($sphere->profit['profit']['auction']['deals'], 2) }}</td>
                        <td class="dirty">{{ number_format($sphere->profit['profit']['auction']['total'], 2) }}</td>
                        <td class="loss">{{ number_format($sphere->profit['profit']['operator'], 2) }}</td>
                        <td class="profit">{{ number_format($sphere->profit['profit']['profit']['leads'], 2) }}</td>
                        <td class="profit">{{ number_format($sphere->profit['profit']['profit']['deals'], 2) }}</td>
                        <td class="profit">{{ number_format($sphere->profit['profit']['profit']['total'], 2) }}</td>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12">
                <div class="pull-right">
                    <table class="table table-striped table-hover total-table">
                        <tbody>
                        <tr>
                            <th>Profit</th>
                            <td class="profit">{{ number_format($sphere->profit['profit']['profit']['total'], 2) }}</td>
                        </tr>
                        <tr>
                            <th>Deposited leads</th>
                            <td class="loss">{{ $sphere->profit['leads'] }}</td>
                        </tr>
                        <tr>
                            <th>Profitability</th>
                            <td class="profit">{{ number_format( $sphere->profit['deposited'], 2) }}</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xs-12">
                <table class="table table-striped table-hover dataTable1" id="agentCreditReportsTable">
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
                    @foreach($sphere->profit['bayed'] as $detail)
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
                        <td @if(intval($sphere->profit['profit_bayed']['opened']))class="dirty" @endif >
                            {{ number_format($sphere->profit['profit_bayed']['opened'], 2) }}
                        </td>
                        <td class="dirty">{{ number_format($sphere->profit['profit_bayed']['deals']['total'], 2) }}</td>
                        <td class="profit">{{ number_format($sphere->profit['profit_bayed']['deals']['our'], 2) }}</td>
                        <td class="dirty">{{ number_format($sphere->profit['profit_bayed']['auction']['leads'], 2) }}</td>
                        <td class="profit">{{ number_format($sphere->profit['profit_bayed']['auction']['deals'], 2) }}</td>
                        <td class="dirty">{{ number_format($sphere->profit['profit_bayed']['auction']['total'], 2) }}</td>
                        <td class="loss">{{ number_format($sphere->profit['profit_bayed']['operator'], 2) }}</td>
                        <td class="profit">{{ number_format($sphere->profit['profit_bayed']['profit']['leads'], 2) }}</td>
                        <td class="profit">{{ number_format($sphere->profit['profit_bayed']['profit']['deals'], 2) }}</td>
                        <td class="profit">{{ number_format($sphere->profit['profit_bayed']['profit']['total'], 2) }}</td>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12">
                <div class="pull-right">
                    <table class="table table-striped table-hover total-table">
                        <tbody>
                        <tr>
                            <th>Profit</th>
                            <td class="profit">{{ number_format($sphere->profit['profit_bayed']['profit']['total'], 2) }}</td>
                        </tr>
                        <tr>
                            <th>Exposion leads</th>
                            <td class="loss">{{ $sphere->profit['openLeads'] }}</td>
                        </tr>
                        <tr>
                            <th>Profitability</th>
                            <td class="profit">{{ number_format( $sphere->profit['exposition'], 2) }}</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endforeach

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
    </style>
@endsection

@section('scripts')
    <script type="text/javascript">
    </script>
@endsection