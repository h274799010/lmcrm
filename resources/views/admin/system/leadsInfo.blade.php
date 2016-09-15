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


                <table class="table">

                    <thead>
                    <tr>
                        <th>имя</th>
                        <th>количество покупок</th>
                        <th>затраченно</th>
                        <th>полученно</th>
                        <th>прибыль агента</th>
                        <th>прибыль системы</th>
                        <th>время завершения</th>
                        <th>статус</th>
                        <th> </th>
                    </tr>
                    </thead>

                    <tbody>

                    @foreach( $leads as $lead )

                        @php( $spend = App\Helper\PayMaster::leadSpend( $lead['id'] ) )
                        @php( $received = App\Helper\PayMaster::leadReceived( $lead['id'] ) )
                        @php( $agentPayment = App\Helper\PayMaster::agentProfit( $lead['id'] ) )
                        @php( $systemPayment = $spend + $received - $agentPayment )

                        <tr>
                            <td>{{ $lead['name'] }}</td>
                            <td> {{ $lead['opened'] }} / {{ $lead->sphere->openLead }}</td>
                            <td style=" color:red; " > {{ $spend  }} </td>
                            <td style=" color:green; " > {{ $received  }} </td>
                            <td> {{ $agentPayment  }} </td>
                            <td> {{ $systemPayment }} </td>
                            <td> @if( $lead['finished'] == 1) Завершен @elseif( $lead['expired'] == 1 ) Время вышло @else {{ $lead['expiry_time'] }} @endif</td>
                            <td> {{ $lead->statusName->name }} </td>
                            <td>

                                <a href="{{ route('admin.system.lead', [$lead['id']])  }}">
                                    <img class="_icon pull-left flip" src="/assets/web/icons/list-edit.png">
                                </a>

                            </td>
                        </tr>
                    @endforeach

                    </tbody>

                </table>

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


    </style>
@stop



@section('scripts')
    <script>
    </script>
@stop

