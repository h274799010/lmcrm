@extends('admin.layouts.default')
{{-- Content --}}
@section('main')
    <div class="page-header">
        <h3>
            {{ trans('admin/wallet.transactions.title') }}
            <div class="pull-right flip">
                <a class="btn btn-primary btn-xs close_popup" href="{{ URL::previous() }}">
                    <span class="glyphicon glyphicon-backward"></span> {!! trans('admin/admin.back') !!}
                </a>
            </div>
        </h3>
    </div>

    <div class="col-md-12" id="content">

        <!-- история кошелька с возможностью добавления -->
        <div class="tab-pane active" id="transactions">

            <table class="table">

                <thead>
                <tr>
                    <th>{{ trans('admin/wallet.time') }}</th>
                    <th>{{ trans('admin/wallet.user') }}</th>
                    <th>{{ trans('admin/wallet.amount') }}</th>
                    <th>{{ trans('admin/wallet.after') }}</th>
                    <th>{{ trans('admin/wallet.wallet_type') }}</th>
                    <th>{{ trans('admin/wallet.type') }}</th>
                    <th>{{ trans('admin/wallet.transaction') }}</th>
                    <th>{{ trans('admin/wallet.initiator_user') }}</th>
                    <th>{{ trans('admin/wallet.status') }}</th>
                </tr>
                </thead>

                <tbody>

                @foreach( $allTransactions as $transaction )
                    @foreach( $transaction['details'] as $detail )

                        <tr class="@if( $detail['amount'] > 0 ) wallet_add @else wallet_decrease @endif">
                            <td>{{ $transaction['created_at'] }}</td>
                            <td> {{ $detail['user']['name'] }}</td>
                            <td> {{ $detail['amount'] }}</td>
                            <td>{{ $detail['after'] }}</td>
                            <td>{{ $detail['wallet_type'] }}</td>
                            <td>{{ $detail['type'] }}</td>
                            <td>{{ $transaction['id'] }}</td>
                            <td>{{ $transaction['initiator']['first_name'] }} {{ $transaction['initiator']['last_name'] }}</td>
                            <td>{{ $transaction['status'] }}</td>
                        </tr>
                    @endforeach
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

