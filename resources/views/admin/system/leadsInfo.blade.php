@extends('admin.layouts.default')
{{-- Content --}}
@section('main')
    <div class="page-header">
        <h3>
            Leads Info
            <div class="pull-right flip">
                <a class="btn btn-primary btn-xs close_popup" href="{{ URL::previous() }}">
                    <span class="glyphicon glyphicon-backward"></span> {!! trans('admin/admin.back') !!}
                </a>
            </div>
        </h3>
    </div>

    <div class="col-md-12" id="content">

        <div class="tab-pane active" id="leads">


            <table class="table table-bordered table-striped table-hover all_leads_info">

                <thead>
                <tr>
                    <th rowspan="2"> name </th>
                    <th colspan="2"> счетчик </th>
                    <th>затраты</th>

                    <th colspan="2">доход</th>

                    <th colspan="2">прибыль с продаж</th>

                    <th colspan="2">время завершения</th>
                    <th colspan="3">статус</th>

                    <th> </th>
                </tr>
                <tr>
                    <th>открытия</th>
                    <th>сделки</th>
                    <th>оператор</th>

                    <th>открытия</th>
                    <th>сделки</th>

                    <th>депозитор</th>
                    <th>система</th>

                    <th class="lead_expiry_time">lead</th>
                    <th>openLeads</th>

                    <th>лид</th>
                    <th>аукцион</th>
                    <th>оплата</th>

                    <th> </th>
                </tr>
                </thead>

                <tbody>

                @foreach( $leads as $lead )

                    <tr>
                        <td>{{ $lead['name'] }}</td>
                        <td class="center"> {{ $lead['opened'] }} / {{ $lead->sphere->openLead }}</td>

                        <td class="center"> {{ $lead->ClosingDealCount() }}</td>

                        <td style=" color:red; " > {{ $lead->operatorSpend() }} </td>
                        <td style=" color:green; " > {{ $lead->revenueForOpen()  }} </td>
                        <td style=" color:green; " > {{ $lead->revenueForClosingDeal()  }} </td>

                        <td> @if( $lead->depositorProfit()<0 ) {{ $lead->depositorProfit() }} wasted @else {{ $lead->depositorProfit() }} @endif </td>


                        <td> {{ $lead->systemProfit() }} </td>



                        <td class="data_time center"> @if( $lead['expiry_time'] =='0000-00-00 00:00:00') - @else {{ $lead['expiry_time'] }} @endif</td>
                        <td class="data_time center"> @if( $lead['open_lead_expired'] =='0000-00-00 00:00:00') - @else {{ $lead['open_lead_expired'] }} @endif</td>

                        <td class="center"> {{ $lead->statusName() }} </td>
                        <td class="center"> @if( $lead->auction_status < 2 ) - @else {{ $lead->auctionStatusName() }} @endif</td>
                        <td class="center"> @if( $lead->payment_status < 1 ) - @else {{ $lead->paymentStatusName() }} @endif</td>

                        <td>

                            <a href="{{ route('admin.system.lead', [$lead['id']])  }}">
                                <img class="_icon pull-left flip" src="/assets/web/icons/list-edit.png">
                            </a>

                        </td>
                    </tr>
                @endforeach

                </tbody>

            </table>

            <div class="text-center">
                {{ $leads->render() }}
            </div>

        </div>


    </div>
@stop

@section('styles')
    <style>

        #wallet{
            padding-top: 10px;
        }


        .agent_wallet{
            padding-bottom: 30px;
        }

        .type_buyed{
            display: inline-block;
            margin-right: 30px;
        }

        .type_earned{
            display: inline-block;
            margin-right: 30px;
        }

        .type_wasted{
            display: inline-block;
        }


        div.wallet_form_block label{
            width: 10px !important;

        }


        div.wallet_form_block{
            display: inline-block;
        }

        div.wallet_form_block.second{
            margin-left: 70px;
        }


        div.wallet_form_block input{
            width: 50px !important;
            background: white !important;
            color: black !important;
            border: none;
        }

        label.label_plus{
            color: green;
        }

        label.label_minus{
            color: red;
        }

        form input.submit_button{
            border: 1px solid grey;
            border-radius: 10px;
            background: #1A7970 !important;
            color: #fff !important;
        }


        .wallet_add{
            background: #A3D9A3;
            color: #2F642F;
        }

        .wallet_decrease{
            background: #E6B9C8;
            color: #833B53;
        }

        .all_leads_info tbody tr td{
            vertical-align: middle;
        }

        .all_leads_info{
            font-size: 13px;
        }

        .all_leads_info thead tr th{
            background: #63A4B8;
            color: white;
            vertical-align: middle;
            text-align: center;
        }

        .center{
            text-align: center;
        }

        .data_time{
            font-size: 10px;
        }

        .lead_expiry_time{
            width: 77px;
        }

    </style>
@stop



@section('scripts')
    <script>
    </script>
@stop

